<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_hotjar\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\oe_webtools\Traits\ApplicationJsonAssertTrait;

/**
 * Tests the hotjar.
 */
class HotjarTest extends BrowserTestBase {

  use ApplicationJsonAssertTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_webtools_hotjar',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test the hotjar widget.
   */
  public function testHotjarWidget(): void {
    $this->drupalGet('<front>');
    // By default, we don't have a hotjar script.
    $this->assertSession()->responseNotContains('{"utility":"hotjar"}');

    // Configure the widget.
    $user = $this->createUser([
      'administer webtools hotjar',
    ]);
    $this->drupalLogin($user);
    $this->drupalGet('/admin/config/system/oe_webtools_hotjar');
    $this->assertSession()->pageTextContains('Webtools Hotjar settings');
    $this->assertSession()->checkboxNotChecked('Enable Hotjar');
    $this->assertSession()->fieldValueEquals('Site', '');
    $this->getSession()->getPage()->checkField('Enable Hotjar');
    $this->getSession()->getPage()->fillField('Site', 'http://example.com');
    $this->getSession()->getPage()->pressButton('Save configuration');
    $this->assertSession()->pageTextContains('The configuration options have been saved.');

    $this->drupalGet('<front>');
    // We still don't see the hotjar because we are logged in.
    $this->assertSession()->responseNotContains('{"utility":"hotjar"}');

    // Re-assert again as anonymous user.
    $this->drupalLogout();
    $this->drupalGet('<front>');
    $this->assertBodyContainsApplicationJson('{"utility":"hotjar","site":"' . addcslashes('http://example.com', '/') . '"}');

    // Remove the site from the config and reload to assert that we don't
    // print without a site.
    \Drupal::configFactory()->getEditable('oe_webtools_hotjar.settings')->set('site', '')->save();
    $this->drupalGet('<front>');
    $this->assertSession()->responseNotContains('{"utility":"hotjar"}');
    $config = \Drupal::configFactory()->getEditable('oe_webtools_hotjar.settings');
    $config->set('site', 'http://example.com');
    $config->set('enabled', FALSE);
    $config->save();
    $this->drupalGet('<front>');
    $this->assertSession()->responseNotContains('{"utility":"hotjar"}');
    $config->set('enabled', TRUE);
    $config->save();
    $this->drupalGet('<front>');
    $this->assertBodyContainsApplicationJson('{"utility":"hotjar","site":"' . addcslashes('http://example.com', '/') . '"}');
  }

}
