<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_page_feedback\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests the configuration form of the Page Feedback Form webtools widget.
 */
class PageFeedbackFormTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_webtools_page_feedback',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests Page Feedback Form configuration.
   */
  public function testPageFeedbackForm(): void {
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
    // Configure the form.
    $page = $this->getSession()->getPage();
    $page->checkField('Enabled');
    $this->assertSession()->elementAttributeContains('css', 'input#edit-feedback-form-id', 'required', 'required');
    $page->fillField('Form ID', '1234');
    $page->pressButton('Save configuration');
    // Assert values are correctly saved.
    $this->assertSession()->pageTextContains('The configuration options have been saved.');
    $this->assertSession()->checkboxChecked('Enabled');
    $this->assertSession()->fieldValueEquals('Form ID', '1234');
    $page_feedback_config = $this->config('oe_webtools_page_feedback.settings');
    $this->assertEquals(TRUE, $page_feedback_config->get('enabled'));
    $this->assertEquals('1234', $page_feedback_config->get('feedback_form_id'));
  }

}
