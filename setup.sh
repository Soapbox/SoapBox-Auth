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

# run vagrant up in root
cd ../
vagrant up
