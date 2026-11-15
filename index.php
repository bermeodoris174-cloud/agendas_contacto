<?php
require_once __DIR__ . '/includes/autenticacion.php';
redirigir(empty($_SESSION['id_usuario']) ? 'iniciar_sesion.php' : 'panel.php');
