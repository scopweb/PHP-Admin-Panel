#!/bin/bash

# Script de inicio rápido para PHP Admin Panel con Docker

echo "🚀 Iniciando PHP Admin Panel con Docker..."
echo ""

# Verificar si Docker está instalado
if ! command -v docker &> /dev/null; then
    echo "❌ Error: Docker no está instalado"
    echo "   Por favor instala Docker desde: https://docs.docker.com/get-docker/"
    exit 1
fi

# Detener contenedores existentes si los hay
echo "🛑 Deteniendo contenedores existentes..."
sudo docker compose down

# Construir y levantar los contenedores
echo ""
echo "🔨 Construyendo y levantando contenedores..."
sudo docker compose up -d --build

# Esperar a que MySQL esté listo
echo ""
echo "⏳ Esperando a que MySQL esté listo..."
sleep 15

# Verificar el estado de los contenedores
echo ""
echo "📊 Estado de los contenedores:"
sudo docker compose ps

echo ""
echo "✅ ¡Listo! Los servicios están corriendo:"
echo ""
echo "   📱 Aplicación:  http://localhost:8080"
echo "   🗄️  phpMyAdmin:  http://localhost:8081"
echo ""
echo "   👤 Usuario:     admin@google.com"
echo "   🔑 Contraseña:  Pt123456789"
echo ""
echo "   💾 Base de datos: admin_panel"
echo "   👤 DB Usuario:    admin"
echo "   🔑 DB Password:   secretpassword"
echo ""
echo "📝 Ver logs:          sudo docker compose logs -f"
echo "🛑 Detener:           sudo docker compose stop"
echo "🗑️  Eliminar todo:     sudo docker compose down -v"
echo ""
