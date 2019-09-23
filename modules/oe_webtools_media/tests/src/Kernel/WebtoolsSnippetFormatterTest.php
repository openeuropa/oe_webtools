<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_media\Kernel;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\json_field\JsonMarkup;

/**
 * @coversDefaultClass \Drupal\oe_webtools_media\Plugin\Field\FieldFormatter\WebtoolsSnippetFormatter
 *
 * @group oe_webtools_media
 */
class WebtoolsSnippetFormatterTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_webtools',
    'oe_webtools_media',
    'json_field',
    'field',
    'user',
    'entity_test',
    'system',
  ];

  /**
   * The entity type.
   *
   * @var string
   */
  protected $entityType;

  /**
   * The bundle of the entity.
   *
   * @var string
   */
  protected $bundle;

  /**
   * The display to render the entity.
   *
   * @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   */
  protected $display;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('entity_test');

    $this->installConfig(['system', 'field']);

    $this->entityType = 'entity_test';
    $this->bundle = $this->entityType;

    $field_storage = FieldStorageConfig::create([
      'field_name' => 'test_field_media_webtools',
      'entity_type' => $this->entityType,
      'type' => 'json',
    ]);
    $field_storage->save();

    $field = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $this->bundle,
    ]);
    $field->save();

    // The display mode.
    $this->display = EntityViewDisplay::create([
      'targetEntityType' => $this->entityType,
      'bundle' => $this->bundle,
      'mode' => 'default',
    ]);
    $this->display->setComponent('test_field_media_webtools', [
      'type' => 'webtools_snippet',
    ]);
    $this->display->save();
  }

  /**
   * Tests that the formatter contains the necessary library.
   */
  public function testFormatterLibrary() {
    $data = '{"service":"map","map":{"background":["osmec"]},"version":"2.0"}';
    $entity = EntityTest::create([
      'test_field_media_webtools' => $data,
    ]);
    $entity->save();

    $build = $this->display->build($entity);
    $html = $this->render($build);

    // Assert the render contains the required library.
    $this->assertContains('<script src="//europa.eu/webtools/load.js" defer></script>', $html);
  }

  /**
   * Tests that the formatter is rendered correctly.
   *
   * @param string $data
   *   The media webtools field value.
   *
   * @dataProvider providerFormatter
   *
   * @throws \Exception
   */
  public function testFormatter($data) {
    $entity = EntityTest::create([
      'test_field_media_webtools' => $data,
    ]);
    $entity->save();

    $build = $this->display->build($entity);
    $output = (string) $this->container->get('renderer')->renderRoot($build);

    // Assert correct format.
    $this->assertContains('<script type="application/json">' . JsonMarkup::create(Json::encode(Json::decode($data))) . '</script>', $output);

    // Assert the output is Xss filtered.
    $this->assertTrue($output === Xss::filter($output, ['script', 'div']), 'The output is Xss filtered');

    // Assert the script tags were escaped.
    $this->assertTrue(substr_count($output, '</script') === 1, 'Script tags were escaped');

    // Assert the html comment tags were escaped.
    $this->assertTrue(substr_count($output, '<!--') === 0, 'Comment tags were escaped');
  }

  /**
   * Data provider for testFormatter().
   *
   * @see ::testFormatter()
   *
   * @return array
   *   Data provider for the webtools snippet field.
   */
  public function providerFormatter() {
    return [
      ['{"service":"map","map":{"background":["osmec"]},"version":"2.0"}'],
      ['{"service":"map","<!--map":{"background":["osmec"]},"version":"2.0"}'],
      ['{"service":"map","custom":"</script>","map":{"background":["osmec"]},"version":"2.0"}'],
      ['{"service":"smk","type":"user","screen_name":"EU_Commission","count":3,"include_rts":false,"rts_display_original":false,"exclude_replies":true,"display_user":true,"display_user_pic":true,"auto_expand_photo":false,"auto_expand_video":false,"tweet_more_btn":true}'],
      ['{"service":"charts","data":{"webtools":{"jsonstat":{"url":"https://ec.europa.eu/eurostat/wdds/rest/data/v2.1/json/en/ttr00012?&geo=AT&geo=BE&geo=BG&geo=CY&geo=CZ&geo=DE&geo=DK&geo=EE&geo=EL&geo=ES&geo=FI&geo=FR&geo=HR&geo=HU&geo=IE&geo=IT&geo=LT&geo=LU&geo=LV&geo=MT&geo=NL&geo=NO&geo=PL&geo=PT&geo=RO&geo=SE&geo=SI&geo=SK&geo=UK&precision=1&time=2007&time=2008&time=2009&time=2010&time=2011&time=2012&time=2013&time=2014","options":{"series":"unit","categories":"time","sheets":"geo"}}},"xAxis":{"type":"category"}},"provider":"highcharts"'],
    ];
  }

}
