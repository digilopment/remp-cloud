#!/bin/bash

# load domain names from the /etc/hosts
hosts_file="/etc/hosts"
domains=($(awk '{print $2}' "$hosts_file" | grep -Eo "(\S+\.)+\S+" | sort -u))

cert_dir="./cert"

if ! command -v mkcert &> /dev/null; then
    echo "mkcert is not installed. Please install mkcert to proceed."
    exit 1
fi

if [[ "$1" == "-f" || "$1" == "--force" ]]; then
    sudo rm -rf ./cert/*.crt
    sudo rm -rf ./cert/*.pem
    sudo rm -rf ./cert/*.key
    sudo rm -rf ./conf.d/*.conf
fi


for domain in "${domains[@]}"; do
  if [[ (! -f "$cert_dir/$domain.pem" || ! -f "$cert_dir/$domain-key.pem") || "$1" == "-f" || "$1" == "--force" ]]; then
    mkcert -cert-file "$cert_dir/$domain.pem" -key-file "$cert_dir/$domain-key.pem" "$domain"
    openssl req -x509 -new -nodes -key "$cert_dir/$domain-key.pem" -days 365 -out "$cert_dir/$domain.crt" -subj "/C=AU/ST=Some-State/L=/O=Internet Widgits Pty Ltd/OU=/CN=/emailAddress="
    openssl pkey -in "$cert_dir/$domain-key.pem" -out "$cert_dir/$domain.key"
    echo "server {
      listen 443 ssl;
      server_name $domain;

      ssl_certificate /etc/ssl/private/$domain.pem;
      ssl_certificate_key /etc/ssl/private/$domain-key.pem;
      
      location / {
        proxy_connect_timeout 1800;
        proxy_send_timeout 1800;
        proxy_read_timeout 1800;
        send_timeout 1800;

        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_set_header X-Forwarded-Port \$server_port;
        proxy_set_header X-Forwarded-Host \$host;
        proxy_pass http://nginx:80;
        proxy_redirect http://$domain https://$domain:443;
      }
    }" > "./conf.d/$domain.conf"
  else
    echo "$domain has a certificate. Run 'bash generate.sh -f' to regenerate certificates."
  fi
done

sudo cp -r ./cert/*.crt /usr/local/share/ca-certificates/
sudo update-ca-certificates -f
sudo chmod -R 0777 ./cert/
sudo chmod -R 0777 ./conf.d/
mkcert -install

