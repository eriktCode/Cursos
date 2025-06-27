<!-- ********** CURSOS ********** -->
// Obtener cursos
http://localhost:8000/controllers/cursosController.php/obtenerCursos

// Obtener curso por email
http://localhost:8000/controllers/cursosController.php/buscarCursosPorEmail

{
  "email": "empresa@cursos.com"
}

// Actualizar curso
http://localhost:8000/controllers/cursosController.php/actualizarCurso

{
  "id": 12,
  "titulo": "Curso actualizado"
}

// Eliminar curso
http://localhost:8000/controllers/cursosController.php/eliminarCurso

{
  "id": 12
}

// Crear curso
http://localhost:8000/controllers/cursosController.php/crearCurso

{
  "titulo": "Curso de PHP desde cero",
  "descripcion": "Aprende lo básico de PHP",
  "instructor": "Mauricio",
  "imagen": "https://miweb.com/img/php1.png",
  "precio": 99.99,
  "id_creador": 1
}

<!-- ********** CLIENTES ********** -->
// Obtener clientes
http://localhost:8000/controllers/clientesController.php/obtenerClientes

// Eliminar cliente
http://localhost:8000/controllers/clientesController.php/eliminarCliente

{
  "id": 13
}

<!-- ********** CARRITO ********** -->
// Agregar al carrito
http://localhost:8000/controllers/carritoController.php/agregarAlCarrito

{
  "email": "empresa@cursos.com",
  "id_curso": 5
}

// Eliminar del carrito
http://localhost:8000/controllers/carritoController.php/eliminarDelCarrito

{
  "email": "empresa@cursos.com",
  "id_curso": 4
}

// Vaciar carrito
http://localhost:8000/controllers/carritoController.php/vaciarCarrito

{
  "email": "empresa@cursos.com"
}

// Obtener carrito
http://localhost:8000/controllers/carritoController.php/obtenerCarrito

{
  "email": "empresa@cursos.com"
}

<!-- ********** COMPRAS ********** -->
// Crear compra
http://localhost:8000/controllers/comprasController.php/crearCompra

{
  "email": "empresa@cursos.com",
  "id_curso": 10,
  "total": 299.99,
  "id_metodo_pago": 2,
  "detalles_pago": "Pago con tarjeta Visa"
}