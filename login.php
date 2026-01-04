<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/db_connection.php';

$errores = [];
$usuario = '';

function e(string $v): string {
  return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Si ya está logueado, fuera
if (!empty($_SESSION['usuario'])) {
  header("Location: index.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $usuario = trim((string)($_POST['usuario'] ?? ''));
  $pass = (string)($_POST['contrasena'] ?? '');

  if ($usuario === '') $errores[] = "El usuario es obligatorio.";
  if ($pass === '') $errores[] = "La contraseña es obligatoria.";

  if (empty($errores)) {
    $st = $pdo->prepare("SELECT id, nombre, nombre_usuario, contrasena_hash FROM usuarios WHERE nombre_usuario = :u LIMIT 1");
    $st->execute([':u' => $usuario]);
    $row = $st->fetch();

    if (!$row || !password_verify($pass, (string)$row['contrasena_hash'])) {
      $errores[] = "Usuario o contraseña incorrectos.";
    } else {
      // Guardamos en sesión lo necesario
      $_SESSION['usuario'] = [
        'id' => (int)$row['id'],
        'nombre' => (string)$row['nombre'],
        'nombre_usuario' => (string)$row['nombre_usuario'],
      ];

      header("Location: index.php");
      exit;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Iniciar sesión - Casas Rurales Pepe</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

<header class="header">
  <div class="header__inner">
    <div class="brand">
      <span class="brand__logo">🏡</span>
      <div class="brand__text">
        <h1 class="brand__title">Casas Rurales Pepe</h1>
        <p class="brand__subtitle">Iniciar sesión</p>
      </div>
    </div>

    <nav class="nav">
      <a class="btn btn--ghost" href="index.php">Volver</a>
    </nav>
  </div>
</header>

<main class="main">
  <section class="hero">
    <h2 class="hero__title">Acceso</h2>
    <p class="hero__desc">Introduce tu usuario y contraseña.</p>
  </section>

  <section style="max-width:560px;">
    <?php if (!empty($errores)): ?>
      <div style="padding:14px;border:1px solid rgba(255,255,255,0.15);border-radius:14px;background:rgba(255,255,255,0.06);">
        <strong>Revisa esto:</strong>
        <ul>
          <?php foreach ($errores as $er): ?>
            <li><?= e($er) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div style="height:12px;"></div>
    <?php endif; ?>

    <form method="POST" style="padding:16px;border:1px solid rgba(255,255,255,0.10);border-radius:18px;background:rgba(255,255,255,0.05);box-shadow:0 12px 30px rgba(0,0,0,0.35);">
      <div style="display:grid;gap:12px;">
        <div>
          <label for="usuario" style="display:block;color:rgba(255,255,255,0.75);margin-bottom:6px;">Nombre de usuario</label>
          <input id="usuario" name="usuario" type="text" value="<?= e($usuario) ?>" required
                 placeholder="Tu usuario"
                 style="width:100%;padding:10px;border-radius:12px;border:1px solid rgba(255,255,255,0.12);background:rgba(0,0,0,0.25);color:white;">
        </div>

        <div>
          <label for="contrasena" style="display:block;color:rgba(255,255,255,0.75);margin-bottom:6px;">Contraseña</label>
          <input id="contrasena" name="contrasena" type="password" required
                 placeholder="Tu contraseña"
                 style="width:100%;padding:10px;border-radius:12px;border:1px solid rgba(255,255,255,0.12);background:rgba(0,0,0,0.25);color:white;">
        </div>

        <div style="display:flex;gap:10px;align-items:center;">
          <button class="btn btn--primary" type="submit">Entrar</button>
          <a class="btn btn--ghost" href="register.php">Crear cuenta</a>
        </div>
      </div>
    </form>
  </section>
</main>

<footer class="footer">
  <div class="footer__inner">
    <p><strong>Casas Rurales Pepe</strong> · Escapadas con encanto</p>
    <p class="footer__small">© <span id="year"></span> · info@casasruralespepe.com</p>
  </div>
</footer>

<script>
  document.getElementById('year').textContent = new Date().getFullYear();
</script>

</body>
</html>
