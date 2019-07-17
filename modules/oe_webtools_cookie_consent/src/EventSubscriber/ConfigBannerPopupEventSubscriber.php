<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_cookie_consent\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\oe_webtools_cookie_consent\Event\ConfigBannerPopupEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to the event fired when visitor data is collected for CCK.
 */
class ConfigBannerPopupEventSubscriber implements EventSubscriberInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs an ConfigBannerPopupEventSubscriber.
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
    $config = $this->configFactory->get(ConfigBannerPopupEvent::CONFIG_NAME);
    $event->addCacheableDependency($config);

    $config_data = $config->get(ConfigBannerPopupEvent::BANNER_POPUP);
    $event->setBannerPopup((boolean) $config_data);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Subscribing to listening to the Cookie Consent event.
    $events[ConfigBannerPopupEvent::NAME][] = ['onSetBannerPopup'];

    return $events;
  }

}
