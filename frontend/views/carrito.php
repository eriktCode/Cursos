<?php
session_start();

// Verificar autenticación
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
    <title>Carrito de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .curso-img {
            width: 100px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        .table-responsive {
            min-height: 300px;
        }
        .summary-card {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-cart"></i> Carrito de Compras</h1>
            <button class="btn btn-danger" id="btnVaciarCarrito">
                <i class="bi bi-trash"></i> Vaciar Carrito
            </button>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Curso</th>
                                        <th>Precio</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsCarrito">
                                    <!-- Los items del carrito se cargarán aquí -->
                                    <tr>
                                        <td colspan="3" class="text-center py-5">
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
            
            <div class="col-md-4">
                <!-- Agrega esto en la sección del resumen de compra (col-md-4) -->
                <div class="card summary-card sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Resumen de Compra</h5>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span id="subtotal">$0.00</span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span>Total:</span>
                            <span class="h5 text-primary" id="total">$0.00</span>
                        </div>

                        <!-- Formulario de método de pago -->
                        <form id="formPago">
                            <div class="mb-3">
                                <label class="form-label">Método de Pago</label>
                                <select class="form-select" id="metodoPago" required>
                                    <option value="" selected disabled>Seleccione un método</option>
                                    <option value="1">Tarjeta de Crédito</option>
                                    <option value="2">PayPal</option>
                                    <option value="3">Transferencia Bancaria</option>
                                </select>
                            </div>
                            
                            <div class="mb-3" id="detallesTarjeta" style="display: none;">
                                <div class="mb-2">
                                    <label class="form-label">Número de Tarjeta</label>
                                    <input type="text" class="form-control" id="numeroTarjeta" placeholder="1234 5678 9012 3456">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Fecha Exp.</label>
                                        <input type="text" class="form-control" id="fechaExpiracion" placeholder="MM/AA">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="cvv" placeholder="123">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100" id="btnPagar">
                                <i class="bi bi-credit-card"></i> Proceder al Pago
                            </button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <small class="text-muted">O continúa <a href="/frontend/views/cursos.php">explorando cursos</a></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            cargarCarrito();
            configurarEventos();
        });

        function configurarEventos() {
            // Logout
            document.getElementById('btnLogout').addEventListener('click', function() {
                fetch('/controllers/logoutController.php', { method: 'POST' })
                    .then(() => window.location.href = '/frontend/views/login.php');
            });

            // Vaciar carrito
            document.getElementById('btnVaciarCarrito').addEventListener('click', function() {
                if (confirm('¿Está seguro que desea vaciar su carrito?')) {
                    vaciarCarrito();
                }
            });
        }

        function cargarCarrito() {
            const email = '<?= $usuario["email"] ?>';
            
            // Usar parámetros en la URL en lugar de body para GET
            fetch(`/controllers/carritoController.php/obtenerCarrito?email=${encodeURIComponent(email)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    mostrarMensajeError(data.error);
                } else {
                    renderizarCarrito(data);
                    calcularTotales(data);
                }
            })
            .catch(error => {
                console.error('Error al cargar carrito:', error);
                mostrarMensajeError('Error al cargar el carrito');
            });
        }

        function renderizarCarrito(items) {
            const contenedor = document.getElementById('itemsCarrito');
            
            if (items.length === 0) {
                contenedor.innerHTML = `
                    <tr>
                        <td colspan="3" class="text-center py-5">
                            <div class="alert alert-info">Tu carrito está vacío</div>
                            <a href="/frontend/views/cursos.php" class="btn btn-primary mt-2">
                                <i class="bi bi-book"></i> Explorar Cursos
                            </a>
                        </td>
                    </tr>
                `;
                document.getElementById('btnVaciarCarrito').disabled = true;
                return;
            }

            let html = '';
            items.forEach(item => {
                const curso = item.cursos;
                html += `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="${curso.imagen || 'https://via.placeholder.com/100x60'}" 
                                    class="curso-img me-3" 
                                    alt="${curso.titulo}">
                                <div>
                                    <h6 class="mb-0">${curso.titulo}</h6>
                                    <small class="text-muted">${curso.instructor || 'Instructor no especificado'}</small>
                                </div>
                            </div>
                        </td>
                        <td class="align-middle">$${curso.precio.toFixed(2)}</td>
                        <td class="align-middle">
                            <button class="btn btn-sm btn-outline-danger btn-eliminar-item" 
                                    data-id="${curso.id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            contenedor.innerHTML = html;

            // Agregar eventos a los botones de eliminar
            document.querySelectorAll('.btn-eliminar-item').forEach(btn => {
                btn.addEventListener('click', function() {
                    const idCurso = this.getAttribute('data-id');
                    eliminarDelCarrito(idCurso);
                });
            });
        }

        function calcularTotales(items) {
            const subtotal = items.reduce((sum, item) => sum + item.cursos.precio, 0);
            document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
            document.getElementById('total').textContent = `$${subtotal.toFixed(2)}`;
        }

        function eliminarDelCarrito(idCurso) {
            const datos = {
                email: '<?= $usuario["email"] ?>',
                id_curso: idCurso
            };

            fetch('/controllers/carritoController.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(datos)
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    cargarCarrito();
                    alert('Curso eliminado del carrito');
                }
            })
            .catch(error => {
                console.error('Error al eliminar del carrito:', error);
                alert('Error al eliminar del carrito');
            });
        }

        function vaciarCarrito() {
            const datos = {
                email: '<?= $usuario["email"] ?>'
            };

            fetch('/controllers/carritoController.php/vaciarCarrito', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(datos)
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    cargarCarrito();
                    alert('Carrito vaciado correctamente');
                }
            })
            .catch(error => {
                console.error('Error al vaciar carrito:', error);
                alert('Error al vaciar el carrito');
            });
        }

        function mostrarMensajeError(mensaje) {
            const contenedor = document.getElementById('itemsCarrito');
            contenedor.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center py-5">
                        <div class="alert alert-danger">${mensaje}</div>
                    </td>
                </tr>
            `;
        }

        // Agrega esto al final del script
        document.getElementById('metodoPago').addEventListener('change', function() {
            const detallesTarjeta = document.getElementById('detallesTarjeta');
            detallesTarjeta.style.display = this.value === '1' ? 'block' : 'none';
        });

        document.getElementById('formPago').addEventListener('submit', function(e) {
            e.preventDefault();
            realizarCompra();
        });

        async function realizarCompra() {
            const btnPagar = document.getElementById('btnPagar');
            btnPagar.disabled = true;
            btnPagar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...';

            try {
                // Verificar método de pago seleccionado
                const metodoPago = document.getElementById('metodoPago');
                if (!metodoPago.value) {
                    throw new Error('Por favor selecciona un método de pago');
                }

                // Validar datos de tarjeta si es necesario
                if (metodoPago.value === '1') {
                    const numeroTarjeta = document.getElementById('numeroTarjeta').value;
                    const fechaExpiracion = document.getElementById('fechaExpiracion').value;
                    const cvv = document.getElementById('cvv').value;
                    
                    if (!numeroTarjeta || !fechaExpiracion || !cvv) {
                        throw new Error('Por favor completa todos los datos de la tarjeta');
                    }
                }

                // Obtener items del carrito
                const itemsResponse = await fetch(`/controllers/carritoController.php/obtenerCarrito?email=${encodeURIComponent('<?= $usuario["email"] ?>')}`);
                
                // Verificar si la respuesta es JSON
                const contentType = itemsResponse.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await itemsResponse.text();
                    throw new Error(text || 'Respuesta no JSON del servidor');
                }
                
                const items = await itemsResponse.json();
                
                if (items.error) {
                    throw new Error(items.error);
                }

                if (items.length === 0) {
                    throw new Error('No hay items en el carrito para comprar');
                }

                // Calcular total
                const total = items.reduce((sum, item) => sum + item.cursos.precio, 0);
                
                // Datos para la compra
                const datosCompra = {
                    email: '<?= $usuario["email"] ?>',
                    total: total,
                    id_metodo_pago: metodoPago.value,
                    detalles_pago: metodoPago.value === '1' ? {
                        numero_tarjeta: document.getElementById('numeroTarjeta').value,
                        fecha_expiracion: document.getElementById('fechaExpiracion').value,
                        cvv: document.getElementById('cvv').value
                    } : null
                };

                // Realizar compra
                const compraResponse = await fetch('/controllers/comprasController.php/crearCompra', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(datosCompra)
                });
                
                // Verificar si la respuesta es JSON
                const compraContentType = compraResponse.headers.get('content-type');
                if (!compraContentType || !compraContentType.includes('application/json')) {
                    const text = await compraResponse.text();
                    throw new Error(text || 'Respuesta no JSON del servidor');
                }
                
                const resultadoCompra = await compraResponse.json();
                
                if (resultadoCompra.error) {
                    throw new Error(resultadoCompra.error);
                }

                // Vaciar carrito después de comprar
                const vaciarResponse = await fetch('/controllers/carritoController.php/vaciarCarrito', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({email: '<?= $usuario["email"] ?>'})
                });
                
                // No necesitamos el resultado aquí, solo asegurarnos que no haya error crítico
                if (!vaciarResponse.ok) {
                    console.error('Error al vaciar carrito:', await vaciarResponse.text());
                }

                // Mostrar mensaje de éxito y redirigir
                alert('¡Compra realizada con éxito! Gracias por tu compra.');
                window.location.href = '/frontend/views/cursos.php';
                
            } catch (error) {
                console.error('Error en la compra:', error);
                alert('Error al procesar la compra: ' + error.message);
            } finally {
                btnPagar.disabled = false;
                btnPagar.innerHTML = '<i class="bi bi-credit-card"></i> Proceder al Pago';
            }
        }
    </script>
</body>
</html>