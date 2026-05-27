-- ============================================
-- GAC - Script de Verificación de Usuario Admin
-- ============================================
-- Ejecuta este script para verificar que el usuario admin existe

USE pocoavbb_gac;

-- Verificar si el usuario admin existe
SELECT 
    id,
    username,
    email,
    role_id,
    active,
    last_login,
    created_at
FROM users 
WHERE username = 'admin';

-- Verificar que el role_id 1 (SUPER_ADMIN) existe
SELECT 
    id,
    name,
    display_name
FROM roles 
WHERE id = 1;

-- Si el usuario no existe, ejecutar seed_admin_user.sql primero
-- Si el role_id 1 no existe, ejecutar seed_settings.sql primero
