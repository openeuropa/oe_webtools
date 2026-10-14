(function ($, Drupal) {
  "use strict";

  Drupal.behaviors.oeWTlc2023Behavior = {
    attach: function (context, settings) {
      // Initialize jQuery objects
      const $etransMessage = $(once('wt-ecl-etrans-translate-message', '.wt-ecl-etrans-message', context));
      const $translateLink = $(once('wt-ecl-translate-link', '.wt-ecl-etrans-message a#_translate', context));
      const $closeLink = $(once('wt-ecl-translate-close', 'button.wt-ecl-message__close', context));
      const $defaultLanguage = $('a.oe-webtools-lc2023-default-language', context);

      // Attach event handlers
      if ($translateLink.length) handleTranslateLink($translateLink, $etransMessage, settings);
      if ($etransMessage.length) handleEtransMessage($etransMessage, $closeLink, context);
      if ($defaultLanguage.length) handleDefaultLanguage($defaultLanguage);

      // Update language elements and add prefLang query parameter.
      $(window, context).on('wtTranslationEnd', function () {
        // Make sure we do not have a live cookie.
        if ($wt && $wt.etrans && !$wt.etrans.getLiveCookie()) {
          addPrefLanguageQueryParam(context);
        }
      });
    }
  };

  // Translate link click event handler
  function handleTranslateLink($translateLink, $etransMessage, settings) {
    $translateLink.on('click', function (e) {
      e.preventDefault();
      $wt.etrans.translate("body", settings.oe_wt_etrans.preferred_language);
      $etransMessage.hide();
    });
  }

  // Etrans message event handlers
  function handleEtransMessage($etransMessage, $closeLink, context) {
    $(window, context).on('wtTranslationAbort wtTranslationStart', function (event) {
      $etransMessage.toggle(event.type === 'wtTranslationAbort');
    });

    $closeLink.on('click', function () {
      $etransMessage.hide();
      removePrefLangFromUrl();
    });
  }

  // Default language click event handler
  function handleDefaultLanguage($defaultLanguage) {
    $defaultLanguage.on('click', function () {
      if ($wt && $wt.etrans.isTranslated()) {
        $wt.etrans.removeLiveCookie();
      }
    });
  }

  // Function to remove 'prefLang' parameter from URL
  function removePrefLangFromUrl() {
    const url = new URL(window.location.href);
    url.searchParams.delete('prefLang');
    window.history.pushState({}, '', url);
  }

  // Add prefLang query parameter to links.
  function addPrefLanguageQueryParam(context) {
    $(context).find('a:not(.ecl-language-list__container a)').each(function () {
      const href = $(this).attr('href');
      if (href && href.startsWith('/') && !href.includes('prefLang=')) {
        const newHref = appendPrefLang(href, $wt.etrans.toLanguage);
        $(this).attr('href', newHref);
      }
    });
  }

  // Append 'prefLang' parameter to the URL.
  function appendPrefLang(href, language) {
    if (language) {
      return href + (href.includes('?') ? '&prefLang=' : '?prefLang=') + language;
    }
    return href;
  }

})(jQuery, Drupal);
