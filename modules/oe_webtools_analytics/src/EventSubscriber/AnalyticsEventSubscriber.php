<?php

declare(strict_types = 1);

/**
 * @file
 * Listening to the AnalyticsEvent.
 */

namespace Drupal\oe_webtools_analytics\EventSubscriber;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\oe_webtools_analytics\AnalyticsEventInterface;
use Drupal\Core\Url;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\oe_webtools_analytics\Event\AnalyticsEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Event Subscriber AnalyticsEventSubscriber.
 */
class AnalyticsEventSubscriber implements EventSubscriberInterface {
  /**
   * The Configuration overrides instance.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $config;

  /**
   * {@inheritdoc}
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $requestStack;

  /**
   * {@inheritdoc}
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerFactory;

  /**
   * AnalyticsEventSubscriber constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request on the stack.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger channel factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory, RequestStack $requestStack, LoggerChannelFactoryInterface $loggerFactory) {
    // Get id from settings.php!
    $this->config = $configFactory->get(AnalyticsEventInterface::CONFIG_NAME);
    $this->requestStack = $requestStack;
    $this->loggerFactory = $loggerFactory->get('oe_webtools');
  }

  /**
   * Kernel request event handler.
   *
   * @param \Drupal\oe_webtools_analytics\AnalyticsEventInterface $event
   *   Response event.
   */
  public function onSetSiteDefaults(AnalyticsEventInterface $event) {
    // SiteID must exist and be an integer.
    $site_id = $this->config->get(AnalyticsEventInterface::SITE_ID);
    if (!is_numeric($site_id)) {
      $this->loggerFactory->warning('The setting "' . AnalyticsEventInterface::SITE_ID . '" is missing from settings file.');
      return;
    }

    // Setting SiteID.
    $event->setSiteId((string) $site_id);

    // SitePath handling.
    $site_path_route = Url::fromRoute('<front>', [], ['absolute' => TRUE])->toString();
    $event->setSitePath([$site_path_route]);
    if ($site_path = $this->config->get(AnalyticsEventInterface::SITE_PATH)) {
      $event->setSitePath((array) $site_path);
    }

    // Set exception flags when access is denied, or page not found.
    $request_exception = $this->requestStack
      ->getCurrentRequest()->attributes->get('exception');
    if ($request_exception instanceof NotFoundHttpException) {
      $event->setIs404Page(TRUE);
    }
    elseif ($request_exception instanceof AccessDeniedHttpException) {
      $event->setIs403Page(TRUE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Subscribing to listening to the Analytics event.
    $events[AnalyticsEvent::NAME][] = ['onSetSiteDefaults'];

    return $events;
  }

}
