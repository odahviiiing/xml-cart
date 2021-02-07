#!/bin/bash
envsubst '\$NGINX_PORT, \$NGINX_VHOST_NAME' < /etc/nginx/conf.d/xml-cart.conf.tpl > /etc/nginx/conf.d/xml-cart.conf
rm /etc/nginx/conf.d/xml-cart.conf.tpl /etc/nginx/conf.d/default.conf
nginx -g "daemon off;"
