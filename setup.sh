#!/bin/sh

# If you would like to do some extra provisioning you may
# add any commands you wish to this file and they will
# be run after the Homestead machine is provisioned.

# run root composer install
echo "Running composer install"
composer install

# run composer install in api-gateway
cd api-gateway
composer install

# run composer install in auth-service
cd ../auth-service
composer install

# run composer install in email-service
cd ../email-service
composer install

# adding to etc/hosts file
sudo -- sh -c -e "echo '192.168.10.10  auth-service.test' >> /etc/hosts";
sudo -- sh -c -e "echo '192.168.10.10  api-gateway.test' >> /etc/hosts";

# run vagrant up in root
cd ../
vagrant up
