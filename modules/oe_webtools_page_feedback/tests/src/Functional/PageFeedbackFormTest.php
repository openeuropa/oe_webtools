<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_page_feedback\FunctionalJavascript;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the Page Feedback Form webtools widget.
 */
class PageFeedbackFormTest extends BrowserTestBase {

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
   * Tests Page Feedback Form block rendering.
   */
  public function testPageFeedbackForm(): void {
    // Create a node and a user for configuring the Page Feedback Form.
    $this->drupalCreateNode(['type' => 'page', 'title' => 'Page node']);

    $page_feedback_config = $this->config('oe_webtools_page_feedback.settings');
    $page_feedback_config->set('enabled', TRUE);
    $page_feedback_config->set('feedback_form_id', 1234)->save();

    // Assert the block is rendered only on node pages following the interface
    // language.
    $this->drupalGet('/node/1');
    $this->assertSession()->pageTextContains('Page node');
    $this->assertSession()->responseContains('<script type="application/json">{"service":"dff","id":1234,"lang":"en"}</script>');
    $this->drupalGet('<front>');
    $this->assertSession()->responseNotContains('"service":"dff"');
    $this->drupalGet('/pt-pt/node/1');
    $this->assertSession()->responseContains('<script type="application/json">{"service":"dff","id":1234,"lang":"pt"}</script>');
    $page_feedback_config->set('feedback_form_id', 12345)->save();
    $this->drupalGet('/pt-pt/node/1');
    $this->assertSession()->responseContains('<script type="application/json">{"service":"dff","id":12345,"lang":"pt"}</script>');

    // Disable the block and assert the block is not rendered and the cache was
    // properly invalidated.
    $page_feedback_config->set('enabled', FALSE);
    $page_feedback_config->set('feedback_form_id', 1234)->save();
    $this->drupalGet('/node/1');
    $this->assertSession()->pageTextContains('Page node');
    $this->assertSession()->responseNotContains('"service":"dff"');
  }

}
