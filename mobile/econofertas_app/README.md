# Econofertas Android

Aplicativo Flutter para consultar supermercados e folhetos publicados pela API Laravel do Econofertas.

## Funcionalidades

- Pesquisa de supermercados
- Favoritos persistentes no aparelho
- Contagem e listagem de folhetos ativos
- Abertura de PDFs no leitor do Android
- Atualização por gesto
- Tratamento de carregamento, falha de conexão e listas vazias

## Desenvolvimento

O emulador Android acessa o computador host pelo endereço `10.0.2.2`:

```powershell
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api
```

## Build

```powershell
flutter build apk --release --dart-define=API_BASE_URL=https://seu-dominio.com/api
```

A saída fica em `build/app/outputs/flutter-apk/app-release.apk`.
