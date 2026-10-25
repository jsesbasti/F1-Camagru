COMPOSE-FILE=docker/docker-compose.yml
COMPOSE=docker compose -f $(COMPOSE-FILE)
ENV-FILE=docker/.env

# Escribe CLOUD_URL en el .env (lo añade si no existe). Uso: $(call set-cloud-url,<url>)
# -i.bak (y luego borrar el .bak) porque el sed de macOS exige extensión en -i
# y el de Linux no la acepta separada: así funciona en los dos.
define set-cloud-url
	if grep -q '^CLOUD_URL=' $(ENV-FILE); then \
		sed -i.bak "s|^CLOUD_URL=.*|CLOUD_URL=\"$(1)\"|" $(ENV-FILE) && rm -f $(ENV-FILE).bak; \
	else \
		printf '\nCLOUD_URL="%s"\n' "$(1)" >> $(ENV-FILE); \
	fi
endef

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
	@$(COMPOSE) --profile cloud stop

down: 
	@echo "🧹 Bajando contenedores..."
	@$(COMPOSE) --profile cloud down
	@[ ! -f $(ENV-FILE) ] || { $(call set-cloud-url,); }

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

# Levanta un quick tunnel de Cloudflare, pone su URL en CLOUD_URL y recrea PHP
# para que la use en vez de APP_URL. La URL cambia cada vez que se ejecuta.
cloud:
	@echo "☁️  Levantando el túnel de Cloudflare..."
	@$(COMPOSE) --profile cloud up -d --force-recreate tunnel
	@for i in $$(seq 30); do \
		url=$$(docker logs camagru-tunnel 2>&1 | grep -o 'https://[a-z0-9-]*\.trycloudflare\.com' | grep -v '//api\.' | head -1); \
		[ -n "$$url" ] && break; sleep 1; \
	done; \
	if [ -z "$$url" ]; then echo "❌ No se pudo obtener la URL del túnel (make logs-tunnel)"; exit 1; fi; \
	$(call set-cloud-url,$$url); \
	$(COMPOSE) up -d php; \
	echo "🌐 Camagru disponible en $$url"

# Vuelve a modo local: para el túnel y vacía CLOUD_URL para que PHP
# vuelva a usar APP_URL.
local:
	@$(COMPOSE) --profile cloud stop tunnel
	@$(call set-cloud-url,)
	@$(COMPOSE) up -d php

css-build:
	@echo "🎨 Compilando Tailwind CSS..."
	@npm run build:css

css-watch:
	@npm run watch:css

logs-%:
	@docker logs -f camagru-$*

sh-db:
	@docker exec -it camagru-db mysql -p"$$(cat ./docker/secrets/db_root_password)" -D "$$(cat ./docker/secrets/db_name)"
sh-%:
	@docker exec -it camagru-$* /bin/bash


re: fclean up

re-vol: fclean down-vol up

.PHONY: all up-logs up build stop down down-volumes clean fclean re env cloud local css-build css-watch
