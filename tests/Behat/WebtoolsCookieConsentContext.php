<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools\Behat;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\media\Entity\Media;

define('OE_WEBTOOLS_COOKIE_CONSENT_EMBED_COOKIE_URL', '//europa.eu/webtools/crs/iframe/');
define('OE_WEBTOOLS_COOKIE_CONSENT_BANNER_COOKIE_URL', '//ec.europa.eu/wel/cookie-consent/consent.js');

/**
 * Behat step definitions related to the oe_webtools_cookie_consent module.
 */
class WebtoolsCookieConsentContext extends RawDrupalContext {

  /**
   * An array of media entities created during a scenario.
   *
   * @var \Drupal\media\MediaInterface[]
   */
  protected $media = [];

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
   * Enables the Media module.
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   *   The scope.
   *
   * @beforeScenario @remote-video
   */
  public function enableModule(BeforeScenarioScope $scope): void {
    \Drupal::service('module_installer')->install(['oe_media']);

    $this->configContext->setConfig('media.settings', 'standalone_url', TRUE);
    \Drupal::service('router.builder')->rebuild();
  }

  /**
   * Disables the Media module.
   *
   * @param \Behat\Behat\Hook\Scope\AfterScenarioScope $scope
   *   The scope.
   *
   * @afterScenario @remote-video
   */
  public function disableModule(AfterScenarioScope $scope): void {
    \Drupal::service('module_installer')->uninstall(['oe_media']);
  }

  /**
   * Creates a remote video media entity using the provided data.
   *
   * Table format:
   * | url                     | title                  | path         |
   * | http://www.yt.com/vg734 | Energy, let's save it! | /media/test  |
   *
   * @param \Behat\Gherkin\Node\TableNode $media_data
   *   Table of remote video media data.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   Thrown when one of the remote video media entities can not be saved.
   *
   * @Given the following remote video entity:
   * @Given the following remote video entities:
   */
  public function createRemoteVideoEntities(TableNode $media_data): void {
    $hash = $media_data->getColumnsHash();
    $media_data = reset($hash);
    if ($media_data) {
      $media = Media::create([
        'bundle' => 'remote_video',
        'name' => $media_data['title'],
        'oe_media_oembed_video' => $media_data['url'],
        'path' => $media_data['path'],
      ]);
      $media->save();
      $this->media[] = $media;
    }
  }

  /**
   * Goes to the detail page of a media entity.
   *
   * @param string $title
   *   The title of the media entity to visit.
   *
   * @throws \Exception
   *   Thrown when the entity cannot be loaded.
   *
   * @Given I visit the remote video entity page :title
   */
  public function iVisitTheRemoteVideoEntityPage(string $title): void {
    $entity_manager = \Drupal::entityTypeManager();
    $storage = $entity_manager->getStorage('media');

    $query = $storage->getQuery()
      ->condition('name', $title)
      ->range(0, 1);
    $results = $query->execute();

    if (empty($results)) {
      throw new \Exception("Media entity with title '$title' was not found.'");
    }
    $result = reset($results);
    /** @var \Drupal\media\MediaInterface $entity */
    $entity = $storage->load($result);
    $this->visitPath($entity->get('path')->value);
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

  /**
   * Cleans up entities created during a scenario.
   *
   * @AfterScenario
   */
  public function clean(AfterScenarioScope $scope) {
    foreach ($this->media as $media) {
      $media->delete();
    }
  }

}
