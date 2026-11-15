<?php
require_once __DIR__ . '/includes/autenticacion.php';

if (!empty($_SESSION['id_usuario'])) redirigir('panel.php');

$error = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();
    $correo   = strtolower(trim((string)($_POST['correo'] ?? '')));
    $password = (string)($_POST['password'] ?? '');

    if ($correo === '' || $password === '') {
        $error = 'Ingresa tu correo y tu contraseña.';
    } else {
        $consulta = conexion()->prepare('SELECT id, password FROM usuarios WHERE correo = ?');
        $consulta->execute([$correo]);
        $usuario = $consulta->fetch();
        // Mensaje único: no revela si falló el correo o la contraseña.
        if ($usuario && password_verify($password, $usuario['password'])) {
            session_regenerate_id(true);
            $_SESSION['id_usuario'] = (int)$usuario['id'];
            redirigir('panel.php');
        }
        $error = 'Correo o contraseña incorrectos.';
    }
}

$titulo = 'Iniciar sesión';
require __DIR__ . '/includes/encabezado.php';
?>
<div class="card tarjeta-formulario shadow-sm">
  <div class="card-body">
    <h1 class="h4 mb-3">Iniciar sesión</h1>
    <?php if ($error): ?><div class="alert alert-danger py-2"><?= escapar($error) ?></div><?php endif; ?>
    <form method="post" novalidate>
      <input type="hidden" name="csrf" value="<?= escapar(token_csrf()) ?>">
      <div class="mb-3"><label class="form-label">Correo electrónico</label>
        <input type="email" class="form-control" name="correo" value="<?= escapar($correo) ?>" required></div>
      <div class="mb-3"><label class="form-label">Contraseña</label>
        <input type="password" class="form-control" name="password" required></div>
      <button class="btn btn-primary w-100">Entrar</button>
    </form>
    <p class="mt-3 mb-0 text-center">¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
  </div>
</div>
<?php require __DIR__ . '/includes/pie.php'; ?>
