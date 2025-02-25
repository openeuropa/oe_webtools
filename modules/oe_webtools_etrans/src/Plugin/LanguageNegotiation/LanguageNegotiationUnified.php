<?php

namespace Drupal\oe_webtools\Plugin\LanguageNegotiation;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\language\Attribute\LanguageNegotiation;
use Drupal\language\Plugin\LanguageNegotiation\LanguageNegotiationUrl;
use Drupal\language\LanguageSwitcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class forcing user selected language on outbound urls.
 */
#[LanguageNegotiation(
  id: LanguageNegotiationUnified::METHOD_ID,
  name: new TranslatableMarkup('Unified Url'),
  types: [LanguageInterface::TYPE_INTERFACE,
    LanguageInterface::TYPE_CONTENT,
    LanguageInterface::TYPE_URL,
  ],
  weight: -5,
  description: new TranslatableMarkup("Language from the URL (Path prefix or domain)."),
  config_route_name: 'language.negotiation_url'
)]
class LanguageNegotiationUnified extends LanguageNegotiationUrl implements OutboundPathProcessorInterface, LanguageSwitcherInterface {

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'unified-language';

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = [], ?Request $request = NULL, ?BubbleableMetadata $bubbleable_metadata = NULL) {
    $path = parent::processOutbound($path, $request, $bubbleable_metadata);
    $config = $this->config->get('language.negotiation')->get('url');
    if ($config['source'] == LanguageNegotiationUrl::CONFIG_PATH_PREFIX) {
      // The cache implementation is taken care in parent class.
      $prefix = $options['language']->getId();
      // As we hardcode all outbound link with the requested language
      // we still need to ignore the language switcher urls to allow
      // users to be able to change the selected language.
      $is_language_link = !empty($options['attributes']['class']) && in_array('unified-link', $options['attributes']['class']);
      if ($request && !$is_language_link) {
        $parts = explode('/', $request->getPathInfo());
        $prefix = !empty($config['prefixes'][$parts[1]]) ? $parts[1] : $prefix;
      }
      if (is_object($options['language']) && !empty($config['prefixes'][$prefix])) {
        $options['prefix'] = $config['prefixes'][$prefix] . '/';
        if ($bubbleable_metadata) {
          $bubbleable_metadata->addCacheContexts(['languages:' . LanguageInterface::TYPE_URL]);
        }
      }
    }
    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguageSwitchLinks(Request $request, $type, Url $url) {
    $links = parent::getLanguageSwitchLinks($request, $type, $url);
    foreach ($links as $link) {
      $link['attributes']['class'][] = 'unified-link';
    }
    return $links;
  }

}
