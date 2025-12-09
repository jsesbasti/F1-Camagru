COMPOSE-FILE=docker/docker-compose.yml
COMPOSE=docker compose -f $(COMPOSE-FILE)

DATA-DIR=db-data

all: configure build up

configure:
	@mkdir -p $(DATA-DIR)
	@chmod +777 $(DATA-DIR)

up:
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
	@rm -rf $(DATA-DIR)
	@docker system prune -af 2>/dev/null || true

re: fclean up

.PHONY: all up build stop down down-volumes clean fclean re 