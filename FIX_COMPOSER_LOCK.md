# Instrucciones para ARREGLARSISTEMÁTICO

## Problema actual
El `composer.lock` tiene dependencias que requieren PHP 8.4, pero Railway usa PHP 8.2.

## Solución - Ejecuta LOCALMENTE en tu PowerShell:

```powershell
cd c:\xampp\htdocs\ChatGeocercano

# 1. Elimina el composer.lock antiguo
Remove-Item composer.lock -ErrorAction SilentlyContinue

# 2. Regenera para PHP 8.2
composer update --no-interaction --prefer-dist

# 3. Verifica que todo está bien
composer validate

# 4. Commit y push
git add composer.lock composer.json
git commit -m "Fix: Update dependencies for PHP 8.2 compatibility"
git push
```

## Alternativa rápida (una línea PowerShell)

```powershell
cd c:\xampp\htdocs\ChatGeocercano; rm composer.lock -ea 0; composer update --no-interaction --prefer-dist; git add composer.lock composer.json; git commit -m "Fix: PHP 8.2 dependency compatibility"; git push
```

## Qué cambié en composer.json:

- PHP: `>=8.2 <8.4` (limitado a 8.2-8.3)
- Doctrine ORM: `^2.15` (compatible con 8.2)
- Doctrine Bundle: `^2.9` (compatible con 8.2)
- Migrations Bundle: `^3.2` (compatible con 8.2)

## Después de actualizar

Railway debería hacer un nuevo build sin errores.
