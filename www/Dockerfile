FROM debian:bookworm

RUN apt-get update && apt-get upgrade -y && apt-get install php -y

CMD ["php", "-S", "localhost:8000"]

EXPOSE 8000