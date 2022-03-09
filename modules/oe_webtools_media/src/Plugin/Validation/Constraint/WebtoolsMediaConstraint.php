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
   * The blacklist violation message.
   *
   * @var string
   */
  public $blacklistMessage = 'Service from the snippet is in the blacklist of %widget_type_name widget.';

  /**
   * The webtools widget type this constraint is checking for.
   *
   * @var string
   */
  public $widgetType;

}
