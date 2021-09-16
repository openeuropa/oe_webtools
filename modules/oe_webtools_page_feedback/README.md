# OpenEuropa Webtools Page Feedback

Provides a widget that integrates with the Webtools Default Feedback Form allowing the users to report on the page
usefulness and potentially provide suggestions to improve the content.

### How it works

This module provides a block that prints the Webtools Default Feedback Form on all the content types if enabled and
placed in the default theme.
More information on this service can be found [here](https://webgate.ec.europa.eu/fpfis/wikis/display/webtools/Default+Feedback+Form).


### How to use

#### Required configuration

In order to have the Webtools Default Feedback Form widget available on your site make sure that after enabling the
OpenEuropa Webtools Page Feedback module the block is placed in the default theme and the default configuration is
provided:

* Enabled: Whether the Page Feedback Form is enabled or not.
* Form ID: The Webtools ID of the corresponding Feedback Form where submissions will be recorded.

This configuration can be provided using the configuration form (`/admin/config/system/oe_webtools_page_feedback`) by
users that were granted with the permission `administer webtools page feedback form` or by providing details in your
site's settings.php file:

```
$config['oe_webtools_page_feedback.settings']['enabled'] = TRUE;
$config['oe_webtools_page_feedback.settings']['feedback_form_id'] = '1234';
```
