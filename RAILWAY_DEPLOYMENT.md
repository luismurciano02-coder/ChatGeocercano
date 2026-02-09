# Railway Deployment Guide

## Pasos para desplegar en Railway

### ⚠️ Importante: Preparar el repositorio local primero

Cada vez que ejecutes `composer install` localmente, confirmá el `composer.lock`:

```bash
composer install
git add composer.lock
git commit -m "Update composer.lock"
git push
```

### 1. Preparar el repositorio para Railway

```bash
git add .
git commit -m "Fixed Railway deployment configuration"
git push
```

### 2. Crear un nuevo proyecto en Railway

1. Ve a [railway.app](https://railway.app)
2. Haz clic en "New Project"
3. Selecciona "Deploy from GitHub"
4. Conecta tu repositorio de GitHub
5. Selecciona la rama principal (main/master)
6. Railway detectará automáticamente los archivos de configuración

### 3. Agregar MySQL Database

1. En Railway, haz clic en "+ Agregar servicio"
2. Selecciona "MySQL"
3. Se creará una base de datos MySQL
4. Railway vinculará automáticamente la variable de entorno `DATABASE_URL`

### 4. Configurar variables de entorno en Railway

En el dashboard de Railway:

1. Ve a tu web service
2. Haz clic en "Settings" → "Variables"
3. Agrega estas variables **obligatorias**:

```
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=<genera-una-clave-aleatoria-de-32-caracteres>
```

Railway automáticamente vinculará:
- `DATABASE_URL` (cuando agregas el servicio MySQL)
- `PORT` (Railway asigna automáticamente)

#### Cómo generar APP_SECRET seguro:

```bash
# En tu máquina local
php -r "echo bin2hex(random_bytes(16));"
# O
openssl rand -hex 16
```

### 5. Configurar dominio personalizado (opcional)

En Railway:
1. Ve a "Settings" → "Domains"
2. Agrega tu dominio personalizado (ej: chatgeocercano.com)
3. Actualiza `APP_URL` en variables si es necesario

### 6. Monitoreo y logs

En Railway:
1. Selecciona tu proyecto
2. Ve a "Logs" para ver logs en tiempo real
3. Ve a "Metrics" para ver CPU, memoria, etc.

## Estructura de despliegue

El Procfile ejecutará automáticamente:

```bash
# 1. Instalar dependencias PHP
composer install --no-dev --optimize-autoloader

# 2. Compilar assets
npm run build

# 3. Ejecutar migraciones de BD
php bin/console doctrine:migrations:migrate --no-interaction

# 4. Limpiar y calentar caché
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# 5. Iniciar servidor con PHP-FPM y Nginx
php-fpm -F (con nginx configurado)
```

## Variables de entorno importantes

| Variable | Valor | Notas |
|----------|-------|-------|
| `APP_ENV` | `prod` | Producción |
| `APP_DEBUG` | `0` | Nunca 1 en producción |
| `APP_SECRET` | Clave segura | Genera nueva, min. 32 caracteres |
| `DATABASE_URL` | Auto | Railway lo vincula automáticamente |
| `PORT` | Auto | Railway asigna puerto |

## Solucionar problemas

### Error: "Doctrine migrations failed"

```bash
# En Railway, ejecuta:
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
```

O en la terminal local:

```bash
DATABASE_URL="mysql://your_user:your_password@your_host:3306/chatgeocercano?serverVersion=8&charset=utf8mb4" \
php bin/console doctrine:migrations:migrate
```

### Error: "APP_SECRET is empty"

Asegúrate que APP_SECRET está configurado en Railway → Variables:
```
APP_SECRET=<tu-clave-segura>
```

### Error: "Cannot connect to database"

1. Verifica que MySQL está arriba en Railway
2. Copia el `DATABASE_URL` de MySQL service en Railway
3. Pégalo en tu web service como variable

### Ver logs en vivo

```bash
# En tu máquina local (si tienes Railway CLI)
railway logs -f

# O en el dashboard de Railway
# Selecciona tu proyecto y ve a Logs
```

### Rebuild manualmente

En Railway dashboard:
1. Selecciona el servicio
2. Haz clic en "Redeploy"
3. O: `git push` (si connected a GitHub)

## Desarrollo local

### Primero, configura `.env.local`:

```bash
cp .env.dev .env.local
```

Edita `.env.local`:

```dotenv
DATABASE_URL="mysql://root:password@127.0.0.1:3306/chatgeocercano?serverVersion=8&charset=utf8mb4"
APP_SECRET=dev-secret-key
```

### Instalar y ejecutar

```bash
# Instalar dependencias
composer install
npm install

# Crear BD y migrar
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Compilar assets
npm run build

# Iniciar servidor
symfony server:start
# O
php -S 127.0.0.1:8000 -t public
```

## Archivos de configuración creados

- **Procfile** - Define comando de inicio para Railway
- **railway.json / railway.toml** - Configuración de Railway
- **.env.prod.local.example** - Template de variables de producción
- **start.sh** - Script de startup que ejecuta migraciones y inicia el servidor
- **nginx.conf** - Configuración de Nginx para servir la app
- **.dockerignore** - Archivos ignorados en build
- **bin/deploy.sh** - Script auxiliar de despliegue

## Notas importantes

1. **Nunca** commités `.env.local` o `.env.prod.local`
2. **Siempre** usa variables de entorno de Railway para secretos
3. **Genera** un nuevo APP_SECRET para cada despliegue
4. **Verifica** que DATABASE_URL está configurado antes de hacer push
5. **Revisa** los logs después de cada despliegue

## URLs útiles

- Railway Dashboard: https://railway.app/dashboard
- Configurador de MySQL: https://railway.app/dashboard  
- Documentación de Railway: https://docs.railway.app
- Guía de PHP en Railway: https://docs.railway.app/guides/php

