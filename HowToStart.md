# Como iniciar o Econofertas

Este guia explica como executar os componentes do repositório em ambiente local. O backend Laravel fornece o site e a API; o aplicativo Flutter usa essa API.

## Requisitos

- PHP 8.2 ou superior, Composer e extensões PHP SQLite, Mbstring, OpenSSL e Fileinfo.
- Node.js e npm para compilar os recursos do site (Vite/Tailwind).
- Flutter SDK compatível com o Dart `^3.11.5` indicado em `mobile/econofertas_app/pubspec.yaml`.
- Para Android: Android Studio/SDK ou dispositivo Android configurado. Para iOS: macOS, Xcode e CocoaPods.

## 1. Preparar o Laravel

Execute na raiz do repositório:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

No Windows PowerShell, use `Copy-Item .env.example .env` no lugar de `cp`.

Configure o banco no `.env`. Para SQLite, crie o arquivo caso ainda não exista e defina `DB_CONNECTION=sqlite` e `DB_DATABASE=/caminho/absoluto/para/database/database.sqlite` (no Windows, use o caminho absoluto local). Depois prepare as tabelas e dados iniciais:

```bash
php artisan migrate --seed
```

O seeder configura dados iniciais e o acesso administrativo local documentado no README: `admin@econofertas.local` / `admin123`. Use credenciais próprias fora do ambiente local.

## 2. Iniciar site e API

Em um terminal, na raiz:

```bash
php artisan serve
```

O site estará em `http://127.0.0.1:8000`. Páginas principais: `/`, `/ofertas`, `/admin/entrar`. A API JSON fica sob `/api`, por exemplo `/api/supermercados` e `/api/ofertas`.

Para que o navegador carregue os estilos e scripts durante desenvolvimento, em outro terminal na raiz:

```bash
npm install
npm run dev
```

O Vite deve permanecer ativo enquanto você desenvolve. Para compilar os recursos para uso sem o servidor Vite:

```bash
npm run build
```

## 3. Atualizar as ofertas coletadas

O comando busca ofertas nos sites configurados para São Vicente e Pague Menos e grava/atualiza os resultados no banco. Com Laravel configurado, execute:

```bash
php artisan offers:sync
```

O agendamento do Laravel está configurado para executar diariamente às 06:00 (`routes/console.php`). Para manter o agendamento ativo em desenvolvimento, rode em outro terminal:

```bash
php artisan schedule:work
```

A coleta depende da disponibilidade e da estrutura HTML dos sites de origem.

## 4. Executar o aplicativo Flutter

Em outro terminal, entre no diretório do app:

```bash
cd mobile/econofertas_app
flutter pub get
flutter devices
```

Mantenha o Laravel iniciado. Informe ao app a URL base da API:

- Emulador Android: `http://10.0.2.2:8000/api` (o endereço especial do emulador para alcançar o computador host).
- Simulador iOS: `http://127.0.0.1:8000/api`.
- Celular físico: `http://IP-DO-COMPUTADOR-NA-REDE:8000/api`; inicie Laravel com `php artisan serve --host=0.0.0.0 --port=8000` e permita o acesso na rede local.

Exemplo Android:

```bash
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api
```

Exemplo iOS:

```bash
flutter run -d ios --dart-define=API_BASE_URL=http://127.0.0.1:8000/api
```

Se `API_BASE_URL` não for informado, o app usa valores locais padrão: `10.0.2.2` no Android e `127.0.0.1` no iOS.

## 5. Gerar builds mobile

APK Android de release:

```bash
cd mobile/econofertas_app
flutter build apk --release --dart-define=API_BASE_URL=https://seu-dominio.com/api
```

O arquivo é gerado em `build/app/outputs/flutter-apk/app-release.apk`. Para iOS, em um Mac com assinatura configurada:

```bash
flutter build ipa --release --dart-define=API_BASE_URL=https://seu-dominio.com/api
```

Use uma URL HTTPS acessível pelos usuários finais nos builds distribuídos; `10.0.2.2` e endereços locais servem apenas para desenvolvimento.

## 6. Testes e verificações

Na raiz do repositório:

```bash
php artisan test
```

No diretório do Flutter:

```bash
flutter test
flutter analyze
```

## Atalho para trabalhar no site

O `composer.json` oferece um comando que inicia Laravel, fila, logs e Vite em paralelo:

```bash
composer run dev
```

Prepare o `.env`, banco e dependências antes de usá-lo. Para desenvolver ou testar o app mobile, mantenha o Laravel acessível e execute `flutter run` com a URL da API apropriada ao dispositivo.
