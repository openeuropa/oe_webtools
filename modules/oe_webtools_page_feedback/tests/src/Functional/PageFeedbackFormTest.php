<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_page_feedback\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\oe_webtools\Traits\ApplicationJsonAssertTrait;
use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Tests the Page Feedback Form webtools widget.
 */
class PageFeedbackFormTest extends BrowserTestBase {

  use ApplicationJsonAssertTrait;

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
    $page_feedback_config->set('feedback_form_id', '1234')->save();

    // Assert the block is rendered only on node pages following the interface
    // language, and that the new DFF widget config (no "version", with
    // "target") is emitted.
    $this->drupalGet('/node/1');
    $this->assertSession()->pageTextContains('Page node');
    $this->assertBodyContainsApplicationJson('{"service":"dff","id":"1234","lang":"en"}');

    $this->drupalGet('<front>');
    $this->assertSession()->responseNotContains('"service":"dff"');

    $this->drupalGet('/pt-pt/node/1');
    $this->assertBodyContainsApplicationJson('{"service":"dff","id":"1234","lang":"pt"}');

    $page_feedback_config->set('feedback_form_id', '1234abc')->save();
    $this->drupalGet('/pt-pt/node/1');
    $this->assertBodyContainsApplicationJson('{"service":"dff","id":"1234abc","lang":"pt"}');

    // Survey passthrough. Reset form id to keep the expected JSON predictable.
    $page_feedback_config->set('feedback_form_id', '1234')->save();

    // When "survey" is empty, the key must be absent from the JSON payload.
    $this->drupalGet('/node/1');
    $this->assertSession()->responseNotContains('"survey"');

    // When "survey" is set, it is forwarded verbatim, including the {zz}
    // language token, which must NOT be URL-encoded.
    $page_feedback_config->set('survey', 'https://example.com/?lang={zz}')->save();
    $this->drupalGet('/node/1');
    $this->assertApplicationJsonEquals([
      'service' => 'dff',
      'id' => '1234',
      'lang' => 'en',
      'survey' => 'https://example.com/?lang={zz}',
    ]);

    // Reset survey to empty for the disable-block assertions below, so a stale
    // value does not leak into the "block disabled" check.
    $page_feedback_config->set('survey', '')->save();

    // Disable the block and assert the block is not rendered and the cache was
    // properly invalidated.
    $page_feedback_config->set('enabled', FALSE);
    $page_feedback_config->set('feedback_form_id', '1234')->save();
    $this->drupalGet('/node/1');
    $this->assertSession()->pageTextContains('Page node');
    $this->assertSession()->responseNotContains('"service":"dff"');
  }

}
