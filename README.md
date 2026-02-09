# ChatGeocercano

Chat application built with Symfony 7.4, Doctrine ORM, and MySQL.

## Requisitos

- PHP >= 8.2
- Node.js >= 16
- Composer
- MySQL 8.0+

## Setup Local

### 1. Clonar el repositorio

```bash
git clone <repository-url>
cd ChatGeocercano
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Instalar dependencias Node.js

```bash
npm install
```

### 4. Configurar variables de entorno

```bash
# Crear archivo local (no se versionará)
cp .env.dev .env.local
```

Edita `.env.local` y configura tu base de datos local:

```dotenv
DATABASE_URL="mysql://your_user:your_password@127.0.0.1:3306/chatgeocercano?serverVersion=8&charset=utf8mb4"
APP_SECRET=your-secret-key
```

### 5. Crear base de datos e instalar/migrar

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 6. Compilar assets

```bash
npm run build
```

O en modo watch para desarrollo:

```bash
npm run watch
```

### 7. Iniciar servidor de desarrollo

```bash
# Opción 1: Symfony CLI
symfony server:start

# Opción 2: PHP Built-in server
php -S 127.0.0.1:8000 -t public
```

Accede a `http://localhost:8000`

## Desarrollo

### Comandos útiles

```bash
# Ver rutas disponibles
php bin/console debug:router

# Ver servicios disponibles
php bin/console debug:container

# Crear migración
php bin/console doctrine:migrations:generate

# Ver migraciones aplicadas
php bin/console doctrine:migrations:status

# Compilar assets en desarrollo
npm run dev

# Compilar assets en producción
npm run build
```

## Desplegar en Railway

Ver [RAILWAY_DEPLOYMENT.md](RAILWAY_DEPLOYMENT.md) para instrucciones completas.

### Resumen rápido:

1. Sube tu código a GitHub
2. Conecta el repositorio en [railway.app](https://railway.app)
3. Configura las variables de entorno en Railway:
   - `APP_SECRET` (genera una clave segura)
   - `DATABASE_URL` (Railway lo proporciona automáticamente)
4. Railway compilará y desplegará automáticamente

## Estructura del Proyecto

```
ChatGeocercano/
├── assets/              # JavaScript y CSS
├── bin/                 # Ejecutables
├── config/              # Configuración de Symfony
├── migrations/          # Migraciones de base de datos
├── public/              # Punto de entrada (index.php)
├── src/                 # Código fuente PHP
│   ├── Controller/      # Controladores
│   ├── Entity/          # Entidades Doctrine
│   ├── Form/            # Formularios
│   └── Repository/      # Repositorios Doctrine
├── templates/           # Plantillas Twig
└── vendor/              # Dependencias (generado)
```

## Tecnologías

- **Symfony** 7.4 - Framework PHP
- **Doctrine** 3.6 - ORM
- **Twig** 3.23 - Motor de plantillas
- **Webpack Encore** - Bundler de assets
- **MySQL** 8.0 - Base de datos

## Licencia

Proprietary
