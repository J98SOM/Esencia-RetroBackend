# 🔐 Sistema de Login con Endpoints Funcionales

## 📝 Descripción

He creado un sistema de login completamente funcional que integra:

- ✅ Página de login con interfaz moderna (Tailwind CSS)
- ✅ Dashboard protegido para usuarios autenticados
- ✅ Integración con los endpoints de autenticación (API)
- ✅ Autenticación con tokens Sanctum
- ✅ Gestión de sesión con localStorage
- ✅ Usuarios hardcodeados para pruebas

---

## 🚀 Usuarios de Prueba

### Admin
- **Email:** `admin@example.com`
- **Password:** `password123`
- **Rol:** admin

### Test User
- **Email:** `test@example.com`
- **Password:** `password123`
- **Rol:** user

---

## 📍 Rutas Disponibles

### Web
- `GET /` - Página de inicio
- `GET /login` - Página de login
- `GET /dashboard` - Dashboard protegido

### API
- `POST /api/register` - Registrar nuevo usuario
- `POST /api/login` - Autenticar usuario
- `POST /api/logout` - Cerrar sesión
- `GET /api/me` - Obtener datos del usuario autenticado
- `GET /api/users` - Listar usuarios
- `GET /api/users/{id}` - Obtener usuario específico
- `PUT /api/users/{id}` - Actualizar usuario
- `DELETE /api/users/{id}` - Eliminar usuario

---

## 🎯 Flujo de Autenticación

### 1. Login
1. El usuario ingresa email y contraseña en `/login`
2. El formulario envía una petición POST a `/api/login`
3. El servidor valida las credenciales y retorna un token
4. El token se guarda en `localStorage`
5. El usuario es redirigido a `/dashboard`

### 2. Dashboard
1. Al cargar `/dashboard`, se verifica que exista un token en localStorage
2. Si el token existe, se cargan los datos del usuario desde localStorage
3. Se muestra la información del usuario autenticado
4. El usuario puede copiar su token para usar en otros requests
5. Puede actualizar sus datos llamando a `/api/me`
6. Puede cerrar sesión, lo que elimina el token y lo redirige a `/login`

### 3. Logout
1. Al hacer logout, se envía una petición POST a `/api/logout`
2. El servidor revoca el token en la base de datos
3. El token se elimina de localStorage
4. El usuario es redirigido a `/login`

---

## 🎨 Características Técnicas

### Frontend (Blade + JavaScript vanilla)
- Uso de Tailwind CSS v4 para estilos modernos
- Validación en cliente
- Manejo de errores con alertas elegantes
- Spinners de carga en botones
- Precompletado de credenciales (para facilitar pruebas)
- Dark mode compatible

### Backend (Laravel)
- Controlador ApiAuthController con métodos bien separados
- Form Requests para validación robusta
- Eloquent API Resources para formateo de respuestas
- Middleware de autenticación Sanctum
- Gestión de errores HTTP adecuados
- Código formateado con Laravel Pint

### Base de Datos
- Migrations para estructura de tablas
- Seeder con usuarios de prueba
- Relación entre User y ApiToken

---

## 💡 Buenas Prácticas Implementadas

### Seguridad
- ✅ Contraseñas hasheadas con bcrypt
- ✅ Tokens de autenticación únicos y seguros
- ✅ Validación de email y contraseña en lado del servidor
- ✅ CSRF protection habilitado
- ✅ Tokens revocables en logout

### Código
- ✅ Comentarios PHPDoc en controlador
- ✅ Type hints explícitos en todos los métodos
- ✅ Separación de responsabilidades (Controller, Request, Resource)
- ✅ Código formateado con Pint
- ✅ Nombres descriptivos de variables y métodos

### UI/UX
- ✅ Interfaz responsive (mobile-friendly)
- ✅ Validación visual con alertas
- ✅ Botones con estados de carga
- ✅ Mensajes de error descriptivos
- ✅ Dark mode support
- ✅ Ejemplos de uso en el dashboard

---

## 🧪 Cómo Probar

### 1. Iniciar servidor
```bash
composer run dev  # o php artisan serve
```

### 2. Ir a la página de login
```
http://localhost:8000/login
```

### 3. Login automático (pre-completado)
- Los campos vienen pre-llenos con `admin@example.com` y `password123`
- Solo da click en "Iniciar Sesión"

### 4. Acceder al dashboard
- Después del login, serás redirigido automáticamente a `/dashboard`
- Verás tu información de usuario y token

### 5. Probar con cURL
```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password123"
  }'

# Usar el token retornado
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## 📂 Archivos Creados/Modificados

### Vistas
- `resources/views/auth/login.blade.php` - Página de login
- `resources/views/dashboard.blade.php` - Dashboard protegido

### Rutas
- `routes/web.php` - Rutas de login y dashboard
- `routes/api.php` - Endpoints de API (ya existía)

### Seeder
- `database/seeders/UserSeeder.php` - Usuarios de prueba
- `database/seeders/DatabaseSeeder.php` - Actualizado

### Configuración
- `bootstrap/app.php` - Middleware de Sanctum
- `config/auth.php` - Guard 'sanctum'

---

## 🔗 Token Sanctum

Los tokens de Sanctum:
- Se generan automáticamente al login
- Se guardan en la tabla `api_tokens`
- Se incluyen en requests con: `Authorization: Bearer {token}`
- Se revocan automáticamente al logout
- Tienen una relación con el usuario que los creó

---

## ✨ Próximos Pasos (Opcionales)

1. Agregar recuperación de contraseña
2. Agregar verificación de email
3. Agregar roles y permisos más complejos
4. Agregar rate limiting en login
5. Agregar 2FA (Two-Factor Authentication)
6. Crear página de registro (usa el endpoint existente)
7. Agregar protección CSRF en formularios web

