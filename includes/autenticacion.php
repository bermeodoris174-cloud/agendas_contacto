<?php
require_once __DIR__ . '/../config/conexion.php';

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

/** Escapa un texto para mostrarlo en HTML (previene XSS). */
function escapar(?string $valor): string
{
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

function redirigir(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function mensaje_flash(string $tipo, string $texto): void
{
    $_SESSION['mensaje'] = ['tipo' => $tipo, 'texto' => $texto];
}

function token_csrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verificar_csrf(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(400);
        exit('Solicitud no válida.');
    }
}

/** Verifica sesión activa; si no, redirige al inicio de sesión. Devuelve el usuario. */
function exigir_sesion(): array
{
    if (empty($_SESSION['id_usuario'])) {
        redirigir('iniciar_sesion.php');
    }
    $consulta = conexion()->prepare('SELECT id, nombre, correo FROM usuarios WHERE id = ?');
    $consulta->execute([$_SESSION['id_usuario']]);
    $usuario = $consulta->fetch();
    if (!$usuario) { // el usuario ya no existe
        session_destroy();
        redirigir('iniciar_sesion.php');
    }
    return $usuario;
}

/** Valida los datos de un contacto. Devuelve [datos limpios, errores]. */
function validar_contacto(array $entrada): array
{
    $datos = [
        'nombre'   => trim((string)($entrada['nombre'] ?? '')),
        'telefono' => trim((string)($entrada['telefono'] ?? '')),
        'correo'   => trim((string)($entrada['correo'] ?? '')),
        'ciudad'   => trim((string)($entrada['ciudad'] ?? '')),
    ];
    $errores = [];
    if (mb_strlen($datos['nombre']) < 2 || mb_strlen($datos['nombre']) > 100) {
        $errores[] = 'El nombre es obligatorio (2 a 100 caracteres).';
    }
    if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $datos['telefono'])) {
        $errores[] = 'El teléfono no es válido (7 a 20 dígitos; se permiten + - ( ) y espacios).';
    }
    if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL) || mb_strlen($datos['correo']) > 150) {
        $errores[] = 'El correo electrónico del contacto no es válido.';
    }
    if (mb_strlen($datos['ciudad']) < 2 || mb_strlen($datos['ciudad']) > 100) {
        $errores[] = 'La ciudad es obligatoria (2 a 100 caracteres).';
    }
    return [$datos, $errores];
}
