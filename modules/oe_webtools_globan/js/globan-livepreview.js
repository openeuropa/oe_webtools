(function ($, Drupal, window) {
  Drupal.behaviors.globanLiveDemoBehavior = {
    attach: function (context, settings) {
      $wt = window.$wt;
      $wt.globan = {
        run: function(demo, config) {
          if (demo) {
            config.globan = config.wtgloban;
          }
          var process = function(lng) {
            $('#globan').remove();
            if (lng === "ar" || lng === "ur" || lng === "he") {
              var xhtml = ['<div id="globan" class="' + style + '">', '<div id="globan-container">', '<button id="globan-button" class="globan-dropdown-selector" aria-controls="globan-dropdown" aria-haspopup="true">' + $wt.label("globan", "button", lng) + '</button>', '<p class="globan-certificate">' + flag + '<span class="globan-approved">' + $wt.label("globan", "flag", lng) + '</span><span class="globan-approved-mobile">' + $wt.label("globan", "flagMobile", lng) + '</span></p>', '<div id="globan-dropdown" class="globan-dropdown-selector" aria-expanded="false">', '<p class="globan-dropdown-selector"><span class="globan-dropdown-selector">' + $wt.label("globan", "dropdown", lng) + '</p>' + link, '</div>', '</div>', '</div>'];
            } else {
              var xhtml = ['<div id="globan" class="' + style + '">', '<div id="globan-container">', '<p class="globan-certificate">' + flag + '<span class="globan-approved">' + $wt.label("globan", "flag", lng) + '</span><span class="globan-approved-mobile">' + $wt.label("globan", "flagMobile", lng) + '</span></p>', '<button id="globan-button" class="globan-dropdown-selector" aria-controls="globan-dropdown" aria-haspopup="true">' + $wt.label("globan", "button", lng) + '</button>', '<div id="globan-dropdown" class="globan-dropdown-selector" aria-expanded="false">', '<p class="globan-dropdown-selector"><span class="globan-dropdown-selector">' + $wt.label("globan", "dropdown", lng) + '</p>' + link, '</div>', '</div>', '</div>'];
            }
            var div = document.createElement('div');
            div.innerHTML = xhtml.join("");
            $('#globan-preview').append(div.innerHTML);
          };
          var events = function() {
            var button = document.getElementById("globan-button");
            var dropdown;
            var dropShow = function(e) {
              if (window.kevent || (e.type === "keydown" && (e.keyCode !== 13 && e.keyCode !== 32))) {
                return;
              }
              button.focus();
              dropdown = document.getElementById('globan-dropdown');
              if (dropdown.getAttribute("aria-expanded") === "true") {
                closeDropdown(true);
              } else {
                closeDropdown(false);
              }
              window.kevent = true;
              setTimeout(function() {
                window.kevent = false;
              }, 150);
              event.preventDefault();

              document.addEventListener("click", canClose);
              document.addEventListener("keydown", canClose);
            };
            var closeDropdown = function(swap) {
              var header = dropdown.parentElement.parentElement || dropdown.parentNode.parentNode;
              if (swap) {
                dropdown.setAttribute("aria-expanded", false);
                dropdown.setAttribute("aria-hidden", true);
                header.className = header.className.replace(/\b globan-show\b/g, "");
              } else {
                dropdown.setAttribute("aria-expanded", true);
                dropdown.setAttribute("aria-hidden", false);
                header.className += " globan-show";
              }
            };
            var canClose = function(event) {
              var keep = (event.target.className !== "globan-dropdown-selector" && event.target.className !== "arabic");
              if (keep || event.keyCode === 27 || event.keyCode === 9) {
                closeDropdown(true);
                removeListener();
              } else {
                button.focus();
              }
              event.preventDefault();
            };
            var removeListener = function() {
              document.removeEventListener("click", canClose);
              document.removeEventListener("keydown", canClose);
            };
            button.addEventListener("click", dropShow);
            button.addEventListener("keydown", dropShow);
          };
          var getLngLink = function(lng) {
            var euLng = ["bg", "cs", "da", "de", "el", "en", "es", "et", "fi", "fr", "ga", "hr", "hu", "it", "lt", "lv", "mv", "nl", "pl", "pt", "ro", "sk", "sl", "sv"];
            if (euLng.indexOf(lng) > -1) {
              return lng;
            }
            return "en";
          };
          if (["000", "001", "010", "011", "100", "101", "110", "111"].indexOf(config.globan) > -1) {
            var cfg = config.globan.split('');
            var lng = config.lang || $wt.getUrlParams(location.href)["lang"] || document.lang;
            var ara = (lng === "ar" || lng === "ur" || lng === "he") ? "ar" : "";
            var css = $wt.root + "/services/globan/custom" + ara + ".css";
            var flag = (cfg[0] === "0") ? '' : '<img class="globan-flag" src="' + $wt.root + '/services/globan/icons/EU-logo-negative.svg" />';
            var style = (cfg[1] === "0") ? "light" : "dark";
            var link;
            $wt.include(css, function() {
              link = (cfg[2] === "0") ? '' : '<a href="//europa.eu/european-union/contact/institutions-bodies_' + getLngLink(lng) + '" class="globan-dropdown-selector">' + $wt.label("globan", "link", lng) + '</a>';
              process(lng);
              events();
            });
          }
        }
      };

      $wt.jsonp($wt.root + "/services/globan/labels.json", function(json, error) {
        $wt.addTranslation(json);
        var flag = $('#edit-display-eu-flag').val();
        var theme = $('#edit-background-theme').val();
        var links = $('#edit-eu-institutions-links').val();
        var lang = $('#edit-override-page-lang').val();

        var globan_options = flag + theme + links;
        $wt.globan.run(false, {'globan': globan_options, 'lang': lang})
      });

      $('#edit-display-eu-flag, #edit-background-theme, #edit-eu-institutions-links, #edit-override-page-lang', context).once('globanUpdateOptions').each(function () {
        $(this).change(function() {
          var flag = $('#edit-display-eu-flag').val();
          var theme = $('#edit-background-theme').val();
          var links = $('#edit-eu-institutions-links').val();
          var lang = $('#edit-override-page-lang').val();

          var globan_options = flag + theme + links;

          $wt.globan.run(false, {'globan': globan_options, 'lang': lang})
        });

      });
    }
  };
})(jQuery, Drupal, window);
