<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_cookie_consent\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\oe_webtools_cookie_consent\Event\ConfigBannerPopupEvent;
use Drupal\oe_webtools_cookie_consent\Event\ConfigVideoPopupEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to the event fired when visitor data is collected for CCK.
 */
class WebtoolsCookieConsentEventSubscriber implements EventSubscriberInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The CCK configuration name.
   */
  public const CONFIG_NAME = 'oe_webtools_cookie_consent.settings';

  /**
   * Constructs an WebtoolsCookieConsentEventSubscriber.
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
   * @param \Drupal\oe_webtools_cookie_consent\Event\ConfigBannerPopupEvent $event
   *   Response event.
   */
  public function onSetBannerPopup(ConfigBannerPopupEvent $event): void {
    $config = $this->configFactory->get(static::CONFIG_NAME);
    $event->addCacheableDependency($config);

    $config_data = $config->get('banner_popup');
    $event->setBannerPopup((boolean) $config_data);
  }

  /**
   * Kernel request event handler.
   *
   * @param \Drupal\oe_webtools_cookie_consent\Event\ConfigVideoPopupEvent $event
   *   Response event.
   */
  public function onSetVideoPopup(ConfigVideoPopupEvent $event): void {
    $config = $this->configFactory->get(static::CONFIG_NAME);
    $event->addCacheableDependency($config);

    $config_data = $config->get('video_popup');
    $event->setVideoPopup((boolean) $config_data);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Subscribing to listening to the Cookie Consent event.
    $events[ConfigBannerPopupEvent::NAME][] = ['onSetBannerPopup'];
    $events[ConfigVideoPopupEvent::NAME][] = ['onSetVideoPopup'];

    return $events;
  }

}
