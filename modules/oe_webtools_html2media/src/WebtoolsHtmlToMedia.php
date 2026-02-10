<?php

namespace Drupal\oe_webtools_html2media;

use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Messenger\Messenger;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Utility\UrlHelper;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Drupal\Component\Serialization\Json;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Get pdf version from webtools webservice.
 */
class WebtoolsHtmlToMedia {

  use StringTranslationTrait;

  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;

  /**
   * Show messages of operations.
   *
   * @var Drupal\Core\Messenger\Legacy\Messenger
   */
  protected $messenger;

  /**
   * The Http Client.
   *
   * @var \Drupal\http_client_manager\ClientInterface
   */
  protected $httpClient;


  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Configuration service.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger
   *   The logger.
   * @param Drupal\Core\Messenger\Messenger $messenger
   *   Message.
   * @param GuzzleHttp\ClientInterface $httpClient
   *   Client HTTP.
   */
  public function __construct(ConfigFactoryInterface $configFactory, LoggerChannelFactory $logger, Messenger $messenger, ClientInterface $httpClient) {
    $this->logger = $logger->get('oe_webtools_html2media');
    $this->messenger = $messenger;
    $this->configFactory = $configFactory;
    $this->httpClient = $httpClient;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client')
    );
  }

  /**
   * Log to watchdog and user interface.
   *
   * @param string $message
   *   The message to show.
   */
  protected function logError(string $message) {
    $this->logger->error($message);
    $this->messenger->addError($message);
  }

  /**
   * Test http response code of a page.
   *
   * @param string $url
   *   The page to convert to pdf.
   *
   * @return int
   *   The http response code.
   */
  public function testPageResponseCode(string $url) {
    try {
      $response = $this->httpClient->head($url);
      return $response->getStatusCode();
    }
    catch (RequestException $e) {
      if ($e->hasResponse()) {
        $response = $e->getResponse();
        // Log the error.
        $this->logError($this->t("Invalid url to print @url (@status_code @reason)",
        [
          '@url' => $url,
          '@status_code' => $response->getStatusCode(),
          '@reason' => $response->getReasonPhrase(),
        ]));
      }
      return FALSE;
    }
  }

  /**
   * Use webtools webservice to get a binary version of the page.
   *
   * @param string $page_url
   *   The page to convert to pdf.
   * @param array $options
   *   The webservice option to convert to pdf.
   * @param bool $verify_url
   *   Check if provided url returns 200.
   */
  public function getMedia(string $page_url, array $options = [], bool $verify_url = TRUE) {

    // Test the url if it returns 200 before passing it to the webservice.
    if ($verify_url !== FALSE) {
      $response_code = $this->testPageResponseCode($page_url);
      if ($response_code != 200) {
        return FALSE;
      }
    }

    // Set options.
    $orientation = $options['orientation'] ?? 'portrait';
    $load_delay = $options['load_delay'] ?? 200;
    $output_format = $options['output_format'] ?? 'pdf';
    $format = $options['format'] ?? 'A4';

    // Get webservice url.
    $webservice_url = $this->configFactory->get('oe_webtools_html2media.settings')->get('url');

    if (!UrlHelper::isValid($webservice_url)) {
      $this->logError($this->t("Webtools webservice url is not valid"));
      return FALSE;
    }

    try {
      $ws = $this->httpClient->post($webservice_url, [
        'form_params' => [
          'url' => $page_url,
          'output_format' => $output_format,
          'format' => $format,
          'load_delay' => $load_delay,
          'orientation' => $orientation,
        ],
        'headers' => [
          'Content-type' => 'application/x-www-form-urlencoded',
        ],
      ])->getBody()->getContents();

      $ws = Json::decode($ws);
      if ($ws['wtstatus']['success'] == 1) {
        // kint($ws);
        return $ws['output'];
      }
      else {
        $this->logError($this->t("Webtools webservice error: @error", ['@error' => $ws['wtstatus']['error']]));
        return FALSE;
      }
    }

    catch (RequestException $e) {
      if ($e->hasResponse()) {
        $response = $e->getResponse();
        // Log the error.
        $this->logError($this->t("Error when trying to reach Webtools webservice: @status_code @reason.",
        [
          '@status_code' => $response->getStatusCode(),
          '@reason' => $response->getReasonPhrase(),
        ]));
      }
      return FALSE;
    }
  }

}
