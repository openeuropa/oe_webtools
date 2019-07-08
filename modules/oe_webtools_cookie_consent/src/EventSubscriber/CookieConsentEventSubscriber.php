<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_cookie_consent\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\oe_webtools_cookie_consent\CookieConsentEventInterface;
use Drupal\oe_webtools_cookie_consent\Event\CookieConsentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to the event fired when visitor data is collected for CCK.
 */
class CookieConsentEventSubscriber implements EventSubscriberInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs an CookieConsentEventSubscriber.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The configuration object factory.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * Kernel request event handler.
   *
   * @param \Drupal\oe_webtools_cookie_consent\CookieConsentEventInterface $event
   *   Response event.
   */
  public function onSetCckEnabled(CookieConsentEventInterface $event): void {
    $config = $this->configFactory->get(CookieConsentEventInterface::CONFIG_NAME);
    $event->addCacheableDependency($config);

    // Setting CckEnabled.
    $cck_enabled = $config->get(CookieConsentEventInterface::CCK_ENABLED);
    $event->setCckEnabled((boolean) $cck_enabled);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Subscribing to listening to the Cookie Consent event.
    $events[CookieConsentEvent::NAME][] = ['onSetCckEnabled'];

    return $events;
  }

}