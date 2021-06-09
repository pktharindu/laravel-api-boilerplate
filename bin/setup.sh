#!/bin/bash

cp .env.example .env
composer install
php artisan key:generate
read -r -n 1 -p "Please fill out the environment variables in the .env file and press any key to continue... "
echo
php artisan migrate --seed
php artisan blueprint:init
