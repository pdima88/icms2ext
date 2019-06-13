<?php
namespace pdima88\icms2ext {

    use cmsDatabase;
    use cmsCache;
    use Exception;

    class Translate {
        const TABLE_NAME = 't';
    
        static $t_Id = '';
        static $t_Params = [];
    
        static $cache = false;
    
        static function init() {
    
        }
    
        static function load() {
            $cacheKey = self::TABLE_NAME.'.cache';
            if (false === ($cache = cmsCache::getInstance()->get($cacheKey))) {
                $cache = cmsDatabase::getInstance()->getPairs(self::TABLE_NAME);
                cmsCache::getInstance()->set($cacheKey, $cache, 86400);
            }
            self::$cache = $cache;
        }
    
        static function add($id, $value) {
            $data = ['id' => $id, 'value' => $value];
            try {
                cmsDatabase::getInstance()->insert(self::TABLE_NAME, $data, false, false, true);
            } catch (Exception $e) {
            }
            self::$cache[$id] = $data;
            $cacheKey = self::TABLE_NAME.'.cache';
            cmsCache::getInstance()->set($cacheKey, self::$cache, 86400);
            return $data;
        }
    
        static function reset() {
            cmsCache::getInstance()->clean(self::TABLE_NAME);
            self::$cache = false;
        }
    
        /**
         * Получить перевод по указанному ключу
         * @param $id Ключ
         * @param bool $default Значение по умолчанию, будет использоваться, если перевод не найден, по умолчанию false
         * @param array $args Массив подставляемых значений
         * @param null $lang Язык, если не указан (null), используется текущий язык из S4Y::$lang или если не указан и он -
         * язык по умолчанию (русский)
         * @param bool $addIfNotExists Записать строку перевода в таблицу, если ее не существует, по умолчанию - не записывать
         * @return bool|string Перевод или значение по умолчанию - если перевод не найден
         */
        static function t($id, $default = false, $args = [], $lang = null, $addIfNotExists = false) {
            if (self::$cache === false) self::load();
            if (!isset(self::$cache[$id]) && $addIfNotExists) {
                self::add($id, $default);
            }
    
            if (!isset(self::$cache[$id])) {
                $t = $default;
            } else {
                $t = self::$cache[$id];
                if (!isset($t[$lang]) || $t[$lang] == '') {
                    if (!isset($t['value']) || $t['value'] == '') {
                        $t = $default;
                    } else {
                        $t = $t['value'];
                    }
                } else {
                    $t = $t[$lang];
                }
            }
    
            if ($t === false || empty($args)) {
                return $t;
            } else {
                return vsprintf($t, $args);
            }
        }
    
    }
}

namespace {

    use pdima88\icms2ext\Translate;

    function t($id, $default = null, ...$params) {
        return Translate::t($id, $default, $params, cmsCore::getLanguageName(), true);
    }
    
    function t_($id, ...$params) {
        Translate::$t_Id = $id;
        Translate::$t_Params = $params;
        ob_start();
        return true;
    }
    
    function _t() {
        $default = ob_get_clean();
        $id = Translate::$t_Id;
        $params = Translate::$t_Params;
        Translate::$t_Id = '';
        Translate::$t_Params = [];
        return Translate::t($id, $default, $params, cmsCore::getLanguageName(), true);
    }

}

/* Visual studio code snippets:

	"Translate helper": {
		"scope": "html,javascript",
		"prefix": "t(",
		"body": [
			"<?= t('${1:id}', '${2:$TM_SELECTED_TEXT}') ?>"
		]
	},

	"Multiline Translate helper": {
		"scope": "html,javascript",
		"prefix": "t_",
		"body": [
			"<?php t_('${1:id}') ?>",
			"${2:$TM_SELECTED_TEXT}",
			"<?= _t() ?>"
		]
	}

*/