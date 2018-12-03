<?php

namespace Drupal\oe_webtools_maps\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Displays a Geofield as a map using the Webtools Maps service.
 *
 * @FieldFormatter(
 *   id = "oe_webtools_maps_map",
 *   label = @Translation("Webtools Map"),
 *   field_types = {
 *     "geofield"
 *   }
 * )
 */
class WebtoolsMapFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'foo' => 'bar',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $elements['foo'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Foo'),
      '#default_value' => $this->getSetting('foo'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->t('Foo: @foo', ['@foo' => $this->getSetting('foo')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#type' => 'item',
        '#markup' => <<<EOD
<script type="application/json">{
  "service": "map",
  "custom": "$module_url/js/semic_community.js"
}</script>
EOD,
        '#attached' => [
          'library' => ['oe_webtools_maps/eu.webtools.load'],
        ],
        // '#attached']['drupalSettings']['semic']['map']['adoptersListUrl'] = "$module_url/adopters.xml";
        '#prefix'] = semic_map_markup($module_url);
      ];
    }

    return $element;
  }

}
