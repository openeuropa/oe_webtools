<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_laco_service\StackMiddleware;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Laco service middleware.
 *
 * Looks for Laco service requests and sets an attribute on the request.
 */
class LacoServiceMiddleware extends LacoServiceMiddlewareBase {

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = TRUE): Response {
    return $this->doHandle($request, $type, $catch);
  }

}
