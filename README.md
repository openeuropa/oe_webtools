# OpenEuropa Webtools

[![Build Status](https://drone.fpfis.eu/api/badges/openeuropa/oe_webtools/status.svg?branch=master)](https://drone.fpfis.eu/openeuropa/oe_webtools)

Webtools integration for OpenEuropa.

## Development setup

You can build the test site by running the following steps.

* Install all the composer dependencies:

```
$ composer install
```

* Customize build settings by copying `runner.yml.dist` to `runner.yml` and
changing relevant values, like your database credentials.

* Setup test site by running:

```
$ ./vendor/bin/run drupal:site-setup
```

This will symlink the theme in the proper directory within the test site and
perform token substitution in test configuration files such as `phpunit.xml.dist`.

* Install test site by running:

```
$ ./vendor/bin/run drupal:site-install
```

Your test site will be available at `./build`.

### Using Docker Compose

Alternatively you can build a test site using Docker and Docker-compose with the provided configuration.

Requirements:

- [Docker](https://www.docker.com/get-docker)
- [Docker-compose](https://docs.docker.com/compose/)

Run:

```
$ docker-compose up -d
```

Then:

```
$ docker-compose exec web composer install
$ docker-compose exec web ./vendor/bin/run drupal:site-setup
$ docker-compose exec web ./vendor/bin/run drupal:site-install
```

Your test site will be available at [http://localhost:8080/build](http://localhost:8080/build).

To run the grumphp test:

```
$ docker-compose exec web ./vendor/bin/grumphp run
```

To run the phpunit test:

```
$ docker-compose exec web ./vendor/bin/phpunit
```

## OpenEuropa Webtools Laco Widget

The Webtools module contains a submodule that provides a widget which integrates
with the Laco service. For more information on how to use and configure this module, 
check out the module [README](modules/oe_webtools_laco_widget/README.md).