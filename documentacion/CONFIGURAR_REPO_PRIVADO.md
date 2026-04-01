# 🔐 Configurar Repositorio Privado - GAC

## ✅ Respuestas Rápidas

**¿Puedo hacer commits y push desde mi computadora?**
✅ **SÍ** - Siempre que estés autenticado en GitHub

**¿Puede cPanel hacer pull automáticamente?**
✅ **SÍ** - Pero necesita autenticación (token o SSH)

---

## 🖥️ Desde tu Computadora (Windows)

### Opción 1: Usar GitHub Desktop (Más Fácil)

1. Descarga [GitHub Desktop](https://desktop.github.com/)
2. Inicia sesión con tu cuenta de GitHub
3. Clona tu repositorio
4. **Listo** - Puedes hacer commits y push sin problemas

### Opción 2: Usar Git con Credenciales Guardadas

#### Primera vez (configurar):

```powershell
cd SISTEMA_GAC

# Configurar Git
git config --global user.name "Tu Nombre"
git config --global user.email "tu@email.com"

# Configurar para guardar credenciales
git config --global credential.helper wincred

# Hacer push (te pedirá usuario y contraseña/token)
git push origin main
```

Cuando te pida credenciales:
- **Usuario:** Tu usuario de GitHub
- **Contraseña:** Tu **Personal Access Token** (no tu contraseña de GitHub)

#### Crear Personal Access Token (si no lo tienes):

1. GitHub → **Settings** → **Developer settings** → **Personal access tokens** → **Tokens (classic)**
2. **Generate new token (classic)**
3. Nombre: `mi-computadora`
4. Scope: **`repo`** (todos los permisos)
5. **Generate token**
6. **Copia el token** (solo se muestra una vez)

**Nota:** Windows guardará estas credenciales, así que solo lo harás una vez.

---

## 🖥️ Desde cPanel (Servidor)

Para que cPanel pueda hacer `git pull` en un repositorio privado, necesita autenticación.

### Opción 1: Personal Access Token en la URL (Recomendado)

#### Paso 1: Crear Token para cPanel

1. GitHub → **Settings** → **Developer settings** → **Personal access tokens** → **Tokens (classic)**
2. **Generate new token (classic)**
3. Nombre: `cpanel-deploy`
4. Scope: **`repo`** (todos los permisos)
5. **Generate token**
6. **Copia el token** (se ve así: `ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`)

#### Paso 2: Configurar en cPanel

En Terminal de cPanel:

```bash
cd ~/public_html/gac

# Reemplaza:
# - TU_TOKEN con el token que copiaste
# - TU_USUARIO con tu usuario de GitHub
# - TU_REPOSITORIO con el nombre de tu repositorio
git remote set-url origin https://TU_TOKEN@github.com/TU_USUARIO/TU_REPOSITORIO.git

# Verificar que funciona
git pull origin main
```

**Ejemplo real:**
```bash
git remote set-url origin https://ghp_abc123xyz456@github.com/miusuario/sistema-gac.git
git pull origin main
```

#### Paso 3: Configurar Cron Job (Opcional - Automático)

En cPanel → **Cron Jobs**:

- **Frecuencia:** Cada 5 minutos, cada hora, etc.
- **Comando:**
```bash
cd /home/usuario/public_html/gac && git pull origin main && composer install --no-dev --optimize-autoloader --quiet
```

**Nota:** El token está en la URL, así que el cron job funcionará automáticamente.

---

### Opción 2: SSH Key (Más Seguro)

#### Paso 1: Generar Clave SSH en cPanel

En Terminal de cPanel:

```bash
ssh-keygen -t rsa -b 4096 -C "cpanel-deploy"
# Presiona Enter para todas las preguntas (usa ubicación por defecto)
```

#### Paso 2: Ver la Clave Pública

```bash
cat ~/.ssh/id_rsa.pub
```

Copia todo el contenido (empieza con `ssh-rsa`).

#### Paso 3: Agregar Clave a GitHub

1. GitHub → **Settings** → **SSH and GPG keys**
2. **New SSH key**
3. Título: `cpanel-deploy`
4. Key: Pega la clave pública
5. **Add SSH key**

#### Paso 4: Cambiar URL a SSH

En Terminal de cPanel:

```bash
cd ~/public_html/gac

# Cambiar de HTTPS a SSH
git remote set-url origin git@github.com:TU_USUARIO/TU_REPOSITORIO.git

# Probar conexión
ssh -T git@github.com
# Debería decir: "Hi TU_USUARIO! You've successfully authenticated..."

# Hacer pull
git pull origin main
```

---

## 🔄 Flujo de Trabajo Completo

### 1. Hacer cambios en tu computadora

```powershell
cd SISTEMA_GAC

# Editar archivos...
# ...

# Agregar cambios
git add .

# Commit
git commit -m "Descripción de los cambios"

# Push (se sube a GitHub)
git push origin main
```

### 2. En cPanel (Automático o Manual)

**Si configuraste Cron Job:** Se actualiza automáticamente cada X tiempo

**Si no:** Entras a Terminal de cPanel y ejecutas:

```bash
cd ~/public_html/gac
git pull origin main
composer install --no-dev --optimize-autoloader
```

---

## 🔐 Seguridad

### Tokens vs SSH

| Método | Seguridad | Facilidad | Recomendado para |
|--------|-----------|-----------|------------------|
| **Token en URL** | ⚠️ Media | ✅ Muy fácil | Desarrollo, pruebas |
| **SSH Key** | ✅ Alta | ⚠️ Media | Producción |

### Buenas Prácticas

1. **No compartas tokens** - Son como contraseñas
2. **No subas tokens a Git** - Ya están en `.gitignore`
3. **Revoca tokens viejos** - Si los pierdes o comprometes
4. **Usa tokens diferentes** - Uno para tu PC, otro para cPanel
5. **Revisa permisos** - Solo da permisos necesarios (`repo` es suficiente)

---

## ✅ Checklist

### Desde tu Computadora:
- [ ] Git configurado con usuario y email
- [ ] Credenciales guardadas (o GitHub Desktop instalado)
- [ ] Puedes hacer `git push` sin problemas

### Desde cPanel:
- [ ] Token creado en GitHub
- [ ] URL del repositorio actualizada con token
- [ ] `git pull` funciona correctamente
- [ ] Cron job configurado (opcional)

---

## 🆘 Solución de Problemas

### Error: "Authentication failed"

- Verifica que el token sea correcto
- Verifica que el token tenga permisos `repo`
- Verifica que la URL tenga el formato correcto: `https://TOKEN@github.com/USER/REPO.git`

### Error: "Permission denied (publickey)"

- Verifica que la clave SSH esté agregada en GitHub
- Verifica que estés usando la URL SSH: `git@github.com:USER/REPO.git`

### El cron job no funciona

- Verifica que el comando esté en una sola línea
- Verifica que las rutas sean absolutas (`/home/usuario/...`)
- Revisa los logs de cron en cPanel

---

## 📝 Resumen

✅ **Sí puedes hacer commits y push** desde tu computadora (con autenticación)

✅ **Sí puede cPanel hacer pull** (con token o SSH configurado)

✅ **Puedes tener el repositorio privado** sin problemas

**Recomendación:** Usa **Personal Access Token** para empezar (más fácil). Si quieres más seguridad después, cambia a SSH.

---

**¿Necesitas ayuda con algún paso específico?** Dime qué método quieres usar y te guío paso a paso.
