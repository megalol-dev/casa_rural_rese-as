<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/db_connection.php';

function e(string $v): string {
  return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

if (empty($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

$usuarioId = (int)($_SESSION['usuario']['id'] ?? 0);
$nombreUsuario = (string)($_SESSION['usuario']['nombre'] ?? $_SESSION['usuario']['nombre_usuario'] ?? 'Usuario');

$casaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($casaId <= 0) {
  header("Location: index.php");
  exit;
}

/* 1) Cargar casa */
$stCasa = $pdo->prepare("SELECT id, nombre, precio_dia, personas_min, personas_max, extras, imagen
                         FROM casas WHERE id = :id AND activa = 1 LIMIT 1");
$stCasa->execute([':id' => $casaId]);
$casa = $stCasa->fetch();
if (!$casa) {
  header("Location: index.php");
  exit;
}

/* 2) ¿El usuario ya ha reseñado esta casa? */
$stMine = $pdo->prepare("SELECT id FROM resenas WHERE casa_id = :c AND usuario_id = :u LIMIT 1");
$stMine->execute([':c' => $casaId, ':u' => $usuarioId]);
$yaResenada = (bool)$stMine->fetch();

/* 3) Insertar reseña (POST) */
$errores = [];
$ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($yaResenada) {
    $errores[] = "Ya has dejado una reseña en esta casa. (En esta versión no se permite editar ni repetir.)";
  } else {
    $estrellas = (int)($_POST['estrellas'] ?? 0);
    $texto = trim((string)($_POST['texto'] ?? ''));

    if ($estrellas < 1 || $estrellas > 5) {
      $errores[] = "La puntuación debe estar entre 1 y 5 estrellas.";
    }

    if ($texto === '') {
      $errores[] = "La reseña no puede estar vacía.";
    } elseif (strlen($texto) > 255) {
      $errores[] = "La reseña no puede superar 255 caracteres.";
    }

    if (empty($errores)) {
      try {
        $ins = $pdo->prepare("INSERT INTO resenas (casa_id, usuario_id, estrellas, texto)
                              VALUES (:c, :u, :e, :t)");
        $ins->execute([
          ':c' => $casaId,
          ':u' => $usuarioId,
          ':e' => $estrellas,
          ':t' => $texto,
        ]);

        $ok = true;
        $yaResenada = true; // ya no puede reseñar otra vez
      } catch (PDOException $ex) {
        // Si intenta duplicar (UNIQUE casa_id, usuario_id)
        if (str_contains($ex->getMessage(), 'UNIQUE')) {
          $errores[] = "Ya has dejado una reseña en esta casa.";
          $yaResenada = true;
        } else {
          $errores[] = "Error al guardar la reseña.";
        }
      }
    }
  }
}

/* 4) Media de estrellas + número de reseñas */
$stStats = $pdo->prepare("SELECT COUNT(*) as total, AVG(estrellas) as media
                          FROM resenas WHERE casa_id = :c");
$stStats->execute([':c' => $casaId]);
$stats = $stStats->fetch() ?: ['total' => 0, 'media' => null];

$totalResenas = (int)($stats['total'] ?? 0);
$media = $stats['media'] !== null ? (float)$stats['media'] : 0.0;

/* 5) Listado de reseñas (con nombre de usuario) */
$stList = $pdo->prepare("
  SELECT r.estrellas, r.texto, r.creada_en, u.nombre_usuario
  FROM resenas r
  JOIN usuarios u ON u.id = r.usuario_id
  WHERE r.casa_id = :c
  ORDER BY r.id DESC
");
$stList->execute([':c' => $casaId]);
$resenas = $stList->fetchAll();

$imagen = trim((string)($casa['imagen'] ?? ''));
$bgHero = $imagen !== '' ? "background-image:url('" . e($imagen) . "');" : "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e((string)$casa['nombre']) ?> - Casas Rurales Pepe</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>

<header class="header">
  <div class="header__inner">
    <div class="brand">
      <span class="brand__logo">🏡</span>
      <div class="brand__text">
        <h1 class="brand__title">Casas Rurales Pepe</h1>
        <p class="brand__subtitle">Detalle de la casa</p>
      </div>
    </div>

    <nav class="nav">
      <a class="btn btn--ghost" href="index.php">Volver</a>
      <a class="btn btn--primary" href="logout.php">Cerrar sesión</a>
    </nav>
  </div>
</header>

<div style="max-width:1100px;margin:10px auto 0;padding:0 18px;">
  <div style="padding:12px 14px;border:1px solid rgba(255,79,163,0.30);border-radius:14px;background:rgba(255,79,163,0.10);">
    👋 Bienvenido <strong><?= e($nombreUsuario) ?></strong>
  </div>
</div>

<main class="main">
  <!-- “Hero” con imagen grande -->
  <section style="border:1px solid rgba(255,255,255,0.10);border-radius:18px;overflow:hidden;box-shadow:0 12px 30px rgba(0,0,0,0.35);">
    <div style="height:280px; <?= $bgHero ?> background-size:cover;background-position:center;
                background-color:rgba(255,255,255,0.05);
                position:relative;">
      <div style="position:absolute;inset:0;background:linear-gradient(180deg, rgba(0,0,0,0.05), rgba(0,0,0,0.55));"></div>
      <div style="position:absolute;left:16px;bottom:16px;right:16px;">
        <h2 style="margin:0;font-size:1.8rem;"><?= e((string)$casa['nombre']) ?></h2>
        <div style="margin-top:8px;display:flex;gap:10px;flex-wrap:wrap;color:rgba(255,255,255,0.80);">
          <span style="padding:6px 10px;border-radius:999px;border:1px solid rgba(255,255,255,0.12);background:rgba(0,0,0,0.25);">
            <?= (int)$casa['precio_dia'] ?>€ / día
          </span>
          <span style="padding:6px 10px;border-radius:999px;border:1px solid rgba(255,255,255,0.12);background:rgba(0,0,0,0.25);">
            <?= (int)$casa['personas_min'] ?>–<?= (int)$casa['personas_max'] ?> personas
          </span>
          <span style="padding:6px 10px;border-radius:999px;border:1px solid rgba(255,255,255,0.12);background:rgba(0,0,0,0.25);">
            Extras: <?= e((string)$casa['extras']) ?>
          </span>
        </div>
      </div>
    </div>
  </section>

  <div style="height:18px;"></div>

  <!-- Resumen de reseñas -->
  <section style="display:flex;gap:14px;flex-wrap:wrap;align-items:center;">
    <div style="flex:1;min-width:260px;padding:14px;border:1px solid rgba(255,255,255,0.10);border-radius:18px;background:rgba(255,255,255,0.05);">
      <div style="font-size:0.95rem;color:rgba(255,255,255,0.75);">Valoración media</div>
      <div style="margin-top:6px;font-size:1.6rem;">
        ⭐ <?= $totalResenas > 0 ? number_format($media, 1) : "—" ?> / 5
      </div>
      <div style="margin-top:4px;color:rgba(255,255,255,0.75);">
        <?= $totalResenas ?> reseña<?= $totalResenas === 1 ? '' : 's' ?>
      </div>
    </div>

    <div style="flex:2;min-width:320px;padding:14px;border:1px solid rgba(255,255,255,0.10);border-radius:18px;background:rgba(255,255,255,0.05);">
      <div style="font-size:0.95rem;color:rgba(255,255,255,0.75);">Tu reseña</div>

      <?php if ($ok): ?>
        <div style="margin-top:10px;padding:12px;border:1px solid rgba(255,79,163,0.35);border-radius:14px;background:rgba(255,79,163,0.12);">
          ✅ Reseña guardada correctamente.
        </div>
      <?php endif; ?>

      <?php if (!empty($errores)): ?>
        <div style="margin-top:10px;padding:12px;border:1px solid rgba(255,255,255,0.15);border-radius:14px;background:rgba(255,255,255,0.06);">
          <strong>Revisa esto:</strong>
          <ul style="margin:8px 0 0 18px;">
            <?php foreach ($errores as $er): ?>
              <li><?= e($er) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if ($yaResenada): ?>
        <div style="margin-top:10px;color:rgba(255,255,255,0.75);">
          Ya has dejado una reseña en esta casa. Gracias 🙌
        </div>
      <?php else: ?>
        <form method="POST" style="margin-top:10px;display:grid;gap:10px;">
          <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <label style="color:rgba(255,255,255,0.8);">Estrellas:</label>
            <select name="estrellas"
                    style="padding:10px;border-radius:12px;border:1px solid rgba(255,255,255,0.12);background:rgba(0,0,0,0.25);color:white;">
              <option value="5">5 ⭐⭐⭐⭐⭐</option>
              <option value="4">4 ⭐⭐⭐⭐</option>
              <option value="3">3 ⭐⭐⭐</option>
              <option value="2">2 ⭐⭐</option>
              <option value="1">1 ⭐</option>
            </select>

            <span style="margin-left:auto;color:rgba(255,255,255,0.60);font-size:0.9rem;">
              Máx. 255 caracteres
            </span>
          </div>

          <textarea name="texto" maxlength="255" rows="3" required
            placeholder="Escribe tu reseña..."
            style="width:100%;padding:10px;border-radius:12px;border:1px solid rgba(255,255,255,0.12);background:rgba(0,0,0,0.25);color:white;resize:vertical;"></textarea>

          <button class="btn btn--primary" type="submit">Publicar reseña</button>
        </form>
      <?php endif; ?>
    </div>
  </section>

  <div style="height:18px;"></div>

  <!-- Listado de reseñas -->
  <section style="padding:14px;border:1px solid rgba(255,255,255,0.10);border-radius:18px;background:rgba(255,255,255,0.05);">
    <h3 style="margin:0 0 10px;">Reseñas</h3>

    <?php if (empty($resenas)): ?>
      <div style="color:rgba(255,255,255,0.75);">Aún no hay reseñas. ¡Sé el primero!</div>
    <?php else: ?>
      <div style="display:grid;gap:12px;">
        <?php foreach ($resenas as $r): ?>
          <?php
            $stars = (int)$r['estrellas'];
            $starsText = str_repeat('⭐', max(0, min(5, $stars)));
          ?>
          <div style="padding:12px;border:1px solid rgba(255,255,255,0.10);border-radius:14px;background:rgba(0,0,0,0.18);">
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
              <strong><?= e((string)$r['nombre_usuario']) ?></strong>
              <span style="color:rgba(255,255,255,0.7);"><?= $starsText ?> (<?= $stars ?>/5)</span>
              <span style="margin-left:auto;color:rgba(255,255,255,0.55);font-size:0.9rem;">
                <?= e((string)$r['creada_en']) ?>
              </span>
            </div>
            <div style="margin-top:8px;color:rgba(255,255,255,0.85);">
              <?= e((string)$r['texto']) ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
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
