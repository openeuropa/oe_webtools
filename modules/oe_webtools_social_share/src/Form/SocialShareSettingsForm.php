<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_social_share\Form;

use Drupal\Component\Utility\SortArray;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides configuration form for the Social share webtools widget.
 */
class SocialShareSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'oe_webtools_social_share_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['oe_webtools_social_share.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('oe_webtools_social_share.settings');

    $form['icons'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display only icons'),
      '#description' => $this->t('Check this box if you would like to display only the icons without labels for the Social share block.'),
      '#default_value' => $config->get('icons'),
    ];

    $form['custom_networks'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Customize social networks'),
      '#default_value' => $config->get('custom_networks'),
    ];

    $form['networks_wrapper'] = [
      '#type' => 'container',
      '#tree' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="custom_networks"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['networks_wrapper']['description'] = [
      '#markup' => $this->t('Drag to reorder networks. Visible networks appear directly on screen. Enabled but not visible networks appear under the "More share options" link. Disabled networks are hidden.'),
    ];
    $form['networks_wrapper']['networks'] = [
      '#type' => 'table',
      '#tree' => TRUE,
      '#header' => [
        $this->t('Network'),
        $this->t('Enabled'),
        $this->t('Visible'),
        $this->t('Weight'),
      ],
      '#empty' => $this->t('No networks available'),
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'network-weight',
        ],
      ],
    ];

    $networks = $config->get('networks') ?? [];
    foreach ($this->socialNetworks() as $network) {
      if (!isset($networks[$network])) {
        $networks[$network] = [
          'enabled' => 0,
          'visible' => 0,
          'weight' => count($networks),
        ];
      }
      else {
        $networks[$network]['enabled'] = 1;
      }
      $networks[$network]['name'] = Unicode::ucfirst($network);
    }
    uasort($networks, [SortArray::class, 'sortByWeightElement']);

    foreach ($networks as $network => $settings) {
      $form['networks_wrapper']['networks'][$network]['#attributes']['class'][] = 'draggable';
      $form['networks_wrapper']['networks'][$network]['name'] = [
        '#markup' => $settings['name'],
      ];
      $form['networks_wrapper']['networks'][$network]['enabled'] = [
        '#type' => 'checkbox',
        '#default_value' => $settings['enabled'],
      ];
      $form['networks_wrapper']['networks'][$network]['visible'] = [
        '#type' => 'checkbox',
        '#default_value' => $settings['visible'],
        '#states' => [
          'disabled' => [
            ':input[name="networks_wrapper[networks][' . $network . '][enabled]"]' => ['checked' => FALSE],
          ],
        ],
      ];
      $form['networks_wrapper']['networks'][$network]['weight'] = [
        '#type' => 'weight',
        '#default_value' => $settings['weight'],
        '#attributes' => [
          'class' => ['network-weight'],
        ],
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $custom_networks = (bool) $form_state->getValue('custom_networks');
    if ($custom_networks) {
      $networks = $form_state->getValue(['networks_wrapper', 'networks']) ?? [];
      $enabled = FALSE;
      foreach ($networks as $settings) {
        if (!empty($settings['enabled'])) {
          $enabled = TRUE;
          break;
        }
      }

      if (!$enabled) {
        $form_state->setErrorByName('networks_wrapper', $this->t('At least one social network must be enabled if you choose to customize networks.'));
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $custom_networks = (bool) $form_state->getValue('custom_networks');
    if ($custom_networks) {
      $networks = $form_state->getValue(['networks_wrapper', 'networks']) ?? [];
      // Filter out disabled networks entirely.
      $networks = array_filter($networks, function ($settings) {
        return !empty($settings['enabled']);
      });

      // Sort the remaining networks by weight.
      uasort($networks, [SortArray::class, 'sortByWeightElement']);

      // Clean up the settings and recalculate the weight.
      $weight = 0;
      foreach ($networks as &$settings) {
        unset($settings['enabled']);
        $settings['visible'] = (bool) $settings['visible'];
        $settings['weight'] = $weight++;
      }
      unset($settings);
    }

    $this->config('oe_webtools_social_share.settings')
      ->set('icons', $form_state->getValue('icons'))
      ->set('custom_networks', $custom_networks)
      ->set('networks', $networks ?? [])
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * Get a list of webtools social networks.
   *
   * @return array
   *   Webtools social networks.
   */
  protected function socialNetworks(): array {
    return [
      'x',
      'facebook',
      'linkedin',
      'email',
      'pinterest',
      'blogger',
      'pocket',
      'tumblr',
      'yammer',
      'digg',
      'reddit',
      'print',
      'viadeo',
      'typepad',
      'threads',
      'netvibes',
      'gmail',
      'yahoomail',
      'qzone',
      'weibo',
      'whatsapp',
      'mastodon',
      'bluesky',
    ];
  }

}
