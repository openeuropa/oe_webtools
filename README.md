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

This will build a fully functional Drupal test site in the `./build` directory that can be used to develop and showcase
the module's functionality.

Before setting up and installing the site make sure to customize default configuration values by copying [runner.yml.dist](runner.yml.dist)
to `./runner.yml` and overriding relevant properties.

This will also:
- Symlink the theme in  `./build/modules/custom/oe_webtools` so that it's available for the test site
- Setup Drush and Drupal's settings using values from `./runner.yml.dist`. This includes adding parameters for EULogin
- Setup PHPUnit and Behat configuration files using values from `./runner.yml.dist`

**Please note:** project files and directories are symlinked within the test site by using the
[OpenEuropa Task Runner's Drupal project symlink](https://github.com/openeuropa/task-runner-drupal-project-symlink) command.

If you add a new file or directory in the root of the project, you need to re-run `drupal:site-setup` in order to make
sure they are be correctly symlinked.

If you don't want to re-run a full site setup for that, you can simply run:

```
$ ./vendor/bin/run drupal:symlink-project
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

#### Step debugging

To enable step debugging from the command line, pass the `XDEBUG_SESSION` environment variable with any value to
the container:

```bash
docker-compose exec -e XDEBUG_SESSION=1 web <your command>
```

Please note that, starting from XDebug 3, a connection error message will be outputted in the console if the variable is
set but your client is not listening for debugging connections. The error message will cause false negatives for PHPUnit
tests.

To initiate step debugging from the browser, set the correct cookie using a browser extension or a bookmarklet
like the ones generated at https://www.jetbrains.com/phpstorm/marklets/.

## Contributing

Please read [the full documentation](https://github.com/openeuropa/openeuropa) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the available versions, see the [tags on this repository](https://github.com/openeuropa/oe_webtools/tags).

[1]: https://www.drupal.org/docs/develop/using-composer/using-composer-to-manage-drupal-site-dependencies#managing-contributed
