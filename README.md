# 🏡 Casas Rurales Pepe – Sistema de Reseñas

Aplicación web desarrollada en **PHP** para la gestión y visualización de casas rurales, con sistema de usuarios, inicio de sesión y reseñas con puntuación por estrellas.

El proyecto simula una web real orientada a **pymes** (alojamientos rurales), permitiendo a los usuarios registrados dejar una única reseña por casa y visualizar la valoración media.

---

## 🚀 Funcionalidades principales

- 🏠 Listado de casas rurales desde base de datos
- 👤 Registro de usuarios con validación
- 🔐 Inicio y cierre de sesión mediante sesiones PHP
- ⭐ Sistema de reseñas con puntuación de 1 a 5 estrellas
- 📝 Una única reseña por usuario y casa
- 📊 Cálculo automático de la media de valoraciones
- 🖼️ Visualización de imágenes dinámicas por casa
- 🔒 Acceso restringido a reseñas solo para usuarios logueados

---

## 🛠️ Tecnologías utilizadas

- **HTML5**
- **CSS3**
- **JavaScript**
- **PHP 8**
- **SQLite**
- Servidor embebido de PHP

*(Proyecto realizado sin frameworks para comprender la lógica y el funcionamiento interno del backend.)*

---

## 🗂️ Estructura del proyecto

```

/casa_rural_rese-as
│
├── index.php # Página principal (listado de casas)
├── casa.php # Detalle de una casa + reseñas
├── register.php # Registro de usuarios
├── login.php # Inicio de sesión
├── logout.php # Cierre de sesión
├── db_connection.php # Conexión a SQLite
├── styles.css # Estilos
├── data.sqlite # Base de datos
└── img/
└── casa1.png ... casa8.png

```

---

## 🗄️ Base de datos

El proyecto utiliza **SQLite** con las siguientes tablas principales:

- **usuarios**
- **casas**
- **resenas**

Características del sistema de reseñas:
- Un usuario solo puede dejar **una reseña por casa**
- Las reseñas incluyen:
  - Texto (máx. 255 caracteres)
  - Puntuación de 1 a 5 estrellas
- Se calcula automáticamente la media de valoraciones

---

## ▶️ Cómo ejecutar el proyecto

### Requisitos
- PHP 8 o superior
- SQLite (incluido por defecto en PHP)

### Clonar el repositorio:
- git clone https://github.com/megalol-dev/casa_rural_rese-as.git
- cd casa_rural_rese-as
- Iniciar el servidor PHP: php -S localhost:8000
- Abrir en el navegador: http://localhost:8000/index.php

---
Proyecto desarrollado por José Luis Escudero Polo
CFGS Desarrollo de Aplicaciones Web (DAW)
