<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_media\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks if webtools media is valid.
 *
 * @Constraint(
 *   id = "ValidWebtoolsMedia",
 *   label = @Translation("Valid webtools media", context = "Validation"),
 *   type = { "json" }
 * )
 */
class WebtoolsMediaConstraint extends Constraint {

  /**
   * The default violation message.
   *
   * @var string
   */
  public $message = 'Invalid Webtools %widget_type_name snippet.';

  /**
   * The webtools widget type this constraint is checking for.
   *
   * @var string
   */
  public $widgetType;

}
