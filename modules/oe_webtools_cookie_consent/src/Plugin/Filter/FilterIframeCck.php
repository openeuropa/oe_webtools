<?php

namespace Drupal\oe_webtools_cookie_consent\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a filter to prepend Cookie Consent Kit on iframe elements.
 *
 * @Filter(
 *   id = "filter_inframe_cck",
 *   title = @Translation("Iframe with cookie consent kit"),
 *   description = @Translation("Prepends the cookie consent kit before the source of the iframes."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class FilterIframeCck extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a FilterIframeCck object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    if (stristr($text, 'iframe') !== FALSE) {
      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);
      foreach ($xpath->query('//iframe[@src]') as $node) {
        $src = $node->getAttribute('src');
        // Prepend the cookie consent kit before the source url.
        $new_src = OE_WEBTOOLS_COOKIE_CONSENT_EMBED_COOKIE_URL . '?oriurl=' . $src . '&lang=' . $this->languageManager->getCurrentLanguage()->getId();
        $node->setAttribute('src', $new_src);
      }
      $result->setProcessedText(Html::serialize($dom));
    }

    return $result;
  }

}
