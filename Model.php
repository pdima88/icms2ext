<?php

namespace pdima88\icms2ext;

class Model extends \cmsModel {

    /** @var cmsDatabase $db */

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

    function getZendDb() {
        return $this->db->getZendDb();
    }

    function zendDbSelect() {
        return $this->db->getZendDb()->select();
    }

}