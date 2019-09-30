<?php

/**
 * @file
 * Contains step definitions specific to the internal scenarios of OE Webtools.
 */

declare(strict_types = 1);

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\media\Entity\Media;

/**
 * Defines non-reusable step definitions to use with OpenEuropa Webtools.
 */
class FeatureContext extends RawDrupalContext {

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
   * Create remote video entity and go to detail page of media.
   *
   * @param \Behat\Gherkin\Node\TableNode $mediasTable
   *   Table of media data.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   Thrown when the entity cannot be created.
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

}
