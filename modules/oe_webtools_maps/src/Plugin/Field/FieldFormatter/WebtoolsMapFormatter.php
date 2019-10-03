<?php

declare(strict_types = 1);

namespace Drupal\oe_webtools_maps\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\oe_webtools_maps\Component\Render\JsonEncoded;

/**
 * Displays a Geofield as a map using the Webtools Maps service.
 *
 * @FieldFormatter(
 *   id = "oe_webtools_maps_map",
 *   label = @Translation("Webtools Map"),
 *   field_types = {
 *     "geofield",
 *   },
 * )
 */
class WebtoolsMapFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'zoom_level' => 4,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements['zoom_level'] = [
      '#type' => 'range',
      '#title' => $this->t('Zoom level'),
      '#description' => $this->t('The zoom level that will be used when the map is displayed.'),
      '#default_value' => $this->getSetting('zoom_level'),
      '#min' => 0,
      '#max' => 18,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Zoom level: @zoom_level', [
      '@zoom_level' => $this->getSetting('zoom_level'),
    ]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#type' => 'html_tag',
        '#tag' => 'script',
        '#value' => new JsonEncoded([
          'service' => 'map',
          'version' => '2.0',
          'map' => [
            'zoom' => $this->getSetting('zoom_level'),
            'center' => [$item->get('lat')->getValue(), $item->get('lon')->getValue()],
          ],
        ]),
        '#attributes' => ['type' => 'application/json'],
      ];
    }

    if ($element) {
      $element['#attached'] = [
        'library' => ['oe_webtools/drupal.webtools-smartloader'],
      ];
    }

    return $element;
  }

}
