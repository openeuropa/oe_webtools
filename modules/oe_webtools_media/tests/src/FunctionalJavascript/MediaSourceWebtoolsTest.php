<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_media\FunctionalJavascript;

use Drupal\media\Entity\Media;
use Drupal\media\Entity\MediaType;
use Drupal\Tests\media\FunctionalJavascript\MediaSourceTestBase;

/**
 * Tests the webtools media source.
 *
 * @group oe_webtools_media
 */
class MediaSourceWebtoolsTest extends MediaSourceTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'system',
    'node',
    'field_ui',
    'media',
    'json_field',
    'oe_webtools',
    'oe_webtools_media',
  ];

  /**
   * Tests the webtools media source.
   */
  public function createWebtoolsMediaType($media_type_id, $widget_type) {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    $source_id = 'webtools';

    $this->drupalGet('admin/structure/media/add');
    $page->fillField('label', $media_type_id);
    $this->getSession()
      ->wait(5000, "jQuery('.machine-name-value').text() === '{$media_type_id}'");

    // Make sure the source is available.
    $assert_session->fieldExists('Media source');
    $assert_session->optionExists('Media source', $source_id);
    $page->selectFieldOption('Media source', $source_id);
    $result = $assert_session->waitForElementVisible('css', 'fieldset[data-drupal-selector="edit-source-configuration"]');
    $this->assertNotEmpty($result);
    $page->selectFieldOption('Widget type', $widget_type);

    // Save the form to create the type.
    $page->pressButton('Save');
    $assert_session->pageTextContains('The media type ' . $media_type_id . ' has been added.');
    $this->drupalGet('admin/structure/media');
    $assert_session->pageTextContains($media_type_id);

    // Create the description custom field.
    $fields = [
      'field_media_webtools_description' => 'string_long',
    ];
    $this->createMediaTypeFields($fields, $media_type_id);

    // Set the fields map.
    $this->drupalGet("admin/structure/media/manage/$media_type_id");
    $assert_session->selectExists('field_map[description]')->setValue('field_media_webtools_description');
    $assert_session->selectExists('field_map[title]')->setValue('name');
    $assert_session->buttonExists('Save')->press();

    // Bundle definitions are statically cached in the context of the test, we
    // need to make sure we have updated information before proceeding with the
    // actions on the UI.
    \Drupal::service('entity_type.bundle.info')->clearCachedBundles();
    \Drupal::service('entity_field.manager')->clearCachedFieldDefinitions();

    return MediaType::load($media_type_id);
  }

  /**
   * Tests the webtools media map source.
   */
  public function testMediaWebtoolsMapSource() {
    $media_type_id = 'test_media_webtools_type';

    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    // Create webtools map media type.
    $this->createWebtoolsMediaType($media_type_id, 'map');

    // Create a webtools map media item with valid webtools map snippet.
    $this->drupalGet("media/add/{$media_type_id}");
    $assert_session->fieldExists('Name')->setValue('Valid world map');
    $assert_session->fieldExists('Webtools Map snippet')->setValue('{"service": "map"}');
    $page->pressButton('Save');

    $assert_session->addressEquals('admin/content/media');

    // Load the media and check that all fields are properly populated.
    $media = Media::load(1);
    $this->assertSame('Valid world map', $media->getName());
    $this->assertSame('{"service": "map"}', $media->get('field_media_webtools')->value);

    // Create a webtools map media item with invalid webtools map snippet.
    $this->drupalGet("media/add/{$media_type_id}");
    $assert_session->fieldExists('Name')->setValue('Invalid world map');
    $assert_session->fieldExists('Webtools Map snippet')->setValue('{"service": "nap"}');
    $page->pressButton('Save');

    $assert_session->pageTextContains('Invalid webtools map snippet.');
  }

}
