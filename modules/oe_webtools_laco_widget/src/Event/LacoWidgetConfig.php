<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_laco_widget\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Enable config to add and remove include and exclude parameters.
 */
class LacoWidgetConfig extends Event {

  /**
   * Declare constant.
   */
  const LACO_WIDGET_CONFIG_EVENT = 'oe_webtools.laco_widget.event';

  /**
   * Classes to be included.
   *
   * @var array
   */
  protected $include;

  /**
   * Classes to be excluded.
   *
   * @var array
   */
  protected $exclude;

  /**
   * LacoWidgetConfig constructor.
   *
   * @param array $include
   *   An array of classes to be included and display the Laco widget.
   * @param array $exclude
   *   An array of classes to be excluded from displaying the Laco widget.
   */
  public function __construct(array $include, array $exclude) {
    $this->include = $include;
    $this->exclude = $exclude;
  }

  /**
   * Get included classes.
   *
   * @return array
   *   Return an array of classes.
   */
  public function getInclude(): array {
    return $this->include;
  }

  /**
   * Set included classes.
   *
   * @param array $include
   *   An array of classes for inclusion.
   */
  public function setInclude(array $include): void {
    $this->include = $include;
  }

  /**
   * Get excluded classes.
   *
   * @return array
   *   Return an array of classes.
   */
  public function getExclude(): array {
    return $this->exclude;
  }

  /**
   * Set excluded classes.
   *
   * @param array $exclude
   *   An array of classes for exclusion.
   */
  public function setExclude(array $exclude): void {
    $this->exclude = $exclude;
  }

}
