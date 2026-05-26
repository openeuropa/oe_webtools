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
    $this->assertSession()->fieldValueEquals('Form ID', '');
    $this->assertFalse($this->assertSession()->elementExists('css', 'input#edit-feedback-form-id')->hasAttribute('required'));
    $this->assertSession()->pageTextContains('Provide your webtools form ID.');
    // The new optional Survey URL field is present and empty by default.
    $this->assertSession()->fieldValueEquals('Survey URL', '');
    $this->assertSession()->pageTextContains('Optional URL to the website survey. May contain the {zz} token as a language placeholder.');

    // Configure the form with a valid form id and a survey URL that contains
    // the {zz} language placeholder.
    // The placeholder must round-trip un-encoded.
    $page = $this->getSession()->getPage();
    $page->checkField('Enabled');
    $this->assertSession()->elementAttributeContains('css', 'input#edit-feedback-form-id', 'required', 'required');
    $page->fillField('Form ID', '1234abc');
    $page->fillField('Survey URL', 'https://example.com/?lang={zz}');
    $page->pressButton('Save configuration');
    // Assert values are correctly saved.
    $this->assertSession()->pageTextContains('The configuration options have been saved.');
    $this->assertSession()->checkboxChecked('Enabled');
    $this->assertSession()->fieldValueEquals('Form ID', '1234abc');
    $this->assertSession()->fieldValueEquals('Survey URL', 'https://example.com/?lang={zz}');
    $page_feedback_config = $this->config('oe_webtools_page_feedback.settings');
    $this->assertEquals(TRUE, $page_feedback_config->get('enabled'));
    $this->assertEquals('1234abc', $page_feedback_config->get('feedback_form_id'));
    $this->assertEquals('https://example.com/?lang={zz}', $page_feedback_config->get('survey'));

    // An invalid URL in the survey field is rejected.
    $page->fillField('Survey URL', 'not-a-url');
    $page->pressButton('Save configuration');
    $this->assertSession()->pageTextContains('The Survey URL must be a valid absolute URL. The {zz} language placeholder is allowed.');
    // The previous value must still be in config — invalid submit did not save.
    $this->drupalGet('/admin/config/system/oe_webtools_page_feedback');
    $this->assertSession()->fieldValueEquals('Survey URL', 'https://example.com/?lang={zz}');

    // An ftp:// URL has the right syntactic shape (UrlHelper::isValid accepts
    // ftp) but is blocked by the http/https scheme restriction.
    $page->fillField('Survey URL', 'ftp://example.com/survey');
    $page->pressButton('Save configuration');
    $this->assertSession()->pageTextContains('The Survey URL must use the http or https scheme.');
    // Previous valid value is still in config.
    $this->drupalGet('/admin/config/system/oe_webtools_page_feedback');
    $this->assertSession()->fieldValueEquals('Survey URL', 'https://example.com/?lang={zz}');

    // Clearing the survey URL is allowed (optional field).
    $page->fillField('Survey URL', '');
    $page->pressButton('Save configuration');
    $this->assertSession()->pageTextContains('The configuration options have been saved.');
    $page_feedback_config = $this->config('oe_webtools_page_feedback.settings');
    $this->assertSame('', (string) $page_feedback_config->get('survey'));

    // Disable the block and check states.
    $this->drupalGet('/admin/config/system/oe_webtools_page_feedback');
    $page->uncheckField('Enabled');
    $this->assertFalse($this->assertSession()->elementExists('css', 'input#edit-feedback-form-id')->hasAttribute('required'));
    $page->pressButton('Save configuration');
    $this->assertSession()->pageTextContains('The configuration options have been saved.');
  }

}
