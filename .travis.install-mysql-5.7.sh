# @see https://github.com/codyzu/jasql/blob/master/.travis.install-mysql-5.7.sh
echo mysql-apt-config mysql-apt-config/select-server select mysql-5.7 | sudo debconf-set-selections
wget http://dev.mysql.com/get/mysql-apt-config_0.8.8-1_all.deb
sudo dpkg --install mysql-apt-config_0.8.8-1_all.deb
sudo apt-get update -q
sudo apt-get sudo apt-get install --allow-unauthenticated mysql-server
sudo mysql_secure_installation
sudo mysql_upgrade --force
service mysql status
service mysql start