<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_laco_service\Kernel;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\oe_webtools_laco_service\LacoServiceHeaders;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the Laco service functionality.
 */
class LacoServiceTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'system',
    'user',
    'field',
    'entity_test',
    'language',
    'content_translation',
    'text',
    'oe_webtools_laco_service',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('entity_test_mul');
    $this->installEntitySchema('user');
    $this->installConfig([
      'field',
      'user',
      'entity_test',
      'language',
      'content_translation',
    ]);

    // Give anonymous users permission to view test entities.
    Role::load(RoleInterface::ANONYMOUS_ID)
      ->grantPermission('view test entity')
      ->save();

    // Set up some languages.
    ConfigurableLanguage::createFromLangcode('fr')->save();
    ConfigurableLanguage::createFromLangcode('nl')->save();

    $this->enableTranslation();
  }

  /**
   * Main Laco service test.
   *
   * Tests that the Laco service works: requests made to entity routes which
   * contain certain headers and language will return empty responses
   * that contain a status code that confirms the existence of a translation
   * in that language.
   */
  public function testLacoService() {
    $requests = $this->createTestRequests();
    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $kernel */
    $kernel = \Drupal::getContainer()->get('http_kernel');
    foreach ($requests as $definition) {
      $request = $definition[0];
      $response = $kernel->handle($request);
      $status = $response->headers->get('Status');
      $this->assertEquals($status, $definition[1], 'The failure is at ' . $definition[2]);

      // Check also that the response content is empty to make sure that the
      // actual route we hit is not the real canonical route.
      $this->assertEmpty($response->getContent(), 'The response contains content.');
    }

    // Make a request to a regular entity route and ensure that we are getting
    // a response which contains content to prove we did not brake the canonical
    // entity routes.
    $entity = $this->createTestMultilingualEntity('entity title');
    $request = Request::create($entity->toUrl()->toString());
    $response = $kernel->handle($request);
    $crawler = new Crawler($response->getContent());
    $title = $crawler->filter('title');
    $this->assertEquals('entity title |', trim($title->text()));
  }

  /**
   * Creates a test entity.
   *
   * @param string $name
   *   The name of the entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   A 'entity_test_mul' entity.
   */
  protected function createTestMultilingualEntity($name) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->container->get('entity_type.manager')
      ->getStorage('entity_test_mul')
      ->create(['name' => $name]);

    $entity->save();
    return $entity;
  }

  /**
   * Enables translation for the entity_test_mul entity type.
   */
  protected function enableTranslation() {
    // Enable translation for the current entity type and ensure the change is
    // picked up.
    $this->container->get('content_translation.manager')->setEnabled('entity_test_mul', 'entity_test_mul', TRUE);
    drupal_static_reset();
    $this->container->get('entity_type.manager')->clearCachedDefinitions();
    $this->container->get('router.builder')->rebuild();
    $this->container->get('entity.definition_update_manager')->applyUpdates();
  }

  /**
   * Creates a few test requests with their expected response status codes.
   */
  protected function createTestRequests() {
    $entity_one = $this->createTestMultilingualEntity('entity one');
    $entity_two = $this->createTestMultilingualEntity('entity two');
    $entity_two->addTranslation('fr', ['name' => 'entity two fr']);
    $entity_two->save();
    $entity_three = $this->createTestMultilingualEntity('entity three');
    $entity_three->addTranslation('nl', ['name' => 'entity two nl']);
    $entity_three->save();

    $requests = [];
    $requests[] = [
      $this->createRequestToEntityAndLanguage($entity_one, 'en'),
      '200 OK',
      'entity one in en',
    ];

    $requests[] = [
      $this->createRequestToEntityAndLanguage($entity_one, 'fr'),
      '404 Not found',
      'entity one in fr',
    ];

    $requests[] = [
      $this->createRequestToEntityAndLanguage($entity_one, 'nl'),
      '404 Not found',
      'entity one in nl',
    ];

    $requests[] = [
      $this->createRequestToEntityAndLanguage($entity_two, 'en'),
      '200 OK',
      'entity two in en',
    ];

    $requests[] = [
      $this->createRequestToEntityAndLanguage($entity_two, 'fr'),
      '200 OK',
      'entity two in fr',
    ];

    $requests[] = [
      $this->createRequestToEntityAndLanguage($entity_two, 'nl'),
      '404 Not found',
      'entity two in nl',
    ];

    $requests[] = [
      $this->createRequestToEntityAndLanguage($entity_three, 'en'),
      '200 OK',
      'entity three in en',
    ];

    $requests[] = [
      $this->createRequestToEntityAndLanguage($entity_three, 'fr'),
      '404 Not found',
      'entity three in fr',
    ];

    $requests[] = [
      $this->createRequestToEntityAndLanguage($entity_three, 'nl'),
      '200 OK',
      'entity three in nl',
    ];

    return $requests;
  }

  /**
   * Creates a Laco Request for a given entity and language.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The 'entity_test_mul'.
   * @param string $language
   *   A language for the request.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   A Request object.
   */
  protected function createRequestToEntityAndLanguage(ContentEntityInterface $entity, $language) {
    $request = Request::create($entity->toUrl()->toString());
    $request->headers->set(LacoServiceHeaders::HTTP_HEADER_SERVICE_NAME, LacoServiceHeaders::HTTP_HEADER_SERVICE_VALUE);
    $request->headers->set(LacoServiceHeaders::HTTP_HEADER_LANGUAGE_NAME, $language);
    $request->setMethod('HEAD');
    return $request;
  }

}
