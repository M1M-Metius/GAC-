# GAC - Estructura Completa de Carpetas

## 📁 Estructura del Proyecto

```
SISTEMA_GAC/
│
├── public/                          # Punto de entrada público
│   ├── index.php                   # Front Controller ✅
│   ├── .htaccess                   # Rewrite rules ✅
│   └── assets/                     # Recursos estáticos
│       ├── css/
│       ├── js/
│       └── images/
│
├── src/                            # Código fuente (PSR-4)
│   ├── Controllers/                # Controladores MVC
│   │   ├── AuthController.php
│   │   ├── CodeController.php
│   │   ├── AdminController.php
│   │   ├── DashboardController.php
│   │   ├── GmailController.php
│   │   ├── PlatformController.php
│   │   ├── UserController.php
│   │   └── RoleController.php
│   │
│   ├── Models/                     # Modelos de datos
│   │   ├── User.php
│   │   ├── Code.php
│   │   ├── EmailAccount.php
│   │   ├── Platform.php
│   │   ├── Role.php
│   │   └── Permission.php
│   │
│   ├── Services/                   # Lógica de negocio
│   │   ├── Email/
│   │   │   ├── ImapService.php
│   │   │   ├── GmailApiService.php
│   │   │   └── EmailParserService.php
│   │   ├── Code/
│   │   │   ├── CodeExtractorService.php
│   │   │   ├── CodeValidatorService.php
│   │   │   └── CodeConsumptionService.php
│   │   ├── Auth/
│   │   │   ├── AuthService.php
│   │   │   └── OAuthService.php
│   │   └── Dashboard/
│   │       └── StatisticsService.php
│   │
│   ├── Repositories/               # Acceso a datos
│   │   ├── CodeRepository.php
│   │   ├── EmailAccountRepository.php
│   │   ├── UserRepository.php
│   │   ├── PlatformRepository.php
│   │   └── StatisticsRepository.php
│   │
│   ├── Middleware/                 # Middleware
│   │   ├── AuthMiddleware.php
│   │   ├── RoleMiddleware.php
│   │   └── CorsMiddleware.php
│   │
│   ├── Helpers/                    # Utilidades
│   │   ├── Database.php
│   │   ├── Logger.php
│   │   ├── Validator.php
│   │   ├── Encryption.php
│   │   └── functions.php          # ✅ Creado
│   │
│   ├── Config/                     # Configuración
│   │   ├── AppConfig.php          # ✅ Creado
│   │   ├── DatabaseConfig.php
│   │   └── GmailConfig.php
│   │
│   └── Core/                       # Núcleo de la aplicación
│       ├── Application.php
│       ├── Router.php
│       └── Request.php
│
├── database/                       # Scripts de base de datos
│   ├── migrations/                 # Migraciones versionadas
│   │   ├── 001_create_roles_table.php
│   │   ├── 002_create_permissions_table.php
│   │   ├── 003_create_users_table.php
│   │   ├── 004_create_platforms_table.php
│   │   ├── 005_create_email_accounts_table.php
│   │   ├── 006_create_codes_table.php
│   │   └── 007_create_settings_table.php
│   │
│   ├── seeds/                      # Datos iniciales
│   │   ├── RolesSeeder.php
│   │   ├── PermissionsSeeder.php
│   │   ├── PlatformsSeeder.php
│   │   └── SettingsSeeder.php
│   │
│   └── schema.sql                  # ✅ Schema completo creado
│
├── cron/                           # Scripts Python para cron jobs
│   ├── email_reader.py             # Lectura automática emails
│   ├── code_extractor.py           # Extracción de códigos
│   ├── warehouse_sync.py           # Sincronización Data Warehouse
│   ├── requirements.txt            # ✅ Creado
│   └── config.py                   # ✅ Creado
│
├── views/                          # Vistas (Templates)
│   ├── layouts/
│   │   ├── header.php
│   │   ├── footer.php
│   │   ├── sidebar.php
│   │   └── admin_layout.php
│   │
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   │
│   ├── codes/
│   │   ├── consult.php
│   │   ├── index.php
│   │   ├── show.php
│   │   └── edit.php
│   │
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── codes/
│   │   │   ├── index.php
│   │   │   └── edit.php
│   │   ├── email_accounts/
│   │   │   ├── index.php
│   │   │   ├── create.php
│   │   │   └── edit.php
│   │   ├── platforms/
│   │   │   ├── index.php
│   │   │   └── edit.php
│   │   ├── settings/
│   │   │   └── index.php
│   │   ├── users/
│   │   │   ├── index.php
│   │   │   ├── create.php
│   │   │   └── edit.php
│   │   ├── roles/
│   │   │   ├── index.php
│   │   │   ├── create.php
│   │   │   └── edit.php
│   │   └── statistics/
│   │       ├── index.php
│   │       └── export.php
│   │
│   ├── gmail/
│   │   ├── connect.php
│   │   └── callback.php
│   │
│   └── profile/
│       ├── index.php
│       └── edit.php
│
├── api/                            # Endpoints API REST
│   ├── v1/
│   │   ├── codes.php
│   │   ├── email_accounts.php
│   │   └── statistics.php
│   └── .htaccess
│
├── tests/                          # Tests unitarios/integración
│   ├── Unit/
│   ├── Integration/
│   └── phpunit.xml
│
├── logs/                           # Logs de aplicación
│   └── .gitkeep                    # ✅ Creado
│
├── vendor/                         # Dependencias Composer (generado)
│
├── .env                            # Variables de entorno (NO en Git)
├── .env.example                    # ✅ Creado
├── .gitignore                      # ✅ Creado
├── composer.json                   # ✅ Creado
├── composer.lock                   # (generado)
├── README.md                       # ✅ Creado
└── INSTALLATION.md                 # ✅ Creado
```

---

## 📝 Estado de Creación

### ✅ Archivos Creados:
- `README.md`
- `.gitignore`
- `.env.example`
- `composer.json`
- `public/index.php`
- `public/.htaccess`
- `src/Config/AppConfig.php`
- `src/Helpers/functions.php`
- `database/schema.sql`
- `cron/requirements.txt`
- `cron/config.py`
- `logs/.gitkeep`
- `INSTALLATION.md`

### 📋 Carpetas a Crear (estructura base):
- `src/Controllers/`
- `src/Models/`
- `src/Services/Email/`
- `src/Services/Code/`
- `src/Services/Auth/`
- `src/Services/Dashboard/`
- `src/Repositories/`
- `src/Middleware/`
- `src/Helpers/` (parcialmente creado)
- `src/Config/` (parcialmente creado)
- `src/Core/`
- `database/migrations/`
- `database/seeds/`
- `cron/` (parcialmente creado)
- `views/` (todas las subcarpetas)
- `api/v1/`
- `tests/`
- `public/assets/`

---

## 🎯 Próximos Pasos

1. Crear estructura de carpetas vacías
2. Crear clases base (Models, Controllers, Services)
3. Implementar sistema de routing
4. Crear migraciones y seeders
5. Implementar autenticación básica
6. Crear vistas base

---

**Estructura lista para comenzar el desarrollo**
