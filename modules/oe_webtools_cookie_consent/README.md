# OpenEuropa Webtools Cookie consent

A Webtools Cookie Consent Kit service that provides information on page access
to a 3rd party CCK service.

### How it works

This module add the CCK js file on the <HEAD> section of the page and override the
src of oEmbed video iframe to include cokie consent.
[here](https://webgate.ec.europa.eu/fpfis/wikis/display/webtools/Cookie+Consent+Kit).

### How to use

#### Required configuration

In order to be able to track visitors to your site the Webtool Cookie Consent module
needs a default configuration in the form of this variable:

* Enable Cookie Consent Kit: Enable the CCK banner.

This configuration can be provided using Drupal 8 configuration system or by
providing details in your sites settings.php file:

```
$config['oe_webtools_cookie_consent.settings']['cckEnabled'] = true;

```

#### Enable the module

Once enabled, the module will provide:
-  an oEmbed video iframe with cookie consent
-  a banner to your pages requesting the user to accept or refuse cookies on your site
