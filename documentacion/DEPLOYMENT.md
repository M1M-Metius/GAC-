# 🚀 Guía de Despliegue Automático - GAC

Esta guía te ayudará a configurar despliegues automáticos desde GitHub a tu servidor cPanel.

## 📋 Opciones de Despliegue

### Opción 1: Despliegue vía SSH (Recomendado)
Usa GitHub Actions con SSH para desplegar directamente al servidor.

### Opción 2: Despliegue vía FTP
Usa GitHub Actions con FTP para desplegar a través del protocolo FTP.

---

## 🔧 Configuración Inicial

### Paso 1: Crear Repositorio en GitHub

1. Ve a [GitHub](https://github.com) y crea un nuevo repositorio
2. Nombre sugerido: `sistema-gac` o `gac-codes-manager`
3. **NO** inicialices con README, .gitignore o licencia (ya los tenemos)

### Paso 2: Inicializar Git en tu Proyecto

```powershell
cd SISTEMA_GAC
git init
git add .
git commit -m "Initial commit: Sistema GAC"
git branch -M main
git remote add origin https://github.com/TU_USUARIO/TU_REPOSITORIO.git
git push -u origin main
```

---

## 🔐 Configuración de Secretos en GitHub

### Para SSH (Opción 1):

1. Ve a tu repositorio en GitHub
2. **Settings** → **Secrets and variables** → **Actions**
3. Agrega los siguientes secretos:

| Secreto | Descripción | Ejemplo |
|---------|-------------|---------|
| `CPANEL_SSH_KEY` | Tu clave privada SSH | `-----BEGIN RSA PRIVATE KEY-----...` |
| `CPANEL_HOST` | IP o dominio del servidor | `tu-servidor.com` o `192.168.1.1` |
| `CPANEL_USER` | Usuario SSH | `usuario_cpanel` |
| `CPANEL_DEPLOY_PATH` | Ruta donde está el proyecto | `/home/usuario/public_html/gac` |
| `CPANEL_URL` | URL pública de la app | `https://gac.tudominio.com` |

### Para FTP (Opción 2):

1. Ve a tu repositorio en GitHub
2. **Settings** → **Secrets and variables** → **Actions**
3. Agrega los siguientes secretos:

| Secreto | Descripción | Ejemplo |
|---------|-------------|---------|
| `FTP_SERVER` | Servidor FTP | `ftp.tudominio.com` |
| `FTP_USERNAME` | Usuario FTP | `usuario_ftp` |
| `FTP_PASSWORD` | Contraseña FTP | `tu_contraseña` |
| `FTP_REMOTE_DIR` | Directorio remoto | `/public_html/gac` |

---

## 🔑 Generar Clave SSH (Solo para Opción 1)

### En Windows (PowerShell):

```powershell
# Generar clave SSH
ssh-keygen -t rsa -b 4096 -C "github-deploy"

# La clave se guardará en: C:\Users\TU_USUARIO\.ssh\id_rsa
# Copiar la clave PRIVADA (id_rsa) completa para CPANEL_SSH_KEY
Get-Content C:\Users\TU_USUARIO\.ssh\id_rsa

# Copiar la clave PÚBLICA (id_rsa.pub) y agregarla al servidor
Get-Content C:\Users\TU_USUARIO\.ssh\id_rsa.pub
```

### Agregar Clave Pública al Servidor:

1. Conéctate a tu servidor vía SSH
2. Edita `~/.ssh/authorized_keys`:
   ```bash
   nano ~/.ssh/authorized_keys
   ```
3. Pega tu clave pública (id_rsa.pub)
4. Guarda y cierra (`Ctrl+X`, luego `Y`, luego `Enter`)
5. Ajusta permisos:
   ```bash
   chmod 600 ~/.ssh/authorized_keys
   chmod 700 ~/.ssh
   ```

---

## 📝 Configurar Workflow

### Para usar SSH (Opción 1):

El archivo `.github/workflows/deploy.yml` ya está configurado. Solo necesitas:
1. Agregar los secretos en GitHub
2. Hacer commit y push

### Para usar FTP (Opción 2):

1. Renombra `.github/workflows/deploy-simple.yml` a `deploy.yml`:
   ```powershell
   Move-Item .github\workflows\deploy-simple.yml .github\workflows\deploy.yml
   ```
2. O edita `deploy.yml` y cambia el contenido por el de `deploy-simple.yml`
3. Agrega los secretos FTP en GitHub

---

## 🚀 Flujo de Trabajo

### Desarrollo Normal:

1. **Hacer cambios** en tu código local
2. **Commit:**
   ```powershell
   git add .
   git commit -m "Descripción de los cambios"
   ```
3. **Push:**
   ```powershell
   git push origin main
   ```
4. **GitHub Actions se ejecuta automáticamente** y despliega a producción

### Ver Estado del Despliegue:

1. Ve a tu repositorio en GitHub
2. Click en la pestaña **Actions**
3. Verás el estado de cada despliegue (✅ éxito, ❌ error)

---

## ⚙️ Configuración del Servidor

### Estructura de Carpetas en cPanel:

```
/home/usuario/
└── public_html/
    └── gac/                    # Tu proyecto aquí
        ├── public/             # Document root
        ├── src/
        ├── vendor/
        ├── .env                # Configuración (NO se sube a Git)
        └── ...
```

### Configurar Document Root en cPanel:

1. Ve a **cPanel** → **Subdominios** o **Dominios**
2. Si usas subdominio `gac.tudominio.com`:
   - Document Root: `/home/usuario/public_html/gac/public`
3. Si usas carpeta `tudominio.com/gac`:
   - El `.htaccess` en `public/` manejará las rutas

### Archivo .env en Producción:

**IMPORTANTE:** El archivo `.env` NO se sube a GitHub por seguridad.

1. Crea manualmente `.env` en el servidor:
   ```bash
   cd /home/usuario/public_html/gac
   nano .env
   ```
2. Copia el contenido de `.env.example` y ajusta los valores:
   ```env
   APP_NAME=GAC
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://gac.tudominio.com
   
   DB_HOST=localhost
   DB_NAME=gac_operational
   DB_USER=usuario_db
   DB_PASS=contraseña_db
   
   # ... resto de configuraciones
   ```
3. Guarda y establece permisos:
   ```bash
   chmod 600 .env
   ```

---

## 🔍 Solución de Problemas

### Error: "Permission denied (publickey)"

- Verifica que `CPANEL_SSH_KEY` tenga la clave privada completa
- Verifica que la clave pública esté en `~/.ssh/authorized_keys` del servidor
- Verifica permisos: `chmod 600 ~/.ssh/authorized_keys`

### Error: "Connection refused"

- Verifica que `CPANEL_HOST` sea correcto
- Verifica que el puerto SSH (22) esté abierto
- Algunos cPanel usan puerto 2222, ajusta en el workflow si es necesario

### Error: "Composer not found"

- Instala Composer en el servidor:
  ```bash
  curl -sS https://getcomposer.org/installer | php
  mv composer.phar /usr/local/bin/composer
  ```

### Los cambios no se reflejan:

- Limpia la caché del navegador (`Ctrl+F5`)
- Verifica que el despliegue se completó exitosamente en GitHub Actions
- Verifica los logs del servidor

### Archivos no se suben:

- Verifica que los archivos no estén en `.gitignore`
- Verifica que `FTP_REMOTE_DIR` sea la ruta correcta
- Verifica permisos de escritura en el servidor

---

## 📚 Recursos Adicionales

- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [cPanel SSH Access](https://support.cpanel.net/hc/en-us/articles/360052624714-How-to-Enable-SSH-Access)
- [Composer Installation](https://getcomposer.org/download/)

---

## ✅ Checklist de Despliegue

- [ ] Repositorio creado en GitHub
- [ ] Git inicializado y código subido
- [ ] Secretos configurados en GitHub
- [ ] Clave SSH generada y agregada al servidor (si usas SSH)
- [ ] Archivo `.env` creado en el servidor
- [ ] Document root configurado en cPanel
- [ ] Workflow configurado (deploy.yml o deploy-simple.yml)
- [ ] Primer despliegue exitoso
- [ ] Verificar que la aplicación funciona en producción

---

**¡Listo!** Ahora cada vez que hagas `git push`, tu aplicación se desplegará automáticamente. 🎉
