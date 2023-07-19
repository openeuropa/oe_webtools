# OpenEuropa Webtools Captcha Mock

### Enable the module

In order to enable the module in your project run:

```bash
./vendor/bin/drush en oe_webtools_captcha_mock
```
Please note that it will set the mock route as the validation endpoint during
installation!

### Usage

Drupal state variable `captcha_mock_response` is used to determine the
if the validation should return `success` or `error`. See the `MockController::class`.
