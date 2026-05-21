@api
Feature: Social share feature
  In order to be able to showcase social share blocks
  As an anonymous user
  I want to use social share blocks

  Scenario Outline: The heading of the social share block is translated
    Given the following languages are available:
      | languages    |
      | bg           |
      | cs           |
      | da           |
      | de           |
      | et           |
      | el           |
      | en           |
      | es           |
      | fr           |
      | ga           |
      | hr           |
      | it           |
      | lv           |
      | lt           |
      | hu           |
      | mt           |
      | nl           |
      | pl           |
      | pt-pt        |
      | ro           |
      | sk           |
      | sl           |
      | fi           |
      | sv           |
    When I am on "the home page"
    And I click "<language>"
    Then I should see "<translation>" in the "page" region

    Examples:
      | language    | translation                     |
      | български   | Споделете страницата            |
      | čeština     | Sdílet tuto stránku             |
      | dansk       | Del denne side                  |
      | eesti       | Jaga seda lehte                 |
      | Deutsch     | Seite weiterempfehlen           |
      | ελληνικά    | Διαδώστε αυτή τη σελίδα         |
      | English     | Share this page                 |
      | español     | Compartir esta página           |
      | français    | Partager cette page             |
      | Gaeilge     | An leathanach seo a chomhroinnt |
      | hrvatski    | Podijelite ovu stranicu         |
      | italiano    | Condividi questa pagina         |
      | latviešu    | Kopīgot šo lapu                 |
      | lietuvių    | Bendrinti šį puslapį            |
      | magyar      | Oldal megosztása                |
      | Malti       | Ixxerja din il-paġna            |
      | Nederlands  | Delen                           |
      | polski      | Udostępnij tę stronę            |
      | português   | Partilhar esta página           |
      | română      | Distribuiți această pagină      |
      | slovenčina  | Podeliť sa o túto stránku       |
      | slovenščina | Povej naprej                    |
      | suomi       | Jaa tämä sivu                   |
      | svenska     | Dela sidan                      |