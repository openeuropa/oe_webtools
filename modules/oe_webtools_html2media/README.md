## 1. Description

This module allows pages to be converted in several formats (png, pdf, ...) 
by providing a wrapper for Webtools HTML 2 Media service. 
The Webtools service creates a static copy of the url, hosts the binary file on its server and returns its path.
see https://webgate.ec.europa.eu/fpfis/wikis/display/webtools/HTML+2+Media

## 1. Usage

Enable the permission 'Use webtools html2media version'

### As site builder.

Use the block "OpenEuropa Webtools PDF version" to get the pdf version of the current page.

### As developer.

#### Use a link

provide a link to the url /oe-webtools-html2media with a least the page url to be converted.
compulsory parameter:
  - url: the url of the page to convert
optional parameters:
  - output_format: pdf by default
  - format: A4 by default
  - orientation: portrait by default
  - load_delay: 200 in miliseconds
  
see for reference https://webgate.ec.europa.eu/fpfis/wikis/display/webtools/HTML+2+Media+-+Technical+details

#### Use a service to retrieve the binary url on webtools server.
use function getMedia(string $page_url, array $options = array()) same $options as for link. 1 extra parameter:
  - $verify_url the url is tested and passed to the webservice only if it returns http code 200. default to TRUE.
