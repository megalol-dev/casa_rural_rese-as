<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/db_connection.php';

function json_response(array $data, int $status = 200): void {
  http_response_code($status);
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

$errores = [];

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  json_response(['ok' => false, 'errores' => ['Método no permitido.']], 405);
}

// Recoger datos
$nombre = trim((string)($_POST['nombre'] ?? ''));
$apellidos = trim((string)($_POST['apellidos'] ?? ''));
$telefono = trim((string)($_POST['telefono'] ?? ''));
$nombre_usuario = trim((string)($_POST['nombre_usuario'] ?? ''));
$contrasena = (string)($_POST['contrasena'] ?? '');
$contrasena2 = (string)($_POST['contrasena2'] ?? '');

// Normalizar teléfono: quitamos espacios, guiones, paréntesis
$telefono = preg_replace('/[\s\-\(\)]/', '', $telefono) ?? $telefono;

// Regex nombre/apellidos (letras + espacios + ' -), mínimo 2
$reNombreApellidos = "/^[\p{L}][\p{L}\s'-]{1,}$/u";

// Validaciones servidor (las buenas)
if ($nombre === '' || !preg_match($reNombreApellidos, $nombre)) {
  $errores[] = "El nombre es obligatorio y no puede contener números (mín. 2 caracteres).";
}

if ($apellidos === '' || !preg_match($reNombreApellidos, $apellidos)) {
  $errores[] = "Los apellidos son obligatorios y no pueden contener números (mín. 2 caracteres).";
}

if ($nombre_usuario === '') {
  $errores[] = "El nombre de usuario es obligatorio.";
}

// Teléfono: + opcional y solo dígitos. 7-15 dígitos sin el +
if ($telefono === '') {
  $errores[] = "El teléfono es obligatorio.";
} else {
  $t = $telefono;
  if (str_starts_with($t, '+')) {
    $t = substr($t, 1);
  }
  if ($t === '' || !ctype_digit($t)) {
    $errores[] = "El teléfono solo puede contener números y el prefijo + al inicio.";
  } else {
    $len = strlen($t);
    if ($len < 7 || $len > 15) {
      $errores[] = "El teléfono debe tener entre 7 y 15 dígitos (sin contar el +).";
    }
  }
}

// Contraseña: mínimo 8 y confirmación
if (strlen($contrasena) < 8) {
  $errores[] = "La contraseña debe tener al menos 8 caracteres.";
}
if ($contrasena !== $contrasena2) {
  $errores[] = "Las contraseñas no coinciden.";
}

if (!empty($errores)) {
  json_response(['ok' => false, 'errores' => $errores], 422);
}

// Usuario único
$st = $pdo->prepare("SELECT id FROM usuarios WHERE nombre_usuario = :u LIMIT 1");
$st->execute([':u' => $nombre_usuario]);
if ($st->fetch()) {
  json_response(['ok' => false, 'errores' => ['Ese nombre de usuario ya existe. Prueba con otro.']], 409);
}

// Insertar
$hash = password_hash($contrasena, PASSWORD_DEFAULT);

$ins = $pdo->prepare("
  INSERT INTO usuarios (nombre, apellidos, telefono, nombre_usuario, contrasena_hash)
  VALUES (:n, :a, :t, :u, :h)
");
$ins->execute([
  ':n' => $nombre,
  ':a' => $apellidos,
  ':t' => $telefono,
  ':u' => $nombre_usuario,
  ':h' => $hash,
]);

json_response(['ok' => true, 'mensaje' => 'Registro completado. Ya puedes iniciar sesión.']);
