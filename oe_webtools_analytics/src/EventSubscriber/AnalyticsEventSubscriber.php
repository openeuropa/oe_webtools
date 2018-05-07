<?php

declare(strict_types = 1);

/**
 * @file
 * Listening to the AnalyticsEvent.
 */

namespace Drupal\oe_webtools_analytics\EventSubscriber;

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
  protected $config;

  /**
   * {@inheritdoc}
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * AnalyticsEventSubscriber constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration object.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request on the stack.
   */
  public function __construct(ConfigFactoryInterface $configFactory, RequestStack $requestStack) {
    // Get id from settings.php!
    if (!$configFactory->get('oe_webtools.analytics')) {
      throw new \InvalidArgumentException(t('The "oe_webtools.analytics" settings is missing from settings.php', [], ['context' => 'oe_webtools']));
    }
    $this->config = $configFactory->get('oe_webtools.analytics');
    $this->requestStack = $requestStack;
  }

  /**
   * Kernel request event handler.
   *
   * @param \Drupal\oe_webtools_analytics\AnalyticsEventInterface $event
   *   Response event.
   */
  public function onSetSiteDefaults(AnalyticsEventInterface $event) {
    // SiteID.
    if ($site_id = $this->config->get(AnalyticsEventInterface::SITE_ID)) {
      $event->setSiteId((string) $site_id);
    }

    // SitePath.
    $event->setSitePath((array) ($_SERVER['HTTP_HOST'] . Url::fromRoute('<front>')->toString()));
    if ($site_path = $this->config->get(AnalyticsEventInterface::SITE_PATH)) {
      $event->setSitePath((array) $site_path);
    }

    // Set exception flags when access is denied, or page not found.
    $request_exception = $this->requestStack->getCurrentRequest()->attributes->get('exception');
    if ($request_exception instanceof NotFoundHttpException) {
      $event->setIs404Page();
    }
    elseif ($request_exception instanceof AccessDeniedHttpException) {
      $event->setIs403Page();
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
