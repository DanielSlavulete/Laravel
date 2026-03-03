<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Servicio temporalmente no disponible</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background-color: #0f172a;
            color: #f1f5f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }

        .card {
            background-color: #1e293b;
            padding: 40px;
            border-radius: 12px;
            max-width: 500px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
        }

        h1 {
            margin-bottom: 20px;
            font-size: 22px;
        }

        p {
            color: #cbd5e1;
        }

        .badge {
            background: #ef4444;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 15px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="card">
    <div class="badge">Servicio temporalmente no disponible</div>

    <h1>No se puede establecer conexión con el sistema de datos</h1>

    <p>El panel no puede cargarse en este momento porque el servicio de base de datos no está disponible.</p>

    <p><strong>Si eres administrador:</strong></p>
    <p>1. Accede al panel del proveedor con las credenciales entregadas.</p>
    <p>2. Verifica que el proyecto o base de datos esté activo.</p>
    <p>3. Si está pausado, reactívalo.</p>
    <p>4. Espera 2-3 minutos y vuelve a recargar esta página.</p>

    <hr style="margin:20px 0; opacity:0.3;">

    <p>Si el problema persiste, consulta la documentación de la aplicación para obtener instrucciones detalladas.</p>

    <button onclick="location.reload()" style="margin-top:15px; padding:8px 16px; border:none; border-radius:6px; background:#3b82f6; color:white; cursor:pointer;">
        Reintentar
    </button>
</div>
</body>
</html>