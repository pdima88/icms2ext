<?php

namespace pdima88\icms2ext;

class FormHelper {
    static function getBackURL($default = 'javascript:history.back(-1);') {
        if (isset($_GET['back'])) return urldecode($_GET['back']);
        return $default;
    }
}