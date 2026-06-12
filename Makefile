COMPOSE-FILE=docker/docker-compose.yml
COMPOSE=docker compose -f $(COMPOSE-FILE)

all: build up

up:
	@$(COMPOSE) up -d

up-logs:
	@$(COMPOSE) up

build:
	@echo "🔧 Build (todos los servicios)..."
	@$(COMPOSE) build 

stop:
	@echo "🔛 Deteniendo servicios..."
	@$(COMPOSE) stop

down: 
	@echo "🧹 Bajando contenedores..."
	@$(COMPOSE) down

clean:
	@echo "🔥 Borrando todas las imágenes de Docker (esto es destructivo)..."
	@docker rmi $$(docker images -aq)

down-vol: down
	@echo "🔥 Eliminando volumenes peristentes (@db)..."
	@docker volume rm $$(docker volume ls -q)

fclean: down-vol clean
	@docker system prune -af

env:
	@cp docker/env.example docker/.env

logs_%:
	@docker logs -f camagru-$*


re: fclean up

re-vol: fclean down-vol up

.PHONY: all up-logs up build stop down down-volumes clean fclean re env