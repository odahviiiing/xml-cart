Для запуска ввести команду `docker-compose up`

Данный скрипт будет доступен по адресу: http://xml-cart.local (можно изменить в .env - потребуется перезапуск контейнеров).

После того как контейнеры стартанут, чтобы открывался url http://xml-cart.local нужно к себе на машину в `/etc/host` добавить запись.
`172.19.0.3 xml-cart.local`

где `172.19.0.3` - адрес nginx контейнера, который можно узнать выполнив команду: 

`docker inspect xml-cart-nginx | grep "IPAddress"`

POST запросы работают на следующих маршрутах
http://xml-cart.local/add - добавление
http://xml-cart.local/remove - удаление
Тело запроса - Multipart Form.
