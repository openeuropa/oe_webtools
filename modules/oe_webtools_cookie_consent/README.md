# OpenEuropa Webtools Cookie consent

This component provides the integration with the Webtools Cookie Consent Kit (CCK).

## What it does
The `oe_webtools_cookie_consent` module performs 3 different tasks:
* Provides a banner that allows the user whether to accept or refuse tickets from the website.
* Preprocesses the media_oembed iframes and alters the URL to go through the EC cookie consent service.
* (soon) Preprocesses iframes provided by the video_embed_field or the video_embed_wysiwyg modules and also redirects
the source through the EC cookie consent service.

For more information see [here](https://webgate.ec.europa.eu/fpfis/wikis/display/webtools/Cookie+Consent+Kit).

## How to use

Simply install the module and all available options will be enabled.
Uninstall the module to disable the services.

### Required configuration

In order to provide the cookie consent functionality the OpenEuropa Webtools Cookie Consent module
needs a default configuration in the form of this variable:

* Enable Cookie Consent Kit: Enable the CCK banner.

This configuration can be provided using Drupal 8 configuration system or by
providing details in your sites settings.php file:

```
$config['oe_webtools_cookie_consent.settings']['banner_popup'] = true;
$config['oe_webtools_cookie_consent.settings']['video_popup'] = true;

```
