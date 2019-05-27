<?php

namespace pdima88\icms2ext;

class ToolbarHelper {
    protected $toolbar = [];
    function __construct(array $toolbarItems = [])
    {
        $this->toolbar = $toolbarItems;
    }

    protected $returnUrlButtons = [];
    protected $export = [];

    function addToolButtons() {

        foreach ($this->toolbar as $toolButtonId => $toolButton) {
            if (!isset($toolButton['href'])) $toolButton['href']  = '#';
            if (isset($toolButton['export'])) {
                if (!isset($toolButton['class'])) $toolButton['class'] = $toolButtonId;

                $this->export[] = $toolButton;
            }
            $toolButtonClass = $toolButton['class'] ?? $toolButtonId;
            $toolButton['class'] = $toolButtonClass;
            $href = $toolButton['href'];
            if (strpos($href, '{returnUrl}') !== false) {
                $toolButton['data-url'] = $href;
                $toolButton['href'] = str_replace('{returnUrl}', urlencode(\cmsCore::getInstance()->uri_absolute), $href);
                $this->returnUrlButtons[] = $toolButton;
            }
            if ($toolButton['hide'] ?? false) {
                $toolButton['class'] .= ' tree_item_hide';
            }
            \cmsTemplate::getInstance()->addToolButton($toolButton);

        }
    }

    function toolbarButtonsInitScript($grid) {
        $script = '';
        foreach ($this->export as $exportButton) {
            $script .= '$(".cp_toolbar .' . $exportButton['class'] . ' a")
                            .addClass("pdgrid-' . $grid->id . '-export")
                            .attr("data-url", $.pdgrid.appendUrlParams($("#pdgrid_' . $grid->id . '").attr("data-url"),
                                                    {export:"' . $exportButton['export'] . '"})).attr("data-export", "' . $exportButton['export'] . '")
                            .attr("href", $.pdgrid.appendUrlParams($("#pdgrid_' . $grid->id . '").attr("data-current-url"),
                                                    {export:"' . $exportButton['export'] . '"}));';
        }

        foreach ($this->returnUrlButtons as $btn) {
            $script .= '$(".cp_toolbar .' . $btn['class'] . ' a")
                            .addClass("pdgrid-' . $grid->id . '-returnUrl")
                            .attr("data-url", "'.$btn['data-url'].'");';
        }

        return $script;
    }

    function idReplaceScript() {
        $script_key0 = '';
        $script = '';
        $script_back = '';
        foreach ($this->toolbar as $toolButtonId => $toolButton) {
            if (!isset($toolButton['href'])) $toolButton['href']  = '#';
            $toolButtonClass = $toolButton['class'] ?? $toolButtonId;
            $href = $toolButton['href'];
            $backHref = '';
            if (strpos($href, '{returnUrl}') !== false) {
                $backHref = '"' . $href . '".replace(/\{returnUrl\}/, encodeURIComponent(href_BackURL))';
            }

            $s = '$(".cp_toolbar .'. $toolButtonClass .' a").attr("href", '.($backHref ?: '"'.$href.'"');
            if (strpos($href, '{id}') !== false) {
                $script .= $s.'.replace(/\\{id\\}/, key))';
                $script_key0 .= $s.'.replace(/\\/\\{id\\}/, ""))';
                if ($backHref) {
                    $s = '.attr("data-url", "'.$href.'"';
                    $script .= $s.'.replace(/\\{id\\}/, key))';
                    $script_key0 .= $s.'.replace(/\\/\\{id\\}/, ""))';
                }
                $script .= ';'.PHP_EOL;
                $script_key0 .= ';'.PHP_EOL;

            }
        }
        return 'var href_BackURL = window.location.pathname+window.location.search;'.PHP_EOL.
        'if (key == 0) { $(".cp_toolbar .tree_item_hide").hide();  '.PHP_EOL.$script_key0.' } else { '.PHP_EOL.
        $script.' $(".cp_toolbar .tree_item_hide").show(); } ';
    }

    /*function hrefReplaceScript() {
        $script_key0 = '';
        $script = '';
        $script_back = '';
        foreach ($this->toolbar as $toolButtonId => $toolButton) {
            $toolButtonClass = $toolButton['class'] ?? $toolButtonId;
            $href = $toolButton['href'];
            $backHref = '';
            if (strpos($href, '{returnUrl}') !== false) {
                $backHref = '"' . $href . '".replace(/\{back\}/, encodeURIComponent(href_BackURL))';
            }

            $s = '$(".cp_toolbar .'. $toolButtonClass .' a").attr("href", '.($backHref ?: '"'.$href.'"');
            if (strpos($href, '{id}') !== false) {
                $script .= $s.'.replace(/\\{id\\}/, key));'.PHP_EOL;
                $script_key0 .= $s.'.replace(/\\/\\{id\\}/, ""));'.PHP_EOL;
            } else {
                if ($backHref) {
                    $script_back .= $s . ');'.PHP_EOL;
                }
            }
        }
        return 'var href_BackURL = window.location.pathname+window.location.search;'.PHP_EOL.
            $script_back.'if (key == 0) { '.PHP_EOL.$script_key0.' } else { '.PHP_EOL.$script.' } ';
    }*/
}