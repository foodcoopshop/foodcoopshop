# FoodCoopShop
The open source software for your foodcoop
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)

FoodCoopShop is a free, open source, software for foodcoops, released under MIT License. It is based on CakePHP 2.

## Requirements
* Apache with `mod_rewrite`
* PHP 5.5 or higher (PHP 7 recommended)
* MySQL 5.4 or higher

## Installation
The latest version can be downloaded on [https://www.foodcoopshop.com/download](https://www.foodcoopshop.com/download).

## Installation details
Please read the [installation details wiki page](https://github.com/foodcoopshop/foodcoopshop/wiki/Installation-details).

## Software documentation (only available in German)
Please read the [software documentation in German](https://github.com/foodcoopshop/foodcoopshop/wiki/Dokumentation-de).  


# Developer area

## Installation

Clone the repository and install the composer vendors (use the "--prefer-dist" option to avoid downloading VCS meta data).
```
$ composer install
```

Installing the bower components 
```
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

## Software documentation (only available in German)
Please read the [software documentation in German](https://github.com/foodcoopshop/foodcoopshop/wiki/Dokumentation-de).  

## Pull requests
Pull requests are welcome, please make sure your modifications are to the develop branch of FoodCoopShop and they are well tested!

# Links
* **Official website**: [https://www.foodcoopshop.com](https://www.foodcoopshop.com)

