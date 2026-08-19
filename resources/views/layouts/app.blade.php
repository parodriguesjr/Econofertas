<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#12643f">
    <title>@yield('title', 'Econofertas')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="{{ route('home') }}" aria-label="Econofertas - início">
                <span class="brand-mark">E</span><span>Econofertas</span>
            </a>
            <a class="admin-link" href="{{ route('login') }}">Área administrativa</a>
        </div>
    </header>
    <main>@yield('content')</main>
    <footer><div class="container">Econofertas · Seus folhetos em um só lugar</div></footer>
</body>
</html>
