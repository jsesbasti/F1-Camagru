FROM mysql:latest

# init.sql is baked into the image instead of bind-mounted: mysql's own
# entrypoint drops from root to its unprivileged "mysql" user before running
# anything in /docker-entrypoint-initdb.d, and that user can't read files
# bind-mounted from the Kerberos-secured NFS4 sgoinfre share (see the nginx/php
# Dockerfile comments for the same issue). Baking it in sidesteps that.
COPY init.sql /docker-entrypoint-initdb.d/init.sql
