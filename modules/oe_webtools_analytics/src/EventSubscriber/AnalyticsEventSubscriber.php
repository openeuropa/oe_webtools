<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_analytics\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Url;
use Drupal\oe_webtools_analytics\AnalyticsEventInterface;
use Drupal\oe_webtools_analytics\Event\AnalyticsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Subscribes to the event fired when visitor data is collected for analytics.
 */
class AnalyticsEventSubscriber implements EventSubscriberInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs an AnalyticsEventSubscriber.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration object factory.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request on the stack.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger channel factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory, RequestStack $requestStack, LoggerChannelFactoryInterface $loggerFactory) {
    $this->configFactory = $configFactory;
    $this->requestStack = $requestStack;
    $this->loggerFactory = $loggerFactory;
  }

  /**
   * Kernel request event handler.
   *
   * @param \Drupal\oe_webtools_analytics\AnalyticsEventInterface $event
   *   Response event.
   */
  public function onSetSiteDefaults(AnalyticsEventInterface $event): void {
    $config = $this->configFactory->get(AnalyticsEventInterface::CONFIG_NAME);
    $event->addCacheableDependency($config);
    $event->addCacheContexts(['url.path']);

    // SiteID must exist.
    $site_id = $config->get(AnalyticsEventInterface::SITE_ID);
    if (empty($site_id)) {
      return;
    }

    // Setting SiteID.
    $event->setSiteId((string) $site_id);

    $instance = $config->get(AnalyticsEventInterface::INSTANCE);

    // Setting Instance.
    $event->setInstance((string) $instance);

    // SitePath handling.
    $route_options = ['absolute' => TRUE];
    $site_path_route = Url::fromRoute('<front>', [], $route_options)->toString();
    $event->setSitePath([$site_path_route]);
    if ($site_path = $config->get(AnalyticsEventInterface::SITE_PATH)) {
      $event->setSitePath((array) $site_path);
    }

    // Set exception flags when access is denied, or page not found.
    $request_exception = $this->requestStack->getCurrentRequest()->attributes->get('exception');
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
  public static function getSubscribedEvents(): array {
    // Subscribing to listening to the Analytics event.
    $events[AnalyticsEvent::NAME][] = ['onSetSiteDefaults'];

    return $events;
  }

  /**
   * Returns the logger for the OpenEuropa Webtools module.
   *
   * @return \Drupal\Core\Logger\LoggerChannelInterface
   *   The logger.
   */
  protected function getLogger(): LoggerChannelInterface {
    return $this->loggerFactory->get('oe_webtools');
  }

}
