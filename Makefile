COMPOSE=docker compose
COMPOSE-FILE=docker-compose.yml

DB_DIR=$(PWD)/data/

export HOST_UID=${UID}
export HOST_GID=${GID}

all: build up

up:
	@mkdir -p $(DB_DIR)
	@chmod -R 777 $(DB_DIR)
	@$(COMPOSE) up -d

build:
	@echo "🔧 Build (todos los servicios)..."
	@$(COMPOSE) build 

stop:
	@echo "🔛 Deteniendo servicios..."
	@$(COMPOSE) stop

down: 
	@echo "🧹 Bajando contenedores..."
	@$(COMPOSE) down

down-volumes:
	@echo "🧹 Bajando contenedores..."
	@$(COMPOSE) down -v	

fclean: down-volumes clean
	@echo "🔥 Borrando todas las imágenes de Docker (esto es destructivo)..."
	@rm -rf $(BD_DIR)
	@docker system prune -af 2>/dev/null || true

re: fclean up

.PHONY: all up build stop down down-volumes clean fclean re 