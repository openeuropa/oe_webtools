<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_social_share\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Social Share' Block.
 *
 * @Block(
 *   id = "social_share",
 *   admin_label = @Translation("Social Share"),
 *   category = @Translation("Webtools"),
 * )
 */
class SocialShareBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $social_share_json = [
      'service' => 'share',
      'popup' => FALSE,
      'selection' => TRUE,
      'to' => [
        'more',
        'twitter',
        'facebook',
        'linkedin',
        'e-mail',
      ],
      'stats' => TRUE,
    ];
    $libraries = ['oe_webtools/drupal.webtools-smartloader'];
    return [
      'script' => [
        '#type' => 'html_tag',
        '#tag' => 'script',
        '#value' => json_encode($social_share_json),
        '#attributes' => ['type' => 'application/json'],
        '#attached' => ['library' => $libraries],

      ],
    ];
  }

}
