<?php
require_once __DIR__ . '/includes/autenticacion.php';
$usuario = exigir_sesion();

$consulta = conexion()->prepare('SELECT id, nombre, telefono, correo, ciudad FROM contactos WHERE id_usuario = ? ORDER BY nombre');
$consulta->execute([$usuario['id']]);
$contactos = $consulta->fetchAll();

$titulo = 'Panel principal';
require __DIR__ . '/includes/encabezado.php';
?>
<div class="card shadow-sm mb-4">
  <div class="card-body">
    <h1 class="h4 mb-1">Bienvenido, <?= escapar($usuario['nombre']) ?></h1>
    <p class="mb-0 text-muted"><?= escapar($usuario['correo']) ?></p>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="h5 mb-0">Mis contactos (<?= count($contactos) ?>)</h2>
  <a href="formulario_contacto.php" class="btn btn-success">+ Nuevo contacto</a>
</div>

<?php if (!$contactos): ?>
  <div class="alert alert-info">Aún no tienes contactos registrados.</div>
<?php else: ?>
<div class="table-responsive">
  <table class="table table-striped bg-white align-middle">
    <thead><tr><th>Nombre</th><th>Teléfono</th><th>Correo</th><th>Ciudad</th><th class="text-end">Acciones</th></tr></thead>
    <tbody>
    <?php foreach ($contactos as $contacto): ?>
      <tr>
        <td><?= escapar($contacto['nombre']) ?></td>
        <td><?= escapar($contacto['telefono']) ?></td>
        <td><?= escapar($contacto['correo']) ?></td>
        <td><?= escapar($contacto['ciudad']) ?></td>
        <td class="text-end">
          <a class="btn btn-sm btn-outline-primary" href="formulario_contacto.php?id=<?= (int)$contacto['id'] ?>">Editar</a>
          <a class="btn btn-sm btn-outline-danger" href="eliminar_contacto.php?id=<?= (int)$contacto['id'] ?>">Eliminar</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
<?php require __DIR__ . '/includes/pie.php'; ?>
