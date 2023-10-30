/**
 * @file
 * Re-triggers the webtools loader to collect all the components.
 *
 * Typically to be included in Ajax callbacks that add new webtools elements
 * to the page.
 */

(function (Drupal, drupalSettings) {

  /**
   * Triggers the webtools collect() API.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.collectComponents = {
    attach(context) {
      $wt.collect();
    }
  };
})(Drupal, drupalSettings);
