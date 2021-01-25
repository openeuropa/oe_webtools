# OpenEuropa Webtools Cookie Consent

This component provides the integration with the Webtools Cookie Consent Kit (CCK).

## What it does
The `oe_webtools_cookie_consent` module performs 3 different tasks:
* Provides a banner that allows the end-user to accept all 1st-party cookies or only those that are technically required.
* Preprocesses the media_oembed iframes and alters the URL to go through the EC Cookie Consent service.
* Preprocesses iframes provided by the video_embed_field or the video_embed_wysiwyg modules and also redirects
the source through the EC Cookie Consent service. (the source through the EC Cookie Consent service.
[In Progress in the issue #78](https://github.com/openeuropa/oe_webtools/issues/78)


For more information see [here](https://webgate.ec.europa.eu/fpfis/wikis/display/webtools/Cookie+Consent+Kit).

## How to use

Simply install the module and all available options will be enabled.
Uninstall the module to disable the services.

### Required configuration

In order to provide the Cookie Consent functionality the OpenEuropa Webtools Cookie Consent module
needs a default configuration in the form of this variable:

* Enable Cookie Consent Kit: Enable the CCK banner.

This configuration can be provided using Drupal 8 configuration system or by
providing details in your sites settings.php file:

```
$config['oe_webtools_cookie_consent.settings']['banner_popup'] = true;
$config['oe_webtools_cookie_consent.settings']['video_popup'] = true;

```

## Upgrade to CCK v2

Cookie Consent Kit v2 requires the smartloader library which is declared in oe_webtools module. This new dependency will be applied by running the post_update_00002 for existing installations.
