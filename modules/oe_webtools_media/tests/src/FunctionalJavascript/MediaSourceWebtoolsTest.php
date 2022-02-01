<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_media\FunctionalJavascript;

use Drupal\media\Entity\MediaType;
use Drupal\media\MediaInterface;
use Drupal\media\MediaTypeInterface;
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
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * Tests the webtools media source.
   */
  public function testMediaWebtoolsSource(): void {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    foreach ($this->getTestMediaWebtoolsSourceData() as $data) {
      $widget_type = $data[0];
      $widget_name = $data[1];
      $service = $data[2];
      $thumbnail_filename = $data[3];

      $media_type_id = 'test_media_webtools_type';

      // Create webtools map media type.
      $media_type = $this->createWebtoolsMediaType($media_type_id, $widget_type);

      // Create a webtools media item with valid webtools snippet.
      $this->drupalGet("media/add/{$media_type_id}");
      $name = "Valid webtools $widget_name item";
      $assert_session->fieldExists('Name')->setValue($name);
      $assert_session->fieldExists("Webtools {$widget_name} snippet")->setValue('{"service": "' . $service . '"}');
      $page->pressButton('Save');
      $assert_session->addressEquals('admin/content/media');

      $media = $this->getMediaByName($name);
      $this->drupalGet('/media/' . $media->id());
      $img_src = $page->find('css', '.field--name-thumbnail .field__item img')->getAttribute('src');
      $this->assertStringContainsString($thumbnail_filename, $img_src);

      // Check that all fields are properly populated.
      $this->assertSame("Valid webtools $widget_name item", $media->getName());
      $this->assertSame('{"service": "' . $service . '"}', $media->get('field_media_webtools')->value);

      // Create a webtools media item with invalid webtools snippet.
      $this->drupalGet("media/add/{$media_type_id}");
      $assert_session->fieldExists('Name')->setValue("Invalid webtools $widget_name item");
      $assert_session->fieldExists("Webtools {$widget_name} snippet")->setValue('{"service": "' . $service . $widget_type . '"}');
      $page->pressButton('Save');

      $assert_session->pageTextContains("Invalid webtools {$widget_name} snippet.");
      $media_type->delete();
      $media->delete();
    }
  }

  /**
   * Provides data to self::testMediaWebtoolsMapSource().
   *
   * @return array
   *   An array of widget types data.
   */
  public function getTestMediaWebtoolsSourceData(): array {
    return [
      ['chart', 'Chart', 'charts', '/charts-embed-no-bg.png'],
      ['chart', 'Chart', 'chart', '/charts-embed-no-bg.png'],
      ['chart', 'Chart', 'racing', '/charts-embed-no-bg.png'],
      ['map', 'Map', 'map', '/maps-embed-no-bg.png'],
      ['social_feed', 'Social feed', 'smk', '/twitter-embed-no-bg.png'],
      ['opwidget', 'OP Publication list', 'opwidget', '/generic.png'],
    ];
  }

  /**
   * Creates webtools media type.
   *
   * @param string $media_type_id
   *   The media type id.
   * @param string $widget_type
   *   The widget type.
   *
   * @return \Drupal\media\MediaTypeInterface
   *   Returns created the media type.
   */
  protected function createWebtoolsMediaType(string $media_type_id, string $widget_type): MediaTypeInterface {
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
    $assert_session->optionExists('Widget type', $widget_type);
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

    // Use the default formatter and settings for image.
    $component = \Drupal::service('plugin.manager.field.formatter')->prepareConfiguration('image', []);

    $entity_display = \Drupal::entityTypeManager()->getStorage('entity_view_display')->load('media.' . $media_type_id . '.default');
    $entity_display->setComponent('thumbnail', $component)->save();

    // Bundle definitions are statically cached in the context of the test, we
    // need to make sure we have updated information before proceeding with the
    // actions on the UI.
    \Drupal::service('entity_type.bundle.info')->clearCachedBundles();
    \Drupal::service('entity_field.manager')->clearCachedFieldDefinitions();

    return MediaType::load($media_type_id);
  }

  /**
   * Loads and returns a Media entity by name.
   *
   * @param string $name
   *   The media name.
   *
   * @return \Drupal\media\MediaInterface
   *   The media.
   */
  protected function getMediaByName(string $name): MediaInterface {
    $entities = \Drupal::entityTypeManager()->getStorage('media')->loadByProperties(['name' => $name]);
    if (!$entities) {
      throw new \Exception(sprintf('The media with the name %s does not exist.', $name));
    }

    return reset($entities);
  }

}
