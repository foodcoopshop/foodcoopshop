# FoodCoopShop - The open source software for your foodcoop
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)

FoodCoopShop is a free, open source, software for foodcoops, released under MIT License. It is based on CakePHP 2.

## TODOs until v1.0.0
* database installation
* how to run unit tests
* how to use cronjobs

## Requirements
* Apache with `mod_rewrite`
* PHP 5.5 or higher (PHP 7 recommended)
* MySQL 5.4 or higher

## Installation
The latest version can be downloaded on [https://www.foodcoopshop.com/download](https://www.foodcoopshop.com/download).

## Database and email setup
Please use the documentation of CakePHP 
* [Cake's database documentation](http://book.cakephp.org/2.0/en/development/configuration.html) 
* [Cake's email documentation](http://book.cakephp.org/2.0/en/core-utility-libraries/email.html) 

## Importing the database dump
Once the database setup is complete, import [the database dump](foodcoopshop/Test/test_files/Config/sql/01-test-db-general.sql)

## Changing security configuration
* TODO To generate a new cookieKey, create an account with superadmin privileges and visit [this page](www.example.com/admin/configurations/generateCookieKey) and copy the key to your custom.config.php
* To change Security.salt and Security.cipherSeed, visit [this page](http://cakephp.thomasv.nl/) and follow the instructions.

## Configuring FoodCoopShop
FoodCoopShop is highly customizable to satisfy the demands of the broad variety of foodcoops.

* the default configuration is found in [app.config.php](Config/app.config.php).
* you can override the default configuration in [custom.config.php](Config/custom.config.default.php), rename this file to custom.config.php
* credentials need to be put in [credentials.php](Config/credentials.default.php), rename the file to credentials.php

# Developer area

## Installation

Clone the repository and install the composer vendors (use the "--prefer-dist" option to avoid downloading VCS meta data).
``` bash
$ composer install
```

Installing the bower components 
``` bash
$ bower install
```

## Setting permissions
``` bash
$ chmod a+w -R /files_private
$ chmod a+w -R /tmp
$ chmod a+w -R /webroot/cache
$ chmod a+w -R /webroot/files
$ chmod a+w -R /webroot/tmp
```

## Virtual host
* Your virutal host should end with ".dev", to automatically recognize dev environment and set the correct debug mode.
* The host's document root must point to /webroot


## Pull requests
Pull requests are welcome, please make sure your modifications are to the develop branch of FoodCoopShop and they are well tested!

# Links
* **Official website**: [https://www.foodcoopshop.com](https://www.foodcoopshop.com)

