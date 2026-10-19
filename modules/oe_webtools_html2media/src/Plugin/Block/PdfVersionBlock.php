<?php

namespace Drupal\oe_webtools_html2media\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a pdf version link block.
 *
 * @Block(
 *   id = "oe_webtools_html2media_pdf_version",
 *   admin_label = @Translation("OpenEuropa Webtools PDF version")
 * )
 */
class PdfVersionBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Request stack.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('request_stack')
    );
  }

  /**
   * Plugin constructor.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param Symfony\Component\HttpFoundation\RequestStack $request
   *   Request params instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RequestStack $request) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->request = $request->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    // Get current page url.
    $params = $this->request->query->all();
    $current_url = Url::fromRoute('<current>', $params)->setAbsolute()->toString();

    $controller_url = Url::fromRoute('oe_webtools_html2media.webtools',
        [
          'url' => $current_url,
        ])->toString();

    return [
      '#theme' => 'oe_webtools_html2media_block_link',
      '#url' => $controller_url,
    ];
  }

}
