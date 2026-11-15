<?php /* Requiere $titulo y (opcional) $usuario */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= escapar($titulo ?? 'Agenda de contactos') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/estilos.css" rel="stylesheet">
</head>
<body class="bg-light d-flex-column min-vh-100">
<nav class="navbar navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand" href="index.php">Agenda de contactos</a>
    <?php if (!empty($usuario)): ?>
      <a class="btn btn-outline-light btn-sm" href="cerrar_sesion.php">Cerrar sesión</a>
    <?php endif; ?>
  </div>
</nav>
<main class="container flex-grow-1">
<?php if (!empty($_SESSION['mensaje'])): $aviso = $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?>
  <div class="alert alert-<?= escapar($aviso['tipo']) ?>"><?= escapar($aviso['texto']) ?></div>
<?php endif; ?>
