# Garagem da Barra

Sistema Laravel para cadastro de veículos antigos, histórico de manutenções, documentos, galerias e controle financeiro.

## Requisitos

- PHP 8.2 ou superior
- MySQL 8 ou MariaDB compatível
- Composer 2
- Node.js 20 ou superior (somente para compilar os arquivos de interface)

## Ambiente local

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan serve
```

## Publicação na Hostinger

O Git deve publicar a branch `main` na raiz `public_html`. O `.htaccess` da raiz encaminha as requisições para `public/`, que é a pasta web segura do Laravel. Os arquivos compilados de `public/build` são versionados para que o servidor não precise executar Node.js.

Depois do primeiro deploy, acesse o servidor por SSH, entre na pasta do site e execute:

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Antes de executar as migrações, edite o `.env` do servidor com `APP_ENV=production`, `APP_DEBUG=false`, a URL HTTPS do site e as credenciais do banco MySQL criado no hPanel. O `.env` nunca deve ser enviado ao Git.

Em novas publicações, normalmente basta executar:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
```

Se houver mudanças em CSS ou JavaScript, compile localmente com `npm run build` e inclua a nova pasta `public/build` no commit.

## Validação

```bash
php artisan test
npm run build
```
