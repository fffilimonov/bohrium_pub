# Firefox Selenium
echo -e 'I open "https://google.com"\nI fill 1st input with "my browser info"\nI will see "What'\''s My Browser"\nI click on the text "What'\''s My Browser"\n' | redis-cli -h redis-scens -n 1 -x hset "test" "1"

docker run --shm-size=256m --rm -e USER_ID=test -e SCEN_ID=1 -e RES_ID=2 -e Browser=chrome --name test --add-host=redis-scens:192.168.20.76 fffilimonov/bohrium

docker build -t fffilimonov/bohrium .;docker push fffilimonov/bohrium;hyper pull fffilimonov/bohrium;hyper images
