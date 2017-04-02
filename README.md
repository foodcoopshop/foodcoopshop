# FoodCoopShop
The open source software for your foodcoop

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)

FoodCoopShop is a free open source software for foodcoops. For more information about features or a demo visit the [project details homepage](https://www.foodcoopshop.com/).

## Installation

This is the Developers area. If You want to use the software "as is", please follow the [installation details](https://github.com/foodcoopshop/foodcoopshop/wiki/Installation-details) in the project's wiki.

# Developers area

If you have questions please drop me an email: office@foodcoopshop.com (Mario).


## Requirements
* Server with root access / sudo and cronjobs
* Apache with `mod_rewrite`
* PHP 5.5 or higher (PHP 7.1.x recommended)
* MySQL 5.4 or higher
* Nodejs and bower ([installation](https://www.npmjs.com/package/bower)) developer packages
* Composer ([installation](https://getcomposer.org/download/)) developer packages
* Basic understanding of Apache Webserver, MySQL Database and Linux Server administration

## Software documentation (only available in German)
Please read the [software documentation in German](https://github.com/foodcoopshop/foodcoopshop/wiki/Dokumentation-de).

## Using a local webserver
If you develop on your local machine, your virtual host should end with ".dev" (e.g. localhost.dev). Then development environment and correct debug mode are set automatically. Simply add the prefered hostname to Your local hosts file (e.g. /etc/hosts). Check in Your browser by loading http://localhost.dev/ It's also possible to have 2 hosts pointing to the same Document Root (e.g. localhost and localhost.dev).

## Installation
* Basically follow the [installation details](https://github.com/foodcoopshop/foodcoopshop/wiki/Installation-details) for setup. But do **clone the repository**!
* Before doing any of the configuration changes, follow the steps below
* If You work on a local machine, do not change the owner of the files to www-data. Instead set permissions as shown below

## Install required packages
Install the composer vendors (use the "--prefer-dist" option to avoid downloading VCS meta data)
```
$ composer install
```

Install the bower components
```
$ bower install
```

## Setting permissions
```
$ cd /path/to/project
$ chmod a+w -R ./files_private
$ chmod a+w -R ./tmp
$ chmod a+w -R ./webroot/cache
$ chmod a+w -R ./webroot/files
$ chmod a+w -R ./webroot/tmp
```

## Unit Testing
* Create second database and add test database configuration to database.php. For details read [Cake's testing documentation](http://book.cakephp.org/2.0/en/development/testing.html)
* Import [this dump](Test/test_files/Config/sql/test-db-structure.sql) into your test database
* When you run tests that require database access, **both default and test database (in your database.php) need to point to the test database!**
* The test suite **AllFrontendAndBackendTests** runs - as the name implies - all tests of the application
* An overview about all existing tests are found on the testing web interface: www.yourdomain.com/test.php

## Compress assets
If the debug level is set to 0 (e.g. not using a *.dev hostname), and you made changes to your assets defined in assets_compress.ini, you can rebuild your compressed assets with

```
$ bash Console/cake asset_compress build
```

# Links
* **Official website**: [https://www.foodcoopshop.com](https://www.foodcoopshop.com)
