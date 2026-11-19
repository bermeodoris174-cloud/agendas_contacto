<?php
require_once __DIR__ . '/includes/autenticacion.php';
$usuario = exigir_sesion();

// Búsqueda y ordenamiento (llegan por la URL con GET)
$busqueda = trim((string)($_GET['q'] ?? ''));
$orden = (($_GET['orden'] ?? 'asc') === 'desc') ? 'desc' : 'asc';
$direccion = ($orden === 'desc') ? 'DESC' : 'ASC'; // solo toma uno de dos valores fijos

$sql = 'SELECT id, nombre, telefono, correo, ciudad FROM contactos WHERE id_usuario = ?';
$parametros = [$usuario['id']];

if ($busqueda !== '') {
    $patron = '%' . addcslashes($busqueda, '%_\\') . '%';
    $sql .= ' AND (nombre LIKE ? OR telefono LIKE ? OR correo LIKE ? OR ciudad LIKE ?)';
    array_push($parametros, $patron, $patron, $patron, $patron);
}

$sql .= " ORDER BY nombre $direccion";

$consulta = conexion() ->prepare($sql);
$consulta->execute($parametros);
$contactos = $consulta ->fetchAll();

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

<form method="get" class="row g-2 mb-3 algin-items-center">
  <div class="col-md-5">
    <input type="search" name="q" class="form-control"
           placeholder="Buscar por nombre, teléfono, correo o ciudad"
           value="<?= escapar($busqueda) ?>">
  </div>
  <div class="col-auto">
    <button class="btn btn-primary btn-sm">Buscar</button>
  </div>
  <div class="col-md-3">
    <select name="orden" class="form-select" onchange="this.form.submit()">
      <option value="asc"  <?= $orden === 'asc'  ? 'selected' : '' ?>>Ordenar de A a Z</option>
      <option value="desc" <?= $orden === 'desc' ? 'selected' : '' ?>>Ordenar de Z a A</option>
    </select>
  </div>
  <div class="col-auto">
    <a href="panel.php" class="btn btn-secondary btn-sm">Limpiar</a>
  </div>
</form>

<?php if (!$contactos): ?>
  <div class="alert alert-info">
    <?= $busqueda !== '' ? 'No se encontraron contactos con esa búsqueda.' : 'Aún no tienes contactos registrados.' ?>
  </div>
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