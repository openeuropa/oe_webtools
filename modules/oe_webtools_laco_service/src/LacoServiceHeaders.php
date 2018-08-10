<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_laco_service;

/**
 * A simple class that contains the headers used by the Laco service.
 */
final class LacoServiceHeaders {

  /**
   * HTTP method the service will be reacting to.
   */
  const HTTP_METHOD = 'HEAD';

  /**
   * HTTP Header name for requesting service.
   */
  const HTTP_HEADER_SERVICE_NAME = 'EC-Requester-Service';

  /**
   * HTTP Header value identifying the requesting service.
   */
  const HTTP_HEADER_SERVICE_VALUE = 'WEBTOOLS LACO';

  /**
   * HTTP Header name for requested language.
   */
  const HTTP_HEADER_LANGUAGE_NAME = 'EC-LACO-lang';

}
