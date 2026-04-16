(function (Drupal, once) {

  'use strict';

  var FALLBACK_TIMEOUT_MS = 5000;

  function triggerFallback(container) {
    var wtagWrapper = container.querySelector('.wtag-wrapper');
    var fallbackWrapper = container.querySelector('.wtag-fallback');
    var inputMode = container.querySelector('input[data-wtag-input-mode]');

    if (wtagWrapper) {
      wtagWrapper.classList.add('wtag-wrapper--hidden');
      wtagWrapper.querySelector('textarea').removeAttribute('required');
    }
    if (fallbackWrapper) {
      fallbackWrapper.classList.remove('wtag-fallback');
      fallbackWrapper.classList.add('wtag-fallback--active');
    }
    if (inputMode) {
      inputMode.value = 'fallback';
    }
  }

  Drupal.behaviors.wtagFallback = {
    attach: function (context) {
      once('wtag-fallback', '[data-wtag-id]', context).forEach(function (container) {
        var elementId = container.getAttribute('data-wtag-id');
        var target = '#' + elementId;
        var timer = null;

        function onReady(e) {
          if (e.parameters && e.parameters.params && e.parameters.params.target === target) {
            clearTimeout(timer);
          }
        }

        function onError() {
          clearTimeout(timer);
          triggerFallback(container);
        }

        window.addEventListener('wtWtagReady', onReady);
        window.addEventListener('wtWtagError', onError);

        timer = setTimeout(function () {
          triggerFallback(container);
        }, FALLBACK_TIMEOUT_MS);
      });
    }
  };

}(Drupal, once));
