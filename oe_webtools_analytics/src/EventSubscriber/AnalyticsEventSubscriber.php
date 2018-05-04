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
    if ($configFactory->get('oe_webtools.analytics')) {
      $this->config = $configFactory->get('oe_webtools.analytics');
      $this->requestStack = $requestStack;
    }
    else {
      throw new \InvalidArgumentException(t('The "oe_webtools.analytics" settings is missing from settings.php', [], ['context' => 'oe_webtools']));
    }
  }

  /**
   * Kernel request event handler.
   *
   * @param \Drupal\oe_webtools_analytics\Event\AnalyticsEvent $event
   *   Response event.
   */
  public function onSetSiteDefaults(AnalyticsEventInterface $event) {
    // SiteID.
    $site_id = $this->config->get(AnalyticsEventInterface::SITE_ID);
    if ($site_id) {
      $event->setSiteId((string) $site_id);
    }
    // SitePath.
    if (\Drupal::configFactory()->get('oe_webtools.analytics')->get(AnalyticsEventInterface::SITE_PATH)) {
      $event->setSitePath((array) $this->config->get(AnalyticsEventInterface::SITE_PATH));
    }
    else {
      $event->setSitePath((array) ($_SERVER['HTTP_HOST'] . Url::fromRoute('<front>')->toString()));
    }

    // Set exception flags when access is denied, or page not found.
    $request_exception = $this->requestStack->getCurrentRequest()->attributes->get('exception');
    if ($request_exception instanceof NotFoundHttpException) {
      $event->setIs404Page();
    }

    if ($request_exception instanceof AccessDeniedHttpException) {
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
