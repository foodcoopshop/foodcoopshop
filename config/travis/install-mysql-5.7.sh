# @see https://github.com/codyzu/jasql/blob/master/.travis.install-mysql-5.7.sh
# Make sure MySQL 5.7.x is installed (MySQL 8.0 is installed by default in the new apt-config)
echo mysql-apt-config mysql-apt-config/select-server select mysql-5.7 | sudo debconf-set-selections
wget http://dev.mysql.com/get/mysql-apt-config_0.8.10-1_all.deb
sudo dpkg --install mysql-apt-config_0.8.10-1_all.deb
sudo apt-get update -q
sudo apt-get install -q -y -o Dpkg::Options::=--force-confnew mysql-server

# Run MySQL upgrade to make sure indexes are upgraded (@see https://dev.mysql.com/doc/relnotes/mysql/5.7/en/news-5-7-23.html#mysqld-5-7-23-bug)
sudo mysql_upgrade

# Check if all is running and restart MySQL.
service mysql status
sudo service mysql restart

