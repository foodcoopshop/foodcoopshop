<h1 align="center">
  <a href="https://www.foodcoopshop.com"><img src="https://raw.githubusercontent.com/foodcoopshop/foodcoopshop/develop/webroot/files/images/logo.png" alt="FoodCoopShop"></a>
</h1>

<h4 align="center">User-friendly open source software for food-coops.</h4>

<p align="center">
  <a href="https://www.foodcoopshop.com/download">
    <img src="https://img.shields.io/packagist/v/foodcoopshop/foodcoopshop.svg?label=stable&style=flat-square"
         alt="Latest stable version">
  </a>
  <a href="https://github.com/foodcoopshop/foodcoopshop/actions">
    <img src="https://img.shields.io/github/workflow/status/foodcoopshop/foodcoopshop/FoodCoopShop%20CI/develop?style=flat-square"
        alt="Build status">
  </a>
  <a href="LICENSE">
    <img src="https://img.shields.io/github/license/foodcoopshop/foodcoopshop?style=flat-square"
         alt="Software license">
  </a>
</p>

<h3 align="center">
  <a href="https://www.foodcoopshop.com">Official Website</a>
  <span> · </span>
  <a href="https://foodcoopshop.github.io">Docs</a>
  <span> · </span>
  <a href="https://www.foodcoopshop.com/crowdfunding-weiterentwicklung">Crowdfunding</a>
  <span> · </span>
  <a href="https://demo-de.foodcoopshop.com">German Demo</a>
  <span> · </span>
  <a href="https://demo-en.foodcoopshop.com">English Demo</a>
  <span> · </span>
  <a href="https://foodcoopshop.github.io/en/foodcoops">Users</a>
</h3>

## ✨ Features
* user-friendly web shop optimized for selling food from different producers
* many delivery rhythms for products (once a week, every first / last friday...)
* admin area for both manufacturers and admins
* the decentralized network plugin supports synchronizing products to different installations
* a cashless payment system based on bank account transfers
* order adaptions (cancellation, adapting weight / price...)
* self-service mode for stock products (including optional barcode scanning)
* the software is webbased and available in German and English

## ✔ Requirements
* Server with **shell access** and **cronjobs**
* Apache with `mod_rewrite`
* PHP >= 7.4
* PHP intl extension INTL_ICU_VERSION >= 50.1
* PHP ZipArchive class
* MySQL >= 5.7.7 (to support utf8mb4)
* Node.js and npm >= v7 ([installation](https://www.npmjs.com/get-npm)) developer packages
* Composer v2 ([installation](https://getcomposer.org/download/)) developer packages
* Basic understanding of Apache Webserver, MySQL Database and Linux Server administration
* PHP needs to be able to call mysqldump with exec() for database backups

## ❗ Legal information
Before installing don't forget to read the legal information in [German](https://foodcoopshop.github.io/de/rechtliches) or [English](https://foodcoopshop.github.io/en/legal-information).

## 🤖 Self-hosting / developing
Our [installation guide](https://foodcoopshop.github.io/en/installation-guide) helps you.

## 😎 Maintainer
[Mario Rothauer](https://github.com/mrothauer) started the project in 2014 and maintains it.
