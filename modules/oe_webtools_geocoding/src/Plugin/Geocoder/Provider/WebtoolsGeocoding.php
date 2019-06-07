<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_geocoding\Plugin\Geocoder\Provider;

use Drupal\geocoder\ProviderUsingHandlerWithAdapterBase;

/**
 * Webtools Geocoding provider for the Geocoder module.
 *
 * @GeocoderProvider(
 *   id = "webtools_geocoding",
 *   name = "Webtools Geocoding",
 *   handler = "\OpenEuropa\Provider\WebtoolsGeocoding\WebtoolsGeocoding",
 * )
 */
class WebtoolsGeocoding extends ProviderUsingHandlerWithAdapterBase {}
