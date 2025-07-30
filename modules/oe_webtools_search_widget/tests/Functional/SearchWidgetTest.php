<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_search_widget\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the Search widget webtools widget.
 */
class SearchWidgetTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'language',
    'block',
    'oe_webtools_search_widget',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The block.
   *
   * @var \Drupal\block\Entity\Block
   */
  protected $block;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->block = $this->drupalPlaceBlock('oe_webtools_search_widget');
    $this->drupalPlaceBlock('page_title_block');
  }

  /**
   * Tests Page Feedback Form block rendering.
   */
  public function testPageFeedbackForm(): void {
    $user = $this->createUser([], '', TRUE);
    $this->drupalLogin($user);

    // Widget is rendered correctly.
    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('{"service":"search","version":"2.0","filters":{"scope":{"sites":[{"selected":false,"name":"","id":[]}]}}}');

    // Edit form.
    $this->drupalGet('/admin/structure/block/manage/' . $this->block->id());
    // Assert default values.
    $this->assertSession()->pageTextContains('OpenEuropa Webtools Search widget');
    $this->assertSession()->checkboxChecked('Global');
    $this->assertSession()->checkboxNotChecked('Local');
    $this->assertSession()->fieldValueEquals('Override local label', '');
    $this->assertSession()->fieldValueEquals('Site ids', '');

    $page = $this->getSession()->getPage();
    $this->getSession()->getPage()->find('css', 'input[name="settings[search_scope]"]')->selectOption('local');
    $page->fillField('Override local label', 'openeuropa site');
    $page->fillField('Site ids', 'id1,id2');
    $page->pressButton('Save block');
    $this->assertSession()->pageTextContains('The block configuration has been saved.');

    // Widget is rendered with correct values.
    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('{"service":"search","version":"2.0","filters":{"scope":{"sites":[{"selected":true,"name":"openeuropa site","id":["id1","id2"]}]}}}');

    // Edit form.
    $this->drupalGet('/admin/structure/block/manage/' . $this->block->id());
    $this->assertSession()->checkboxNotChecked('Global');
    $this->assertSession()->checkboxChecked('Local');
    $this->assertSession()->fieldValueEquals('Override local label', 'openeuropa site');
    $this->assertSession()->fieldValueEquals('Site ids', 'id1,id2');
    $this->getSession()->getPage()->find('css', 'input[name="settings[search_scope]"]')->selectOption('global');
    $page->fillField('Override local label', 'openeuropa site2');
    $page->fillField('Site ids', 'id1,id3');
    $page->pressButton('Save block');

    // Widget is rendered with correct values.
    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('{"service":"search","version":"2.0","filters":{"scope":{"sites":[{"selected":false,"name":"openeuropa site2","id":["id1","id3"]}]}}}');

    // Set form selector.
    $this->drupalGet('/admin/structure/block/manage/' . $this->block->id());
    $page->fillField('Form selector', '.ecl-search-form');
    $page->pressButton('Save block');

    // Widget is rendered with form selector.
    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('{"service":"search","version":"2.0","filters":{"scope":{"sites":[{"selected":false,"name":"openeuropa site2","id":["id1","id3"]}]}},"form":".ecl-search-form"}');
  }

}
