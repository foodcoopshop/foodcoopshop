# FoodCoopShop

[![Latest Stable Version](https://img.shields.io/packagist/v/foodcoopshop/foodcoopshop.svg?label=stable)](https://www.foodcoopshop.com/download)
[![Build Status](https://travis-ci.org/foodcoopshop/foodcoopshop.svg)](https://travis-ci.org/foodcoopshop/foodcoopshop)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[![Dependency Status](https://dependencyci.com/github/foodcoopshop/foodcoopshop/badge)](https://dependencyci.com/github/foodcoopshop/foodcoopshop)

FoodCoopShop is a free open source software for foodcoops.

* Official homepage: [https://www.foodcoopshop.com](https://www.foodcoopshop.com/)
* Demo version: [https://demo.foodcoopshop.com](https://demo.foodcoopshop.com)
* Software documentation: [https://foodcoopshop.github.io](https://foodcoopshop.github.io)

## Roadmap for 2018

In the 4th year of existance, FoodCoopShop continues to grow. Here you can take a look on the planned work for [Q1/2018 - v2.0](https://github.com/foodcoopshop/foodcoopshop/milestone/1) / [Q2/2018 - v2.1](https://github.com/foodcoopshop/foodcoopshop/milestone/2) / [Q3/2018 - v2.2](https://github.com/foodcoopshop/foodcoopshop/milestone/3) and [Q4/2018 - v2.3](https://github.com/foodcoopshop/foodcoopshop/milestone/4). You are welcome to comment the issues!

## Legal information

* Before installing please read the legal information in [German](https://foodcoopshop.github.io/de/rechtliches) or [English](https://foodcoopshop.github.io/en/legal-information).

## Requirements
* Server with root access / sudo and cronjobs
* Apache with `mod_rewrite`
* PHP >= 7.0 and < 7.2
* MySQL >= 5.6
* Nodejs and npm ([installation](https://www.npmjs.com/get-npm)) developer packages
* Composer ([installation](https://getcomposer.org/download/)) developer packages
* Basic understanding of Apache Webserver, MySQL Database and Linux Server administration

## Installation
* This is the developers area. If you want to use the software "as is", please follow the [installation details](https://foodcoopshop.github.io/en/installation-details) in the software documentation. If you have questions or if you **want be informed if a new version is released**, please drop me an email: office@foodcoopshop.com (Mario).
* Basically follow the [installation details](https://foodcoopshop.github.io/en/installation-details) for setup. But do **clone the repository**!
* Before doing any of the configuration changes, follow the steps below
* If You work on a local machine, do not change the owner of the files to www-data. Instead set permissions as shown below

## Install required packages
Install the composer vendors (use the "--prefer-dist" option to avoid downloading VCS meta data)
```
$ composer install
```

Install the npm components
```
$ cd webroot
$ npm install
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
* Import [this dump](Config/sql/_installation/clean-db-structure.sql) into your test database
* The test suite **AllFoodCoopShopTests** runs all tests of the application
* An overview about all existing tests are found on the testing web interface: www.yourdomain.com/test.php

