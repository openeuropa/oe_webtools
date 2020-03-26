<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_webtools_globan\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests the 'oe_webtools_goban.visibility' service.
 *
 * @group oe_webtools_globan
 */
class VisibilityServiceTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_webtools_globan',
    'system',
  ];

  /**
   * Tests the 'oe_webtools_goban.visibility' service.
   */
  public function testVisibilityService(): void {
    $this->installConfig(['oe_webtools_globan', 'system']);

    // Test empty path patterns.
    $this->assertPathMatchesVisibility('/user/login', 'show', '');
    $this->assertPathMatchesVisibility('/user', 'show', '');
    $this->assertPathMatchesVisibility('/user/login', 'hide', '');
    $this->assertPathMatchesVisibility('/user', 'hide', '');

    // The front page is '/user/login' by default. See system.site config.
    $this->assertPathMatchesVisibility('/user/login', 'show', '<front>');
    $this->assertPathNotMatchesVisibility('/user', 'show', '<front>');
    $this->assertPathNotMatchesVisibility('/contact', 'show', '<front>');

    // Test multiple patterns.
    $this->assertPathMatchesVisibility('/user/login', 'show', "/user/*\n/contact");
    $this->assertPathMatchesVisibility('/user/3/edit', 'show', "/user/*\n/contact");
    $this->assertPathMatchesVisibility('/user/password', 'show', "/user/*\n/contact");
    $this->assertPathMatchesVisibility('/contact', 'show', "/user/*\n/contact");
    $this->assertPathNotMatchesVisibility('/user', 'show', "/user/*\n/contact");

    // Test pattern negation.
    $this->assertPathNotMatchesVisibility('/user/password', 'hide', "/user/*");
    $this->assertPathNotMatchesVisibility('/user/3/edit', 'hide', "/user/*");
    $this->assertPathMatchesVisibility('/contact', 'hide', "/user/*");
  }

  /**
   * Asserts that a given path satisfies a pattern.
   *
   * @param string $path
   *   The path to be checked.
   * @param string $action
   *   The action: 'show' or 'hide'.
   * @param string $pages
   *   The list of page patterns as text.
   */
  protected function assertPathMatchesVisibility(string $path, string $action, string $pages): void {
    $this->assertTrue($this->getVisibility($path, $action, $pages));
  }

  /**
   * Asserts that a given path doesn't satisfy a pattern.
   *
   * @param string $path
   *   The path to be checked.
   * @param string $action
   *   The action: 'show' or 'hide'.
   * @param string $pages
   *   The list of page patterns as text.
   */
  protected function assertPathNotMatchesVisibility(string $path, string $action, string $pages): void {
    $this->assertFalse($this->getVisibility($path, $action, $pages));
  }

  /**
   * Checks that a given path satisfies a pattern.
   *
   * @param string $path
   *   The path to be checked.
   * @param string $action
   *   The action: 'show' or 'hide'.
   * @param string $pages
   *   The list of page patterns as text.
   *
   * @return bool
   *   TRUE if the path satisfies the pattern.
   */
  protected function getVisibility(string $path, string $action, string $pages): bool {
    $this->config('oe_webtools_globan.settings')
      ->set('visibility.action', $action)
      ->set('visibility.pages', $pages)
      ->save();

    $request = Request::create($path);
    $request_stack = $this->container->get('request_stack');
    $request_stack->pop();
    $request_stack->push($request);

    $service = $this->container->get('oe_webtools_goban.visibility');
    return $service->shouldDisplayBanner();
  }

}
