<?php

namespace Drupal\oe_webtools_html2media\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\oe_webtools_html2media\WebtoolsHtmlToMedia;

/**
 * Controller routines for Grow Print Version routes.
 */
class WebtoolsToMediaController extends ControllerBase {

  /**
   * ATOF webtools.
   *
   * @var \Drupal\oe_webtools_html2media\WebtoolsHtmlToMedia
   */
  protected $webtools;

  /**
   * PrintVersionController constructor.
   *
   * @param \Drupal\oe_webtools_html2media\WebtoolsHtmlToMedia $webtools
   *   Webtools.
   */
  public function __construct(WebtoolsHtmlToMedia $webtools) {
    $this->webtools = $webtools;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('oe_webtools_html2media.webtools')
    );
  }

  /**
   * Callback for webtools version.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return string
   *   returns the binary, which will end up being a page.
   */
  public function getPage(Request $request) {
    // Get url parameters.
    $params = $request->query->all();

    // Set url to convert.
    $url = $params['url'] ?? '';
    if (empty($url)) {
      return [
        '#markup' => $this->t('Error: please provide the url of the page to convert'),
      ];
    }

    $print_url = $this->webtools->getMedia($url, $params);

    if (!empty($print_url)) {
      $response_headers = ['Cache-Control' => 'no-cache, no-store, must-revalidate'];
      $response = new TrustedRedirectResponse($print_url, 302, $response_headers);
      $response->addCacheableDependency($print_url);
      return $response;
    }
    else {
      // Allow user to try again in case of temporary failure.
      $current_url = Url::fromRoute('<current>', $params)->setAbsolute()->toString();
      return [
        '#markup' => $this->t('Error processing your request. Please <a href=":url">Try again</a>', [':url' => $current_url]),
      ];

    }

  }

}
