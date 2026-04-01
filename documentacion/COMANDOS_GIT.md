# 📦 Comandos Git - GAC

## 🚀 Configuración Inicial

### 1. Inicializar Git
```powershell
cd SISTEMA_GAC
git init
```

### 2. Agregar todos los archivos
```powershell
git add .
```

### 3. Primer commit
```powershell
git commit -m "Initial commit: Sistema GAC v2.0"
```

### 4. Conectar con GitHub
```powershell
# Reemplaza TU_USUARIO y TU_REPOSITORIO con tus datos
git remote add origin https://github.com/TU_USUARIO/TU_REPOSITORIO.git
```

### 5. Cambiar rama a main
```powershell
git branch -M main
```

### 6. Subir código
```powershell
git push -u origin main
```

---

## 📝 Flujo de Trabajo Diario

### Ver estado de cambios
```powershell
git status
```

### Agregar archivos específicos
```powershell
git add archivo.php
git add carpeta/
```

### Agregar todos los cambios
```powershell
git add .
```

### Hacer commit
```powershell
git commit -m "Descripción de los cambios"
```

### Subir cambios (despliega automáticamente)
```powershell
git push origin main
```

---

## 🔄 Comandos Útiles

### Ver historial de commits
```powershell
git log --oneline
```

### Ver diferencias
```powershell
git diff
```

### Deshacer cambios no guardados
```powershell
git checkout -- archivo.php
```

### Actualizar desde GitHub
```powershell
git pull origin main
```

### Crear nueva rama
```powershell
git checkout -b nombre-rama
```

### Cambiar de rama
```powershell
git checkout main
```

---

## ⚠️ Archivos que NO se suben (están en .gitignore)

- `.env` (configuración sensible)
- `vendor/` (se instala en el servidor)
- `logs/*.log` (archivos de log)
- `node_modules/` (si usas Node.js)

---

## 🚨 Si algo sale mal

### Deshacer último commit (mantener cambios)
```powershell
git reset --soft HEAD~1
```

### Deshacer último commit (eliminar cambios)
```powershell
git reset --hard HEAD~1
```

### Forzar push (¡CUIDADO!)
```powershell
git push -f origin main
```

---

**Recuerda:** Cada `git push` activa el despliegue automático si está configurado. ✅
