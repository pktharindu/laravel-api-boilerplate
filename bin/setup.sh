#!/bin/bash

cp .env.example .env
composer install
php artisan migrate --seed
