# Econofertas

Aplicação Laravel para publicar e consultar folhetos de ofertas de supermercados.

## Recursos

- Vitrine pública responsiva para celulares e computadores
- Catálogo de supermercados e folhetos ativos
- Visualização protegida de arquivos PDF
- Painel administrativo com autenticação
- Cadastro de supermercados e upload de folhetos
- Controle de publicação e período de validade

## Requisitos

- PHP 8.2 ou superior
- Composer
- Extensões PHP SQLite, Mbstring, OpenSSL e Fileinfo

## Instalação

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

A aplicação ficará disponível em `http://127.0.0.1:8000`.

## Acesso administrativo local

- E-mail: `admin@econofertas.local`
- Senha: `admin123`

Altere as credenciais antes de colocar a aplicação em produção.

## Testes

```powershell
php artisan test
```

Arquivos `.env`, bancos SQLite, PDFs enviados, logs, dependências e outros dados locais não são versionados.
