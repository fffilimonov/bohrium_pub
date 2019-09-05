#!/bin/bash

date
localSLEEP=0;

if [ "$SLEEP" != "" ]; then
  localSLEEP=$SLEEP;
fi;

/etc/init.d/selenium start;

RES="";
i=0;
while true; do
  RES=$(timeout 3 redis-cli -h redis-scens PING);
  echo $RES;
  if [ "$RES" == "PONG" ]; then
     break;
  fi;
  let i=$i+1;
  if [ $i -gt 15 ]; then
     exit 1;
  fi;
  sleep 1;
done;

echo $USER_ID $SCEN_ID $RES_ID $Browser;
mkdir /opt/codeception/tests/acceptance;
echo "Feature: main" > /opt/codeception/tests/acceptance/main.feature;
echo "Scenario: main" >> /opt/codeception/tests/acceptance/main.feature;
redis-cli -h redis-scens -n 1 hget "$USER_ID" "$SCEN_ID" >> /opt/codeception/tests/acceptance/main.feature;

export SID=$RES_ID;

startTime=$(date +%s);
echo -n "{\"Started\": true}" | redis-cli -h redis-scens -x setex $SID 3600

nowStr=$(redis-cli -h redis-scens -n 3 hget "$USER_ID" "$SID")
echo -n "$nowStr, {\"StartTime\": $startTime}, {\"Name\": \"$SCEN_ID\"}, {\"Browser\": \"$Browser\"}" | redis-cli -h redis-scens -n 3 -x hset "$USER_ID" $SID

timeout 20 grep -m 1 "Selenium Server is up and running" <(tail -f /var/log/selenium_output.log);

cd /opt/codeception;
vendor/bin/codecept run acceptance -vvv;
exit_behat=$?;
echo -n ",{\"Done\": $exit_behat}" | redis-cli -h redis-scens -x append $SID

sleep $localSLEEP&
NODE_PID=$!
wait $NODE_PID
stopTime=$(date +%s);
let timeDiff=$startTime-$stopTime;
redis-cli -h redis-scens -n 2 hincrby "$USER_ID" "Seconds" "$timeDiff";

nowStr=$(redis-cli -h redis-scens -n 3 hget "$USER_ID" "$SID")
echo -n "$nowStr, {\"Elapsed\": $timeDiff}, {\"Done\": $exit_behat}" | redis-cli -h redis-scens -n 3 -x hset "$USER_ID" $SID

date
