<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: /frontend/views/login.php');
    exit();
}

$usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .curso-img {
            width: 80px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Plataforma de Cursos</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3"><?= htmlspecialchars($usuario['nombre']) ?></span>
                <a href="/frontend/views/cursos.php" class="btn btn-outline-light me-2">
                    <i class="bi bi-book"></i> Cursos
                </a>
                <button class="btn btn-outline-light" id="btnLogout">Cerrar sesión</button>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h1 class="mb-4"><i class="bi bi-receipt"></i> Mis Compras</h1>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Curso</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Método</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="listaCompras">
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            cargarCompras();
            
            document.getElementById('btnLogout').addEventListener('click', function() {
                fetch('/controllers/logoutController.php', { method: 'POST' })
                    .then(() => window.location.href = '/frontend/views/login.php');
            });
        });

        function cargarCompras() {
            fetch(`/controllers/comprarController.php?email=${encodeURIComponent('<?= $usuario["email"] ?>')}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    mostrarError(data.error);
                } else {
                    renderizarCompras(data);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarError('Error al cargar las compras');
            });
        }

        function renderizarCompras(compras) {
            const contenedor = document.getElementById('listaCompras');
            
            if (compras.length === 0) {
                contenedor.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="alert alert-info">No has realizado ninguna compra aún</div>
                            <a href="/frontend/views/cursos.php" class="btn btn-primary">
                                <i class="bi bi-book"></i> Explorar Cursos
                            </a>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            compras.forEach(compra => {
                const fecha = new Date(compra.fecha_compra).toLocaleDateString();
                const metodoPago = obtenerMetodoPago(compra.id_metodo_pago);
                
                html += `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="${compra.cursos.imagen || 'https://via.placeholder.com/80x50'}" 
                                    class="curso-img me-3" 
                                    alt="${compra.cursos.titulo}">
                                <div>
                                    <h6 class="mb-0">${compra.cursos.titulo}</h6>
                                    <small class="text-muted">${compra.cursos.instructor || 'Instructor no especificado'}</small>
                                </div>
                            </div>
                        </td>
                        <td>${fecha}</td>
                        <td>$${compra.total.toFixed(2)}</td>
                        <td>${metodoPago}</td>
                        <td>
                            <a href="/frontend/views/curso.php?id=${compra.id_curso}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Ver Curso
                            </a>
                        </td>
                    </tr>
                `;
            });

            contenedor.innerHTML = html;
        }

        function obtenerMetodoPago(id) {
            const metodos = {
                1: 'Tarjeta de Crédito',
                2: 'PayPal',
                3: 'Transferencia Bancaria'
            };
            return metodos[id] || 'Desconocido';
        }

        function mostrarError(mensaje) {
            const contenedor = document.getElementById('listaCompras');
            contenedor.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-5">
                        <div class="alert alert-danger">${mensaje}</div>
                    </td>
                </tr>
            `;
        }
    </script>
</body>
</html>