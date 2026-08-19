<!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Entrar - Econofertas</title><link rel="stylesheet" href="{{ asset('css/app.css') }}"></head>
<body class="login-page">
<main class="login-panel">
    <a class="brand" href="{{ route('home') }}"><span class="brand-mark">E</span><span>Econofertas</span></a>
    <div><p class="eyebrow">Área administrativa</p><h1>Acesse o painel</h1><p class="muted">Gerencie supermercados e publique novos folhetos.</p></div>
    <form method="POST" action="{{ route('login.store') }}" class="form-stack">@csrf
        <label>E-mail<input type="email" name="email" value="{{ old('email') }}" required autofocus>@error('email')<small class="field-error">{{ $message }}</small>@enderror</label>
        <label>Senha<input type="password" name="password" required></label>
        <label class="check"><input type="checkbox" name="remember" value="1"> Manter conectado</label>
        <button class="button full">Entrar</button>
    </form>
    <a class="back-link dark" href="{{ route('home') }}">← Voltar para o site</a>
</main>
</body></html>
