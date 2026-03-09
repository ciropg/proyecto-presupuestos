# Proyecto Presupuestos

Aplicacion web construida con Laravel para gestionar presupuestos, catalogos de recursos y publicacion de presupuestos para consulta publica. El sistema distingue entre usuarios administradores y usuarios operativos, y permite trabajar con partidas jerarquicas dentro de cada presupuesto.

## Funcionalidades principales

- Autenticacion de usuarios con login, registro, recuperacion y cambio de contrasena.
- Roles `admin` y `user`.
- CRUD de presupuestos con codigo autogenerado, fecha, estado y costo total.
- Partidas jerarquicas con soporte para items padre e hijos.
- Recalculo automatico de subtotales e importe total del presupuesto.
- Publicacion de presupuestos para mostrarlos en la portada publica.
- Administracion de categorias, unidades y recursos desde el panel de administracion.

## Stack tecnico

- PHP 8.2
- Laravel 12
- Blade
- Laravel Breeze
- Vite
- Tailwind CSS 4
- Alpine.js
- MariaDB como configuracion base en `.env.example`

## Estados y permisos

### Presupuestos

Los presupuestos manejan estos estados:

- `draft`
- `published`
- `cancelled`

Cuando un presupuesto esta publicado y `is_published = true`, aparece en la portada (`/`) y puede consultarse de forma publica.

### Roles

- `admin`: puede acceder al panel `/admin`, gestionar catalogos y ver o administrar presupuestos.
- `user`: puede crear y administrar sus propios presupuestos.

## Estructura funcional

### Catalogos administrativos

El panel de administracion permite gestionar:

- Categorias
- Unidades
- Recursos

Los recursos se relacionan con una categoria y una unidad, y pueden reutilizarse al crear items dentro de un presupuesto.

### Presupuestos

Cada presupuesto incluye:

- `code`: codigo autogenerado con formato `BGT-000001`
- `title`
- `description`
- `budget_date`
- `status`
- `is_published`
- `total_cost`

### Items del presupuesto

Los items pueden:

- Ser partidas raiz o subpartidas
- Tomar un recurso existente del catalogo
- Tener unidad, cantidad, precio unitario y subtotal
- Ordenarse dentro del mismo nivel mediante `sort_order`

El total del presupuesto se recalcula en base a la jerarquia completa de items.

## Requisitos

- PHP 8.2 o superior
- Composer
- Node.js 20 o superior
- npm
- MariaDB o una base de datos compatible con la configuracion de Laravel

## Instalacion

1. Instala dependencias PHP:

```bash
composer install
```

2. Copia el archivo de entorno:

```bash
copy .env.example .env
```

3. Configura la conexion a base de datos en `.env`.

Valores de ejemplo incluidos en el repositorio:

```env
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=app
DB_USERNAME=root
DB_PASSWORD=
```

4. Genera la clave de la aplicacion:

```bash
php artisan key:generate
```

5. Ejecuta migraciones y seeders:

```bash
php artisan migrate --seed
```

6. Instala dependencias frontend:

```bash
npm install
```

7. Levanta el entorno de desarrollo:

```bash
composer run dev
```

Ese comando inicia:

- servidor Laravel
- listener de colas
- Vite en modo desarrollo

Si prefieres preparar todo de una vez despues de configurar `.env`, puedes usar:

```bash
composer run setup
```

## Usuario inicial

El seeder actual crea un usuario de prueba:

- Email: `test@example.com`
- Password: `password`
- Rol inicial: `user`

Ese usuario no accede al panel de administracion hasta que se le cambie el rol a `admin`.

Para promoverlo:

```bash
php artisan tinker
```

```php
App\Models\User::where('email', 'test@example.com')->update(['role' => 'admin']);
```

## Flujo recomendado de uso

1. Ingresar como administrador.
2. Crear categorias, unidades y recursos en `/admin`.
3. Crear un presupuesto desde `/budgets`.
4. Agregar items raiz y subitems.
5. Publicar el presupuesto cuando deba aparecer en la portada publica.

## Rutas principales

- `/`: listado publico de presupuestos publicados
- `/budgets/public/{budget}`: detalle publico de un presupuesto publicado
- `/dashboard`: panel general de usuario autenticado
- `/budgets`: gestion de presupuestos
- `/admin/dashboard`: panel administrativo
- `/admin/categories`: administracion de categorias
- `/admin/resources`: administracion de recursos
- `/admin/units`: administracion de unidades

## Scripts utiles

```bash
composer run dev
composer run test
npm run dev
npm run build
```

## Notas

- El seeder incluido solo crea un usuario de prueba; no carga categorias, unidades ni recursos.
- Los catalogos base deben cargarse desde el panel de administracion.
- La pagina principal muestra unicamente presupuestos publicados.
