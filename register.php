<?php
declare(strict_types=1);

function e(string $v): string {
  return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registro - Casas Rurales Pepe</title>
  <link rel="stylesheet" href="styles.css" />
</head>

<body>
  <header class="header">
    <div class="header__inner">
      <div class="brand">
        <span class="brand__logo">🏡</span>
        <div class="brand__text">
          <h1 class="brand__title">Casas Rurales Pepe</h1>
          <p class="brand__subtitle">Registro de usuario</p>
        </div>
      </div>

      <nav class="nav">
        <a class="btn btn--ghost" href="index.php">Volver</a>
      </nav>
    </div>
  </header>

  <main class="main">
    <section class="hero">
      <h2 class="hero__title">Crear cuenta</h2>
      <p class="hero__desc">Rellena tus datos para poder comentar reseñas.</p>
    </section>

    <section style="max-width:720px;">
      <!-- Mensajes (JS los rellena) -->
      <div id="msgOk" style="display:none; padding:14px;border:1px solid rgba(255,79,163,0.35);border-radius:14px;background:rgba(255,79,163,0.12);"></div>
      <div style="height:12px;"></div>

      <div id="msgErr" style="display:none; padding:14px;border:1px solid rgba(255,255,255,0.15);border-radius:14px;background:rgba(255,255,255,0.06);">
        <strong>Revisa esto:</strong>
        <ul id="errList" style="margin:10px 0 0 18px;"></ul>
      </div>
      <div style="height:12px;"></div>

      <form id="registerForm" autocomplete="off"
        style="padding:16px;border:1px solid rgba(255,255,255,0.10);border-radius:18px;background:rgba(255,255,255,0.05);box-shadow:0 12px 30px rgba(0,0,0,0.35);">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div>
            <label for="nombre" style="display:block;color:rgba(255,255,255,0.75);margin-bottom:6px;">Nombre</label>
            <input id="nombre" name="nombre" type="text" required
              placeholder="Ej: José"
              style="width:100%;padding:10px;border-radius:12px;border:1px solid rgba(255,255,255,0.12);background:rgba(0,0,0,0.25);color:white;">
          </div>

          <div>
            <label for="apellidos" style="display:block;color:rgba(255,255,255,0.75);margin-bottom:6px;">Apellidos</label>
            <input id="apellidos" name="apellidos" type="text" required
              placeholder="Ej: Escudero Polo"
              style="width:100%;padding:10px;border-radius:12px;border:1px solid rgba(255,255,255,0.12);background:rgba(0,0,0,0.25);color:white;">
          </div>

          <div>
            <label for="telefono" style="display:block;color:rgba(255,255,255,0.75);margin-bottom:6px;">Teléfono</label>
            <input id="telefono" name="telefono" type="text" required
              placeholder="Ej: +34600000000"
              style="width:100%;padding:10px;border-radius:12px;border:1px solid rgba(255,255,255,0.12);background:rgba(0,0,0,0.25);color:white;">
          </div>

          <div>
            <label for="nombre_usuario" style="display:block;color:rgba(255,255,255,0.75);margin-bottom:6px;">Nombre de usuario</label>
            <input id="nombre_usuario" name="nombre_usuario" type="text" required
              placeholder="Ej: megalol-dev"
              style="width:100%;padding:10px;border-radius:12px;border:1px solid rgba(255,255,255,0.12);background:rgba(0,0,0,0.25);color:white;">
          </div>

          <div>
            <label for="contrasena" style="display:block;color:rgba(255,255,255,0.75);margin-bottom:6px;">Contraseña</label>
            <input id="contrasena" name="contrasena" type="password" required minlength="8"
              placeholder="Mínimo 8 caracteres"
              style="width:100%;padding:10px;border-radius:12px;border:1px solid rgba(255,255,255,0.12);background:rgba(0,0,0,0.25);color:white;">
          </div>

          <div>
            <label for="contrasena2" style="display:block;color:rgba(255,255,255,0.75);margin-bottom:6px;">Confirmar contraseña</label>
            <input id="contrasena2" name="contrasena2" type="password" required minlength="8"
              placeholder="Repite la contraseña"
              style="width:100%;padding:10px;border-radius:12px;border:1px solid rgba(255,255,255,0.12);background:rgba(0,0,0,0.25);color:white;">
          </div>
        </div>

        <div style="display:flex;gap:10px;margin-top:14px;align-items:center;">
          <button id="btnSubmit" class="btn btn--primary" type="submit">Registrar</button>
          <a class="btn btn--ghost" href="index.php">Volver</a>
          <span id="miniHint" style="margin-left:auto;color:rgba(255,255,255,0.65);font-size:0.9rem;"></span>
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

    const form = document.getElementById('registerForm');
    const msgOk = document.getElementById('msgOk');
    const msgErr = document.getElementById('msgErr');
    const errList = document.getElementById('errList');
    const btn = document.getElementById('btnSubmit');
    const hint = document.getElementById('miniHint');

    // Regex JS (misma idea que servidor)
    const reNombreApellidos = /^[\p{L}][\p{L}\s'-]{1,}$/u;

    function normalizaTelefono(v) {
      return v.replace(/[\s\-\(\)]/g, '');
    }

    function setErrors(errors) {
      errList.innerHTML = '';
      errors.forEach(e => {
        const li = document.createElement('li');
        li.textContent = e;
        errList.appendChild(li);
      });
      msgErr.style.display = errors.length ? 'block' : 'none';
    }

    function setOk(text) {
      msgOk.textContent = text;
      msgOk.style.display = 'block';
    }

    function clearMsgs() {
      msgOk.style.display = 'none';
      msgErr.style.display = 'none';
      errList.innerHTML = '';
    }

    function validarFront() {
      const nombre = form.nombre.value.trim();
      const apellidos = form.apellidos.value.trim();
      const telefono = normalizaTelefono(form.telefono.value.trim());
      const user = form.nombre_usuario.value.trim();
      const pass = form.contrasena.value;
      const pass2 = form.contrasena2.value;

      const errors = [];

      if (!reNombreApellidos.test(nombre)) errors.push("El nombre es obligatorio y no puede contener números (mín. 2 caracteres).");
      if (!reNombreApellidos.test(apellidos)) errors.push("Los apellidos son obligatorios y no pueden contener números (mín. 2 caracteres).");
      if (user === '') errors.push("El nombre de usuario es obligatorio.");

      if (telefono === '') {
        errors.push("El teléfono es obligatorio.");
      } else {
        let t = telefono;
        if (t.startsWith('+')) t = t.slice(1);
        if (!/^\d+$/.test(t)) errors.push("El teléfono solo puede contener números y el prefijo + al inicio.");
        else if (t.length < 7 || t.length > 15) errors.push("El teléfono debe tener entre 7 y 15 dígitos (sin contar el +).");
      }

      if (pass.length < 8) errors.push("La contraseña debe tener al menos 8 caracteres.");
      if (pass !== pass2) errors.push("Las contraseñas no coinciden.");

      return { ok: errors.length === 0, errors, telefonoNormalizado: telefono };
    }

    // Mini feedback en vivo (opcional)
    form.addEventListener('input', () => {
      const { ok, errors } = validarFront();
      hint.textContent = ok ? "✅ Todo correcto" : (errors.length ? "⚠️ Revisa los campos" : "");
    });

    form.addEventListener('submit', async (ev) => {
      ev.preventDefault();
      clearMsgs();

      const v = validarFront();
      if (!v.ok) {
        setErrors(v.errors);
        return;
      }

      // Enviamos con fetch sin recargar
      btn.disabled = true;
      btn.textContent = 'Registrando...';

      try {
        const fd = new FormData(form);
        // sustituimos teléfono por el normalizado
        fd.set('telefono', v.telefonoNormalizado);

        const res = await fetch('register_action.php', {
          method: 'POST',
          body: fd
        });

        const data = await res.json().catch(() => null);

        if (!data) {
          setErrors(["Respuesta inválida del servidor."]);
          return;
        }

        if (data.ok) {
          setOk(data.mensaje || "Registro completado.");
          form.reset();
          hint.textContent = "";
        } else {
          setErrors(data.errores || ["No se pudo registrar."]);
        }
      } catch (e) {
        setErrors(["Error de red o servidor."]);
      } finally {
        btn.disabled = false;
        btn.textContent = 'Registrar';
      }
    });
  </script>
</body>
</html>
