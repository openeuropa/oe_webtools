<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_geocoding\Kernel;

use Drupal\Core\Config\Schema\SchemaCheckTrait;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the config schema of the Webtools Geocoding module.
 *
 * @group oe_webtools_geocoding
 */
class ConfigSchemaTest extends KernelTestBase {

  use SchemaCheckTrait;

  /**
   * The typed config manager.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface
   */
  protected $typedConfigManager;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'geocoder',
    'oe_webtools_geocoding',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['geocoder']);
    $this->typedConfigManager = \Drupal::service('config.typed');
  }

  /**
   * Tests whether the config schema is correct.
   */
  public function testConfigSchema() {
    $config = $this->config('geocoder.settings');
    $result = $this->checkConfigSchema($this->typedConfigManager, 'geocoder.settings', $config->get());
    $this->assertTrue($result);
  }

}
