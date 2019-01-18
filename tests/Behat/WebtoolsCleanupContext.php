<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools\Behat;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\ScenarioInterface;
use Drupal\Core\Path\AliasStorage;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Context to clean up entities created through the UI during scenarios.
 */
class WebtoolsCleanupContext extends RawDrupalContext {

  /**
   * The IDs of the existing entities in the system, keyed by entity type.
   *
   * @var array
   */
  protected $existing = [];

  /**
   * The PIDs of the existing url aliases.
   *
   * @var array
   */
  protected $existingAliases = [];

  /**
   * Collect the IDs of entities existing before the execution of the scenario.
   *
   * Entity types can be marked for cleanup by adding a tag that starts with
   * "cleanup:" followed by the entity type machine name.
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   *   The scenario scope.
   *
   * @beforeScenario
   */
  public function collectExistingEntities(BeforeScenarioScope $scope): void {
    // Reset the entity list at the beginning of each scenario.
    $this->existing = [];

    $entity_types = $this->getEntityTypesToCleanup($scope->getScenario());
    foreach ($entity_types as $entity_type) {
      $this->existing[$entity_type] = $this->getAllEntityIdsOfType($entity_type);
    }
  }

  /**
   * Collect the PIDs of aliases existing before the execution of the scenario.
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   *   The scenario scope.
   *
   * @beforeScenario @cleanup-aliases
   */
  public function collectExistingUrlAliases(BeforeScenarioScope $scope): void {
    // Reset the alias list at the beginning of each scenario.
    $this->existingAliases = [];
    // Executing database query,
    // because AliasStorage->load() returns only a single url alias.
    $query = \Drupal::database()->select(AliasStorage::TABLE, 'ua');
    $query->fields('ua', ['pid']);
    $this->existingAliases = $query->execute()->fetchAllKeyed(0, 0);
  }

  /**
   * Deletes url aliases created though the scenario.
   *
   * @param \Behat\Behat\Hook\Scope\AfterScenarioScope $scope
   *   The scenario scope.
   *
   * @afterScenario @cleanup-aliases
   */
  public function cleanupCreatedUrlAliases(AfterScenarioScope $scope): void {
    $query = \Drupal::database()->delete(AliasStorage::TABLE);
    if ($this->existingAliases) {
      $query->condition('pid', $this->existingAliases, 'NOT IN');
    }
    $query->execute();
    Cache::invalidateTags(['route_match']);
  }

  /**
   * Deletes entities of specified type created though the scenario.
   *
   * @param \Behat\Behat\Hook\Scope\AfterScenarioScope $scope
   *   The scenario scope.
   *
   * @afterScenario
   */
  public function cleanupCreatedEntities(AfterScenarioScope $scope): void {
    foreach ($this->existing as $entity_type => $ids) {
      $current_ids = $this->getAllEntityIdsOfType($entity_type);
      $test_entity_ids = array_diff($current_ids, $ids);

      if ($test_entity_ids) {
        $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
        $storage->delete($storage->loadMultiple($test_entity_ids));
      }
    }
  }

  /**
   * Returns the entity types marked for cleanup in a scenario.
   *
   * @param \Behat\Gherkin\Node\ScenarioInterface $scenario
   *   The test scenario.
   *
   * @return string[]
   *   A list of entity types marked for cleanup.
   */
  protected function getEntityTypesToCleanup(ScenarioInterface $scenario): array {
    $entity_types = [];

    foreach ($scenario->getTags() as $tag) {
      if (strpos($tag, 'cleanup:') === 0) {
        $entity_types[] = substr($tag, 8);
      }
    }

    return $entity_types;
  }

  /**
   * Returns the IDs of all the entities of a certain type.
   *
   * @param string $entity_type
   *   The entity type.
   *
   * @return int[]
   *   An array of entity IDs.
   */
  protected function getAllEntityIdsOfType(string $entity_type): array {
    return \Drupal::entityTypeManager()->getStorage($entity_type)->getQuery()->execute();
  }

}