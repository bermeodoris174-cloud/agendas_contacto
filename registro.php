<?php
require_once __DIR__ . '/includes/autenticacion.php';

if (!empty($_SESSION['id_usuario'])) redirigir('panel.php');

$errores = [];
$nombre = $correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();
    $nombre    = trim((string)($_POST['nombre'] ?? ''));
    $correo    = strtolower(trim((string)($_POST['correo'] ?? '')));
    $password  = (string)($_POST['password'] ?? '');
    $confirmar = (string)($_POST['confirmar'] ?? '');

    if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 100) $errores[] = 'El nombre completo es obligatorio (3 a 100 caracteres).';
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || mb_strlen($correo) > 150) $errores[] = 'Ingresa un correo electrónico válido.';
    if (strlen($password) < 8) $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    if ($password !== $confirmar) $errores[] = 'Las contraseñas no coinciden.';

    if (!$errores) {
        $consulta = conexion()->prepare('SELECT id FROM usuarios WHERE correo = ?');
        $consulta->execute([$correo]);
        if ($consulta->fetch()) {
            $errores[] = 'Ese correo electrónico ya está registrado.';
        } else {
            try {
                $insertar = conexion()->prepare('INSERT INTO usuarios (nombre, correo, password) VALUES (?, ?, ?)');
                $insertar->execute([$nombre, $correo, password_hash($password, PASSWORD_DEFAULT)]);
                mensaje_flash('success', 'Registro exitoso. Ya puedes iniciar sesión.');
                redirigir('iniciar_sesion.php');
            } catch (PDOException $e) {
                // 23000 = violación de UNIQUE (correo duplicado por concurrencia)
                $errores[] = $e->getCode() === '23000'
                    ? 'Ese correo electrónico ya está registrado.'
                    : 'No se pudo completar el registro. Inténtalo más tarde.';
                error_log($e->getMessage());
            }
        }
    }
}

$titulo = 'Registro';
require __DIR__ . '/includes/encabezado.php';
?>
<div class="card tarjeta-formulario shadow-sm">
  <div class="card-body">
    <h1 class="h4 mb-3">Crear cuenta</h1>
    <?php foreach ($errores as $error): ?><div class="alert alert-danger py-2"><?= escapar($error) ?></div><?php endforeach; ?>
    <form method="post" novalidate>
      <input type="hidden" name="csrf" value="<?= escapar(token_csrf()) ?>">
      <div class="mb-3"><label class="form-label">Nombre completo</label>
        <input class="form-control" name="nombre" value="<?= escapar($nombre) ?>" required></div>
      <div class="mb-3"><label class="form-label">Correo electrónico</label>
        <input type="email" class="form-control" name="correo" value="<?= escapar($correo) ?>" required></div>
      <div class="mb-3"><label class="form-label">Contraseña (mín. 8 caracteres)</label>
        <input type="password" class="form-control" name="password" required></div>
      <div class="mb-3"><label class="form-label">Confirmar contraseña</label>
        <input type="password" class="form-control" name="confirmar" required></div>
      <button class="btn btn-primary w-100">Registrarme</button>
    </form>
    <p class="mt-3 mb-0 text-center">¿Ya tienes cuenta? <a href="iniciar_sesion.php">Inicia sesión</a></p>
  </div>
</div>
<?php require __DIR__ . '/includes/pie.php'; ?>
