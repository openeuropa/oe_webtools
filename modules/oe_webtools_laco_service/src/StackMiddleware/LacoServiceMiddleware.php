<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_laco_service\StackMiddleware;

use Drupal\oe_webtools_laco_service\LacoServiceHeaders;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Laco service middleware.
 *
 * Looks for Laco service requests and sets an attribute on the request.
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
    if ($this->isLacoServiceRequest($request)) {
      $request->attributes->set('_is_laco_request', TRUE);
    }

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
  protected function isLacoServiceRequest(Request $request): bool {
    if ($request->getMethod() !== 'HEAD') {
      return FALSE;
    }

    $headers = $request->headers;
    $header = LacoServiceHeaders::HTTP_HEADER_SERVICE_NAME;
    $value = LacoServiceHeaders::HTTP_HEADER_SERVICE_VALUE;
    return $headers->get($header) === $value && $headers->has(LacoServiceHeaders::HTTP_HEADER_LANGUAGE_NAME);
  }

}
