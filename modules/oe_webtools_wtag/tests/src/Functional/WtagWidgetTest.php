<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_wtag\Functional;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;

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
   * The entity form display for the page content type.
   *
   * @var \Drupal\Core\Entity\Entity\EntityFormDisplay
   */
  protected EntityFormDisplay $entityFormDisplay;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

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
    $this->entityFormDisplay = EntityFormDisplay::collectRenderDisplay(Node::create(['type' => 'page']), 'default');
    $this->entityFormDisplay->setComponent('wtag', [
      'weight' => 1,
      'region' => 'content',
      'type' => 'oe_webtools_wtag',
      'settings' => [],
      'third_party_settings' => [],
    ]);
    $this->entityFormDisplay->save();
    $user = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($user);
  }

  /**
   * Test the Wtag widget.
   */
  public function testWtagWidget(): void {
    $this->drupalGet('/node/add/page');

    // Assert the webtools script does not exist on the page if the field does
    // not have a concept scheme set.
    $this->assertSession()->responseNotContains('<script type="application/json">{"service":"wtag","target":"#edit-wtag-target-id--wtag","title":"Wtag","description":"Use the search field or the tree view to find and add one or more tags."}</script>');
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
    $this->assertSession()->responseNotContains('<script type="application/json">{"service":"wtag","target":"#edit-wtag-target-id--wtag","title":"Wtag","description":"Use the search field or the tree view to find and add one or more tags."}</script>');

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
    $this->assertSession()->responseNotContains('<script type="application/json">{"service":"wtag","target":"#edit-wtag-target-id--wtag","title":"Wtag","description":"Use the search field or the tree view to find and add one or more tags."}</script>');

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
    $this->assertSession()->responseContains('<script type="application/json">{"service":"wtag","target":"#edit-wtag-target-id--wtag","title":"Wtag","description":"Use the search field or the tree view to find and add one or more tags."}</script>');

    // Update the modal title and description.
    $this->entityFormDisplay->setComponent('wtag', [
      'weight' => 1,
      'region' => 'content',
      'type' => 'oe_webtools_wtag',
      'settings' => [
        'modal_title' => 'Custom Wtag title',
        'modal_description' => 'Custom Wtag modal description.',
      ],
      'third_party_settings' => [],
    ]);
    $this->entityFormDisplay->save();

    $this->drupalGet('/node/add/page');
    // Assert the webtools script is updated.
    $this->assertSession()->responseContains('<script type="application/json">{"service":"wtag","target":"#edit-wtag-target-id--wtag","title":"Custom Wtag title","description":"Custom Wtag modal description."}</script>');

    // Change the field storage cardinality.
    $field_storage = FieldStorageConfig::load('node.wtag');
    $field_storage->setCardinality(1)->save();

    $this->drupalGet('/node/add/page');
    // Assert the webtools script does not exist if the field storage
    // cardinality is not unlimited.
    $this->assertSession()->responseNotContains('<script type="application/json">{"service":"wtag","target":"#edit-wtag-target-id--wtag","title":"Custom Wtag title","description":"Custom Wtag modal description."}</script>');
  }

  /**
   * Tests the Wtag fallback element structure and form submission.
   */
  public function testWtagFallback(): void {
    // Switch the field to DET scheme so the widget renders.
    $field_config = FieldConfig::load('node.page.wtag');
    $field_config->set('settings', [
      'handler' => 'default:skos_concept',
      'handler_settings' => ['concept_schemes' => ['http://data.europa.eu/uxp/det']],
    ])->save();

    // Assert both sub-elements are present with correct visibility.
    $this->drupalGet('/node/add/page');
    // Fallback wrapper has the wtag-fallback CSS class (hidden by CSS).
    $this->assertSession()->elementExists('css', '.wtag-element-wrapper');
    $this->assertSession()->elementExists('css', '.wtag-wrapper');
    $this->assertSession()->elementExists('css', '.wtag-fallback');
    // The wtag-wrapper is visible (no --hidden class) by default.
    $this->assertSession()->elementNotExists('css', '.wtag-wrapper--hidden');

    // Assert submitting via the wtag saves correctly.
    $concepts = \Drupal::entityTypeManager()
      ->getStorage('skos_concept')
      ->loadByProperties(['in_scheme' => 'http://data.europa.eu/uxp/det']);
    if (empty($concepts)) {
      $this->fail('No SKOS concepts available');
    }
    $concept = reset($concepts);
    $concept_json = json_encode([$concept->id() => $concept->label()]);

    $this->submitForm([
      'title[0][value]' => 'Test node',
      'wtag[target_id][wtag]' => $concept_json,
    ], 'Save');

    $this->assertSession()->pageTextContains('Basic page Test node has been created.');
    $node = $this->drupalGetNodeByTitle('Test node');
    $this->assertEquals($concept->id(), $node->get('wtag')->target_id);

    // Assert submitting via the fallback path (input_mode = 'fallback').
    $this->drupalGet('/node/add/page');
    $this->getSession()->getPage()
      ->find('css', 'input[data-wtag-input-mode]')
      ->setValue('fallback');
    $this->submitForm([
      'title[0][value]' => 'Test node fallback',
      'wtag[target_id][fallback]' => $concept->label() . ' (' . $concept->id() . ')',
    ], 'Save');
    $this->assertSession()->pageTextContains('Basic page Test node fallback has been created.');
    $node = $this->drupalGetNodeByTitle('Test node fallback');
    $this->assertEquals($concept->id(), $node->get('wtag')->target_id);

    // Assert pre-population on edit for both children.
    $this->drupalGet('/node/' . $node->id() . '/edit');
    // The wtag child should contain JSON with the concept id.
    $wtag_field = $this->assertSession()->fieldExists('wtag[target_id][wtag]');
    $this->assertStringContainsString($concept->id(), $wtag_field->getValue());
    // The fallback child should show "Label (id)".
    $fallback_field = $this->assertSession()->fieldExists('wtag[target_id][fallback]');
    $this->assertStringContainsString($concept->id(), $fallback_field->getValue());

    // Assert invalid JSON on the wtag path shows a form error.
    $this->drupalGet('/node/add/page');
    $this->submitForm([
      'title[0][value]' => 'Bad node',
      'wtag[target_id][wtag]' => 'not-valid-json',
    ], 'Save');
    $this->assertSession()->pageTextContains('The submitted tag data is not in the correct format.');
  }

}
