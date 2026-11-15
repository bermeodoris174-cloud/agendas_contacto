<?php
// Ajusta estas constantes a tu entorno (XAMPP/Laragon: root sin contraseña).
const BD_SERVIDOR = 'localhost';
const BD_NOMBRE   = 'agenda_contactos';
const BD_USUARIO  = 'root';
const BD_CLAVE    = '';

function conexion(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . BD_SERVIDOR . ';dbname=' . BD_NOMBRE . ';charset=utf8mb4',
                BD_USUARIO,
                BD_CLAVE,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('Error de conexión: ' . $e->getMessage());
            http_response_code(500);
            exit('No se pudo conectar con el sistema. Inténtalo más tarde.');
        }
    }
    return $pdo;
}
