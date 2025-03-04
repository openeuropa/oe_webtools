<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_maps\Functional;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Tests\BrowserTestBase;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Tests the Webtools maps formatter.
 */
class WebtoolsMapsFormatterTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'oe_webtools_maps',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);
    FieldStorageConfig::create([
      'field_name' => 'wmaps',
      'entity_type' => 'node',
      'type' => 'geofield',
      'settings' => [
        'backend' => 'geofield_backend_default',
      ],
      'cardinality' => 1,
    ])->save();
    FieldConfig::create([
      'entity_type' => 'node',
      'field_name' => 'wmaps',
      'bundle' => 'page',
      'settings' => [],
    ])->save();

    \Drupal::service('entity_display.repository')->getViewDisplay('node', 'page', 'default')
      ->setComponent('wmaps', [
        'weight' => 1,
        'region' => 'content',
        'type' => 'oe_webtools_maps_map',
        'settings' => [
          'show_marker' => TRUE,
          'zoom_level' => 4,
        ],
        'third_party_settings' => [],
      ])
      ->save();
  }

  /**
   * Test the Webtools maps formatter.
   */
  public function testWebtoolsMapsFormatter(): void {
    $node = $this->drupalCreateNode(['type' => 'page', 'title' => 'Page node']);
    $value = \Drupal::service('geofield.wkt_generator')->WktGenerateGeometry();
    $node->set('wmaps', $value);
    $node->save();

    $this->drupalGet($node->toUrl());

    $geom = \Drupal::service('geofield.geophp')->load($value);
    $centroid = $geom->getCentroid();
    $lon = $centroid->getX();
    $lat = $centroid->getY();

    $webtools_maps = Json::decode($this->getSession()->getPage()->find('css', 'script[type="application/json"]')->getHtml());
    $this->assertEquals([
      'service' => 'map',
      'version' => '3.0',
      'map' => [
        'zoom' => 4,
        'center' => [
          $lat,
          $lon,
        ],
      ],
      'layers' => [
        'markers' => [
          [
            'data' => [
              'type' => 'FeatureCollection',
              'features' => [
                [
                  'type' => 'Feature',
                  'properties' => [
                    'name' => 'Coordinates',
                    'description' => "Longitude: $lon, Latitude: $lat",
                  ],
                  'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [
                      $lon,
                      $lat,
                    ],
                  ],
                ],
              ],
            ],
          ],
        ],
      ],
    ], $webtools_maps);

    EntityViewDisplay::load('node.page.default')
      ->setComponent('wmaps', [
        'type' => 'oe_webtools_maps_map',
        'settings' => [
          'show_marker' => FALSE,
          'zoom_level' => 4,
        ],
      ])
      ->save();

    $this->drupalGet($node->toUrl());

    $webtools_maps = Json::decode($this->getSession()->getPage()->find('css', 'script[type="application/json"]')->getHtml());
    $this->assertEquals([
      'service' => 'map',
      'version' => '3.0',
      'map' => [
        'zoom' => 4,
        'center' => [
          $lat,
          $lon,
        ],
      ],
    ], $webtools_maps);
  }

}
