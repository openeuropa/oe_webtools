<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_wtag\Functional;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the Wtag widget using the Wtag form element.
 */
class WtagWidgetTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'oe_webtools_wtag',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test the Wtag widget.
   */
  public function testWtagWidget(): void {
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);
    // Create a SKOS reference field.
    FieldStorageConfig::create([
      'field_name' => 'wtag',
      'entity_type' => 'node',
      'type' => 'skos_concept_entity_reference',
      'settings' => [
        'target_type' => 'skos_concept',
      ],
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    ])->save();
    FieldConfig::create([
      'entity_type' => 'node',
      'field_name' => 'wtag',
      'bundle' => 'page',
      'settings' => [
        'handler' => 'default:skos_concept',
        'handler_settings' => [
          'concept_schemes' => [],
        ],
      ],
    ])->save();
    $entity_form_display = EntityFormDisplay::collectRenderDisplay(Node::create(['type' => 'page']), 'default');
    $entity_form_display->setComponent('wtag', [
      'weight' => 1,
      'region' => 'content',
      'type' => 'oe_webtools_wtag',
      'settings' => [],
      'third_party_settings' => [],
    ]);
    $entity_form_display->save();
    $user = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($user);

    $this->drupalGet('/node/add/page');

    // Assert the webtools script does not exist on the page if the field does
    // not have a concept scheme set.
    $this->assertSession()->responseNotContains('<script type="application/json">{"service":"wtag","target":"#edit-wtag-target-id","title":"Wtag","description":"Use the search field or the tree view to find and add one or more tags."}</script>');
    // Set a concept scheme for the field.
    $settings = [
      'handler' => 'default:skos_concept',
      'handler_settings' => [
        'concept_schemes' => [
          'http://data.europa.eu/uxp/non-EC_bodies',
        ],
      ],
    ];
    $field_config = FieldConfig::load('node.page.wtag');
    $field_config->set('settings', $settings)->save();

    $this->drupalGet('/node/add/page');
    // Assert the webtools script still does not exist because the field does
    // not use the DET concept scheme.
    $this->assertSession()->responseNotContains('<script type="application/json">{"service":"wtag","target":"#edit-wtag-target-id","title":"Wtag","description":"Use the search field or the tree view to find and add one or more tags."}</script>');

    // Set multiple concept schemes for the field.
    $settings = [
      'handler' => 'default:skos_concept',
      'handler_settings' => [
        'concept_schemes' => [
          'http://data.europa.eu/uxp/det',
          'http://data.europa.eu/uxp/non-EC_bodies',
        ],
      ],
    ];
    $field_config->set('settings', $settings)->save();

    $this->drupalGet('/node/add/page');
    // Assert the webtools script does not exist in the page.
    $this->assertSession()->responseNotContains('<script type="application/json">{"service":"wtag","target":"#edit-wtag-target-id","title":"Wtag","description":"Use the search field or the tree view to find and add one or more tags."}</script>');

    // Set only the DET concept scheme for the field.
    $settings = [
      'handler' => 'default:skos_concept',
      'handler_settings' => [
        'concept_schemes' => [
          'http://data.europa.eu/uxp/det',
        ],
      ],
    ];
    $field_config->set('settings', $settings)->save();

    $this->drupalGet('/node/add/page');
    // Assert the webtools script exists in the page.
    $this->assertSession()->responseContains('<script type="application/json">{"service":"wtag","target":"#edit-wtag-target-id","title":"Wtag","description":"Use the search field or the tree view to find and add one or more tags."}</script>');

    // Update the modal title and description.
    $entity_form_display->setComponent('wtag', [
      'weight' => 1,
      'region' => 'content',
      'type' => 'oe_webtools_wtag',
      'settings' => [
        'modal_title' => 'Custom Wtag title',
        'modal_description' => 'Custom Wtag modal description.',
      ],
      'third_party_settings' => [],
    ]);
    $entity_form_display->save();

    $this->drupalGet('/node/add/page');
    // Assert the webtools script is updated.
    $this->assertSession()->responseContains('<script type="application/json">{"service":"wtag","target":"#edit-wtag-target-id","title":"Custom Wtag title","description":"Custom Wtag modal description."}</script>');

    // Change the field storage cardinality.
    $field_storage = FieldStorageConfig::load('node.wtag');
    $field_storage->setCardinality(1)->save();

    $this->drupalGet('/node/add/page');
    // Assert the webtools script does not exist if the field storage
    // cardinality is not unlimited.
    $this->assertSession()->responseNotContains('<script type="application/json">{"service":"wtag","target":"#edit-wtag-target-id","title":"Custom Wtag title","description":"Custom Wtag modal description."}</script>');
  }

}
