<h1 align="center">
  <a href="https://www.foodcoopshop.com"><img src="https://raw.githubusercontent.com/foodcoopshop/foodcoopshop/develop/webroot/files/images/logo.jpg" alt="FoodCoopShop"></a>
</h1>

<h4 align="center">An easy-to-use open source software for food-coops.</h4>

<p align="center">
  <a href="https://www.foodcoopshop.com/download">
    <img src="https://img.shields.io/packagist/v/foodcoopshop/foodcoopshop.svg?label=stable"
         alt="Latest stable version">
  </a>
  <a href="https://travis-ci.org/foodcoopshop/foodcoopshop">
    <img src="https://travis-ci.org/foodcoopshop/foodcoopshop.svg"
         alt="Build status">
  </a>
  <a href="LICENSE">
    <img src="https://img.shields.io/badge/license-MIT-brightgreen.svg"
         alt="Software license">
  </a>
</p>

<h1></h1>

**FoodCoopShop** provides plenty of useful functions to help the people running a food-coop. Such as:

* a user-friendly web shop for the consumers
* an admin area for both manufacturers and admins 
* a fully automated payment system based on a credit system
* different delivery rhythms for products
* adapting orders in many ways
* stock management... and much more!

The software can be used with any modern web browser, tablet or smartphone. It's available in **German** and **English**.

## Links
* Official homepage: [https://www.foodcoopshop.com](https://www.foodcoopshop.com/)
* Demo version in [German](https://demo-de.foodcoopshop.com) or [English](https://demo-en.foodcoopshop.com)
* Software documentation: [https://foodcoopshop.github.io](https://foodcoopshop.github.io)

## Who uses FoodCoopShop?
* [List](https://foodcoopshop.github.io/en/foodcoops) / [OpenStreetMap](http://umap.openstreetmap.fr/en/map/verbreitung-foodcoopshop_211165)

## Legal information
* Before installing please read the legal information in [German](https://foodcoopshop.github.io/de/rechtliches) or [English](https://foodcoopshop.github.io/en/legal-information).

## Requirements
* Server with shell access and cronjobs
* Apache with `mod_rewrite`
* PHP >= 7.1
* MySQL >= 5.6
* Node.js and npm ([installation](https://www.npmjs.com/get-npm)) developer packages
* Composer ([installation](https://getcomposer.org/download/)) developer packages
* Basic understanding of Apache Webserver, MySQL Database and Linux Server administration
* PHP needs to be able to call mysqldump with exec() for database backups

## Installation
* This is the developers area. If you want to use the software "as is", please follow the [installation details](https://foodcoopshop.github.io/en/installation-details) in the software documentation. If you have questions or if you **want be informed if a new version is released**, please drop me an email: office@foodcoopshop.com (Mario).
* Basically follow the [installation details](https://foodcoopshop.github.io/en/installation-details) for setup. But do **clone the repository**!
* Before doing any of the configuration changes, follow the steps below
* If You work on a local machine, do not change the owner of the files to www-data. Instead set permissions as shown below

## Install required packages
Install the composer vendors
```
$ composer install
```

Install packages from package.json
```
$ npm --prefix ./webroot install ./webroot
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
* Create second database and add test database configuration to database.php. For details read [Cake's testing documentation](https://book.cakephp.org/3.0/en/development/testing.html)
* Import [this dump](config/sql/_installation/clean-db-structure.sql) into your test database
```
$ vendor/bin/phpunit
```
