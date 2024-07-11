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

## Generating token for the first time

Acessar o endpoint /api/auth/bitrix24 que irá fazer o cadastro da chave de acesso

## Creating test database

Para criar o banco de dados de teste do Laravel:

```
docker exec -it db-br24-container mysql -u root -p

GRANT ALL PRIVILEGES ON `laravel_test`.* TO 'admin'@'%';
```

## Observations

Poderia ter usado injeção do objeto Company direto no controller, mas como deixei flexível pra usar tanto a versão de banco local e da API Bitrix, decidi usar somente o id da empresa mesmo
Pode usar a variável de ambiente para trocar entre os repositórios
