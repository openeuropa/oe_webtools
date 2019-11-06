<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools\Behat;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\media\Entity\Media;

define('OE_WEBTOOLS_COOKIE_CONSENT_BANNER_COOKIE_URL', '//ec.europa.eu/wel/cookie-consent/consent.js');

/**
 * Behat step definitions related to the oe_webtools_cookie_consent module.
 */
class WebtoolsCookieConsentContext extends RawDrupalContext {

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
