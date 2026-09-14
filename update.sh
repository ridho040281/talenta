#!/usr/bin/env bash

git pull origin main
php artisan config:clear
php artisan cache:clear

