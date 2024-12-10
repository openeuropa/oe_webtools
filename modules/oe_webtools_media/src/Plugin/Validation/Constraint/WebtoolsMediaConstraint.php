<?php

declare(strict_types=1);

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
   * The unsupported widget type message.
   *
   * @var string
   */
  public $unsupportedWidgetTypeMessage = 'The service "%widget_type" is no longer supported.';

  /**
   * The incorrect WCLOUD URL message.
   *
   * @var string
   */
  public $incorrectUrlMessage = 'The provided WCLOUD URL is not valid.';

  /**
   * The incorrect WCLOUD URL domain message.
   *
   * @var string
   */
  public $incorrectUrlDomainMessage = 'The WCLOUD URL needs to be in the europa.eu domain.';

  /**
   * The incorrect WCLOUD content message.
   *
   * @var string
   */
  public $incorrectUrlContentMessage = 'Could not parse contents of the WCLOUD URL.';

  /**
   * The inaccessible WCLOUD URL message.
   *
   * @var string
   */
  public $inaccessibleUrlMessage = 'Cannot access the contents of the URL. Please verify that it exists and it’s accessible for anonymous users.';

  /**
   * The webtools widget type this constraint is checking for.
   *
   * @var string
   */
  public $widgetType;

}
