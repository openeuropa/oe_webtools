# OpenEuropa Webtools Location Services

This is a Drupal module that provides integration with Webtools location
services, such as geocoding and maps.

## Supported services

The different Webtools location services are placed in submodules. This allows
you do only enable the submodules for the services you actually need. Currently
the following services are supported:

* [Webtools Geocoding](https://webgate.ec.europa.eu/fpfis/wikis/display/webtools/Geocoding)

## Requirements

This depends on the following software:

* [PHP 7.1](http://php.net/)

### Requirements for Webtools Geocoding

* [geocoder-php/geocoder 4.x](https://github.com/geocoder-php/Geocoder)
* [drupal/geocoder 3.x](https://www.drupal.org/project/geocoder)
* [openeuropa/webtools-geocoding-provider](https://github.com/openeuropa/webtools-geocoding-provider)

## Installation

* Install the package and its dependencies:

```bash
$ composer require openeuropa/oe_webtools_location
```

## Usage

### Webtools Geocoding

If you want to use the Webtools Geocoding service, enable the submodule:

```bash
$ drush en oe_webtools_geocoding
```

## Setting up a development environment

You can build a local development environment by executing the following steps:

### Using a local LAMP stack

*Step 1: Install dependencies*

```bash
$ composer install
```

*Step 2: Configure the environment*

Copy `runner.yml.dist` to `runner.yml` and change the configuration to match
your local environment. Typically you will need to specify `localhost` as your
database host, and change your base URL and database credentials.

*Step 3: Build*

```bash
$ ./vendor/bin/run drupal:site-setup
```

This will symlink the module in the proper directory within the test environment
and perform token substitution in test configuration files.

*Step 4: Install*

```bash
$ ./vendor/bin/run drupal:site-install
```

Your test site will be available at `./build`.

### Using Docker Compose

*Step 0: Download images*

```bash
$ docker-compose up -d
```

*Step 1: Install dependencies*

```bash
$ docker-compose exec web composer install
```

*Step 2: Configure the environment*

Copy `runner.yml.dist` to `runner.yml` and change the configuration to match
your local environment if needed. Usually this can be skipped since the module
ships with default configuration that matches the Docker environment.

*Step 3: Build*

```bash
$ docker-compose exec web ./vendor/bin/run drupal:site-setup
```

This will symlink the module in the proper directory within the test environment
and perform token substitution in test configuration files.

*Step 4: Install*

```bash
$ docker-compose exec web ./vendor/bin/run drupal:site-install
```

Your test site will be available at [http://localhost:8080/build](http://localhost:8080/build).

## Running tests

### Using a local LAMP stack

*Coding standards*

```bash
$ ./vendor/bin/grumphp run
```

*Unit tests*

```bash
$ ./vendor/bin/phpunit
```

### Using Docker Compose

*Coding standards*

```bash
$ docker-compose exec web ./vendor/bin/grumphp run
```

*Unit tests*

```bash
$ docker-compose exec web ./vendor/bin/phpunit
```
