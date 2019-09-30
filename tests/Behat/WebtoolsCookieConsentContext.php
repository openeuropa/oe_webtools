<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools\Behat;

use Drupal\DrupalExtension\Context\RawDrupalContext;

define('OE_WEBTOOLS_COOKIE_CONSENT_EMBED_COOKIE_URL', '//europa.eu/webtools/crs/iframe/');
define('OE_WEBTOOLS_COOKIE_CONSENT_BANNER_COOKIE_URL', '//ec.europa.eu/wel/cookie-consent/consent.js');

/**
 * Behat step definitions related to the oe_webtools_cookie_consent module.
 */
class WebtoolsCookieConsentContext extends RawDrupalContext {

  /**
   * Checks that an OEmbed iframe url uses CCK service.
   *
   * @Then I should see the oEmbed video iframe with Cookie Consent
   */
  public function assertOembedIframeWithCckUsage(): void {
    $iframe_url = $this->getSession()->getPage()->find('css', 'iframe')->getAttribute('src');
    $this->visitPath(str_replace(rtrim($this->getDrupalParameter('drupal')['drupal_root'], '/'), '', $iframe_url));
    $this->assertSession()->elementExists('css', "iframe[src^='" . OE_WEBTOOLS_COOKIE_CONSENT_EMBED_COOKIE_URL . "?oriurl=']");
  }

  /**
   * Checks that an OEmbed iframe url doesn't use CCK service.
   *
   * @Then I should not see the oEmbed video iframe with Cookie Consent
   */
  public function assertNoOembedIframeWithCckUsage(): void {
    $iframe_url = $this->getSession()->getPage()->find('css', 'iframe')->getAttribute('src');
    $this->visitPath(str_replace(rtrim($this->getDrupalParameter('drupal')['drupal_root'], '/'), '', $iframe_url));
    $this->assertSession()->elementNotExists('css', "iframe[src^='" . OE_WEBTOOLS_COOKIE_CONSENT_EMBED_COOKIE_URL . "?oriurl=']");
  }

  /**
   * Checks that the CCK is loaded on the <HEAD> section of the page.
   *
   * @Then the CCK javascript is loaded on the head section of the page
   */
  public function assertCckJsLoaded(): void {
    $this->assertSession()->elementExists('css', "head > script[src^='" . OE_WEBTOOLS_COOKIE_CONSENT_BANNER_COOKIE_URL . "']");
  }

  /**
   * Checks that the CCK is not loaded on the <HEAD> section of the page.
   *
   * @Then the CCK javascript is not loaded on the head section of the page
   */
  public function assertNoCckJsLoaded(): void {
    $this->assertSession()->elementNotExists('css', "head > script[src^='" . OE_WEBTOOLS_COOKIE_CONSENT_BANNER_COOKIE_URL . "']");
  }

}
