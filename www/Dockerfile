FROM nginx:1.29

COPY nginx.conf /etc/nginx/nginx.conf

COPY . /usr/share/nginx/html
WORKDIR /usr/share/nginx/html

CMD ["nginx", "-g", "daemon off;"]

EXPOSE 80