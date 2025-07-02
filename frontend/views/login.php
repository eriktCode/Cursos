<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .card {
            border-radius: 10px;
        }
        body {
            background-color: #f8f9fa;
        }
        .hidden {
            display: none;
        }
        .form-toggle {
            color: #0d6efd;
            cursor: pointer;
            text-decoration: underline;
        }
    </style>
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="card p-4 shadow" style="min-width: 320px;">
    <!-- Login Form (existing) -->
    <div id="loginContainer">
        <h2 class="text-center mb-4">Iniciar Sesión</h2>
        <form id="loginForm">
            <div class="mb-3">
                <label for="email" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" id="email" required />
            </div>
            <div class="mb-3">
                <label for="llave_secreta" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="llave_secreta" required />
            </div>
            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
            <p class="text-center mt-3">¿No tienes cuenta? <span class="form-toggle" onclick="toggleForm()">Regístrate aquí</span></p>
            <div id="loginMessage" class="mt-3 text-center"></div>
        </form>
    </div>

    <!-- Register Form (new) -->
    <div id="registerContainer" class="hidden">
        <h2 class="text-center mb-4">Registro</h2>
        <form id="registerForm">
            <div class="mb-3">
                <label for="regNombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="regNombre" required />
            </div>
            <div class="mb-3">
                <label for="regApellido" class="form-label">Apellido</label>
                <input type="text" class="form-control" id="regApellido" required />
            </div>
            <div class="mb-3">
                <label for="regEmail" class="form-label">Email</label>
                <input type="email" class="form-control" id="regEmail" required />
            </div>
            <div class="mb-3">
                <label for="regPassword" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="regPassword" minlength="8" required />
            </div>
            <button type="submit" class="btn btn-success w-100">Registrarse</button>
            <p class="text-center mt-3">¿Ya tienes cuenta? <span class="form-toggle" onclick="toggleForm()">Inicia sesión</span></p>
            <div id="registerMessage" class="mt-3 text-center"></div>
        </form>
    </div>
</div>

<script>
// Toggle between forms
function toggleForm() {
    document.getElementById('loginContainer').classList.toggle('hidden');
    document.getElementById('registerContainer').classList.toggle('hidden');
}

// Handle login form
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('llave_secreta').value.trim();
    const messageDiv = document.getElementById('loginMessage');
    messageDiv.innerHTML = '';

    try {
        const response = await fetch('/controllers/loginController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, llave_secreta: password })
        });

        // First check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error(text || 'Respuesta no válida del servidor');
        }

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Credenciales incorrectas');
        }

        // Successful login
        window.location.href = data.redirect || '/views/cursos.php';
        
    } catch (error) {
        console.error('Login error:', error);
        messageDiv.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
    }
});

// Handle registration form
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        nombre: document.getElementById('regNombre').value.trim(),
        apellido: document.getElementById('regApellido').value.trim(),
        email: document.getElementById('regEmail').value.trim(),
        llave_secreta: document.getElementById('regPassword').value.trim()
    };
    
    const messageDiv = document.getElementById('registerMessage');
    messageDiv.innerHTML = '';

    try {
        const response = await fetch('/controllers/clientesController.php/crearCLiente', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });

        // Check response type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error(text || 'Error en el registro');
        }

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Error en el registro');
        }

        // Registration successful
        messageDiv.innerHTML = '<div class="alert alert-success">¡Registro exitoso! Por favor inicia sesión</div>';
        setTimeout(() => toggleForm(), 1500);
        
    } catch (error) {
        console.error('Registration error:', error);
        messageDiv.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
    }
});
</script>

</body>
</html>