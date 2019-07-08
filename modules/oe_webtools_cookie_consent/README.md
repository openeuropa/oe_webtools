# OpenEuropa Webtools Cookie consent

This component integrates the Webtools Cookie Consent Kit (CCK) with a site.

### How it works

This module adds the CCK js file on the <HEAD> section of the page and overrides the
src of any oEmbed video iframe to include a cookie consent form.
[here](https://webgate.ec.europa.eu/fpfis/wikis/display/webtools/Cookie+Consent+Kit).

### How to use

#### Required configuration

In order to provide the cookie consent functionality the OpenEuropa Webtools Cookie Consent module
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
