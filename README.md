# Laravel 12 + Livewire + Vite + Docker (Dev)

Entorno de desarrollo con **recarga en caliente (Hot Reload)** para **Laravel + Livewire**, sin necesidad de ejecutar `npm run build`, usando **Docker**.

---

## 🧱 Stack principal

- **PHP-FPM 8.3** (con Composer y extensiones de Laravel)
- **Node 20** + **Vite** (para CSS/JS y Livewire)
- **Nginx** sirviendo `/public`
- **MySQL 8** + **phpMyAdmin**
- **Volúmenes montados** para reflejar cambios al instante

---

## 🚀 Requisitos

- Docker ≥ 24  
- Docker Compose v2  
- Puertos libres: **80**, **5173**, **3306** (opcional), **8080**

---

## ⚙️ Instalación y primer arranque

### 1️⃣ Construir e iniciar contenedores
```bash
docker compose up -d --build
```

### 2️⃣ Entrar al contenedor de la app
```bash
docker compose exec app bash
```

### 3️⃣ Instalar dependencias y preparar Laravel (solo primera vez)
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

> ⚠️ Si cambias el dominio o accedes desde otro host (no `localhost`), actualiza la variable:
> ```
> VITE_HMR_CLIENT_HOST=localhost
> ```

### 4️⃣ Instalar dependencias del frontend y ejecutar Vite
> Deja **esta terminal** dedicada al watcher para hot reload.
```bash
npm install
export CHOKIDAR_USEPOLLING=true
export WATCHPACK_POLLING=true
npm run dev -- --host
```

### 5️⃣ Listo. Accesos rápidos
- Aplicación Laravel: **http://localhost**
- Vite Dev Server: **http://localhost:5173**
- phpMyAdmin: **http://localhost:8080**
  - Host: `db`
  - Usuario: `laravel`
  - Contraseña: `laravel`

---

## 🧰 Comandos útiles

### Ver logs
```bash
docker compose logs -f nginx
docker compose logs -f app
```

### Reiniciar todo
```bash
docker compose down
docker compose up -d --build
```

### Limpiar cachés de Laravel (cuando cambies Blade/config/rutas)
```bash
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan config:clear
```

### Permisos de storage/bootstrap (si dieran problemas)
```bash
docker compose exec app bash -lc "chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache"
```

### Ejecutar migraciones / seeders
```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate --seed
```

---

## 🔁 Hot Reload (cómo funciona)

- **Frontend (Blade + Livewire + Vite):**  
  Los cambios en tus vistas `.blade.php`, componentes Livewire y archivos de estilo/JS compilados por Vite se reflejan de inmediato.  
  Livewire detecta actualizaciones en los componentes sin recargar toda la página.

- **Archivos PHP (controladores, modelos):**  
  Requieren refrescar el navegador para ver los cambios.

Si ves comportamiento extraño, limpia las cachés con los comandos anteriores.

---

## 🗄️ Base de datos

### Credenciales por defecto
- Host: `db`
- Puerto: `3306`
- Base de datos: `laravel`
- Usuario: `laravel`
- Contraseña: `laravel`

### Backups rápidos
```bash
# Dump
docker compose exec db sh -lc 'mysqldump -ularavel -plaravel laravel' > backup.sql

# Restore
cat backup.sql | docker compose exec -T db sh -lc 'mysql -ularavel -plaravel laravel'
```

---

## 🧩 Stack resumen

| Servicio     | Versión | Descripción |
|---------------|----------|--------------|
| PHP-FPM       | 8.3 | Backend de Laravel |
| Nginx         | latest | Servidor web |
| Node          | 20 | Compilación con Vite |
| MySQL         | 8.0 | Base de datos |
| phpMyAdmin    | latest | Administración visual de DB |
| Livewire      | 3.x | Interactividad en tiempo real |
| Vite          | 5.x | Bundler y HMR |

---

## 🧾 Notas adicionales

- **Livewire** ya incluye soporte para Vite y recarga en caliente.  
- Si utilizas Alpine.js u otras librerías JS, instálalas como dependencias de NPM.  
- Para producción, recuerda ejecutar:
```bash
npm run build
php artisan optimize
```

---

Desarrollado con ❤️ usando **Laravel 12 + Livewire + Docker + Vite**.
