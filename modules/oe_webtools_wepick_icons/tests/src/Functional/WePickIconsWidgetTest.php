<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_wepick_icons\Functional;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Tests\BrowserTestBase;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;

/**
 * Tests the WePick Icons widget using the WePick Icons form element.
 */
class WePickIconsWidgetTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'oe_webtools_wepick_icons',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests the WePick Icons widget renders the correct JSON config.
   */
  public function testWePickIconsWidget(): void {
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);

    // Create a WePick Icons field.
    FieldStorageConfig::create([
      'field_name' => 'field_icon',
      'entity_type' => 'node',
      'type' => 'oe_webtools_wepick_icons',
    ])->save();
    FieldConfig::create([
      'entity_type' => 'node',
      'field_name' => 'field_icon',
      'bundle' => 'page',
    ])->save();
    $entity_form_display = EntityFormDisplay::collectRenderDisplay(Node::create(['type' => 'page']), 'default');
    $entity_form_display->setComponent('field_icon', [
      'weight' => 1,
      'region' => 'content',
      'type' => 'oe_webtools_wepick_icons',
      'settings' => [],
      'third_party_settings' => [],
    ]);
    $entity_form_display->save();

    $user = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($user);

    $this->drupalGet('/node/add/page');

    // Assert the pickicons script exists on the page with default settings.
    $this->assertSession()->responseContains('<script type="application/json">{"service":"pickicons","target":"#edit-field-icon-0","title":"Icon picker"}</script>');

    // Update the modal title.
    $entity_form_display->setComponent('field_icon', [
      'weight' => 1,
      'region' => 'content',
      'type' => 'oe_webtools_wepick_icons',
      'settings' => [
        'modal_title' => 'Custom icon picker title',
        'include_names' => '',
        'include_families' => '',
        'include_tags' => '',
        'exclude_names' => '',
        'exclude_families' => '',
        'exclude_tags' => '',
      ],
      'third_party_settings' => [],
    ]);
    $entity_form_display->save();

    $this->drupalGet('/node/add/page');
    // Assert the script is updated with the custom title.
    $this->assertSession()->responseContains('<script type="application/json">{"service":"pickicons","target":"#edit-field-icon-0","title":"Custom icon picker title"}</script>');

    // Set include and exclude filters.
    $entity_form_display->setComponent('field_icon', [
      'weight' => 1,
      'region' => 'content',
      'type' => 'oe_webtools_wepick_icons',
      'settings' => [
        'modal_title' => 'Custom icon picker title',
        'include_names' => 'digg, blogger',
        'include_families' => 'networks-color',
        'include_tags' => '',
        'exclude_names' => '',
        'exclude_families' => 'networks',
        'exclude_tags' => '',
      ],
      'third_party_settings' => [],
    ]);
    $entity_form_display->save();

    $this->drupalGet('/node/add/page');
    // Assert the script includes the filter configuration.
    $this->assertSession()->responseContains('"include":{"name":["digg","blogger"],"family":["networks-color"]}');
    $this->assertSession()->responseContains('"exclude":{"family":["networks"]}');

    // Set combined widget settings: custom title with include and exclude
    // filters, including tags.
    $entity_form_display->setComponent('field_icon', [
      'weight' => 1,
      'region' => 'content',
      'type' => 'oe_webtools_wepick_icons',
      'settings' => [
        'modal_title' => 'Custom icon picker title',
        'include_names' => 'be, fr',
        'include_families' => 'flags',
        'include_tags' => 'unordered-list',
        'exclude_names' => 'spotify',
        'exclude_families' => 'networks-color',
        'exclude_tags' => 'deprecated',
      ],
      'third_party_settings' => [],
    ]);
    $entity_form_display->save();

    $this->drupalGet('/node/add/page');
    // Assert all combined settings are rendered in the JSON config.
    $this->assertSession()->responseContains('"title":"Custom icon picker title"');
    $this->assertSession()->responseContains('"include":{"name":["be","fr"],"family":["flags"],"tags":["unordered-list"]}');
    $this->assertSession()->responseContains('"exclude":{"name":["spotify"],"family":["networks-color"],"tags":["deprecated"]}');

    // Create a node with a selected icon value programmatically, then verify
    // the value is pre-populated when editing the node.
    $node = Node::create([
      'type' => 'page',
      'title' => 'Test icon node',
      'field_icon' => [
        'icon_name' => 'ro',
        'icon_family' => 'flags',
      ],
    ]);
    $node->save();

    $this->drupalGet('/node/' . $node->id() . '/edit');
    $icon_field = $this->assertSession()->elementExists('css', '#edit-field-icon-0');
    $this->assertEquals('{"name":"ro","family":"flags"}', $icon_field->getValue());
  }

}
