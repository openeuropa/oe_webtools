<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_search_widget\Plugin\Block;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides Webtools Widget Search block.
 *
 * @Block(
 *   id = "oe_webtools_search_widget",
 *   admin_label = @Translation("OpenEuropa Webtools Search widget"),
 *   category = @Translation("Forms"),
 * )
 */
class SearchWidgetBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $ids = empty($this->configuration['site_ids']) ? [] : explode(',', $this->configuration['site_ids']);
    $search_widget_json = [
      'service' => 'search',
      'version' => '2.0',
      'filters' => [
        'scope' => [
          'sites' => [
            [
              'selected' => !($this->configuration['search_scope'] == 'global'),
              'name' => $this->configuration['local_label'] ?? '',
              'id' => $ids,
            ],
          ],
        ],
      ],
    ];

    // Add form selector.
    if (!empty($this->configuration['form_selector'])) {
      $search_widget_json['form'] = $this->configuration['form_selector'];
    }

    $build = [];

    $build['webtools_script'] = [
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#value' => Json::encode($search_widget_json),
      '#attributes' => ['type' => 'application/json'],
    ];

    $build['#attached']['library'][] = 'oe_webtools/drupal.webtools-smartloader';

    CacheableMetadata::createFromRenderArray($build)
      ->applyTo($build);

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'local_label' => '',
      'search_scope' => 'global',
      'site_ids' => '',
      'form_selector' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['search_scope'] = [
      '#type' => 'radios',
      '#title' => $this->t('Default search scope'),
      '#default_value' => $this->configuration['search_scope'] ?? 'local',
      '#required' => TRUE,
      '#options' => [
        'global' => $this->t('Global'),
        'local' => $this->t('Local'),
      ],
    ];

    $form['local_search'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Customize local configuration'),
      '#expanded' => TRUE,
    ];

    $form['local_search']['local_label'] = [
      '#title' => $this->t('Override local label'),
      '#type' => 'textfield',
      '#description' => $this->t('Site label to use in local mode.'),
      '#default_value' => $this->configuration['local_label'],
    ];

    $form['local_search']['site_ids'] = [
      '#title' => $this->t('Site ids'),
      '#type' => 'textfield',
      '#description' => $this->t('List of site ids to include in local scope (splitted by comma).'),
      '#default_value' => $this->configuration['site_ids'],
    ];

    $form['form_selector'] = [
      '#title' => $this->t('Form selector'),
      '#type' => 'textfield',
      '#description' => $this->t('Apply the widget to an existing form. Use directly the css selector (e.g.: .ecl-search-form).'),
      '#default_value' => $this->configuration['form_selector'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configuration['local_label'] = $values['local_search']['local_label'];
    $this->configuration['search_scope'] = $values['search_scope'];
    $this->configuration['site_ids'] = $values['local_search']['site_ids'];
    $this->configuration['form_selector'] = $values['form_selector'];
  }

}
