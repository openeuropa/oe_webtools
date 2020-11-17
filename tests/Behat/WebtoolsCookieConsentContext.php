<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools\Behat;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\media\Entity\Media;
use DrupalTest\BehatTraits\Traits\BrowserCapabilityDetectionTrait;

/**
 * Behat step definitions related to the oe_webtools_cookie_consent module.
 */
class WebtoolsCookieConsentContext extends RawDrupalContext {

  use BrowserCapabilityDetectionTrait;

  /**
   * The config context.
   *
   * @var \Drupal\DrupalExtension\Context\ConfigContext
   */
  protected $configContext;

  /**
   * Gathers some other contexts.
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   *   The before scenario scope.
   *
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope) {
    $environment = $scope->getEnvironment();
    $this->configContext = $environment->getContext('Drupal\DrupalExtension\Context\ConfigContext');
  }

  /**
   * Enables the Media and the Path modules.
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   *   The scope.
   *
   * @beforeScenario @remote-video
   */
  public function enableModule(BeforeScenarioScope $scope): void {
    \Drupal::service('module_installer')->install(['oe_media', 'path']);

    $this->configContext->setConfig('media.settings', 'standalone_url', TRUE);
    \Drupal::service('router.builder')->rebuild();
  }

  /**
   * Disables the Media module and the Path modules.
   *
   * @param \Behat\Behat\Hook\Scope\AfterScenarioScope $scope
   *   The scope.
   *
   * @afterScenario @remote-video
   */
  public function disableModule(AfterScenarioScope $scope): void {
    \Drupal::service('module_installer')->uninstall(['oe_media', 'path']);
  }

  /**
   * Create remote video entity and go to detail page of media.
   *
   * @param \Behat\Gherkin\Node\TableNode $mediasTable
   *   Table of media data.
   *
   * @Given I visit the remote video entity page:
   */
  public function iVisitTheRemoteVideoEntityPage(TableNode $mediasTable): void {
    $hash = $mediasTable->getColumnsHash();
    $media_data = reset($hash);
    if ($media_data) {
      $media = Media::create([
        'bundle' => 'remote_video',
        'name' => $media_data['title'],
        'oe_media_oembed_video' => $media_data['url'],
        'path' => $media_data['path'],
      ]);
      $media->save();
      $this->visitPath($media_data['path']);
    }
  }

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
   * Checks that the CCK JSON is present on the <HEAD> section of the page.
   *
   * @Then the CCK JSON is present on the head section of the page
   */
  public function assertCckJsonExists(): void {
    if (!$this->cookieConsentJsonExists('{"utility":"cck"}')) {
      throw new \Exception(sprintf('No cookie consent json found.'));
    }
  }

  /**
   * Checks that the CCK JSON is NOT present on the <HEAD> section of the page.
   *
   * @Then the CCK JSON is not present on the head section of the page
   */
  public function assertNoCckJsonExists(): void {
    if ($this->cookieConsentJsonExists('{"utility":"cck"}')) {
      throw new \Exception(sprintf('Cookie consent json found.'));
    }
  }

  /**
   * Check that the cookie consent kit JSON code present on page.
   *
   * @param string $snippet
   *   String with encoded JSON.
   *
   * @return bool
   *   Is webtools JSON snippet present on page.
   */
  protected function cookieConsentJsonExists(string $snippet): bool {
    $xpath_query = "//script[@type='application/json'][.='" . addcslashes($snippet, '\\\'') . "']";
    // Assert presence of webtools JSON with enabled javascript.
    if (!$this->browserSupportsJavaScript()) {
      return count($this->getSession()->getPage()->findAll('xpath', $xpath_query)) === 1;
    }
    else {
      // Retrieve the unprocessed page HTML with AJAX.
      // JS-enabled drivers execute scripts that might modify the markup.
      // In order to retrieve the unprocessed HTML, reload the page with AJAX,
      // so all the cookies are passed. Note that this works
      // only for pages loaded with GET.
      $script = <<<JS
        (function(window) {
          var xhr = new XMLHttpRequest();
          xhr.open('GET', window.location.href, false);
          xhr.send();
          return xhr.responseText;
        })(window)
JS;

      $raw_html = $this->getSession()->evaluateScript($script);
      $doc = new \DOMDocument();
      @$doc->loadHTML($raw_html);
      $xpath = new \DOMXpath($doc);
      return count($xpath->query($xpath_query)) === 1;
    }

    return FALSE;
  }

}
