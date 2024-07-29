<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_social_share\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the Social share widget settings form.
 */
class SocialShareSettingsFormTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'language',
    'block',
    'oe_webtools_social_share',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests Social share form configuration.
   */
  public function testSocialShareForm(): void {
    // Assert user without permissions cannot access the settings form.
    $this->drupalLogin($this->createUser());
    $this->drupalGet('/admin/config/system/oe_webtools_social_share');
    $assert = $this->assertSession();
    $assert->pageTextContains('Access denied');
    $assert->pageTextContains('You are not authorized to access this page.');

    // Create another user with access to the form.
    $this->drupalLogin($this->createUser(['administer webtools social share block']));
    $this->drupalGet('/admin/config/system/oe_webtools_social_share');
    $assert->pageTextContainsOnce('Webtools Social share settings');
    // The block renders the icons labels, by default.
    $assert->checkboxNotChecked('Display only icons');
    $assert->pageTextContainsOnce('Check this box if you would like to display only the icons without labels for the Social share block.');
    $this->assertEquals(FALSE, $this->config('oe_webtools_social_share.settings')->get('icons'));

    // Enable the icons option.
    $this->getSession()->getPage()->checkField('Display only icons');
    $this->getSession()->getPage()->pressButton('Save configuration');
    $this->container->get('config.factory')->reset('oe_webtools_social_share.settings');
    // Assert the values are saved.
    $assert->pageTextContainsOnce('The configuration options have been saved.');
    $assert->checkboxChecked('Display only icons');
    $this->assertEquals(TRUE, $this->config('oe_webtools_social_share.settings')->get('icons'));
  }

}
