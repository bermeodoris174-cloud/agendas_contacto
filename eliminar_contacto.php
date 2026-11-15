<?php
require_once __DIR__ . '/includes/autenticacion.php';
$usuario = exigir_sesion();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) redirigir('panel.php');

$consulta = conexion()->prepare('SELECT id, nombre, telefono, correo, ciudad FROM contactos WHERE id = ? AND id_usuario = ?');
$consulta->execute([$id, $usuario['id']]);
$contacto = $consulta->fetch();
if (!$contacto) {
    mensaje_flash('danger', 'El contacto solicitado no existe.');
    redirigir('panel.php');
}

// Solo se elimina por POST, tras la pantalla de confirmación.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();
    $borrar = conexion()->prepare('DELETE FROM contactos WHERE id = ? AND id_usuario = ?');
    $borrar->execute([$id, $usuario['id']]);
    mensaje_flash('success', 'Contacto eliminado correctamente.');
    redirigir('panel.php');
}

$titulo = 'Eliminar contacto';
require __DIR__ . '/includes/encabezado.php';
?>
<div class="card tarjeta-formulario shadow-sm border-danger">
  <div class="card-body">
    <h1 class="h4 text-danger">¿Eliminar este contacto?</h1>
    <p class="mb-1"><strong><?= escapar($contacto['nombre']) ?></strong></p>
    <p class="text-muted"><?= escapar($contacto['telefono']) ?> · <?= escapar($contacto['correo']) ?> · <?= escapar($contacto['ciudad']) ?></p>
    <p>Esta acción no se puede deshacer.</p>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= escapar(token_csrf()) ?>">
      <input type="hidden" name="id" value="<?= (int)$contacto['id'] ?>">
      <button class="btn btn-danger">Sí, eliminar</button>
      <a href="panel.php" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>
</div>
<?php require __DIR__ . '/includes/pie.php'; ?>
