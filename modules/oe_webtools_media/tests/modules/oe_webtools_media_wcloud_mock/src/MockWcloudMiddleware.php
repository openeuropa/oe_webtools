<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_media_wcloud_mock;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

/**
 * Mock a wcloud response.
 */
class MockWcloudMiddleware {

  /**
   * Invoked method that returns a promise.
   */
  public function __invoke() {
    return function ($handler) {
      return function (RequestInterface $request, array $options) use ($handler) {
        $uri = $request->getUri();

        if ($uri->getHost() === 'europa.eu' && $uri->getPath() === '/correct-wcloud') {
          $parameters = \GuzzleHttp\Psr7\parse_query($uri->getQuery());
          $response = new Response(200, [], '{"service": "' . $parameters['widget'] . '"}');
          return new FulfilledPromise($response);
        }

        // Otherwise, no intervention. We defer to the handler stack.
        return $handler($request, $options);
      };
    };
  }

}
