# Camagru

Red social de fotos con temática de F1: haces una foto con la webcam (o subes una imagen), le superpones overlays (cascos, coches…) y la publicas para que otros usuarios le den like y la comenten.

Hecho en PHP sin framework, MySQL, nginx y JavaScript sin librerías, todo en Docker.

**Funcionalidades:**
- Registro con verificación por email, login, logout y recuperación de contraseña.
- Editor de fotos:
  - cámara en directo u opción de subir imagen;
  - overlays que se pueden mover y reescalar en tiempo real;
  - borradores;
  - publicación con descripción.

  La superposición final se hace en el servidor con GD.
- Feed con scroll infinito, likes, comentarios, respuestas y menciones `@usuario`.
- Perfil público y página individual de cada post.
- Settings:
  - foto de perfil;
  - nombre de usuario;
  - email, con confirmación en la dirección nueva;
  - contraseña;
  - avisos por email de comentarios;
  - borrar la cuenta.
- Avisos por email al cambiar datos de la cuenta y cuando alguien comenta en tus fotos.

---

## Requisitos

| Herramienta | Para qué | Comprobar |
| --- | --- | --- |
| Docker con Docker Compose v2 | Levanta nginx, PHP y MySQL | `docker compose version` |
| GNU Make | Atajos de los comandos de Docker | `make --version` |
| Una cuenta de Gmail con verificación en dos pasos | Enviar los emails (verificación, avisos…) | — |
| Node.js 20 o superior *(opcional)* | Solo si vas a modificar el CSS | `node --version` |

No hace falta instalar PHP, Composer ni MySQL en tu máquina: todo corre dentro de los contenedores.

Puertos que tienen que estar libres: **8080** (la web) y **33006** (MySQL, por si quieres conectarte desde fuera).

---

## Puesta en marcha paso a paso

### 1. Clonar el repositorio

```sh
git clone <url-del-repo> camagru
cd camagru
```

### 2. Crear una contraseña de aplicación de Gmail

La app envía los emails a través de Gmail, y Gmail no acepta la contraseña normal de la cuenta para esto.

1. Activa la verificación en dos pasos en la cuenta de Google que vayas a usar.
2. Ve a <https://myaccount.google.com/apppasswords> y crea una contraseña de aplicación (el nombre da igual, por ejemplo "camagru").
3. Copia la contraseña de 16 letras que te da Google. La usarás en el paso siguiente, sin espacios.

### 3. Crear los secrets

Las credenciales van en ficheros dentro de `docker/secrets/`, uno por valor, y Docker se los pasa a los contenedores. Esta carpeta está en `.gitignore`: no se sube nunca al repositorio.

```sh
mkdir -p docker/secrets
printf '%s' 'camagru'              > docker/secrets/db_name
printf '%s' 'camagru_user'         > docker/secrets/db_user
printf '%s' 'una-contraseña-larga' > docker/secrets/db_password
printf '%s' 'otra-contraseña-root' > docker/secrets/db_root_password
printf '%s' 'tu.cuenta@gmail.com'  > docker/secrets/smtp_mail
printf '%s' 'abcdefghijklmnop'     > docker/secrets/smtp_password
```

| Fichero | Contenido |
| --- | --- |
| `db_name` | Nombre de la base de datos |
| `db_user` | Usuario de MySQL que usará la app |
| `db_password` | Contraseña de ese usuario |
| `db_root_password` | Contraseña de root de MySQL |
| `smtp_mail` | La dirección de Gmail desde la que se envían los emails |
| `smtp_password` | La contraseña de aplicación del paso 2 (no la de la cuenta) |

Se usa `printf` en vez de `echo` para que los ficheros no acaben en salto de línea.

### 4. Crear el `.env`

```sh
make env
```

Esto copia `docker/env.example` a `docker/.env`. Revisa los valores:

| Variable | Valor | Para qué |
| --- | --- | --- |
| `DB_SERVER` | `camagru-db` | Host de MySQL dentro de la red de Docker. Déjalo así. |
| `APP_URL` | `http://localhost:8080` | Dirección pública de la web. Se usa en **todos los enlaces de los emails**: verificación, cambio de contraseña, confirmación de email y avisos. |
| `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_DATABASE` | rutas a `/run/secrets/…` | Déjalas como vienen. |

