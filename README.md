# Laravel 12 + Inertia (React) + Vite + Docker (Dev)

Entorno de desarrollo con **hot reload real** (sin `npm run build`) usando **Docker**:

- **PHP-FPM 8.3** (con Composer y extensiones de Laravel)
- **Node 20** + **Vite** (HMR)
- **Nginx** sirviendo `/public`
- **MySQL 8** + **phpMyAdmin**
- Montaje de volúmenes para reflejar cambios al instante

---

## Requisitos

- Docker ≥ 24
- Docker Compose v2
- Puertos libres: **80**, **5173**, **3306** (opcional), **8080**

---

## Instalación y primer arranque

1) **Construir e iniciar contenedores**
```bash
docker compose up -d --build
```

2) **Entrar al contenedor de la app**
```bash
docker compose exec app bash
```

3) **Dependencias y clave de app (primera vez)**
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```
**Nota:** Si cambias el host de acceso (no usas `localhost`), actualiza `VITE_HMR_CLIENT_HOST`.

4) **Dependencias del front y Vite (HMR)**
> Deja **esta terminal** dedicada al watcher.
```bash
npm install
export CHOKIDAR_USEPOLLING=true
export WATCHPACK_POLLING=true
npm run dev -- --host
```

5) **Listo. Rutas de acceso**
- App: **http://localhost**
- Vite: **http://localhost:5173** (opcional para verificar HMR)
- phpMyAdmin: **http://localhost:8080**
  - Host: `db`
  - Usuario: `laravel`
  - Pass: `laravel`

---

## Comandos frecuentes

**Ver logs**
```bash
docker compose logs -f nginx
docker compose logs -f app
```

**Reiniciar todo**
```bash
docker compose down
docker compose up -d --build
```

**Limpiar cachés de Laravel (cuando cambies PHP/Blade/config)**
```bash
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan config:clear
```

**Permisos de storage/bootstrap (si dieran problemas)**
```bash
docker compose exec app bash -lc "chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache"
```

**Ejecutar migraciones / seeders**
```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate --seed
```

---

## Hot Reload (cómo funciona)

- **Frontend (React/Inertia):** HMR vía Vite. Cambios en `resources/js/**` se aplican al instante sin recargar toda la página.
- **Blade/PHP:** No tienen HMR. Cambios en Blade o controladores requieren refrescar el navegador.
  Si ves comportamiento raro, usa los comandos de *Limpiar cachés*.

---

## Base de datos

**Credenciales por defecto**
- Host: `db`
- Puerto: `3306`
- DB: `laravel`
- User: `laravel`
- Pass: `laravel`

**Backups (rápido)**
```bash
# Dump
docker compose exec db sh -lc 'mysqldump -ularavel -plaravel laravel' > backup.sql

# Restore
cat backup.sql | docker compose exec -T db sh -lc 'mysql -ularavel -plaravel laravel'
```
