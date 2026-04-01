# 🚀 Despliegue Automático SIMPLE - GAC

## 🎯 Opción MÁS FÁCIL: Git en el Servidor (Recomendado)

Esta es la forma **más simple** y **no requiere credenciales en GitHub**.

### ¿Cómo funciona?

1. **Subes tu código a GitHub** (como siempre)
2. **En el servidor cPanel**, haces `git pull` cuando quieras actualizar
3. **Opcional:** Configuras un cron job en cPanel para que haga `git pull` automáticamente

### ✅ Ventajas:
- ✅ No necesitas configurar secretos en GitHub
- ✅ No necesitas claves SSH complicadas
- ✅ Es más seguro (no expones credenciales)
- ✅ Funciona con cualquier hosting que tenga Git

### 📝 Pasos:

#### 1. Subir código a GitHub (solo una vez)

```powershell
cd SISTEMA_GAC
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/TU_USUARIO/TU_REPOSITORIO.git
git push -u origin main
```

#### 2. En cPanel, clonar el repositorio

**⚠️ IMPORTANTE:** Si tu repositorio es **privado**, necesitas autenticarte. Ver `SOLUCION_GIT_CPANEL.md` para solucionar errores de autenticación.

**Opción A: Repositorio PÚBLICO (más fácil)**

```bash
cd ~/public_html
git clone https://github.com/TU_USUARIO/TU_REPOSITORIO.git gac
cd gac
composer install --no-dev --optimize-autoloader
```

**Opción B: Repositorio PRIVADO (requiere token)**

1. Crea un Personal Access Token en GitHub (ver `SOLUCION_GIT_CPANEL.md`)
2. Luego ejecuta:
```bash
cd ~/public_html
git clone https://TU_TOKEN@github.com/TU_USUARIO/TU_REPOSITORIO.git gac
cd gac
composer install --no-dev --optimize-autoloader
```

**Opción B: Subir archivos manualmente y luego conectar Git**

1. Sube todos los archivos a `public_html/gac/` vía File Manager
2. En Terminal de cPanel:
```bash
cd ~/public_html/gac
git init
git remote add origin https://github.com/TU_USUARIO/TU_REPOSITORIO.git
git pull origin main
```

#### 3. Configurar Document Root

En cPanel → **Subdominios** o **Dominios**:
- Si usas subdominio `gac.tudominio.com`:
  - Document Root: `/home/usuario/public_html/gac/public`
- Si usas carpeta `tudominio.com/gac`:
  - Ya está listo (el `.htaccess` maneja las rutas)

#### 4. Actualizar cuando hagas cambios

**Opción Manual:**
```bash
# En Terminal de cPanel
cd ~/public_html/gac
git pull origin main
composer install --no-dev --optimize-autoloader
```

**Opción Automática (Cron Job):**

En cPanel → **Cron Jobs**:
- Frecuencia: Cada hora o cada 5 minutos
- Comando:
```bash
cd /home/usuario/public_html/gac && git pull origin main && composer install --no-dev --optimize-autoloader --quiet
```

---

## 🔄 Flujo de Trabajo Diario

### 1. Hacer cambios en tu computadora
```powershell
# Editar archivos...
```

### 2. Commit y Push
```powershell
git add .
git commit -m "Descripción de cambios"
git push origin main
```

### 3. En el servidor (automático o manual)

**Si configuraste Cron Job:** Se actualiza automáticamente cada X tiempo

**Si no:** Entras a Terminal de cPanel y ejecutas:
```bash
cd ~/public_html/gac
git pull origin main
composer install --no-dev --optimize-autoloader
```

---

## 🔐 Configurar Repositorio Privado

Si tu repositorio es **privado**, necesitas autenticarte. **Ver `CONFIGURAR_REPO_PRIVADO.md` para guía completa.**

Resumen rápido:

### Opción 1: Personal Access Token (Recomendado)

1. GitHub → **Settings** → **Developer settings** → **Personal access tokens** → **Tokens (classic)**
2. Genera un token con permisos `repo`
3. En cPanel Terminal:
```bash
cd ~/public_html/gac
git remote set-url origin https://TU_TOKEN@github.com/TU_USUARIO/TU_REPOSITORIO.git
```

### Opción 2: SSH Key (Más seguro)

1. Genera clave SSH en cPanel Terminal:
```bash
ssh-keygen -t rsa -b 4096 -C "cpanel-deploy"
# Presiona Enter para guardar en ~/.ssh/id_rsa
```

2. Muestra la clave pública:
```bash
cat ~/.ssh/id_rsa.pub
```

3. En GitHub → **Settings** → **SSH and GPG keys** → **New SSH key**
4. Pega la clave pública

5. Cambia la URL del repositorio:
```bash
cd ~/public_html/gac
git remote set-url origin git@github.com:TU_USUARIO/TU_REPOSITORIO.git
```

---

## 📋 Comparación de Opciones

| Método | Dificultad | Automático | Seguridad |
|--------|-----------|------------|-----------|
| **Git en servidor** | ⭐ Fácil | ⚠️ Con cron | ✅ Alta |
| **GitHub Actions + SSH** | ⭐⭐⭐ Media | ✅ Total | ⚠️ Media |
| **GitHub Actions + FTP** | ⭐⭐ Fácil | ✅ Total | ⚠️ Baja |

---

## ✅ Recomendación

**Para empezar:** Usa **Git en el servidor** (Opción más fácil)

**Cuando quieras automatizar más:** Configura un **Cron Job** en cPanel

**Si quieres despliegue instantáneo:** Usa **GitHub Actions** (pero requiere más configuración)

---

## 🆘 ¿Tu cPanel no tiene Terminal?

Algunos hostings no tienen Terminal habilitado. En ese caso:

1. **Usa GitHub Actions con FTP** (ver `DEPLOYMENT.md`)
2. **O sube archivos manualmente** vía File Manager cuando hagas cambios

---

## 📚 Más Información

- **Despliegue avanzado:** Ver `DEPLOYMENT.md`
- **Comandos Git:** Ver `COMANDOS_GIT.md`
