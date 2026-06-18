# App Android y API PHP

## Bases de datos reales

Importa primero estos archivos en MySQL/XAMPP:

1. `p2gestiongeneral.sql`
2. `p2gestiondocumentos.sql`
3. `SQL/mobile_api_patch.sql` para agregar mejoras usadas por la app movil.

Los nombres esperados por la API son:

- `p2gestiongeneral`
- `p2gestiondocumentos`

No uses `ds2gestiongeneral` ni `ds2gestiondocumentos`, salvo que hayas importado manualmente las bases con esos nombres y tambien cambies `APP/Backend/PHP/config/bd.php`.

## Configuracion PHP

La API vive en:

```text
APP/Backend/PHP/api/
```

`APP/Backend/PHP/config/bd.php` usa por defecto:

- host: `127.0.0.1`
- puerto: `3306`
- usuario: `root`
- password: vacio

Tambien acepta variables de entorno `DB_HOST`, `DB_PORT`, `DB_USER` y `DB_PASS`.

La API crea automaticamente `sesiones_app` si acabas de importar una base nueva que no la tiene. El parche `SQL/mobile_api_patch.sql` sigue siendo recomendado porque tambien agrega el indice `idx_documento_postulante_idPostulante` en `p2gestiondocumentos.documento_postulante`.

## Endpoints principales

- `POST auth/login.php`
- `POST auth/register.php`
- `GET postulante/me.php`
- `GET documentos/list.php`
- `GET documentos/detail.php?id=ID`
- `GET documentos/download.php?id=ID`
- `GET catalogos/tipos_documentos.php`

Los endpoints protegidos usan:

```http
Authorization: Bearer TOKEN
```

## Base URL en emulador Android

En el emulador de Android, XAMPP se accede con:

```kotlin
const val BASE_URL = "http://10.0.2.2/NOMBRE_CARPETA/APP/Backend/PHP/api/"
```

Ejemplo:

```kotlin
const val BASE_URL = "http://10.0.2.2/DS9P2Future_Bank/APP/Backend/PHP/api/"
```

Si la carpeta dentro de `htdocs` tiene otro nombre, cambia `DS9P2Future_Bank` por el nombre real. La app permite editar la Base URL desde la pantalla de login.

## Alcance de la app

La app puede:

- Registrar usuario.
- Iniciar sesion.
- Consultar datos del postulante autenticado.
- Consultar documentos.
- Abrir PDF con el endpoint protegido `documentos/download.php`.

La app no crea ni edita postulantes, no sube PDF, no elimina PDF y no edita documentos.

## Diseño visual de la app

La app usa una identidad visual alineada con Future Bank:

- Fondo oscuro con degradados: `#080511`, `#070410` y `#120622`.
- Morado principal y acentos: `#6D28D9`, `#3B0764` y `#A855F7`.
- Tonos plateados para texto, bordes e iconos: `#E5E7EB`, `#C7C9D1` y `#8B8FA3`.
- Texto principal claro: `#F8FAFC`; texto secundario: `#CBD5E1`.
- Tarjetas glassmorphism con superficies blancas translúcidas y borde sutil.

La UI está construida en Jetpack Compose con componentes reutilizables en:

```text
app/src/main/java/com/example/project2/ui/components/
```

Incluye fondo `BankGradientBackground`, tarjetas `BankGlassCard`, botones bancarios, campos de texto oscuros, top app bar, navegación inferior, chips, estados vacíos/error/loading y tarjetas de documento.
