<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_social_share\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\oe_webtools\Component\Render\JsonEncoded;

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
  public function defaultConfiguration() {
    return ['label_display' => FALSE];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $social_share_json = new JsonEncoded([
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
    ]);
    return [
      '#theme' => 'oe_webtools_social_share',
      '#title' => $this->t('Share this page'),
      '#icons_json' => $social_share_json,
      '#attached' => ['library' => ['oe_webtools/drupal.webtools-smartloader']],
    ];
  }

}
