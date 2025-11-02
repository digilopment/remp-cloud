FROM nginx:latest

ENV DEBIAN_FRONTEND=noninteractive
# Copy certificate files
COPY ./cert/*.crt /etc/ssl/certs/
COPY ./cert/*.key /etc/ssl/private/
COPY ./cert/*.pem /etc/ssl/private/

# Copy NGINX configuration files
COPY ./conf.d/* /etc/nginx/conf.d/

ENV TZ=Europe/Bratislava

# Expose port 443 for HTTPS
EXPOSE 443

# Start NGINX
CMD ["nginx", "-g", "daemon off;"]

