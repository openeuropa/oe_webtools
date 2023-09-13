# OpenEuropa Webtools Captcha

A Webtools Captcha provides integration for Webtools captcha services.

### How it works

This module creates a new `Webtools captcha` captcha validation which can be
configured via captcha module's configuration to certain forms.

### How to use

#### Validation endpoint configuration

In order to validate the captcha challenge via webtools captcha a default
validation endpoint is set in `config/install`, however this can be overridden
from settings.php as follows:

```
$config['oe_webtools_captcha.settings']['validationEndpoint'] = 'http://example.endpoint';

```
If you wish to use the default, you don't need to do anything, it works out of
the box.
