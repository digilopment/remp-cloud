docker compose down
sudo rm -rf volumes/* www/* 

bash up -d
sleep 15

#init and install
cd setup
bash init.sh
bash install.sh
bash pluginsAndThemes.sh

#migrations
cd ../
bash migration.sh users
bash migration.sh pages
bash migration.sh products
bash migration.sh posts
bash migration.sh settings
bash migration.sh woocommerceSettings
