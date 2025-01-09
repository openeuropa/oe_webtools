/**
 * @file
 * Handles the machine translation.
 */

(function (Drupal) {

  /**
   * Handles the machine translation.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.etrans = {
    attach: function (context) {

      const translated = once('oe-multilang-unified-etrans', 'body', context);
      if (!translated) {
        return;
      }

      const unifiedTranslationRequest = document.getElementById('unified-translation-request');
      const translateLink = document.querySelector('.oe-multilingual-unified-etrans--translate');
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
