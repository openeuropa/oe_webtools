<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_webtools_captcha\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Tests that the captcha is rendered and validates correctly.
 *
 * @group oe_webtools_captcha
 */
class CaptchaTest extends WebDriverTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'config',
    'system',
    'user',
    'oe_webtools_captcha_mock',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'olivero';

  /**
   * Tests The configuration is well saved from the form to config.
   */
  public function testCaptchaForm(): void {
    // Set the CAPTCHA on login form.
    /** @var \Drupal\captcha\Entity\CaptchaPoint $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()->getStorage('captcha_point')->load('user_login_form');
    $captcha_point->setCaptchaType('oe_webtools_captcha/Webtools captcha');
    $captcha_point->enable()->save();

    // Set the mock to return error.
    \Drupal::state()->set('captcha_mock_response', 'error');

    $this->drupalGet('user/login');

    $user = $this->createUser();
    $page = $this->getSession()->getPage();

    // First make sure we can't log in without the captcha.
    $page->fillField('Username', $user->getAccountName());
    $page->fillField('Password', $user->passRaw);
    $this->waitForWebtoolsCaptcha();
    $page->pressButton('Log in');
    $this->assertSession()->pageTextContainsOnce('The answer you entered for the CAPTCHA was not correct.');

    // Set the mock to pass validation.
    \Drupal::state()->set('captcha_mock_response', 'success');
    $page->fillField('Username', $user->getAccountName());
    $page->fillField('Password', $user->passRaw);
    $this->waitForWebtoolsCaptcha();
    $page->pressButton('Log in');
    $this->assertSession()->pageTextNotContains('The answer you entered for the CAPTCHA was not correct.');
    $this->assertSession()->pageTextContains('Member for');
    $this->assertSession()->fieldNotExists('Username');
    $this->assertSession()->fieldNotExists('Password');
  }

  /**
   * Waits for the Webtools Captcha to be rendered.
   *
   * Submitting the page too early, before the full captcha HTML is generated,
   * can cause JS errors.
   */
  protected function waitForWebtoolsCaptcha(): void {
    $this->assertNotNull($this->assertSession()->waitForElementVisible('css', '.wt-captcha--challenge'));
  }

}
