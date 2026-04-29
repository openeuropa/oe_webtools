<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_wepick_icons\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'oe_webtools_wepick_icons' widget.
 *
 * @FieldWidget(
 *   id = "oe_webtools_wepick_icons",
 *   label = @Translation("Webtools WePick Icons"),
 *   description = @Translation("An icon picker widget using the Webtools pickicons service."),
 *   field_types = {
 *     "oe_webtools_wepick_icons"
 *   }
 * )
 */
class WePickIconsWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'modal_title' => t('Icon picker'),
      'include_names' => '',
      'include_families' => '',
      'include_tags' => '',
      'exclude_names' => '',
      'exclude_families' => '',
      'exclude_tags' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['modal_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Modal title'),
      '#default_value' => $this->getSetting('modal_title'),
      '#description' => $this->t('The title of the icon picker modal.'),
    ];

    $element['include_names'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Include icon names'),
      '#default_value' => $this->getSetting('include_names'),
      '#description' => $this->t('Comma-separated list of icon names to include.'),
    ];

    $element['include_families'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Include icon families'),
      '#default_value' => $this->getSetting('include_families'),
      '#description' => $this->t('Comma-separated list of icon families to include.'),
    ];

    $element['include_tags'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Include tags'),
      '#default_value' => $this->getSetting('include_tags'),
      '#description' => $this->t('Comma-separated list of tags to include.'),
    ];

    $element['exclude_names'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Exclude icon names'),
      '#default_value' => $this->getSetting('exclude_names'),
      '#description' => $this->t('Comma-separated list of icon names to exclude.'),
    ];

    $element['exclude_families'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Exclude icon families'),
      '#default_value' => $this->getSetting('exclude_families'),
      '#description' => $this->t('Comma-separated list of icon families to exclude.'),
    ];

    $element['exclude_tags'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Exclude tags'),
      '#default_value' => $this->getSetting('exclude_tags'),
      '#description' => $this->t('Comma-separated list of tags to exclude.'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = $this->t('Modal title: @title', ['@title' => $this->getSetting('modal_title')]);

    $include_parts = array_filter([
      $this->getSetting('include_names'),
      $this->getSetting('include_families'),
      $this->getSetting('include_tags'),
    ]);
    if ($include_parts) {
      $summary[] = $this->t('Include filters active');
    }

    $exclude_parts = array_filter([
      $this->getSetting('exclude_names'),
      $this->getSetting('exclude_families'),
      $this->getSetting('exclude_tags'),
    ]);
    if ($exclude_parts) {
      $summary[] = $this->t('Exclude filters active');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element += [
      '#type' => 'oe_webtools_wepick_icons',
      '#modal_title' => $this->getSetting('modal_title'),
      '#include' => $this->buildFilterArray('include'),
      '#exclude' => $this->buildFilterArray('exclude'),
      '#default_value' => [
        'icon_name' => $items[$delta]->icon_name ?? '',
        'icon_family' => $items[$delta]->icon_family ?? '',
      ],
    ];

    return $element;
  }

  /**
   * Builds a filter array from comma-separated widget settings.
   *
   * @param string $type
   *   The filter type: 'include' or 'exclude'.
   *
   * @return array
   *   The filter array with 'name', 'family', and 'tags' keys.
   */
  protected function buildFilterArray(string $type): array {
    $filter = [];
    $map = [
      'names' => 'name',
      'families' => 'family',
      'tags' => 'tags',
    ];

    foreach ($map as $setting_suffix => $json_key) {
      $value = $this->getSetting("{$type}_{$setting_suffix}");
      if (!empty($value)) {
        $filter[$json_key] = array_filter(array_map('trim', explode(',', $value)));
      }
    }

    return $filter;
  }

}
