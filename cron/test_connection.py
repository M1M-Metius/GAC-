#!/usr/bin/env python3
"""
GAC - Script de Prueba de Conexión
Verifica que todas las conexiones y configuraciones estén correctas
"""

import sys
import os

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from cron.database import Database
from cron.repositories import EmailAccountRepository, PlatformRepository, SettingsRepository
from cron.config import DB_CONFIG, WAREHOUSE_DB_CONFIG

def test_database_connections():
    """Probar conexiones a bases de datos"""
    print("=" * 60)
    print("Probando conexiones a bases de datos...")
    print("=" * 60)
    
    # BD Operativa
    try:
        conn = Database.get_connection()
        cursor = conn.cursor()
        cursor.execute("SELECT 1")
        cursor.close()
        print("✓ Conexión a BD operativa: OK")
        print(f"  - Host: {DB_CONFIG['host']}")
        print(f"  - Database: {DB_CONFIG['database']}")
    except Exception as e:
        print(f"✗ Error en BD operativa: {e}")
        return False
    
    # BD Warehouse
    try:
        conn = Database.get_warehouse_connection()
        cursor = conn.cursor()
        cursor.execute("SELECT 1")
        cursor.close()
        print("✓ Conexión a BD warehouse: OK")
        print(f"  - Host: {WAREHOUSE_DB_CONFIG['host']}")
        print(f"  - Database: {WAREHOUSE_DB_CONFIG['database']}")
    except Exception as e:
        print(f"✗ Error en BD warehouse: {e}")
        return False
    
    return True

def test_repositories():
    """Probar repositorios"""
    print("\n" + "=" * 60)
    print("Probando repositorios...")
    print("=" * 60)
    
    # Email Accounts
    try:
        accounts = EmailAccountRepository.find_all_enabled()
        print(f"✓ EmailAccountRepository: {len(accounts)} cuenta(s) encontrada(s)")
        for acc in accounts[:3]:  # Mostrar primeras 3
            print(f"  - {acc['email']} ({acc['type']})")
    except Exception as e:
        print(f"✗ Error en EmailAccountRepository: {e}")
    
    # Platforms
    try:
        platforms = ['netflix', 'disney', 'prime']
        for platform in platforms:
            p = PlatformRepository.find_by_name(platform)
            if p:
                print(f"✓ Plataforma '{platform}': {p['display_name']} (enabled: {p['enabled']})")
            else:
                print(f"✗ Plataforma '{platform}': No encontrada")
    except Exception as e:
        print(f"✗ Error en PlatformRepository: {e}")
    
    # Settings
    try:
        netflix_enabled = SettingsRepository.is_platform_enabled('netflix')
        print(f"✓ SettingsRepository: Netflix habilitado = {netflix_enabled}")
        
        subjects = SettingsRepository.get_email_subjects_for_platform('netflix')
        print(f"  - Asuntos para Netflix: {len(subjects)} patrón(es)")
    except Exception as e:
        print(f"✗ Error en SettingsRepository: {e}")

def main():
    """Función principal"""
    print("\n" + "=" * 60)
    print("GAC - Test de Conexión y Configuración")
    print("=" * 60 + "\n")
    
    # Probar conexiones
    if not test_database_connections():
        print("\n✗ Falló la prueba de conexiones. Verifica la configuración.")
        sys.exit(1)
    
    # Probar repositorios
    test_repositories()
    
    # Cerrar conexiones
    Database.close_connections()
    
    print("\n" + "=" * 60)
    print("✓ Todas las pruebas completadas")
    print("=" * 60 + "\n")

if __name__ == '__main__':
    main()