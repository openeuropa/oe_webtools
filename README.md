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

