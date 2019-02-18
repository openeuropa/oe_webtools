<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools\Behat;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\media\Entity\Media;

/**
 * Behat step definitions related to the oe_webtools_cookie_consent module.
 */
class WebtoolsCookieConsentContext extends RawDrupalContext {

  /**
   * Enables the Media module.
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   *   The scope.
   *
   * @beforeScenario @remote-video
   */
  public function enableTestModule(BeforeScenarioScope $scope): void {
    \Drupal::service('module_installer')->install(['oe_media']);
  }

  /**
   * Disables the Media module.
   *
   * @param \Behat\Behat\Hook\Scope\AfterScenarioScope $scope
   *   The scope.
   *
   * @afterScenario @remote-video
   */
  public function disableTestModule(AfterScenarioScope $scope): void {
    \Drupal::service('module_installer')->uninstall(['oe_media']);
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
      ]);
      $media->save();
      $this->visitPath('media/' . $media->id());
    }
  }

  /**
   * Checks that an OEmbed iframe url use CCK service.
   *
   * @Then I should see the oEmbed video iframe with cookie consent
   */
  public function assertOembedIframeWithCckUsage(): void {
    $iframe_url = $this->getSession()->getPage()->find('css', 'iframe')->getAttribute('src');
    $this->visitPath(str_replace(rtrim($this->getDrupalParameter('drupal')['drupal_root'], '/'), '', $iframe_url));
    $this->assertSession()->elementExists('css', "iframe[src^='//ec.europa.eu/cookie-consent/iframe?oriurl=']");
  }

}
