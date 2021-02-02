# OpenEuropa Webtools

[![Build Status](https://drone.fpfis.eu/api/badges/openeuropa/oe_webtools/status.svg?branch=master)](https://drone.fpfis.eu/openeuropa/oe_webtools)
[![Packagist](https://img.shields.io/packagist/v/openeuropa/oe_webtools.svg)](https://packagist.org/packages/openeuropa/oe_webtools)

[Webtools](http://ec.europa.eu/ipg/services/interactive_services/index_en.htm) integration for OpenEuropa.
Webtools are interactive services available to integrate in a website.

**Table of contents:**

- [Installation](#installation)
- [Development setup](#development-setup)
- [Contributing](#contributing)
- [Versioning](#versioning)

## Installation

The recommended way of installing the OpenEuropa Webtools module is via [Composer][1].

```bash
composer require openeuropa/oe_webtools
```

### Enable the module

In order to enable the module in your project run:

```bash
./vendor/bin/drush en oe_webtools
```

### OpenEuropa Webtools Analytics

The Webtools module contains a submodule that provides a service for providing
analytics information. For more information on how to use and configure this module,
check out the module [README](modules/oe_webtools_analytics/README.md).

### OpenEuropa Webtools eTrans

The Webtools eTrans module provides a block that will show a link to the machine
translation service of the European Commission. Visitors can click this link to
have the current page translated in their preferred language.

### OpenEuropa Webtools Laco Service

The Webtools module contains a submodule that provides a service for retrieving
information about language coverage of entity resources. For more information on 
how to use and test this module, check out the module [README](modules/oe_webtools_laco_service/README.md).

### OpenEuropa Webtools Laco Widget

The Webtools module contains a submodule that provides a widget which integrates
with the Laco service. For more information on how to use and configure this module, 
check out the module [README](modules/oe_webtools_laco_widget/README.md).

### OpenEuropa Webtools Geocoding

The Webtools module contains a submodule that provides a widget which integrates
with the Geocoding service.

#### Requirements for Webtools Geocoding

* [geocoder-php/geocoder 4.x](https://github.com/geocoder-php/Geocoder)
* [drupal/geocoder 3.x](https://www.drupal.org/project/geocoder)
* [openeuropa/webtools-geocoding-provider](https://github.com/openeuropa/webtools-geocoding-provider)

#### Webtools Geocoding Usage

If you want to use the Webtools Geocoding service, enable the submodule:

```bash
drush en oe_webtools_geocoding
```

### OpenEuropa Webtools Maps

The Webtools module contains a submodule that provides a widget which integrates
with the maps service.

#### Requirements for Webtools Maps

* [drupal/geofield 1.x](https://www.drupal.org/project/geofield)

### OpenEuropa Webtools Cookie Consent

The Webtools module contains a submodule that provides a service for providing
Cookie Consent Kit. For more information on how to use and configure this module,
check out the module [README](modules/oe_webtools_cookie_consent/README.md).

### OpenEuropa Webtools Media

The Webtools module contains a submodule that provides webtools widgets as
supported media providers.

#### Requirements for Webtools Media

* [drupal/json_field 1.x-rc3](https://www.drupal.org/project/json_field)

### OpenEuropa Webtools Social Share

The Webtools module contains a submodule that provides social sharing functionality for a site.

## Development setup

You can build the test site by running the following steps.

* Install all the composer dependencies:

```bash
composer install
```

* Customize build settings by copying `runner.yml.dist` to `runner.yml` and
changing relevant values, like your database credentials.

* Generate configuration files:

```bash
./vendor/bin/run drupal:site-setup
```

* Install the site:

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

#### Running the tests

To run the grumphp checks:

```bash
docker-compose exec web ./vendor/bin/grumphp run
```

To run the phpunit tests:

```bash
docker-compose exec web ./vendor/bin/phpunit
```

To run the behat tests:

```bash
docker-compose exec web ./vendor/bin/behat
```

## Contributing

Please read [the full documentation](https://github.com/openeuropa/openeuropa) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the available versions, see the [tags on this repository](https://github.com/openeuropa/oe_webtools/tags).

[1]: https://www.drupal.org/docs/develop/using-composer/using-composer-to-manage-drupal-site-dependencies#managing-contributed
