# 📋 Análisis del Sistema Original

Este documento contiene el análisis del sistema original para entender su funcionamiento y replicarlo en GAC.

---

## 🗄️ Estructura de Base de Datos

### Tablas Principales

#### 1. `admin`
```sql
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
)
```
- Almacena usuarios administradores
- Usa hash de contraseñas

#### 2. `settings`
```sql
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    value TEXT NOT NULL
)
```
- Almacena configuraciones del sistema
- Usa pares clave-valor

**Configuraciones Importantes:**
- `PAGE_TITLE` - Título de la página
- `HABILITAR_NETFLIX`, `HABILITAR_DISNEY`, etc. - Habilitar/deshabilitar plataformas
- `EMAIL_AUTH_ENABLED` - Habilitar autenticación por email
- `NETFLIX_1`, `NETFLIX_2`, etc. - Asuntos de email para Netflix
- `DISNEY_1`, `DISNEY_2`, etc. - Asuntos de email para Disney+
- `PRIME_1`, `PRIME_2`, etc. - Asuntos de email para Amazon Prime
- `FOOTER_TEXTO`, `FOOTER_CONTACTO`, `FOOTER_NUMERO_WHATSAPP` - Configuración del footer

#### 3. `email_servers`
```sql
CREATE TABLE email_servers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_name VARCHAR(50) NOT NULL,
    enabled TINYINT(1) NOT NULL DEFAULT 0,
    imap_server VARCHAR(100) NOT NULL,
    imap_port INT NOT NULL DEFAULT 993,
    imap_user VARCHAR(100) NOT NULL,
    imap_password VARCHAR(100) NOT NULL
)
```
- Almacena configuraciones de servidores IMAP
- Permite múltiples servidores de correo
- Cada servidor tiene su propia configuración IMAP

---

## 📧 Asuntos de Email por Plataforma

### Netflix
- `NETFLIX_1`: "Tu código de acceso temporal de Netflix"
- `NETFLIX_2`: "Importante: Cómo actualizar tu Hogar con Netflix"
- `NETFLIX_3`: "Netflix: Tu código de inicio de sesión"
- `NETFLIX_4`: "Completa tu solicitud de restablecimiento de contraseña"

### Disney+
- `DISNEY_1`: "Tu código de acceso único para Disney+"
- `DISNEY_2`, `DISNEY_3`, `DISNEY_4`: "Asunto 2", "Asunto 3", "Asunto 4" (placeholders)

### Amazon Prime Video
- `PRIME_1`: "amazon.com: Sign-in attempt"
- `PRIME_2`: "amazon.com: Intento de inicio de sesión"
- `PRIME_3`, `PRIME_4`: Placeholders

### Crunchyroll
- `CRUNCHYROLL_1`: "Crunchyroll: Código de acceso"
- `CRUNCHYROLL_2`: "Crunchyroll: Actualización de cuenta"
- `CRUNCHYROLL_3`: "Crunchyroll: Solicitud de inicio de sesión"
- `CRUNCHYROLL_4`: "Crunchyroll: Restablecimiento de contraseña"

### Paramount+
- `PARAMOUNT_1`: "Paramount Plus: Código de acceso"
- `PARAMOUNT_2`: "Paramount Plus: Actualización de cuenta"
- `PARAMOUNT_3`: "Paramount Plus: Solicitud de inicio de sesión"
- `PARAMOUNT_4`: "Paramount Plus: Restablecimiento de contraseña"

### ChatGPT
- `CHATGPT_1`: "Cambio de Contraseña"
- `CHATGPT_2`: "Cambio de Correo Electrónico"
- `CHATGPT_3`: "Cambio de Nombre"
- `CHATGPT_4`: "Cambio de Cuenta"

### Spotify
- `SPOTIFY_1`, `SPOTIFY_2`, `SPOTIFY_3`, `SPOTIFY_4`: Placeholders

### Canva
- `CANVA_1`, `CANVA_2`, `CANVA_3`, `CANVA_4`: Placeholders

---

## 🔄 Funcionamiento Inferido

### 1. Lectura de Emails (IMAP)
- El sistema se conecta a servidores IMAP configurados en `email_servers`
- Lee emails desde el buzón configurado
- Filtra emails por asunto usando los patrones almacenados en `settings`
- Extrae códigos de los emails usando expresiones regulares

### 2. Consulta de Códigos
- Usuario ingresa su email y selecciona una plataforma
- El sistema busca el código más reciente disponible para ese email y plataforma
- Marca el código como consumido después de entregarlo

### 3. Autenticación
- Sistema de login simple con `admin` table
- Usa hash de contraseñas

---

## 🎨 Estilos y Diseño

### CSS Global
- Fondo oscuro (`#212529` o `#141414`)
- Imagen de fondo: `/images/fondo/fondo.jpg`
- Efecto de parpadeo con animación CSS
- Logo centrado

### Estilos de Inicio
- Contenedores centrados con flexbox
- Tablas con ancho reducido (50% en desktop, 100% en mobile)
- Botones inline con gap entre ellos
- Formularios con ancho reducido (70% en desktop, 100% en mobile)

---

## 📝 Notas Importantes

1. **Archivos Codificados**: Los archivos PHP principales están codificados con ionCube, por lo que no se puede ver el código fuente directamente.

2. **Estructura Simple**: El sistema original parece tener una estructura más simple que la que estamos implementando en GAC.

3. **Múltiples Servidores IMAP**: El sistema permite configurar múltiples servidores IMAP, cada uno con su propia configuración.

4. **Asuntos Configurables**: Los asuntos de email están almacenados en la base de datos, permitiendo configurarlos sin modificar código.

5. **Plataformas Habilitables**: Cada plataforma puede habilitarse/deshabilitarse mediante settings.

---

## 🔍 Puntos Clave para Implementación

1. **Extracción de Códigos**: Necesitamos implementar expresiones regulares para extraer códigos de los emails según la plataforma.

2. **Filtrado por Asunto**: El sistema debe filtrar emails por asunto usando los patrones almacenados en `settings`.

3. **Múltiples Servidores**: GAC debe soportar múltiples cuentas de email (IMAP y Gmail).

4. **Consulta por Email y Plataforma**: El sistema debe buscar códigos disponibles para un email específico y plataforma.

5. **Marcado como Consumido**: Después de entregar un código, debe marcarse como consumido.

---

## 📚 Referencias

- Archivo SQL: `instalacion/instalacion.sql`
- Estilos: `code.pocoyoni.com/styles/`
- Estructura de carpetas: `code.pocoyoni.com/`

---

**Última actualización:** 2024