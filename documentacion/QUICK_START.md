# ⚡ Inicio Rápido - GAC

## 🚀 Ejecutar el Sistema

### Opción 1: Servidor PHP Built-in (Desarrollo)

```powershell
cd SISTEMA_GAC
php -S localhost:8000 -t public public/router.php
```

Luego abre: **http://localhost:8000**

### Opción 2: Apache/XAMPP (Producción)

1. Copia `SISTEMA_GAC` a `C:\xampp\htdocs\gac`
2. Configura el Virtual Host apuntando a `public/`
3. Abre: **http://localhost/gac**

---

## 📦 Instalación Inicial

```powershell
# 1. Instalar dependencias
composer install --ignore-platform-req=ext-imap

# 2. Crear .env
Copy-Item .env.example .env

# 3. Editar .env con tus configuraciones
notepad .env
```

---

## 🔧 Solución del Error 404

Si ves **404 Not Found** al acceder a `http://localhost:8000`:

### ✅ Solución: Usar el router

```powershell
php -S localhost:8000 -t public public/router.php
```

**Nota:** El servidor PHP built-in no procesa `.htaccess`, por eso necesitas el `router.php`.

---

## 📚 Más Información

- **Despliegue:** Ver `DEPLOYMENT.md`
- **Git:** Ver `COMANDOS_GIT.md`
- **Arquitectura:** Ver `ARCHITECTURE.md`
