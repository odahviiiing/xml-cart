ARG nginx_version=1.15
FROM nginx:${nginx_version}

LABEL maintainer="Ryan Gellis <ryan.gellis@rmgmedia.com>"

ARG nginx_port=80
ENV NGINX_PORT=${nginx_port}

ARG nginx_vhost_name="_"
ENV NGINX_VHOST_NAME=${nginx_vhost_name}

COPY xml-cart.conf.tpl /etc/nginx/conf.d/

COPY ./docker-entrypoint.sh /usr/local/bin
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE ${nginx_port}

ENTRYPOINT ["docker-entrypoint.sh"]
