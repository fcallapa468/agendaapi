# AgendaAPI

API REST de una agenda: usuarios y sus contactos. Proyecto de la exposición del tema 04, "API REST avanzada, documentación y versionamiento", de Tecnología Web (INF513), UAGRM.

## Qué incluye

- CRUD de usuarios y de contactos (cada usuario tiene sus contactos).
- Búsqueda, orden y paginación en los listados.
- Códigos de estado HTTP (200, 201, 204, 401, 403, 404, 405, 422, 429, 502, 503) con un formato de error único y mensajes en español.
- Versionamiento por URL: `/api/v1` (obsoleta, con cabeceras `Deprecation`, `Sunset` y `Link`) y `/api/v2` (actual).
- Autenticación con tokens Bearer (Laravel Sanctum) y límite de 5 intentos de login por minuto.
- Documentación OpenAPI generada automáticamente con Scramble.
- Consumo de dos APIs externas: Open-Meteo (clima, sin registro) y ExchangeRate-API (tipo de cambio, con clave).

## Tecnologías

Laravel 13, PHP 8.5, PostgreSQL 18, Laravel Sanctum y Scramble. Probado en Fedora 44.

## Instalación

Requisitos: PHP 8.3 o superior con las extensiones `pdo_pgsql` y `mbstring`, Composer, PostgreSQL y Git.

1. Clonar el repositorio y entrar a la carpeta: 
git clone https://github.com/USUARIO/agendaapi.git
    cd agendaapi

Paso 2. Instalar las dependencias:

    composer install

Paso 3. Crear el archivo de configuración y su clave:

    cp .env.example .env
    php artisan key:generate

Paso 4. En PostgreSQL, crear el usuario `agenda` y la base `agendaapi`. Luego poner la contraseña en `DB_PASSWORD` del `.env`.

Paso 5. (Opcional) Registrarse gratis en https://www.exchangerate-api.com y poner la clave en `EXCHANGERATE_KEY` del `.env`.

Paso 6. Crear las tablas y, si se quiere, 25 usuarios de prueba (contraseña `password`):

    php artisan migrate
    php artisan tinker --execute="App\Models\User::factory()->count(25)->create()"

Paso 7. Encender el servidor:

    php artisan serve --host=0.0.0.0 --port=8000

## Endpoints principales

| Método | Ruta | Token |
|---|---|---|
| POST | `/api/v1/registro` | No |
| POST | `/api/v1/login` | No |
| GET | `/api/v1/perfil` | Sí |
| POST | `/api/v1/logout` | Sí |
| GET | `/api/v1/users` y `/api/v1/users/{id}` | No |
| POST, PUT, DELETE | `/api/v1/users/...` | Sí |
| GET | `/api/v1/users/{id}/contactos` y `/api/v1/contactos/{id}` | No |
| POST, PUT, DELETE | `/api/v1/users/{id}/contactos` y `/api/v1/contactos/{id}` | Sí |
| GET | `/api/v2/users` y `/api/v2/users/{id}` | No |
| GET | `/api/v2/externas/clima` | No |
| GET | `/api/v2/externas/tipo-cambio?monto=100&de=USD&a=BOB` | No |

## Documentación

Con el servidor encendido y `APP_ENV=local`:

- v1: `http://localhost:8000/docs/v1` (archivo OpenAPI en `/docs/v1.json`)
- v2: `http://localhost:8000/docs/v2` (archivo OpenAPI en `/docs/v2.json`)

Los archivos `.json` se pueden importar en Postman para tener todas las peticiones listas.
