<?php

namespace pdima88\icms2ext;

use Nette\Utils\Html;

class GridHelper {
    static function getActions($buttons = []) {
        $html = '';
        foreach ($buttons as $btnClass => $btn) {
            if (!isset($btn['class'])) {
                $btn['class'] = $btnClass;
            }
            if (isset($btn['confirmDelete'])) {
                $btn['onclick'] = 'return $.pdgrid.confirmDelete(this)';
                unset($btn['confirmDelete']);
            }
            $html .= Html::el('a', $btn);
        }
        if ($html) {
        return Html::el('div', [
            'class' => 'datagrid'
        ])->setHtml(Html::el('div', [
            'class' => 'actions'
        ])->setHtml($html));
        } else return '';
    }
}