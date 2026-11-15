<?php
require_once __DIR__ . '/includes/autenticacion.php';
$usuario = exigir_sesion();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: null;
$datos = ['nombre' => '', 'telefono' => '', 'correo' => '', 'ciudad' => ''];
$errores = [];

if ($id) {
    // El filtro por id_usuario impide acceder a contactos ajenos cambiando la URL.
    $consulta = conexion()->prepare('SELECT nombre, telefono, correo, ciudad FROM contactos WHERE id = ? AND id_usuario = ?');
    $consulta->execute([$id, $usuario['id']]);
    $existente = $consulta->fetch();
    if (!$existente) {
        mensaje_flash('danger', 'El contacto solicitado no existe.');
        redirigir('panel.php');
    }
    $datos = $existente;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();
    [$datos, $errores] = validar_contacto($_POST);
    if (!$errores) {
        if ($id) {
            $consulta = conexion()->prepare('UPDATE contactos SET nombre=?, telefono=?, correo=?, ciudad=? WHERE id=? AND id_usuario=?');
            $consulta->execute([$datos['nombre'], $datos['telefono'], $datos['correo'], $datos['ciudad'], $id, $usuario['id']]);
            mensaje_flash('success', 'Contacto actualizado correctamente.');
        } else {
            $consulta = conexion()->prepare('INSERT INTO contactos (id_usuario, nombre, telefono, correo, ciudad) VALUES (?,?,?,?,?)');
            $consulta->execute([$usuario['id'], $datos['nombre'], $datos['telefono'], $datos['correo'], $datos['ciudad']]);
            mensaje_flash('success', 'Contacto registrado correctamente.');
        }
        redirigir('panel.php');
    }
}

$titulo = $id ? 'Editar contacto' : 'Nuevo contacto';
require __DIR__ . '/includes/encabezado.php';
?>
<div class="card tarjeta-formulario shadow-sm">
  <div class="card-body">
    <h1 class="h4 mb-3"><?= escapar($titulo) ?></h1>
    <?php foreach ($errores as $error): ?><div class="alert alert-danger py-2"><?= escapar($error) ?></div><?php endforeach; ?>
    <form method="post" novalidate>
      <input type="hidden" name="csrf" value="<?= escapar(token_csrf()) ?>">
      <?php if ($id): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>
      <div class="mb-3"><label class="form-label">Nombre</label>
        <input class="form-control" name="nombre" value="<?= escapar($datos['nombre']) ?>" required></div>
      <div class="mb-3"><label class="form-label">Teléfono</label>
        <input class="form-control" name="telefono" value="<?= escapar($datos['telefono']) ?>" required></div>
      <div class="mb-3"><label class="form-label">Correo electrónico</label>
        <input type="email" class="form-control" name="correo" value="<?= escapar($datos['correo']) ?>" required></div>
      <div class="mb-3"><label class="form-label">Ciudad</label>
        <input class="form-control" name="ciudad" value="<?= escapar($datos['ciudad']) ?>" required></div>
      <button class="btn btn-primary">Guardar</button>
      <a href="panel.php" class="btn btn-secondary">Cancelar</a>
    </form>
  </div>
</div>
<?php require __DIR__ . '/includes/pie.php'; ?>
