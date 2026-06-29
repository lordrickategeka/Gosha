<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="GarageHQ Marketplace - Quality auto parts from verified suppliers">
    <title>{{ $title ?? 'Marketplace' }} | GarageHQ</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-base-100">
    {{ $slot }}
</body>
</html>
