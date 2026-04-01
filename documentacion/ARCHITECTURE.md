# GAC - Arquitectura del Sistema

## 📐 Patrón Arquitectónico

**MVC + Service Layer + Repository Pattern**

```
Frontend (Views)
    ↓
Controllers (Routing, Validación)
    ↓
Services (Lógica de Negocio)
    ↓
Repositories (Acceso a Datos)
    ↓
Database (MySQL)
```

---

## 🏗️ Capas del Sistema

### 1. **Capa de Presentación (Views)**
- Templates PHP
- HTML/CSS/JavaScript
- Responsive Design

### 2. **Capa de Controladores (Controllers)**
- Manejo de requests HTTP
- Validación de entrada
- Respuestas al cliente

### 3. **Capa de Servicios (Services)**
- Lógica de negocio
- Reglas de negocio
- Orquestación de operaciones

### 4. **Capa de Repositorios (Repositories)**
- Acceso a base de datos
- Abstracción de datos
- Queries optimizadas

### 5. **Capa de Modelos (Models)**
- Entidades de datos
- Validaciones de modelo
- Relaciones entre entidades

---

## 🔄 Flujo de Datos

### Consulta de Código:
```
Usuario → CodeController@consult
    ↓
CodeService::getLatestCode()
    ↓
CodeRepository::findLatestAvailable()
    ↓
MySQL Query
    ↓
CodeService::markAsConsumed()
    ↓
Response JSON/View
```

### Lectura de Emails (Cron):
```
Cron Job → email_reader.py
    ↓
ImapService / GmailApiService
    ↓
EmailParserService
    ↓
CodeExtractorService
    ↓
CodeRepository::save()
    ↓
MySQL (Operativa + Warehouse)
```

---

## 🔐 Sistema de Seguridad

- **Autenticación:** Sessions PHP
- **Autorización:** Roles y Permisos
- **Cifrado:** Tokens OAuth cifrados
- **Validación:** Input sanitization
- **SQL Injection:** Prepared Statements

---

## 📊 Base de Datos

### Operativa (`gac_operational`)
- Datos activos
- Consultas rápidas
- Índices optimizados

### Warehouse (`gac_warehouse`)
- Histórico completo
- Reportes y análisis
- Estadísticas

---

## 🚀 Tecnologías

- **Backend:** PHP 7.4+ / 8.0+
- **Database:** MySQL 8.0+
- **Frontend:** HTML5, CSS3, JavaScript
- **Cron:** Python 3.9+
- **API:** RESTful

---

**Arquitectura escalable y mantenible**
