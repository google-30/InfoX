#!/bin/bash
set -v
sudo apt-get install vim screen git pgadmin3

git config --global user.name "philipzhou2009"
git config --global user.email "philip.zhou.2009@gmail.com"
git config --global credential.helper cache
git config --global credential.helper 'cache --timeout=72000'
git clone https://github.com/philipzhou2009/InfoX.git

cd InfoX
sudo ./sysprep.sh
sudo ./correction.sh

sudo cp scripts/infox /etc/apache2/sites-available/
sudo a2dissite default
sudo a2ensite infox
sudo a2enmod rewrite
sudo service apache2 reload

#sudo su postgres
#createuser -E -P hcs_admin (password: 123456)
#createdb -O hcs_admin hcsdatabase
#edit /etc/postgresql/9.1/main/pg_hba.conf, add this line: 
#host    all             all             192.168.56.0/24         trust
# ./update.sh


