<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_laco_widget\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 *
 */
class LacoWidgetConfig extends Event {

  /**
   *
   */
  const LACO_WIDGET_CONFIG_EVENT = 'oe_webtools.laco_widget.event';

  /**
   * @var array
   */
  protected $include;

  /**
   * @var array
   */
  protected $exclude;

  /**
   * LacoWidgetConfig constructor.
   *
   * @param array $include
   * @param array $exclude
   */
  public function __construct(array $include, array $exclude) {
    $this->include = $include;
    $this->exclude = $exclude;
  }

  /**
   * @return array
   */
  public function getInclude(): array {
    return $this->include;
  }

  /**
   * @param array $include
   */
  public function setInclude(array $include): void {
    $this->include = $include;
  }

  /**
   * @return array
   */
  public function getExclude(): array {
    return $this->exclude;
  }

  /**
   * @param array $exclude
   */
  public function setExclude(array $exclude): void {
    $this->exclude = $exclude;
  }

}
