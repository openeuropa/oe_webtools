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
  public $blacklistMessage = 'This service is supported by a dedicated asset type or feature, please use that instead.';

  /**
   * The incorrect wcloud url message.
   *
   * @var string
   */
  public $incorrectUrlMessage = 'The provided wcloud url is not valid.';

  /**
   * The incorrect wcloud url domain message.
   *
   * @var string
   */
  public $incorrectUrlDomainMessage = 'The wcloud url needs to be in the europa.eu domain.';

  /**
   * The incorrect wcloud content message.
   *
   * @var string
   */
  public $incorrectUrlContentMessage = 'Could not parse contents of the wcloud url.';

  /**
   * The webtools widget type this constraint is checking for.
   *
   * @var string
   */
  public $widgetType;

}
