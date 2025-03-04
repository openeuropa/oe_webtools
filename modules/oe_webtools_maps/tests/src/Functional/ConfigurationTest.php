<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_maps\Functional;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\oe_webtools\Traits\ApplicationJsonAssertTrait;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;

/**
 * Tests that the configured settings are correctly reflected in the page.
 */
class ConfigurationTest extends BrowserTestBase {

  use ApplicationJsonAssertTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'geofield',
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

    $this->drupalCreateContentType([
      'type' => 'test',
    ]);
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'geofield_field',
      'entity_type' => 'node',
      'type' => 'geofield',
      'settings' => [
        'backend' => 'geofield_backend_default',
      ],
    ]);
    $field_storage->save();

    FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'test',
      'settings' => [
        'backend' => 'geofield_backend_default',
      ],
    ])->save();

    /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display */
    $display = \Drupal::entityTypeManager()
      ->getStorage('entity_view_display')
      ->load('node.test.default');

    $display->setComponent('geofield_field', [
      'label' => 'above',
      'type' => 'oe_webtools_maps_map',
    ]);
    $display->save();

    EntityFormDisplay::load('node.test.default')
      ->setComponent($field_storage->getName(), [
        'type' => 'geofield_latlon',
      ])
      ->save();

    $this->drupalLogin($this->drupalCreateUser([], NULL, TRUE));
  }

  /**
   * Tests if changing configuration changes the map JSON.
   */
  public function testConfigurationChanges(): void {
    $node = Node::create([
      'type' => 'test',
      'user_id' => 1,
      'title' => 'My map',
      'geofield_field' => [
        [
          'value' => 'POINT (-2.1021 42.2257)',
        ],
      ],
    ]);
    $node->save();

    $this->drupalGet('/node/1');

    // New installations receive map version 3.0 by default.
    $this->assertBodyContainsApplicationJson('{"service":"map","version":"3.0","map":{"zoom":4,"center":[42.2257,-2.1021]}}');

    // Change the config, assert the map changed.
    $this->drupalGet('admin/config/system/oe_webtools_maps');
    $page = $this->getSession()->getPage();
    $page->selectFieldOption('Map Version', '2.0');
    $page->pressButton('Save configuration');

    $this->drupalGet('/node/1');
    $this->assertBodyContainsApplicationJson('{"service":"map","version":"2.0","map":{"zoom":4,"center":[42.2257,-2.1021]}}');

    // Change it back to version 3.0.
    $this->drupalGet('admin/config/system/oe_webtools_maps');
    $page = $this->getSession()->getPage();
    $page->selectFieldOption('Map Version', '3.0');
    $page->pressButton('Save configuration');

    $this->drupalGet('/node/1');
    $this->assertBodyContainsApplicationJson('{"service":"map","version":"3.0","map":{"zoom":4,"center":[42.2257,-2.1021]}}');

    // Delete the config to emulate an upgrade scenario.
    \Drupal::configFactory()->getEditable('oe_webtools_maps.settings')
      ->delete();
    $this->drupalGet('/node/1');
    $this->assertBodyContainsApplicationJson('{"service":"map","version":"2.0","map":{"zoom":4,"center":[42.2257,-2.1021]}}');
  }

}
