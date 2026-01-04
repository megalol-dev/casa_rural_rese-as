<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/db_connection.php';

// Traer casas activas
$stmt = $pdo->prepare("SELECT id, nombre, precio_dia, personas_min, personas_max, extras, imagen 
                       FROM casas
                       WHERE activa = 1
                       ORDER BY id ASC");
$stmt->execute();
$casas = $stmt->fetchAll();

function e(string $value): string {
  return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$logueado = !empty($_SESSION['usuario']);
$nombreBienvenida = $logueado ? (string)($_SESSION['usuario']['nombre'] ?? $_SESSION['usuario']['nombre_usuario'] ?? '') : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Casas Rurales Pepe</title>
  <link rel="stylesheet" href="styles.css" />
</head>

<body>
  <!-- HEADER -->
  <header class="header">
    <div class="header__inner">
      <div class="brand">
        <span class="brand__logo">🏡</span>
        <div class="brand__text">
          <h1 class="brand__title">Casas Rurales Pepe</h1>
          <p class="brand__subtitle">Escápate, respira y desconecta</p>
        </div>
      </div>

      <nav class="nav">
        <?php if (!$logueado): ?>
          <a class="btn btn--ghost" href="login.php">Iniciar sesión</a>
          <a class="btn btn--primary" href="register.php">Registrarse</a>
        <?php else: ?>
          <a class="btn btn--primary" href="logout.php">Cerrar sesión</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <!-- Mensaje debajo del header -->
  <div style="max-width:1100px;margin:10px auto 0;padding:0 18px;">
    <?php if ($logueado): ?>
      <div style="padding:12px 14px;border:1px solid rgba(255,79,163,0.30);border-radius:14px;background:rgba(255,79,163,0.10);">
        👋 Bienvenido <strong><?= e($nombreBienvenida) ?></strong>
      </div>
    <?php else: ?>
      <div style="padding:12px 14px;border:1px solid rgba(255,255,255,0.10);border-radius:14px;background:rgba(255,255,255,0.04);">
        Inicia sesión para ver detalles y poder comentar reseñas.
      </div>
    <?php endif; ?>
  </div>

  <!-- MAIN -->
  <main class="main">
    <section class="hero">
      <h2 class="hero__title">Elige tu casa rural ideal</h2>
      <p class="hero__desc">
        8 alojamientos con encanto: naturaleza, chimenea, piscina y relax.
      </p>
    </section>

    <section class="grid" aria-label="Listado de casas rurales">
      <?php if (empty($casas)): ?>
        <div style="padding:14px;border:1px solid rgba(255,255,255,0.10);border-radius:14px;background:rgba(255,255,255,0.05);">
          No hay casas disponibles ahora mismo.
        </div>
      <?php else: ?>
        <?php foreach ($casas as $casa): ?>
          <?php
            $id     = (int)$casa['id'];
            $nombre = (string)$casa['nombre'];
            $precio = (int)$casa['precio_dia'];
            $pmin   = (int)$casa['personas_min'];
            $pmax   = (int)$casa['personas_max'];
            $extras = (string)$casa['extras'];
            $imagen = isset($casa['imagen']) ? trim((string)$casa['imagen']) : '';

            $bgStyle = '';
            if ($imagen !== '') {
              $bgStyle = "background-image: url('" . e($imagen) . "'); background-size: cover; background-position: center;";
            }

            // Si está logueado, el botón apunta a una página de detalles (placeholder)
            // Si no, le mandamos al login
            $detalleUrl = $logueado ? ("casa.php?id=" . $id) : "login.php";
          ?>
          <article class="card" data-id="<?= $id ?>">
            <div class="card__img" role="img" aria-label="Casa <?= e($nombre) ?>" style="<?= $bgStyle ?>"></div>

            <div class="card__body">
              <h3 class="card__title"><?= e($nombre) ?></h3>

              <ul class="card__meta">
                <li><span>Precio/día</span><strong><?= $precio ?>€</strong></li>
                <li><span>Personas</span><strong><?= $pmin ?>–<?= $pmax ?></strong></li>
                <li><span>Extras</span><strong><?= e($extras) ?></strong></li>
              </ul>

              <a class="btn btn--secondary" href="<?= e($detalleUrl) ?>">
                Ver detalles
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </main>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="footer__inner">
      <p><strong>Casas Rurales Pepe</strong> · Escapadas con encanto</p>
      <p class="footer__small">
        © <span id="year"></span> · Contacto: info@casasruralespepe.com · Tel: 600 000 000
      </p>
    </div>
  </footer>

  <script>
    document.getElementById('year').textContent = new Date().getFullYear();
  </script>
</body>
</html>
