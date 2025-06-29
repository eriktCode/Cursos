<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="card p-4 shadow" style="min-width: 320px;">
    <h2 class="text-center mb-4">Iniciar Sesión</h2>

    <form id="loginForm">
        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico</label>
            <input type="email" class="form-control" id="email" placeholder="ejemplo@correo.com" required />
        </div>
        <div class="mb-3">
            <label for="llave_secreta" class="form-label">Contraseña (llave secreta)</label>
            <input type="password" class="form-control" id="llave_secreta" placeholder="Tu clave" required />
        </div>
        <button type="submit" class="btn btn-primary w-100">Ingresar</button>
        <div id="mensaje" class="mt-3 text-center"></div>
    </form>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const email = document.getElementById('email').value;
    const llave = document.getElementById('llave_secreta').value;

    try {
        const response = await fetch('/controllers/loginController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, llave_secreta: llave })
        });

        const data = await response.json();
        const mensajeDiv = document.getElementById('mensaje');

        if (response.ok) {
            console.log('Login exitoso:', data);
            // Usa la ruta proporcionada por el servidor o la ruta por defecto
            window.location.href = data.redirect || '/views/cursos.php';
        } else {
            console.error('Error en login:', data.error);
            mensajeDiv.innerHTML = `<span class="text-danger">${data.error}</span>`;
        }
    } catch (error) {
        console.error('Error de red:', error);
        mensajeDiv.innerHTML = `<span class="text-danger">Error de conexión</span>`;
    }
});
</script>

</body>
</html>
