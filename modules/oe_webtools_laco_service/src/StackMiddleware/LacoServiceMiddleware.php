<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_laco_service\StackMiddleware;

use Drupal\oe_webtools_laco_service\LacoServiceHeaders;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Laco service middleware.
 *
 * Looks for Laco service requests and sets a format for them so that
 * "duplicate" entity routes can match on it and deliver Laco information about
 * the requested entity and language.
 */
class LacoServiceMiddleware implements HttpKernelInterface {

  /**
   * The wrapped HTTP kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * Constructs a LacoServiceMiddleware object.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel
   *   The decorated kernel.
   */
  public function __construct(HttpKernelInterface $http_kernel) {
    $this->httpKernel = $http_kernel;
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = TRUE) {
    if (!$this->isLacoServiceRequest($request)) {
      return $this->httpKernel->handle($request, $type, $catch);
    }

    $request->attributes->set('_format', 'laco');
    return $this->httpKernel->handle($request, $type, $catch);
  }

  /**
   * Checks whether the current request is a Laco request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return bool
   *   Whether the request is for Laco or not.
   */
  private function isLacoServiceRequest(Request $request) {
    if ($request->getMethod() !== 'HEAD') {
      return FALSE;
    }

    $headers = $request->headers;
    $header = LacoServiceHeaders::HTTP_HEADER_SERVICE_NAME;
    $value = LacoServiceHeaders::HTTP_HEADER_SERVICE_VALUE;
    if ($headers->get($header) === $value && $headers->has(LacoServiceHeaders::HTTP_HEADER_LANGUAGE_NAME)) {
      return TRUE;
    }

    return FALSE;
  }

}
