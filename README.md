# OpenEuropa Webtools Location Services

[![Build Status](https://drone.fpfis.eu/api/badges/openeuropa/oe_webtools_location/status.svg?branch=master)](https://drone.fpfis.eu/openeuropa/oe_webtools_location)

This is a Drupal module that provides integration with Webtools location
services, such as geocoding and maps.

**Table of contents:**

- [Supported services](#supported-services)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Development setup](#development-setup)
- [Running tests](#running-tests)
- [Contributing](#contributing)
- [Versioning](#versioning)

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
composer require openeuropa/oe_webtools_location
```

## Usage

### Webtools Geocoding

If you want to use the Webtools Geocoding service, enable the submodule:

```bash
drush en oe_webtools_geocoding
```

## Development setup

You can build a local development environment by executing the following steps:

### Using a local LAMP stack

*Step 1: Install dependencies*

```bash
composer install
```

*Step 2: Configure the environment*

Copy `runner.yml.dist` to `runner.yml` and change the configuration to match
your local environment. Typically you will need to specify `localhost` as your
database host, and change your base URL and database credentials.

*Step 3: Install*

```bash
./vendor/bin/run drupal:site-install
```

Your test site will be available at `./build`.

### Using Docker Compose

Alternatively, you can build a development site using [Docker](https://www.docker.com/get-docker) and 
[Docker Compose](https://docs.docker.com/compose/) with the provided configuration.

Docker provides the necessary services and tools such as a web server and a database server to get the site running, 
regardless of your local host configuration.

#### Requirements:

- [Docker](https://www.docker.com/get-docker)
- [Docker Compose](https://docs.docker.com/compose/)

#### Configuration

By default, Docker Compose reads two files, a `docker-compose.yml` and an optional `docker-compose.override.yml` file.
By convention, the `docker-compose.yml` contains your base configuration and it's provided by default.
The override file, as its name implies, can contain configuration overrides for existing services or entirely new 
services.
If a service is defined in both files, Docker Compose merges the configurations.

Find more information on Docker Compose extension mechanism on [the official Docker Compose documentation](https://docs.docker.com/compose/extends/).

#### Usage

To start, run:

```bash
docker-compose up
```

It's advised to not daemonize `docker-compose` so you can turn it off (`CTRL+C`) quickly when you're done working.
However, if you'd like to daemonize it, you have to add the flag `-d`:

```bash
docker-compose up -d
```

Then:

```bash
docker-compose exec web composer install
docker-compose exec web ./vendor/bin/run drupal:site-install
```

Using default configuration, the development site files should be available in the `build` directory and the development site
should be available at: [http://127.0.0.1:8080/build](http://127.0.0.1:8080/build).

## Running tests

### Using a local LAMP stack

*Coding standards*

```bash
./vendor/bin/grumphp run
```

*Unit tests*

```bash
./vendor/bin/phpunit
```

### Using Docker Compose

*Coding standards*

```bash
docker-compose exec web ./vendor/bin/grumphp run
```

*Unit tests*

```bash
docker-compose exec web ./vendor/bin/phpunit
```

## Contributing

Please read [the full documentation](https://github.com/openeuropa/openeuropa) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the available versions, see the [tags on this repository](https://github.com/openeuropa/oe_webtools_location/tags).
