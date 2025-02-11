/**
 * @file
 * Handles webtools eTrans unified block js.
 */

(function (Drupal) {

  /**
   * Handles the machine translation.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.oe_webtools_unified_etrans = {
    attach: function (context) {

      const translated = once('oe-webtools-unified-etrans', 'body', context);
      if (!translated) {
        return;
      }

      const unifiedTranslationRequest = document.getElementById('unified-translation-request');
      const translateLink = document.querySelector('.oe-webtools-unified-etrans--translate');
      translateLink.addEventListener('click', (e) => {
        e.preventDefault();
        $wt.etrans.translate('body', drupalSettings.path.languageTo);
      });

      window.addEventListener('wtTranslationStart', function(e) {
        unifiedTranslationRequest.remove();
      });

      const closeButton = document.querySelector('.utr-close');
      closeButton.addEventListener('click', () => {
        unifiedTranslationRequest.remove();
      });
            
    }
  };
})(Drupal);
