# Plataforma Web de Integración Empresarial

## 🚀 Requisitos
- Docker
- Docker Compose

## ▶️ Cómo ejecutar
```bash
# Levantar los contenedores
docker-compose up -d --build

# Instalar dependencias PHP
docker-compose exec app composer install

# Importar la base de datos
docker exec -i mysql_db mysql -uroot -proot integracion < migrations/schema.sql
