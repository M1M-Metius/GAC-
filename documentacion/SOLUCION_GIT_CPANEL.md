# 🔧 Solución: Error Git en cPanel

## ❌ Error que estás viendo:

```
fatal: no se pudo leer el nombre de usuario para 'https://github.com': 
No existe dicho dispositivo o dirección
```

Esto significa que Git necesita autenticarse con GitHub.

---

## ✅ Soluciones (de más fácil a más compleja)

### **Solución 1: Usar Personal Access Token (Recomendado)**

Esta es la forma más fácil y segura.

#### Paso 1: Crear Token en GitHub

1. Ve a GitHub → Tu perfil → **Settings**
2. **Developer settings** → **Personal access tokens** → **Tokens (classic)**
3. Click en **Generate new token (classic)**
4. Dale un nombre: `cpanel-deploy`
5. Selecciona el scope: **`repo`** (marca la casilla completa)
6. Click en **Generate token**
7. **¡IMPORTANTE!** Copia el token inmediatamente (solo lo verás una vez)
   - Se ve así: `ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`

#### Paso 2: Usar el Token en cPanel

En Terminal de cPanel, ejecuta:

```bash
cd ~/public_html/gac

# Reemplaza TU_TOKEN con el token que copiaste
# Reemplaza TU_USUARIO y TU_REPOSITORIO con tus datos
git remote set-url origin https://TU_TOKEN@github.com/TU_USUARIO/TU_REPOSITORIO.git

# Ahora intenta clonar o hacer pull
git pull origin main
```

**Ejemplo real:**
```bash
git remote set-url origin https://ghp_abc123xyz@github.com/miusuario/sistema-gac.git
```

---

### **Solución 2: Hacer el Repositorio Público (Si no es sensible)**

Si tu código no es confidencial, puedes hacer el repositorio público:

1. Ve a tu repositorio en GitHub
2. **Settings** → **General** → Scroll hasta **Danger Zone**
3. Click en **Change visibility** → **Make public**

Luego en cPanel:
```bash
cd ~/public_html/gac
git pull origin main
```

---

### **Solución 3: Usar SSH (Más seguro, pero más complejo)**

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

Copia todo el contenido (empieza con `ssh-rsa` y termina con tu email).

#### Paso 3: Agregar Clave a GitHub

1. GitHub → **Settings** → **SSH and GPG keys**
2. Click en **New SSH key**
3. Título: `cpanel-deploy`
4. Key: Pega la clave pública que copiaste
5. Click en **Add SSH key**

#### Paso 4: Cambiar URL del Repositorio

En Terminal de cPanel:
```bash
cd ~/public_html/gac

# Cambiar de HTTPS a SSH
git remote set-url origin git@github.com:TU_USUARIO/TU_REPOSITORIO.git

# Probar conexión
ssh -T git@github.com
# Debería decir: "Hi TU_USUARIO! You've successfully authenticated..."

# Ahora hacer pull
git pull origin main
```

---

### **Solución 4: Configurar Git Credential Helper**

Si ninguna de las anteriores funciona, configura Git para guardar credenciales:

```bash
# Configurar Git
git config --global user.name "Tu Nombre"
git config --global user.email "tu@email.com"

# Configurar credential helper (solo para este repositorio)
cd ~/public_html/gac
git config credential.helper store

# Intentar pull (te pedirá usuario y contraseña/token)
git pull origin main
# Usuario: TU_USUARIO
# Contraseña: TU_TOKEN (el Personal Access Token)
```

---

## 🎯 Recomendación

**Para empezar rápido:** Usa **Solución 1 (Personal Access Token)**

**Para mayor seguridad:** Usa **Solución 3 (SSH)**

---

## 🔍 Verificar que Funciona

Después de aplicar cualquier solución, verifica:

```bash
cd ~/public_html/gac
git pull origin main
```

Si funciona, verás algo como:
```
Updating abc123..def456
Fast-forward
 archivo.php | 10 +++++-----
 1 file changed, 5 insertions(+), 5 deletions(-)
```

---

## 🆘 Si Nada Funciona

### Verificar Conexión a GitHub

```bash
# Probar si el servidor puede acceder a GitHub
ping github.com

# O probar HTTPS
curl -I https://github.com
```

### Verificar que Git está Instalado

```bash
which git
git --version
```

### Verificar Permisos

```bash
# Asegúrate de tener permisos en la carpeta
cd ~/public_html
chmod -R 755 gac
```

---

## 📝 Notas Importantes

- **El token es como una contraseña:** No lo compartas ni lo subas a Git
- **Si el token se compromete:** Revócalo en GitHub y crea uno nuevo
- **Los tokens no expiran:** A menos que los revoques manualmente
- **Para repositorios privados:** Siempre necesitas autenticación (token o SSH)

---

## ✅ Checklist

- [ ] Token creado en GitHub (Solución 1) o repositorio público (Solución 2)
- [ ] URL del repositorio actualizada con token o SSH
- [ ] `git pull` funciona correctamente
- [ ] Composer install ejecutado después del pull

---

**¿Necesitas ayuda con algún paso específico?** Dime cuál solución quieres usar y te guío paso a paso.
