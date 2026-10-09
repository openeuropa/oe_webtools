<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_page_feedback\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Tests the Page Feedback Form webtools widget configuration.
 */
class PageFeedbackAdminFormTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'language',
    'block',
    'oe_webtools_page_feedback',
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

    $this->drupalPlaceBlock('oe_webtools_page_feedback_form');
    $this->drupalPlaceBlock('page_title_block');
    $this->drupalCreateContentType(['type' => 'page', 'name' => 'Basic page']);
    ConfigurableLanguage::createFromLangcode('pt-pt')->save();
  }

  /**
   * Tests Page Feedback Form configuration.
   */
  public function testPageFeedbackForm(): void {
    // Create a node and a user for configuring the Page Feedback Form.
    $this->drupalCreateNode(['type' => 'page', 'title' => 'Page node']);
    $user = $this->createUser([
      'administer webtools page feedback form',
    ]);
    $this->drupalLogin($user);
    $this->drupalGet('/admin/config/system/oe_webtools_page_feedback');
    // Assert default values.
    $this->assertSession()->pageTextContains('Webtools Page Feedback Form settings');
    $this->assertSession()->checkboxNotChecked('Enabled');
    $this->assertSession()->pageTextContains('Check this box if you would like to enable the Page feedback form on this site.');
    $this->assertSession()->fieldValueEquals('Embed code', '');
    $this->assertFalse($this->assertSession()->elementExists('css', 'textarea#edit-embed-code')->hasAttribute('required'));
    $this->assertSession()->pageTextContains('JSON-encoded parameters passed to the Page Feedback Form widget');

    // Configure the form with a valid embed code.
    $page = $this->getSession()->getPage();
    $page->checkField('Enabled');
    $this->assertSession()->elementAttributeContains('css', 'textarea#edit-embed-code', 'required', 'required');
    $page->fillField('Embed code', '{"id": "1234abc"}');
    $page->pressButton('Save configuration');
    // Assert values are correctly saved.
    $this->assertSession()->pageTextContains('The configuration options have been saved.');
    $this->assertSession()->checkboxChecked('Enabled');
    $this->assertSession()->fieldValueEquals('Embed code', '{"id": "1234abc"}');
    $page_feedback_config = $this->config('oe_webtools_page_feedback.settings');
    $this->assertEquals(TRUE, $page_feedback_config->get('enabled'));
    $this->assertEquals('{"id": "1234abc"}', $page_feedback_config->get('embed_code'));

    // Invalid JSON in the embed code is rejected.
    $page->fillField('Embed code', '{invalid json');
    $page->pressButton('Save configuration');
    $this->assertSession()->pageTextContains('The embed code must be valid JSON.');
    // The previous value must still be in config — invalid submit did not save.
    $this->drupalGet('/admin/config/system/oe_webtools_page_feedback');
    $this->assertSession()->fieldValueEquals('Embed code', '{"id": "1234abc"}');

    // Disable the block and check states.
    $page->uncheckField('Enabled');
    $this->assertFalse($this->assertSession()->elementExists('css', 'textarea#edit-embed-code')->hasAttribute('required'));
    $page->pressButton('Save configuration');
    $this->assertSession()->pageTextContains('The configuration options have been saved.');
  }

}