Si la web se va a abrir desde otro ordenador, `APP_URL` tiene que ser la dirección con la que se accede desde allí (por ejemplo `http://10.12.5.6:8080` o `https://camagru.midominio.com`). Si se queda en `localhost`, los enlaces de los emails solo funcionarán en la máquina que ejecuta Docker. Escríbela con `http://` o `https://` delante.

### 5. Construir y arrancar

```sh
make
```

Construye las imágenes y levanta los tres contenedores (`camagru-nginx`, `camagru-php` y `camagru-db`). La primera vez tarda un poco: MySQL crea la base de datos a partir de `docker/mysql/init.sql`.

Comprueba que están los tres en marcha:

```sh
docker ps --format '{{.Names}}: {{.Status}}'
```

### 6. Generar el autoload de PHP

La carpeta `vendor/` no está en el repositorio. Genérala una vez con el Composer que trae el contenedor de PHP:

```sh
docker exec camagru-php composer install
```

Sin este paso todas las páginas dan error, porque falta `vendor/autoload.php`.

### 7. Abrir la web

<http://localhost:8080>

Regístrate, abre el email de verificación y haz login. Si el email no llega, mira [Problemas frecuentes](#problemas-frecuentes).

---

## Comandos del Makefile

| Comando | Qué hace |
| --- | --- |
| `make` | Construye las imágenes y arranca todo (`build` + `up`) |
| `make up` | Arranca los contenedores |
| `make up-logs` | Arranca en primer plano mostrando los logs |
| `make stop` | Para los contenedores sin borrarlos |
| `make down` | Para y borra los contenedores. Los datos se conservan. |
| `make logs-php` / `logs-nginx` / `logs-db` | Sigue los logs de un contenedor |
| `make sh-php` / `sh-nginx` | Abre una shell dentro del contenedor |
| `make sh-db` | Abre la consola de MySQL sobre la base de datos de la app |
| `make env` | Copia `docker/env.example` a `docker/.env` |
| `make css-build` / `make css-watch` | Compila el CSS (ver [Modificar el CSS](#modificar-el-css)) |

> **⚠️ Cuidado con estos.** No se limitan a este proyecto: afectan a **todo Docker en tu máquina**.
>
> | Comando | Qué borra |
> | --- | --- |
> | `make down-vol` | **Todos** los volúmenes de Docker del sistema, incluidos la base de datos y las fotos subidas de Camagru |
> | `make clean` | **Todas** las imágenes de Docker |
> | `make fclean`, `make re`, `make re-vol` | Combinan los anteriores y además hacen `docker system prune -af` |
>
> Úsalos solo si no tienes otros proyectos en Docker que quieras conservar.

---

## Dónde se guardan los datos

- **Base de datos:** volumen de Docker `db-data`.
- **Fotos subidas y avatares:** volumen `uploads-data`, montado en `public/uploads/` dentro de los contenedores. No aparecen en la carpeta del proyecto en tu máquina.

Los dos sobreviven a `make down`. Solo se borran con `make down-vol` (o cualquiera de los comandos que lo incluyen).

### Si la base de datos se creó con una versión anterior

`init.sql` solo se ejecuta **la primera vez** que se crea el volumen `db-data`. Si actualizas el código y el esquema ha cambiado (columnas o tablas nuevas), la base de datos existente no se actualiza sola. Tienes dos opciones:

- **Empezar de cero** (se pierden usuarios, fotos y comentarios): borra solo los volúmenes de este proyecto y vuelve a arrancar.
  ```sh
  make down
  docker volume rm docker_db-data docker_uploads-data
  make up
  ```
- **Conservar los datos:** aplica a mano las columnas o tablas que falten desde `make sh-db`, mirando `docker/mysql/init.sql`.

---

## Overlays

Los overlays del editor son los PNG de `public/overlays/`. Para añadir uno nuevo basta con dejar ahí un PNG con fondo transparente. El nombre del fichero (letras, números, `-` y `_`) es su identificador, y aparece en el editor al recargar la página.

---

## Modificar el CSS

Los estilos se escriben en `resources/css/app.css` con Tailwind CSS v4 y se compilan a `public/assets/css/app.css`. El fichero compilado está en el repositorio, así que **solo hace falta compilar si cambias estilos**:

```sh
npm install         # la primera vez
make css-build      # compila una vez
make css-watch      # recompila cada vez que guardas
```

---

## Publicarlo en internet

Para que se pueda entrar desde cualquier sitio y los enlaces de los emails funcionen fuera de tu máquina:

1. Expón el puerto 8080 con una dirección pública, por ejemplo:
   - con un túnel (Cloudflare Tunnel, ngrok…);
   - o en un servidor con IP pública, con un dominio que apunte a él (por ejemplo un subdominio gratuito de DuckDNS).
2. Pon esa dirección en `APP_URL` en `docker/.env`, completa y con `https://` si la tienes, por ejemplo `APP_URL="https://camagru.midominio.com"`.
3. Recrea el contenedor de PHP para que lea el nuevo valor:
   ```sh
   make up
   ```
   Docker solo lee el `.env` al crear el contenedor, así que esto hace falta cada vez que cambies `APP_URL`.

---

## Problemas frecuentes

**Los emails no llegan.**
- Mira el log de PHP: `make logs-php`. Si falla el envío aparece `Mailer: could not send …`.
- `smtp_password` tiene que ser la **contraseña de aplicación** de Google, no la de la cuenta, y sin espacios.
- `smtp_mail` tiene que ser la misma cuenta de Gmail con la que creaste esa contraseña.
- Revisa la carpeta de spam.
- Si cambias algún secret, recrea los contenedores: `make down && make up`.

**Los enlaces de los emails van a `localhost`.** `APP_URL` no está puesto o el contenedor no se ha recreado desde que lo cambiaste. Ver [Publicarlo en internet](#publicarlo-en-internet), paso 3.

**Todas las páginas dan error 500 o salen en blanco.** Seguramente falta `vendor/`: ejecuta el [paso 6](#6-generar-el-autoload-de-php). Los detalles del error están en `make logs-php`.

**`camagru-php` no arranca o se queda esperando.** Espera a que MySQL esté listo: la primera vez puede tardar unos segundos más. Si no arranca, mira `make logs-db`; lo más habitual es que falte algún fichero en `docker/secrets/`.

**`port is already allocated`.** Otro programa usa el puerto 8080 o el 33006. Páralo, o cambia el puerto de la izquierda en `docker/docker-compose.yml` (por ejemplo `"8081:80"`) y ajusta `APP_URL`.

**La cámara no se activa.** Los navegadores solo permiten la cámara en `localhost` o con `https://`. Si entras por una IP con `http://`, usa la opción de subir imagen o publícalo con HTTPS.

**Errores de base de datos tras actualizar el código** (por ejemplo `Unknown column`). La base de datos es de una versión anterior: mira [Si la base de datos se creó con una versión anterior](#si-la-base-de-datos-se-creó-con-una-versión-anterior).

**En los ordenadores de 42 (sgoinfre):** nginx y PHP-FPM corren como root a propósito. La carpeta del proyecto está en un NFS con Kerberos que solo reconoce a tu usuario, y los usuarios sin privilegios de los contenedores no podrían leerla. Lo explican los comentarios de `docker/docker-compose.yml` y `docker/php/Dockerfile`.

---

## Estructura del proyecto

```
app/
  Controllers/   Una clase por sección (Home, Login, Register, Password, Photo, Post, User, Settings)
  Core/          Router, Controller base, Session, Model (PDO), Helper, Mailer
  Models/        User, Image, Comment, Like, Overlay, Verification, PasswordReset, EmailChange
  Validators/    Validación de usuario, email y contraseña
  Views/         Plantillas PHP: layouts/, partials/, modals/ y una vista por página
config/
  routes.php     Todas las rutas (GET/POST → Controlador::método)
  config.php     Conexión a la base de datos (lee los secrets)
docker/
  docker-compose.yml, env.example, nginx/, php/, mysql/init.sql
public/          Raíz web: index.php, js/, assets/css/, overlays/, uploads/
resources/css/   Fuente de Tailwind
```
