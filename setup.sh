docker exec -it remp-beam bash -c "cd /var/www/html/Beam && bash ./install.sh"
docker exec -it remp-campaign bash -c "cd /var/www/html/Campaign && bash ./install.sh"
docker exec -it remp-sso bash -c "cd /var/www/html/Sso && bash ./install.sh"
docker exec -it remp-mailer bash -c "cd /var/www/html/Mailer && bash ./install.sh"


#docker exec -it remp-crm bash -c "cd /var/www/html/Crm && bash ./install.sh"
#docker exec -it remp-mailer bash -c "cd /var/www/html/Mailer && bash ./install.sh"

cd Crm && bash ./install.sh && cd ../
cd Web && bash ./install.sh && cd ../