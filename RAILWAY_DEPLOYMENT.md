# Railway Deployment Guide

## Pasos para desplegar en Railway

### 1. Preparar el repositorio
```bash
git add .
git commit -m "Add Railway configuration"
git push
```

### 2. Crear un nuevo proyecto en Railway

1. Ve a [railway.app](https://railway.app)
2. Haz clic en "New Project"
3. Selecciona "Deploy from GitHub"
4. Conecta tu repositorio de GitHub
5. Selecciona la rama principal (main/master)

### 3. Configurar variables de entorno en Railway

En el dashboard de Railway, ve a "Variables" y agrega:

```
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=<genera-una-clave-aleatoria-larga>
DATABASE_URL=<railway-proporciona-esto-automaticamente>
APP_URL=https://<your-railway-domain>.railway.app
PORT=8000
```

### 4. Agregar base de datos MySQL

1. En Railway, haz clic en "+ Agregar servicio"
2. Selecciona "MySQL"
3. Railway vinculará automáticamente la `DATABASE_URL`

### 5. Configurar variables de desarrollo local

Crea `.env.local` en la raíz del proyecto:

```dotenv
DATABASE_URL="mysql://root:password@localhost:3306/chatgeocercano?serverVersion=8&charset=utf8mb4"
APP_SECRET=your-secret-key-for-dev
```

### 6. Ejecutar localmente

```bash
# Instalar dependencias
composer install
npm install

# Ejecutar migraciones
php bin/console doctrine:migrations:migrate

# Iniciar servidor de desarrollo
symfony server:start
# O
php -S 127.0.0.1:8000 -t public
```

### 7. Build y deploy automático

Railway ejecutará automáticamente:
- `npm run build` - Compila los assets con Webpack
- `composer install --no-dev` - Instala dependencias sin dev
- `php bin/console doctrine:migrations:migrate` - Ejecuta migraciones
- `php -S 0.0.0.0:8000 -t public` - Inicia el servidor

## Archivos creados

- **Procfile**: Define el comando para iniciar la aplicación
- **railway.json / railway.toml**: Configuración de Railroad
- **.env.production**: Variables de entorno para producción
- **bin/deploy.sh**: Script de despliegue manual
- **.dockerignore**: Archivos a ignorar en build

## Solucionar problemas

### Errores de base de datos
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### Limpiar cache
```bash
php bin/console cache:clear --env=prod
```

### Ver logs en Railway
En el dashboard de Railway, selecciona tu proyecto y ve a "Logs"

## Variables de entorno importantes

- `APP_ENV`: prod (producción) o dev (desarrollo)
- `APP_DEBUG`: 0 (producción) o 1 (desarrollo)
- `APP_SECRET`: Clave secreta (genera una nueva)
- `DATABASE_URL`: Conectar con MySQL
- `DEFAULT_URI`: URL base de la aplicación
- `PORT`: Puerto (Railway asigna automáticamente)
