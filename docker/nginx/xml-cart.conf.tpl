upstream fastcgi_backend {
  server php-fpm:9000;
}

server {
  listen ${NGINX_PORT};
  server_name ${NGINX_VHOST_NAME};
  root /var/www/xml-cart;

  index index.php;

  charset utf-8;

  location / {
    try_files $uri $uri/ /index.php?$query_string;
  }

  location ~ \.php$ {
    fastcgi_pass   fastcgi_backend;
    fastcgi_buffers 4 32k;
    fastcgi_buffer_size 32k;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
  }

  location ~ /\.(?!well-known).* {
    deny all;
  }
}
