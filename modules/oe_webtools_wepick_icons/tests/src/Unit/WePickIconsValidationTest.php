<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_wepick_icons\Unit;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\oe_webtools_wepick_icons\Element\WePickIcons;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the validation of the WePick Icons form element.
 *
 * @coversDefaultClass \Drupal\oe_webtools_wepick_icons\Element\WePickIcons
 */
class WePickIconsValidationTest extends UnitTestCase {

  /**
   * Tests the validateWePickIcons method.
   *
   * @param string $input
   *   The input value for the element.
   * @param array|null $expected_value
   *   The expected value set on the form state.
   * @param string|null $expected_error
   *   The expected error message, or NULL if no error.
   *
   * @covers ::validateWePickIcons
   * @dataProvider providerValidateWePickIcons
   */
  public function testValidateWePickIcons(string $input, ?array $expected_value, ?string $expected_error): void {
    $element = ['#value' => $input];
    $complete_form = [];

    $form_state = $this->createMock(FormStateInterface::class);
    $form_state->expects($this->once())
      ->method('setValueForElement')
      ->with($element, $expected_value);

    if ($expected_error !== NULL) {
      $form_state->expects($this->once())
        ->method('setError')
        ->with($element, $this->callback(function ($message) use ($expected_error) {
          $this->assertInstanceOf(TranslatableMarkup::class, $message);
          $this->assertEquals($expected_error, $message->getUntranslatedString());
          return TRUE;
        }));
    }
    else {
      $form_state->expects($this->never())
        ->method('setError');
    }

    WePickIcons::validateWePickIcons($element, $form_state, $complete_form);
  }

  /**
   * Data provider for testValidateWePickIcons().
   *
   * @return array
   *   The test cases.
   */
  public static function providerValidateWePickIcons(): array {
    return [
      'empty value' => [
        '',
        NULL,
        NULL,
      ],
      'valid icon' => [
        '{"name":"digg","family":"networks-color"}',
        ['icon_name' => 'digg', 'icon_family' => 'networks-color'],
        NULL,
      ],
      'invalid JSON string' => [
        'not-json',
        NULL,
        'The submitted icon value is not in the correct format.',
      ],
      'JSON string instead of object' => [
        '"just a string"',
        NULL,
        'The submitted icon value is not in the correct format.',
      ],
      'missing family' => [
        '{"name":"digg"}',
        NULL,
        'The selected icon must have a valid name and family.',
      ],
      'missing name' => [
        '{"family":"networks-color"}',
        NULL,
        'The selected icon must have a valid name and family.',
      ],
      'empty name' => [
        '{"name":"","family":"networks-color"}',
        NULL,
        'The selected icon must have a valid name and family.',
      ],
      'empty family' => [
        '{"name":"digg","family":""}',
        NULL,
        'The selected icon must have a valid name and family.',
      ],
      'name is not a string' => [
        '{"name":123,"family":"networks-color"}',
        NULL,
        'The selected icon must have a valid name and family.',
      ],
      'family is not a string' => [
        '{"name":"digg","family":["networks-color"]}',
        NULL,
        'The selected icon must have a valid name and family.',
      ],
    ];
  }

}
