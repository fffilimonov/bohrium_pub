hyper pull redis
hyper volume create --size 10 --name redis-scens
hyper run -v redis-scens:/data --name redis-scens --hostname redis-scens -d redis redis-server --appendonly yes

hyper pull fffilimonov/bohrium-firefox
hyper run --rm -e USER_ID=test -e SCEN_ID=foo --link redis-scens:redis-scens --name firefox fffilimonov/bohrium-firefox

hyper pull fffilimonov/apijwt
hyper run --size=s2 -p 5000 -d --hostname apijwt --name apijwt fffilimonov/apijwt

hyper pull fffilimonov/frontjwt
hyper run --size=s2 -p 80 -p 443 -d --name frontjwt fffilimonov/frontjwt
hyper fip attach 199.245.56.214 frontjwt
