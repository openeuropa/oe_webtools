<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_wtag\FunctionalJavascript;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\rdf_skos\Entity\Concept;

/**
 * Tests the Wtag fallback JS behaviour.
 */
class WtagFallbackJsTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'oe_webtools_wtag',
    'oe_webtools_js_mock',
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
      'field_name' => 'wtag',
      'entity_type' => 'node',
      'type' => 'skos_concept_entity_reference',
      'settings' => ['target_type' => 'skos_concept'],
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    ])->save();
    FieldConfig::create([
      'entity_type' => 'node',
      'field_name' => 'wtag',
      'bundle' => 'page',
      'required' => TRUE,
      'settings' => [
        'handler' => 'default:skos_concept',
        'handler_settings' => [
          'concept_schemes' => ['http://data.europa.eu/uxp/det'],
        ],
      ],
    ])->save();
    EntityFormDisplay::collectRenderDisplay(Node::create(['type' => 'page']), 'default')
      ->setComponent('wtag', [
        'weight' => 1,
        'region' => 'content',
        'type' => 'oe_webtools_wtag',
        'settings' => [],
        'third_party_settings' => [],
      ])->save();
    $user = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($user);
  }

  /**
   * Tests that wtWtagError event triggers the fallback.
   */
  public function testWtagWithFallback(): void {
    $this->drupalGet('/node/add/page');

    // Confirm initial state: wtag visible, fallback hidden.
    $this->assertSession()->elementExists('css', '.wtag-wrapper');
    $this->assertSession()->elementNotExists('css', '.wtag-wrapper--hidden');

    // Dispatch wtWtagError on window and confirm DOM changes happen.
    $classes = $this->getSession()->evaluateScript(
      "(function() {
        window.dispatchEvent(new CustomEvent('wtWtagError'));
        return document.querySelector('.wtag-wrapper').className;
      })()"
    );
    $this->assertStringContainsString('wtag-wrapper--hidden', $classes);

    // Fallback is now visible, wtag wrapper is hidden.
    $this->assertSession()->waitForElementVisible('css', '.wtag-fallback--active', 2000);
    $this->assertSession()->elementExists('css', '.wtag-wrapper--hidden');

    // input_mode hidden field is set to 'fallback'.
    $inputMode = $this->getSession()->evaluateScript(
      "document.querySelector('[data-wtag-input-mode]').value"
    );
    $this->assertEquals('fallback', $inputMode);

    $concept = Concept::load('http://data.europa.eu/uxp/1031');

    // Assert that because the field is required, we cannot submit empty.
    $this->drupalGet('/node/add/page');
    $this->getSession()->evaluateScript("window.dispatchEvent(new CustomEvent('wtWtagError'))");
    $this->assertSession()->waitForElementVisible('css', '.wtag-fallback--active');
    $this->submitForm(['title[0][value]' => 'Test node'], 'Save');
    $this->assertSession()->pageTextContains('field is required');
    $this->drupalGet('/node/add/page');
    $this->getSession()->evaluateScript("window.dispatchEvent(new CustomEvent('wtWtagError'))");
    $this->assertSession()->waitForElementVisible('css', '.wtag-fallback--active');
    $this->getSession()->getPage()->fillField(
      'wtag[target_id][fallback]',
      $concept->label() . ' (' . $concept->id() . ')'
    );
    $this->submitForm(['title[0][value]' => 'Test Required Fallback'], 'Save');
    $this->assertSession()->pageTextContains('Test Required Fallback has been created.');
    $node = $this->drupalGetNodeByTitle('Test Required Fallback');
    $this->assertEquals($concept->id(), $node->get('wtag')->target_id);

    // Now test without the fallback to confirm everything works like before.
    $concept_json = json_encode([$concept->id() => $concept->label()]);
    $this->drupalGet('/node/add/page');
    // Dispatch the wtag ready event to prevent the fallback.
    $this->getSession()->evaluateScript(
      "(function() {
        var id = document.querySelector('[data-wtag-id]').getAttribute('data-wtag-id');
        var e = new CustomEvent('wtWtagReady');
        e.parameters = {params: {target: '#' + id}};
        window.dispatchEvent(e);
      })()"
    );
    // Set the textarea like webtools would (since it's hidden).
    $this->getSession()->evaluateScript(
      "(function() {
        var id = document.querySelector('[data-wtag-id]').getAttribute('data-wtag-id');
        document.getElementById(id).value = " . json_encode($concept_json) . ";
      })()"
    );
    $this->submitForm(['title[0][value]' => 'Test Required Wtag'], 'Save');
    $this->assertSession()->pageTextContains('Test Required Wtag has been created.');
    $node = $this->drupalGetNodeByTitle('Test Required Wtag');
    $this->assertEquals($concept->id(), $node->get('wtag')->target_id);
  }

  /**
   * Tests that the 5-second timeout triggers the fallback.
   *
   * This test waits for the full timeout to fire — expect ~5 seconds.
   */
  public function testTimeoutTriggersFallback(): void {
    $this->drupalGet('/node/add/page');

    // Wait up to 7 seconds for the 5-second timeout to trigger the fallback.
    $this->assertSession()->waitForElementVisible('css', '.wtag-fallback--active', 7000);
    $this->assertSession()->elementExists('css', '.wtag-wrapper--hidden');
  }

}
