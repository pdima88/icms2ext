<?php

namespace pdima88\icms2ext;

use cmsDatabase;
use ReflectionClass;
use Zend_Db_Adapter_Mysqli;
use Zend_Db_Table_Abstract;

class ZendDb extends Zend_Db_Adapter_Mysqli
{
    function __construct()
    {
        $options = Model::getPrivateProperty(cmsDatabase::getInstance(), 'options');
        $config = [
            'host' => $options['db_host'],
            'username' => $options['db_user'],
            'password' => $options['db_pass'],
            'dbname' => $options['db_base'],
        ];
        parent::__construct($config);
        Zend_Db_Table_Abstract::setDefaultAdapter($this);
    }

    protected function _connect()
    {
        $this->_connection = Model::getPrivateProperty(cmsDatabase::getInstance(), 'mysqli');
    }
}

class Model extends \cmsModel {

    private static $zendDb = null;

    /**
     * @param $name
     * @return Table
     */
    function getTable($name) {
        $className = 'table'.string_ucfirst($this->name).'_'.$name;
        return Table::getInstance($className);
    }

    function getTableNameWithPrefix($table_name) {
        return $this->db->prefix.$table_name;
    }

    static function zendDb() {
        if (!isset(self::$zendDb)) {
            self::$zendDb = new ZendDb();
        }
        return self::$zendDb;
    }

    static function zendDbSelect() {
        return self::zendDb()->select();
    }

    static function getPrivateProperty($obj, $prop)
    {
        $myClassReflection = new ReflectionClass(get_class($obj));
        $secret = $myClassReflection->getProperty($prop);
        $secret->setAccessible(true);
        return $secret->getValue($obj);
    }
}
