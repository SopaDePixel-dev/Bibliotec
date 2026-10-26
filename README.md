# 📚 BiblioTec - Sistema de Gestión Bibliotecaria

Sistema web desarrollado en **PHP 8** y **MySQL** para la gestión integral de una biblioteca escolar, cubriendo el control de acervo, estudiantes, préstamos, devoluciones, autenticación de usuarios y reportes estadísticos.

---

## 🚀 Características del Sistema

* **Autenticación y Roles**: Sistema de login seguro con contraseñas cifradas (`password_hash`) y control de acceso por roles (`ADMIN` y `BIBLIOTECARIO`).
* **Módulo de Estudiantes**: Registro y control de alumnos con validación de número de control de 9 caracteres y asignación de carrera.
* **Módulo de Categorías**: Clasificación organizada del acervo bibliográfico.
* **Módulo de Libros**: Catálogo con control de stock y ejemplares disponibles en tiempo real.
* **Módulo de Préstamos**: Asignación de libros a estudiantes con cálculo automático de fecha límite (3 días) y descuento automático de stock.
* **Módulo de Devoluciones**: Registro de entregas, condición del ejemplar y reingreso automático al stock.
* **Reportes y Estadísticas**: Métricas en tiempo real del estado de la biblioteca y opción de impresión de reportes.

---

## 🛠️ Tecnologías Utilizadas

* **Backend**: PHP 8.x con PDO (PHP Data Objects) para consultas seguras y prevención de inyección SQL.
* **Base de Datos**: MySQL (compatible con XAMPP).
* **Frontend**: HTML5, CSS3 con diseño responsivo y la tipografía *Inter*.
* **Control de Versiones**: Git y GitHub.

---

## ⚙️ Requisitos e Instalación Local (XAMPP)

Para correr este proyecto en tu computadora de manera local, sigue estos pasos:

1. **Clonar o descargar** este repositorio dentro de la carpeta de proyectos de XAMPP:
   ```bash
   C:\xampp\htdocs\BiblioTec