<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Woodkraft Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <div style="padding: 20px; text-align: center;">
            <p>Chargement de l'interface admin...</p>
            <p style="color: red; font-size: 12px;">Si cette page reste affichée, vérifiez la console du navigateur pour les erreurs.</p>
        </div>
    </div>
</body>
</html>

