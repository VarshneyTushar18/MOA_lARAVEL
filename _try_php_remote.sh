#!/bin/bash
HOME_DIR="/home/sites/41b/b/ba690bc503"
for v in php81 php82 php83 php80 php74; do
  cat > "$HOME_DIR/.htaccess" <<HTA
#+PHPVersion
#=$v
AddHandler x-httpd-$v .php
#-PHPVersion
HTA
  sleep 2
  code=$(curl -s -o /tmp/out.txt -w '%{http_code}' "https://phi-ltbi-aiia.in/ok.php")
  body=$(head -c 80 /tmp/out.txt)
  echo "$v $code $body"
  if [ "$code" = "200" ]; then
    echo WORKING=$v
    break
  fi
done
