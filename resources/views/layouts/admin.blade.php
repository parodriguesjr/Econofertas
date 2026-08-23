<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Painel') - Econofertas</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="admin-body">
<header class="admin-header">
    <a class="brand" href="{{ route('admin.dashboard') }}"><span class="brand-mark">E</span><span>Econofertas</span></a>
    <nav><a href="{{ route('admin.dashboard') }}">Visão geral</a><a href="{{ route('admin.supermarkets.index') }}">Supermercados</a><a href="{{ route('admin.flyers.index') }}">Folhetos</a></nav>
    <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="link-button">Sair</button></form>
</header>
<main class="admin-main">
    @if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert error">Revise os campos destacados e tente novamente.</div>@endif
    @yield('content')
</main>
</body>
</html>

