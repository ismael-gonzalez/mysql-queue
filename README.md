# mysql-queue
Laravel 4.2 Mysql Queue 

# Installation

Open your `composer.json` to modifiy some config, then remove `"miniminum-stability": "stable"` if exists.

Add this repository to your `composer.json`.
```json
"repositories": [
  {
    "type": "git",
    "url":  "https://github.com/matriphe/mysql-queue.git"
  }
],
```
Run this command to pull and install the package.
```console
composer require ismael-gonzalez/mysql-queue:dev-master
```

# Configuration

Open `app/config/app.php` and add the service provider in the `providers` section.
```php
'Mysql\Queue\MysqlQueueServiceProvider',
```
Open `app/config/queue.php` and add this config in `connections` section.
```php
'mysql' => array(
  'driver' => 'mysql',
  'table' => 'jobs',
  'default' => 'default',
),
```
Set the default driver to `mysql`.

# Migration

Copy file from `vendor/ismael-gonzalez/mysql-queue/src/Migrations/create_jobs_table.php` to `app/database/migrations` using this command.
```console
cp vendor/ismael-gonzalez/mysql-queue/src/Migrations/create_jobs_table.php app/database/migrations/$(date +"%Y_%m_%d_%H%M%S")_create_jobs_table.php
```
Execute migration.
```console
php artisan migrate
```
# Done

Done! Yay!
