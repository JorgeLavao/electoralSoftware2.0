# electoralSoftware2.0

Entorno de desarrollo para una aplicacion Laravel 12 + Livewire + Vite con Docker.

La infraestructura actual del proyecto corre con servicios separados para:

- `app`: PHP-FPM + Composer
- `nginx`: servidor web
- `vite`: frontend con hot reload
- `db`: MySQL 8
- `redis`: cache y soporte para colas/eventos
- `queue`: worker de Laravel
- `phpmyadmin`: administracion visual de base de datos

## Requisitos

Antes de iniciar, asegurate de tener:

- Docker Desktop instalado y corriendo
- Docker Compose v2
- Puertos libres:
  - `80`
  - `5173`
  - `3306`
  - `6379`
  - `8080`

## Estructura Docker

El proyecto esta preparado para que Docker haga la mayor parte del trabajo automaticamente:

- El contenedor `app` instala dependencias de Composer si `vendor` no existe.
- El contenedor `vite` instala dependencias de Node si `node_modules` no existe.
- El contenedor `queue` ejecuta `php artisan queue:work`.
- `nginx` sirve la aplicacion Laravel en `http://localhost`.

Esto significa que el flujo de arranque ya no depende de abrir una shell manualmente para levantar Vite.

## Primer arranque

Ubicate en la raiz del proyecto:

```powershell
cd C:\Users\USER\Documents\NVST\electoralSoftware2.0
```

### 1. Construir y levantar contenedores

```powershell
docker compose up -d --build
```

Esto levantara:

- `app`
- `nginx`
- `vite`
- `db`
- `redis`
- `queue`
- `phpmyadmin`

### 2. Ejecutar migraciones y seeders

```powershell
docker compose exec app php artisan migrate --seed
```

### 3. Crear el enlace simbolico de storage

```powershell
docker compose exec app php artisan storage:link
```

### 4. Verificar que los servicios esten arriba

```powershell
docker compose ps
```

## Accesos locales

Una vez levantado el entorno, puedes entrar a:

- Aplicacion: [http://localhost](http://localhost)
- Vite dev server: [http://localhost:5173](http://localhost:5173)
- phpMyAdmin: [http://localhost:8080](http://localhost:8080)

Credenciales de base de datos por defecto:

- Host: `db`
- Puerto: `3306`
- Base de datos: `laravel`
- Usuario: `laravel`
- Contrasena: `laravel`

## Arranque diario

Si ya construiste imagenes y volumenes antes, normalmente solo necesitas:

```powershell
docker compose up -d
```

Si quieres revisar el estado:

```powershell
docker compose ps
```

Si cambiaste Dockerfiles, dependencias base o configuracion de imagenes, usa:

```powershell
docker compose up -d --build
```

## Detener el entorno

Para detener los contenedores:

```powershell
docker compose down
```

Para detener y eliminar volumenes del entorno:

```powershell
docker compose down -v
```

Usa `-v` solo si realmente quieres reiniciar datos persistidos como MySQL, Redis, `vendor` o `node_modules`.

## Comandos utiles

### Ver logs

```powershell
docker compose logs -f app
docker compose logs -f nginx
docker compose logs -f vite
docker compose logs -f queue
docker compose logs -f db
docker compose logs -f redis
```

### Ejecutar comandos de Laravel

```powershell
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
```

### Ejecutar migraciones

```powershell
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan migrate:fresh --seed
```

### Abrir shell dentro del contenedor de app

```powershell
docker compose exec app sh
```

### Abrir shell dentro del contenedor de Vite

```powershell
docker compose exec vite sh
```

## Hot Reload

El hot reload lo maneja el servicio `vite`, que se ejecuta automaticamente dentro de Docker.

No necesitas correr manualmente:

```bash
npm run dev
```

salvo que estes haciendo alguna prueba puntual fuera del flujo normal del proyecto.

Si el frontend no refleja cambios:

1. Revisa que el contenedor `vite` este arriba.
2. Revisa logs con `docker compose logs -f vite`.
3. Confirma que `VITE_HMR_CLIENT_HOST=localhost` siga correcto en `.env`.

## Base de datos y cola

Servicios disponibles:

- MySQL en `db:3306`
- Redis en `redis:6379`
- Worker de colas en el servicio `queue`

Nota importante:

- El archivo `.env` actual usa `QUEUE_CONNECTION=database`.
- Aunque Redis esta disponible, las colas seguiran usando base de datos mientras esa variable no cambie.

## Troubleshooting

### El contenedor no levanta

Reconstruye imagenes:

```powershell
docker compose down
docker compose up -d --build
```

### La app responde con errores de Laravel

Limpia caches:

```powershell
docker compose exec app php artisan optimize:clear
```

### Problemas con permisos en `storage` o `bootstrap/cache`

```powershell
docker compose exec app sh -lc "chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache"
```

### Problemas con migraciones

Verifica que MySQL este saludable:

```powershell
docker compose ps
docker compose logs -f db
```

Luego vuelve a correr:

```powershell
docker compose exec app php artisan migrate --seed
```

### Vite no sirve assets o el navegador no conecta con HMR

Revisa:

- que `vite` este arriba
- que el puerto `5173` no este ocupado
- que `.env` tenga:

```env
VITE_DEV_SERVER_HOST=0.0.0.0
VITE_DEV_SERVER_PORT=5173
VITE_HMR_CLIENT_HOST=localhost
VITE_HMR_CLIENT_PORT=5173
```

## Flujo recomendado para desarrollo

1. Levantar entorno con `docker compose up -d --build`
2. Ejecutar `php artisan migrate --seed` si hubo cambios de base de datos
3. Entrar a [http://localhost](http://localhost)
4. Revisar logs solo si algo falla

## Resumen rapido

Primer arranque:

```powershell
docker compose up -d --build
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

Arranque diario:

```powershell
docker compose up -d
```
