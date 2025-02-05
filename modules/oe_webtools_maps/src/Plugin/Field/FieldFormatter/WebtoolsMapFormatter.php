<?php

declare(strict_types=1);

namespace Drupal\oe_webtools_maps\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\oe_webtools_maps\Component\Render\JsonEncoded;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a WebtoolsMapFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, ConfigFactoryInterface $configFactory) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->fieldDefinition = $field_definition;
    $this->settings = $settings;
    $this->label = $label;
    $this->viewMode = $view_mode;
    $this->thirdPartySettings = $third_party_settings;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'show_marker' => FALSE,
      'zoom_level' => 4,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements['show_marker'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show marker'),
      '#description' => $this->t('Show a marker at the provided coordinates.'),
      '#default_value' => $this->getSetting('show_marker'),
    ];

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
    $summary[] = $this->t('Zoom level: @zoom_level, Show markers: @show_marker', [
      '@zoom_level' => $this->getSetting('zoom_level'),
      '@show_marker' => $this->getSetting('show_marker') ? $this->t('Yes') : $this->t('No'),
    ]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $config = $this->configFactory->get('oe_webtools_maps.settings');
    // Fallback to version 2.0 for BC.
    $map_version = $config->get('map_version') ?? '2.0';

    foreach ($items as $delta => $item) {
      $data_array = [
        'service' => 'map',
        'version' => $map_version,
        'map' => [
          'zoom' => $this->getSetting('zoom_level'),
          'center' => [
            $item->get('lat')->getValue(),
            $item->get('lon')->getValue(),
          ],
        ],
      ];

      $markers_data = [
        'type' => 'FeatureCollection',
        'features' => [
          [
            'type' => 'Feature',
            'properties' => [
              'name' => $this->t('Coordinates'),
              'description' => $this->t('Longitude: @lon, Latitude: @lat', [
                '@lon' => $item->get('lon')->getValue(),
                '@lat' => $item->get('lat')->getValue(),
              ]),
            ],
            'geometry' => [
              'type' => 'Point',
              'coordinates' => [
                // Even though in other places the Latitude (lat) comes
                // first and the Longitude (lon) second, this array
                // requires these values to be reversed.
                $item->get('lon')->getValue(),
                $item->get('lat')->getValue(),
              ],
            ],
          ],
        ],
      ];

      if ($this->getSetting('show_marker')) {
        $data_array['layers'] = [
          'markers' => [
            [
              'data' => $markers_data,
            ],
          ],
        ];

        if ($map_version === '2.0') {
          $data_array['layers'] = [
            [
              'markers' => $markers_data,
            ],
          ];
        }
      }

      $element[$delta] = [
        '#type' => 'html_tag',
        '#tag' => 'script',
        '#value' => new JsonEncoded($data_array),
        '#attributes' => ['type' => 'application/json'],
      ];
    }

    if ($element) {
      $element['#attached'] = [
        'library' => ['oe_webtools/drupal.webtools-smartloader'],
      ];

      $cache_metadata = new CacheableMetadata();
      $cache_metadata->addCacheableDependency($config);
      $cache_metadata->applyTo($element);
    }

    return $element;
  }

}
