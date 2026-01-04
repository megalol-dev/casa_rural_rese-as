<?php
/**
 * Conexión a la base de datos SQLite
 * Archivo reutilizable para todo el proyecto
 */

try {
    // Ruta a la base de datos SQLite
    $dbPath = __DIR__ . '/database.db';

    // Crear conexión PDO
    $pdo = new PDO("sqlite:$dbPath");

    // Configuración recomendada
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("PRAGMA foreign_keys = ON");

} catch (PDOException $e) {
    // Error crítico: detenemos la app
    die("Error de conexión con la base de datos: " . $e->getMessage());
}
