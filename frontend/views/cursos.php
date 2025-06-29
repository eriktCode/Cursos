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
    <title>Listado de Cursos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .card-img-top {
            height: 180px;
            object-fit: cover;
        }
        .curso-card {
            transition: transform 0.3s;
        }
        .curso-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Plataforma de Cursos</a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3"><?= htmlspecialchars($usuario['nombre']) ?></span>
                <a href="/frontend/views/carrito.php" class="btn btn-outline-light me-2">
                    <i class="bi bi-cart"></i> Carrito
                </a>
                <button class="btn btn-outline-light" id="btnLogout">Cerrar sesión</button>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Listado de Cursos</h1>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalCrearCurso">
                <i class="bi bi-plus-circle"></i> Nuevo Curso
            </button>
        </div>

        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="inputBuscar" placeholder="Buscar cursos...">
                    <button class="btn btn-outline-secondary" type="button" id="btnBuscar">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group">
                    <button class="btn btn-outline-primary" id="btnTodos">Todos</button>
                    <button class="btn btn-outline-primary" id="btnMisCursos">Mis Cursos</button>
                </div>
            </div>
        </div>

        <!-- Listado de cursos -->
        <div class="row" id="contenedorCursos">
            <!-- Los cursos se cargarán aquí dinámicamente -->
            <div class="col-12 text-center my-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para crear curso -->
    <div class="modal fade" id="modalCrearCurso" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Nuevo Curso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCrearCurso">
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título</label>
                            <input type="text" class="form-control" id="titulo" required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="instructor" class="form-label">Instructor</label>
                            <input type="text" class="form-control" id="instructor">
                        </div>
                        <div class="mb-3">
                            <label for="imagen" class="form-label">URL de la imagen</label>
                            <input type="url" class="form-control" id="imagen" required>
                        </div>
                        <div class="mb-3">
                            <label for="precio" class="form-label">Precio ($)</label>
                            <input type="number" step="0.01" class="form-control" id="precio" required>
                        </div>
                        <input type="hidden" id="id_creador" value="<?= $usuario['id'] ?>">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnCrearCurso">Crear Curso</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar curso -->
    <div class="modal fade" id="modalEditarCurso" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Curso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarCurso">
                        <input type="hidden" id="edit_id">
                        <div class="mb-3">
                            <label for="edit_titulo" class="form-label">Título</label>
                            <input type="text" class="form-control" id="edit_titulo" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="edit_descripcion" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_instructor" class="form-label">Instructor</label>
                            <input type="text" class="form-control" id="edit_instructor">
                        </div>
                        <div class="mb-3">
                            <label for="edit_imagen" class="form-label">URL de la imagen</label>
                            <input type="url" class="form-control" id="edit_imagen" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_precio" class="form-label">Precio ($)</label>
                            <input type="number" step="0.01" class="form-control" id="edit_precio" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnActualizarCurso">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variables globales
        let cursos = [];
        let filtroActual = 'todos';

        // DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function() {
            cargarCursos();
            configurarEventos();
        });

        // Configurar eventos
        function configurarEventos() {
            // Botones de filtro
            document.getElementById('btnTodos').addEventListener('click', () => {
                filtroActual = 'todos';
                renderizarCursos();
            });

            document.getElementById('btnMisCursos').addEventListener('click', () => {
                filtroActual = 'mis-cursos';
                renderizarCursos();
            });

            // Buscador
            document.getElementById('btnBuscar').addEventListener('click', buscarCursos);
            document.getElementById('inputBuscar').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') buscarCursos();
            });

            // Crear curso
            document.getElementById('btnCrearCurso').addEventListener('click', crearCurso);

            // Editar curso
            document.getElementById('btnActualizarCurso').addEventListener('click', actualizarCurso);

            // Logout
            document.getElementById('btnLogout').addEventListener('click', function() {
                fetch('/controllers/logoutController.php', { method: 'POST' })
                    .then(() => window.location.href = '/frontend/views/login.php');
            });
        }

        // Cargar cursos desde la API
        function cargarCursos() {
            fetch('/controllers/cursosController.php/obtenerCursos')
                .then(response => response.json())
                .then(data => {
                    cursos = data;
                    renderizarCursos();
                })
                .catch(error => {
                    console.error('Error al cargar cursos:', error);
                    document.getElementById('contenedorCursos').innerHTML = `
                        <div class="col-12 text-center my-5">
                            <div class="alert alert-danger">Error al cargar los cursos. Intente nuevamente.</div>
                        </div>
                    `;
                });
        }

        // Renderizar cursos según filtro
        function renderizarCursos() {
            const contenedor = document.getElementById('contenedorCursos');
            
            // Filtrar cursos
            let cursosFiltrados = [...cursos];
            if (filtroActual === 'mis-cursos') {
                const idUsuario = <?= $usuario['id'] ?>;
                cursosFiltrados = cursos.filter(curso => curso.id_creador === idUsuario);
            }

            if (cursosFiltrados.length === 0) {
                contenedor.innerHTML = `
                    <div class="col-12 text-center my-5">
                        <div class="alert alert-info">No se encontraron cursos.</div>
                    </div>
                `;
                return;
            }

            // Generar HTML de los cursos
            let html = '';
            cursosFiltrados.forEach(curso => {
                const esCreador = curso.id_creador === <?= $usuario['id'] ?>;
                
                html += `
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 curso-card">
                            <img src="${curso.imagen}" class="card-img-top" alt="${curso.titulo}">
                            <div class="card-body">
                                <h5 class="card-title">${curso.titulo}</h5>
                                <p class="card-text text-muted">${curso.instructor}</p>
                                <p class="card-text">${curso.descripcion || 'Sin descripción'}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 text-primary">$${curso.precio.toFixed(2)}</span>
                                    ${esCreador ? `
                                        <div>
                                            <button class="btn btn-sm btn-outline-primary btn-editar" data-id="${curso.id}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="${curso.id}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <button class="btn btn-sm btn-success btn-agregar-carrito" data-id="${curso.id}">
                                                <i class="bi bi-cart-plus"></i>
                                            </button>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            contenedor.innerHTML = html;

            // Agregar eventos a los botones de editar/eliminar
            document.querySelectorAll('.btn-editar').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    abrirModalEdicion(id);
                });
            });

            document.querySelectorAll('.btn-eliminar').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    if (confirm('¿Está seguro que desea eliminar este curso?')) {
                        eliminarCurso(id);
                    }
                });
            });

            document.querySelectorAll('.btn-agregar-carrito').forEach(btn => {
                btn.addEventListener('click', function() {
                    const idCurso = this.getAttribute('data-id');
                    agregarAlCarrito(idCurso);
                });
            });
        }

        // Buscar cursos
        function buscarCursos() {
            const termino = document.getElementById('inputBuscar').value.toLowerCase();
            if (!termino) {
                renderizarCursos();
                return;
            }

            const cursosFiltrados = cursos.filter(curso => 
                curso.titulo.toLowerCase().includes(termino) || 
                (curso.descripcion && curso.descripcion.toLowerCase().includes(termino)) ||
                curso.instructor.toLowerCase().includes(termino)
            );

            if (cursosFiltrados.length === 0) {
                document.getElementById('contenedorCursos').innerHTML = `
                    <div class="col-12 text-center my-5">
                        <div class="alert alert-warning">No se encontraron cursos que coincidan con "${termino}"</div>
                    </div>
                `;
                return;
            }

            // Mostrar resultados (podría optimizarse reutilizando renderizarCursos)
            let html = '';
            cursosFiltrados.forEach(curso => {
                html += `
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 curso-card">
                            <img src="${curso.imagen}" class="card-img-top" alt="${curso.titulo}">
                            <div class="card-body">
                                <h5 class="card-title">${curso.titulo}</h5>
                                <p class="card-text text-muted">${curso.instructor}</p>
                                <p class="card-text">${curso.descripcion || 'Sin descripción'}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 text-primary">$${curso.precio.toFixed(2)}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            document.getElementById('contenedorCursos').innerHTML = html;
        }

        // Abrir modal de edición
        function abrirModalEdicion(id) {
            const curso = cursos.find(c => c.id == id);
            if (!curso) return;

            document.getElementById('edit_id').value = curso.id;
            document.getElementById('edit_titulo').value = curso.titulo;
            document.getElementById('edit_descripcion').value = curso.descripcion || '';
            document.getElementById('edit_instructor').value = curso.instructor || '';
            document.getElementById('edit_imagen').value = curso.imagen;
            document.getElementById('edit_precio').value = curso.precio;

            const modal = new bootstrap.Modal(document.getElementById('modalEditarCurso'));
            modal.show();
        }

        // Crear nuevo curso
        function crearCurso() {
            const curso = {
                titulo: document.getElementById('titulo').value,
                descripcion: document.getElementById('descripcion').value,
                instructor: document.getElementById('instructor').value,
                imagen: document.getElementById('imagen').value,
                precio: parseFloat(document.getElementById('precio').value),
                id_creador: parseInt(document.getElementById('id_creador').value)
            };

            fetch('/controllers/cursosController.php/crearCurso', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(curso)
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) throw new Error(data.error);
                
                // Cerrar modal y resetear formulario
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalCrearCurso'));
                modal.hide();
                document.getElementById('formCrearCurso').reset();
                
                // Recargar cursos
                cargarCursos();
                
                // Mostrar notificación
                alert('Curso creado exitosamente!');
            })
            .catch(error => {
                console.error('Error al crear curso:', error);
                alert('Error al crear el curso: ' + error.message);
            });
        }

        // Actualizar curso
        function actualizarCurso() {
            const curso = {
                id: document.getElementById('edit_id').value,
                titulo: document.getElementById('edit_titulo').value,
                descripcion: document.getElementById('edit_descripcion').value,
                instructor: document.getElementById('edit_instructor').value,
                imagen: document.getElementById('edit_imagen').value,
                precio: parseFloat(document.getElementById('edit_precio').value)
            };

            fetch('/controllers/cursosController.php/actualizarCurso', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(curso)
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) throw new Error(data.error);
                
                // Cerrar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarCurso'));
                modal.hide();
                
                // Recargar cursos
                cargarCursos();
                
                // Mostrar notificación
                alert('Curso actualizado exitosamente!');
            })
            .catch(error => {
                console.error('Error al actualizar curso:', error);
                alert('Error al actualizar el curso: ' + error.message);
            });
        }

        // Eliminar curso
        function eliminarCurso(id) {
            fetch('/controllers/cursosController.php/eliminarCurso', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: id })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) throw new Error(data.error);
                
                // Recargar cursos
                cargarCursos();
                
                // Mostrar notificación
                alert('Curso eliminado exitosamente!');
            })
            .catch(error => {
                console.error('Error al eliminar curso:', error);
                alert('Error al eliminar el curso: ' + error.message);
            });
        }

        // Función para agregar curso al carrito
        function agregarAlCarrito(idCurso) {
            const datos = {
                email: '<?= $usuario["email"] ?>',
                id_curso: idCurso
            };

            fetch('/controllers/carritoController.php/agregarAlCarrito', {
                method: 'POST',
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
                    alert('Curso agregado al carrito exitosamente!');
                }
            })
            .catch(error => {
                console.error('Error al agregar al carrito:', error);
                alert('Error al agregar al carrito');
            });
        }
    </script>
</body>
</html>