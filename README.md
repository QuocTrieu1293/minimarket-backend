## Overview
GreenMart  aims to create a seamless and user-friendly online platform for customers to conveniently purchase a wide range of grocery products from the comfort of their homes. This project is driven by the increasing demand for online shopping and the need for a reliable and efficient solution for grocery shopping.


## Tech Stack
* Laravel
* Filament (a powerful, customizable admin panel for Laravel to quickly build your app's admin interfaces)

## Installation 
1. Make sure you've installed and setup:
   - XAMP server / Wamp server
   - PHP (recommend vars)
   - Composer
2. Run wamp or xampp server (run mysql and apache modules).
3. Clone the repo to your local machine and place it in `htdocs` folder of xampp or wamp server (ex: `C:\xampp\htdocs\` or `C:\wamp\www\`).
4. Go to `localhost/phpmyadmin`, create a new database named `dbminimarket` and import the sql file `file sql/dbminimarket.sql` of the repo to the database for creating tables and inserting data.
5. In the repo folder location you have just cloned (ex: `C:\xampp\htdocs\minimarket-backend\`) -> Open cmd -> run `composer install` to install all dependencies.
6. Check source files, go to `.env` , if there is only `.env.example` -> rename it to `.env`. In .env, change:
   - Database connection:
      ```
      DB_CONNECTION=mysql 
      DB_HOST=127.0.0.1 // your localhost 
      DB_PORT=3306 
      DB_DATABASE=dbminimarket // name of the database you have just created above
      DB_USERNAME= root // your db username (usually root)
      DB_PASSWORD= // your db password (usually empty)
      ```
   - Optionally, you can change the app name and turn laravel debug mode off for faster loading:
      ```
      APP_NAME='Green Market'
      APP_DEBUG=false // false for faster loading
      ```
7. Run `php artisan key:generate` to generate the app key in `.env` file, for example:
   ```
   APP_KEY=base64:hLRDeP6kxVDqwM3bqc9AOd5mFxP39O9eut3O3Im6Ohw=
   ```
8. Run the command `php artisan storage:link` to create a symbolic link from "public/storage" to "storage/app/public". This is necessary for the product image upload functionality in the admin panel.
9. Finally run `php artisan serve` to start the server. You can now access the admin page at `localhost:8000`. The default admin account is `admin@gmail.com` and password is `minimarket`.

> To check api working for customer website, go to ***`localhost:8000/api/danhmuc`*** to see all category_group in json format.

> All the pre-existing accounts in the ***`users`*** table of the database have **'`minimarket`'** as their password.

## Contact
Team DVGs @2023