# GAC - Gestor Automatizado de Códigos

**Versión:** 2.0.0  
**Sistema:** Gestor Automatizado de Códigos  
**Descripción:** Sistema web PHP para gestión automatizada de códigos de acceso para servicios de streaming

---

## 📋 Características Principales

- ✅ Extracción automática de códigos desde emails (IMAP + Gmail API)
- ✅ Gestión completa de códigos de acceso
- ✅ Sistema de roles y permisos
- ✅ Dashboard con estadísticas
- ✅ Data Warehouse para histórico
- ✅ API REST completa

---

## 🏗️ Arquitectura

- **Patrón:** MVC + Service Layer + Repository Pattern
- **PHP:** 7.4+ / 8.0+ (Recomendado 8.1+)
- **Base de Datos:** MySQL 8.0+
- **Frontend:** HTML5, CSS3, JavaScript
- **Background Jobs:** Python 3.9+

---

## 📁 Estructura del Proyecto

```
SISTEMA_GAC/
├── public/              # Punto de entrada público
├── src/                 # Código fuente (PSR-4)
├── database/            # Scripts de BD
├── cron/                # Scripts Python
├── views/               # Vistas/Templates
├── api/                 # Endpoints API REST
├── tests/               # Tests
├── logs/                # Logs de aplicación
└── vendor/              # Dependencias Composer
```

---

## 🚀 Instalación

Ver `documentacion/INSTALLATION.md` para instrucciones detalladas.

---

## 📚 Documentación

Toda la documentación se encuentra en la carpeta `documentacion/`:

- `ARCHITECTURE.md` - Arquitectura técnica completa
- `CRITERIOS_DESARROLLO.md` - Criterios y estándares de desarrollo
- `DEPLOYMENT.md` - Guía de despliegue avanzado
- `DEPLOYMENT_SIMPLE.md` - Guía de despliegue simple
- `CONFIGURAR_REPO_PRIVADO.md` - Configurar repositorio privado
- `SOLUCION_GIT_CPANEL.md` - Solución de problemas con Git en cPanel
- `INSTALLATION.md` - Guía de instalación
- `QUICK_START.md` - Inicio rápido
- `EJECUTAR.md` - Guía de ejecución
- `COMANDOS_GIT.md` - Comandos Git básicos
- Y más...

---

**Desarrollado con ❤️ para gestión eficiente de códigos de acceso**
