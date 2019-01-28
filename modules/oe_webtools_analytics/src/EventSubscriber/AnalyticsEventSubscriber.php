<?php

declare(strict_types = 1);

/**
 * @file
 * Listening to the AnalyticsEvent.
 */

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
 * Event Subscriber AnalyticsEventSubscriber.
 */
class AnalyticsEventSubscriber implements EventSubscriberInterface {
  /**
   * The Configuration overrides instance.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * {@inheritdoc}
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * {@inheritdoc}
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

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
    $this->logger = $loggerFactory->get('oe_webtools');
  }

  /**
   * Kernel request event handler.
   *
   * @param \Drupal\oe_webtools_analytics\AnalyticsEventInterface $event
   *   Response event.
   */
  public function onSetSiteDefaults(AnalyticsEventInterface $event): void {
    $event->addCacheableDependency($this->config);
    $event->addCacheContexts(['url.path']);

    // SiteID must exist and be an integer.
    $site_id = $this->config->get(AnalyticsEventInterface::SITE_ID);
    if (!is_numeric($site_id)) {
      $this->logger->warning('The setting "' . AnalyticsEventInterface::SITE_ID . '" is missing from settings file.');
      return;
    }

    // Setting SiteID.
    $event->setSiteId((string) $site_id);

    $instance = $this->config->get(AnalyticsEventInterface::INSTANCE);

    // Setting Instance.
    $event->setInstance((string) $instance);

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
  public static function getSubscribedEvents(): array {
    // Subscribing to listening to the Analytics event.
    $events[AnalyticsEvent::NAME][] = ['onSetSiteDefaults'];

    return $events;
  }

}
