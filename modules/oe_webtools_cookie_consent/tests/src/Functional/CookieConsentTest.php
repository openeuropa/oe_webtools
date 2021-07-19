<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_cookie_consent\Functional;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\BrowserTestBase;

/**
 * Check if the Laco widget code is on the front page.
 */
class CookieConsentTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_webtools_cookie_consent',
    'config_translation',
    'language',
    'locale',
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

    ConfigurableLanguage::createFromLangcode('fr')->save();
  }

  /**
   * Test the cookie consent widget.
   */
  public function testCookieConsentWidget(): void {
    $this->drupalGet('<front>');
    // By default, we should have the cookie consent kit, with no page URL.
    $this->assertSession()->responseContains('{"utility":"cck"}');

    // Configure the widget.
    $user = $this->createUser([
      'administer webtools cookie consent',
      'translate configuration',
    ]);
    $this->drupalLogin($user);
    $this->drupalGet('/admin/config/system/oe_webtools_cookie_consent');
    $this->assertSession()->pageTextContains('Webtools Cookie Consent settings');
    $this->assertSession()->checkboxChecked('Enable the CCK banner.');

    // Set an EN cookie page URL.
    $this->getSession()->getPage()->fillField('Cookie Notice Page URL', 'http://example.com/cookie');
    $this->getSession()->getPage()->pressButton('Save configuration');
    $this->assertSession()->pageTextContains('The configuration options have been saved.');

    // Re-assert again as anonymous user.
    $this->drupalLogout();
    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('{"utility":"cck","url":"' . addcslashes('http://example.com/cookie', '/') . '"}');

    // Translate the cookie consent URL.
    $this->drupalLogin($user);
    $this->drupalGet('/admin/config/system/oe_webtools_cookie_consent/translate');
    // We only have FR enabled so only one Add button will show.
    $this->getSession()->getPage()->clickLink('Add');
    $this->getSession()->getPage()->fillField('Cookie Notice Page URL', 'http://example.com/fr/cookie');
    $this->getSession()->getPage()->pressButton('Save translation');
    $this->assertSession()->pageTextContains('Successfully saved French translation.');

    // Re-assert as anonymous user.
    $this->drupalLogout();
    $this->drupalGet('<front>');
    $this->assertSession()->responseContains('{"utility":"cck","url":"' . addcslashes('http://example.com/cookie', '/') . '"}');
    $this->drupalGet('/fr');
    $this->assertSession()->responseContains('{"utility":"cck","url":"' . addcslashes('http://example.com/fr/cookie', '/') . '"}');

    // Disable CCK.
    $this->drupalLogin($user);
    $this->drupalGet('/admin/config/system/oe_webtools_cookie_consent');
    $this->getSession()->getPage()->uncheckField('Enable the CCK banner.');
    $this->getSession()->getPage()->pressButton('Save configuration');
    $this->assertSession()->pageTextContains('The configuration options have been saved.');
    $this->drupalLogout();
    $this->drupalGet('<front>');
    $this->assertSession()->responseNotContains('"utility":"cck"');
  }

}
