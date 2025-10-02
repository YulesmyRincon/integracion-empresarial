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

# (Opcional) Eliminar y crear la base de datos limpia
docker exec -it mysql_db mysql -uroot -proot -e "DROP DATABASE empresa_db; CREATE DATABASE empresa_db;"

# Importar la base de datos
Get-Content .\migrations\bd_empresa.sql | docker exec -i mysql_db mysql -uroot -proot empresa_db
```