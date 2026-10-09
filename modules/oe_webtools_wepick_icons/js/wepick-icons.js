/**
 * @file
 * Removes the required attribute from the WePick Icons hidden textarea.
 */

(function (Drupal, once) {

  'use strict';

  Drupal.behaviors.wePickIconsRequired = {
    attach: function (context) {
      once('wepick-icons-required', 'textarea[data-wepick-icons]', context).forEach(function (textarea) {
        textarea.removeAttribute('required');
      });
    }
  };

}(Drupal, once));
