<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools\Plugin\Geocoder\Provider;

use Drupal\geocoder\ProviderUsingHandlerWithAdapterBase;

/**
 * Webtools Geocoding provider for the Geocoder module.
 *
 * @GeocoderProvider(
 *   id = "webtools_geocoding",
 *   name = "WebtoolsGeocoding",
 *   handler = "\OpenEuropa\Provider\WebtoolsGeocoding\WebtoolsGeocoding",
 * )
 */
class WebtoolsGeocoding extends ProviderUsingHandlerWithAdapterBase {}
