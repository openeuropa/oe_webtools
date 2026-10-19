<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_cookie_consent\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;
use Drupal\Tests\media\Traits\OEmbedTestTrait;
use Drupal\Tests\oe_webtools\Traits\ApplicationJsonAssertTrait;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\media\Entity\Media;
use Drupal\media_test_oembed\Controller\ResourceController;
use Drupal\media_test_oembed\UrlResolver;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use OpenEuropa\TestingUtilities\Traits\CachedDatabaseInstallTrait;

/**
 * Tests the cookie consent.
 *
 * @group batch1
 */
class CookieConsentTest extends BrowserTestBase {

  use ApplicationJsonAssertTrait;
  use CachedDatabaseInstallTrait;
  use MediaTypeCreationTrait;
  use OEmbedTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_webtools_cookie_consent',
    'config_translation',
    'language',
    'locale',
    'media',
    'media_test_oembed',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->cacheDbInstall = TRUE;
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
    $this->assertBodyContainsApplicationJson('{"utility":"cck","url":"' . addcslashes('http://example.com/cookie', '/') . '"}');

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
    $this->assertBodyContainsApplicationJson('{"utility":"cck","url":"' . addcslashes('http://example.com/cookie', '/') . '"}');
    $this->drupalGet('/fr');
    $this->assertBodyContainsApplicationJson('{"utility":"cck","url":"' . addcslashes('http://example.com/fr/cookie', '/') . '"}');

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

  /**
   * Tests that remote videos use the cookie consent kit service.
   */
  public function testRemoteVideoCookieConsent(): void {
    $this->config('media.settings')->set('standalone_url', TRUE)->save();
    $this->container->get('router.builder')->rebuild();

    $media_type = $this->createMediaType('oembed:video');
    $display = $this->container->get('entity_display.repository')->getViewDisplay('media', $media_type->id());
    $media_type->getSource()->prepareViewDisplay($media_type, $display);
    $display->save();

    $video_url = 'https://vimeo.com/7073899';
    $this->hijackProviderEndpoints();
    $fixture = $this->container->get('extension.list.module')->getPath('oe_webtools_cookie_consent') . '/tests/fixtures/oembed/video_vimeo.json';
    ResourceController::setResourceUrl($video_url, $fixture);
    UrlResolver::setEndpointUrl($video_url, Url::fromRoute('media_test_oembed.resource.get', [], [
      'query' => ['url' => $video_url],
      'absolute' => TRUE,
    ])->toString());

    $media = Media::create([
      'bundle' => $media_type->id(),
      'name' => 'The European Union goals at COP 28 2023',
      $media_type->getSource()->getSourceFieldDefinition($media_type)->getName() => $video_url,
    ]);
    $media->save();
    $this->grantPermissions(Role::load(RoleInterface::ANONYMOUS_ID), ['view media']);

    $cck_iframe_selector = "iframe[src^='" . OE_WEBTOOLS_COOKIE_CONSENT_EMBED_COOKIE_URL . "?oriurl=']";

    // The oEmbed iframe uses the cookie consent kit service by default.
    $this->drupalGet($media->toUrl());
    $this->drupalGet($this->getAbsoluteUrl($this->assertSession()->elementExists('css', 'iframe')->getAttribute('src')));
    $this->assertSession()->elementExists('css', $cck_iframe_selector);

    // Disable the video banner.
    $this->drupalLogin($this->createUser(['administer webtools cookie consent']));
    $this->drupalGet('/admin/config/system/oe_webtools_cookie_consent');
    $this->assertSession()->pageTextContains('Webtools Cookie Consent settings');
    $this->assertSession()->checkboxChecked('Enable CCK video banner for the supported video elements.');
    $this->getSession()->getPage()->uncheckField('Enable CCK video banner for the supported video elements.');
    $this->getSession()->getPage()->pressButton('Save configuration');
    $this->assertSession()->pageTextContains('The configuration options have been saved.');
    $this->assertSession()->checkboxNotChecked('Enable CCK video banner for the supported video elements.');

    // The oEmbed iframe no longer uses the cookie consent kit service.
    $this->drupalGet($media->toUrl());
    $this->drupalGet($this->getAbsoluteUrl($this->assertSession()->elementExists('css', 'iframe')->getAttribute('src')));
    $this->assertSession()->elementExists('css', 'iframe');
    $this->assertSession()->elementNotExists('css', $cck_iframe_selector);
  }

}
