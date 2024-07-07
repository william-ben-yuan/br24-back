# Getting started

Para iniciar o projeto crie o arquivo .env e depois rode o comando 

```
docker compose build
docker compose up -d
```

Entrar no container

```
docker exec -it db-br24-container bash
```

Instalar os pacotes

```
composer install
```

Criar as tabelas

```
php artisan migrate
```


### Creating test database

Para criar o banco de dados de teste do Laravel: 

```
docker exec -it db-br24-container mysql -u root -p

mysql> GRANT ALL PRIVILEGES ON `laravel_test`.* TO 'admin'@'%';
```