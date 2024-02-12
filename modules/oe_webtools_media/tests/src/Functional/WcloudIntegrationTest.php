<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_media\Functional;

use Drupal\Tests\media\Functional\MediaFunctionalTestBase;

/**
 * Tests the Webtools media source WCLOUD integration.
 *
 * @group oe_webtools_media
 */
class WcloudIntegrationTest extends MediaFunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'json_field',
    'oe_webtools',
    'oe_webtools_media',
    'oe_webtools_media_wcloud_mock',
  ];

  /**
   * Tests the Webtools media source support for WCLOUD.
   */
  public function testMediaWebtoolsWcloudIntegration(): void {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    // Create a Webtools media type for charts.
    $this->createMediaType('webtools', [
      'id' => 'test_webtools_cloud',
      'label' => 'Test WCLOUD webtools',
      'source' => 'webtools',
      'source_configuration' => [
        'widget_type' => 'chart',
      ],
    ]);

    // Create a Webtools WCLOUD media item without a URL.
    $this->drupalGet('media/add/test_webtools_cloud');
    $name = "Valid webtools WCLOUD item";
    $assert_session->fieldExists('Name')->setValue($name);
    $assert_session->fieldExists('Webtools Chart snippet')->setValue('{"utility": "wcloud"}');
    $page->pressButton('Save');
    $assert_session->pageTextContains('The provided WCLOUD URL is not valid.');

    // Create a Webtools WCLOUD media item with an invalid URL.
    $assert_session->fieldExists('Webtools Chart snippet')->setValue('{"utility": "wcloud", "url": "not-a-url"}');
    $page->pressButton('Save');
    $assert_session->pageTextContains('The provided WCLOUD URL is not valid.');

    // Create a Webtools WCLOUD media item with a URL outside the europa.eu
    // domain.
    $assert_session->fieldExists('Webtools Chart snippet')->setValue('{"utility": "wcloud", "url": "https://google.com"}');
    $page->pressButton('Save');
    $assert_session->pageTextContains('The WCLOUD URL needs to be in the europa.eu domain.');

    // Create a Webtools WCLOUD media item with a URL that contains europa.eu
    // but is not in the domain.
    $assert_session->fieldExists('Webtools Chart snippet')->setValue('{"utility": "wcloud", "url": "https://webtools.europa.eunot.com"}');
    $page->pressButton('Save');
    $assert_session->pageTextContains('The WCLOUD URL needs to be in the europa.eu domain.');

    // Create a Webtools WCLOUD media item with a URL that returns a 404.
    $assert_session->fieldExists('Webtools Chart snippet')->setValue('{"utility": "wcloud", "url": "https://europa.eu/correct-error?code=404"}');
    $page->pressButton('Save');
    $assert_session->pageTextContains('Cannot access the contents of the URL. Please verify that it exists and it’s accessible for anonymous users.');

    // Create a Webtools WCLOUD media item with a URL that returns a 500.
    $assert_session->fieldExists('Webtools Chart snippet')->setValue('{"utility": "wcloud", "url": "https://europa.eu/correct-error?code=500"}');
    $page->pressButton('Save');
    $assert_session->pageTextContains('Cannot access the contents of the URL. Please verify that it exists and it’s accessible for anonymous users.');

    // Create a Webtools WCLOUD media item with a URL that returns an empty
    // response.
    $assert_session->fieldExists('Webtools Chart snippet')->setValue('{"utility": "wcloud", "url": "https://europa.eu/empty-wcloud"}');
    $page->pressButton('Save');
    $assert_session->pageTextContains('Cannot access the contents of the URL. Please verify that it exists and it’s accessible for anonymous users.');

    // Create a Webtools WCLOUD media item with a URL that returns a correct
    // response, but for the wrong widget type.
    $assert_session->fieldExists('Webtools Chart snippet')->setValue('{"utility": "wcloud", "url": "https://europa.eu/correct-wcloud?widget=map"}');
    $page->pressButton('Save');
    $assert_session->pageTextContains('Invalid webtools chart snippet.');

    // Create a Webtools WCLOUD media item with a URL that returns a correct
    // response.
    $assert_session->fieldExists('Webtools Chart snippet')->setValue('{"utility": "wcloud", "url": "https://europa.eu/correct-wcloud?widget=chart"}');
    $page->pressButton('Save');
    $assert_session->pageTextContains('Test wcloud webtools Valid webtools WCLOUD item has been created.');
    $assert_session->addressEquals('admin/content/media');
    $entities = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['name' => $name]);
    if (!$entities) {
      throw new \Exception(sprintf('The media with the name %s does not exist.', $name));
    }

    $media = reset($entities);
    // Check that all fields are properly populated.
    $this->assertSame($name, $media->getName());
    $this->assertSame('{"utility": "wcloud", "url": "https://europa.eu/correct-wcloud?widget=chart"}', $media->get('field_media_webtools')->value);
  }

}
