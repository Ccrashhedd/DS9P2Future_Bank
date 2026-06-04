# Cambios realizados en el frontend

Se preparó una estructura tipo SPA simple para el proyecto bancario **Future Bank**.

## Archivos principales modificados

- `WEB/Frontend/index.html`
- `WEB/Frontend/JS/controller.js`
- `WEB/Frontend/Partials/header.html`
- `WEB/Frontend/Partials/footer.html`
- `WEB/Frontend/Views/home.html`
- `WEB/Frontend/Views/login.html`
- `WEB/Frontend/Views/postulacion.html`
- `WEB/Styles/main.css`
- `WEB/Styles/variables.css`
- `WEB/Styles/base.css`
- `WEB/Styles/parts/header.css`
- `WEB/Styles/parts/footer.css`
- `WEB/Styles/pantallas/index.css`

## Cómo funciona el controller

El archivo `WEB/Frontend/JS/controller.js` usa rutas con hash:

- `#/home`
- `#/login`
- `#/postulacion`

El controller carga dinámicamente:

- Header desde `./Partials/header.html`
- Footer desde `./Partials/footer.html`
- Vista central desde `./Views/...`

## Importante

Como se usa `fetch()` para cargar las vistas, el proyecto debe abrirse desde un servidor local. Recomendado:

- XAMPP o Laragon, colocando el proyecto dentro de `htdocs` o `www`.
- Live Server en Visual Studio Code.

No se recomienda abrir el archivo con doble clic usando `file://`, porque el navegador puede bloquear la carga de archivos locales.

## Backend pendiente

Los formularios apuntan a rutas PHP sugeridas dentro de:

`WEB/Backend/PHP/controller/`

Ejemplos:

- `login.php`
- `registro.php`
- `postulacion.php`

Actualmente el JavaScript evita que el formulario falle visualmente si esos controladores PHP todavía no existen. Cuando el backend esté listo, puedes quitar el bloque preventivo dentro de la función `bindDemoForms()` o agregar `data-backend-ready="true"` al formulario.
