<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_cookie_consent\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\oe_webtools_cookie_consent\Event\ConfigVideoPopupEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to the event fired when visitor data is collected for CCK.
 */
class ConfigVideoPopupEventSubscriber implements EventSubscriberInterface {

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
   * @param \Drupal\oe_webtools_cookie_consent\Event\ConfigVideoPopupEvent $event
   *   Response event.
   */
  public function onSetVideoPopup(ConfigVideoPopupEvent $event): void {
    $config = $this->configFactory->get(ConfigVideoPopupEvent::CONFIG_NAME);
    $event->addCacheableDependency($config);

    $config_data = $config->get(ConfigVideoPopupEvent::VIDEO_POPUP);
    $event->setVideoPopup((boolean) $config_data);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Subscribing to listening to the Cookie Consent event.
    $events[ConfigVideoPopupEvent::NAME][] = ['onSetVideoPopup'];

    return $events;
  }

}
