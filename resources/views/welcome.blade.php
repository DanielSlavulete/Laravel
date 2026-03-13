<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inclusión Madrid21</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f8fafc;
            color: #1f2937;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card {
            background: white;
            max-width: 600px;
            width: 100%;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .logo {
            max-width: 140px;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 16px;
            color: #111827;
        }

        p {
            font-size: 1rem;
            line-height: 1.6;
            color: #4b5563;
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            background: #f59e0b;
            color: white;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 10px;
            font-weight: bold;
            transition: 0.2s ease;
        }

        .btn:hover {
            background: #d97706;
        }

        .small-text {
            margin-top: 20px;
            font-size: 0.9rem;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="card">
        {{-- Si tienes logo, descomenta esta línea y ajusta la ruta --}}
        {{-- <img src="{{ asset('images/logo.png') }}" alt="Logo Inclusión Madrid21" class="logo"> --}}

        <h1>Bienvenido a Inclusión Madrid21</h1>

        <p>
            Acceso al panel de administración de la aplicación.
            Desde aquí los administradores pueden iniciar sesión y gestionar solicitudes, socios y cuotas.
        </p>

        <a href="{{ url('/admin') }}" class="btn">Iniciar sesión</a>

        <div class="small-text">
            Acceso exclusivo para administradores.
        </div>
    </div>
</body>
</html>