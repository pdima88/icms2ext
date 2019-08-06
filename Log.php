<?php

namespace pdima88\icms2ext;

use cmsDatabase;
use cmsUser;

class Log {
    const LEVEL_DEBUG = -1;
    const LEVEL_INFO = 0;
    const LEVEL_WARN = 1;
    const LEVEL_ERROR = 2;
    const LEVEL_FATAL = 3;

    static $logs = [];

    static function debug($msg = null, $controller = null, $action = null, $logId = null, $itemId = null) {
        self::log( $msg, $controller, $action, $logId, $itemId, self::LEVEL_DEBUG);
    }

    static function warn($msg = null, $controller = null, $action = null, $logId = null, $itemId = null) {
        self::log( $msg, $controller, $action, $logId, $itemId, self::LEVEL_WARN);
    }

    static function error($msg = null, $controller = null, $action = null, $logId = null, $itemId = null) {
        self::log( $msg, $controller, $action, $logId, $itemId, self::LEVEL_ERROR);
    }

    static function fatal($msg = null, $controller = null, $action = null, $logId = null, $itemId = null) {
        self::log( $msg, $controller, $action, $logId, $itemId, self::LEVEL_FATAL);
    }

    static function log($msg = null, $controller = null, $action = null, $logId = null, $itemId = null, $level = self::LEVEL_INFO) {
        foreach (self::$logs as $log) {
            $log->write($msg, $controller, $action, $logId, $itemId, $level);
        }
    }

    function write($msg = null, $controller = null, $action = null, $logId = null, $itemId = null, $level) {
        cmsDatabase::getInstance()->insert('log', [
            'user_id' => cmsUser::getInstance()->id,
            'controller' => $controller,
            'log_id' => $logId,
            'item_id' => $itemId,
            'action' => $action,
            'log_date' => now(),
            'msg' => $msg,
            'level' => $level
        ]);
    }

    static function helper($controller, $logId, $itemId = null) {
        return new LogHelper($controller, $logId, $itemId);
    }

    static function register() {
        self::$logs[] = new static();
    }
}

class LogHelper {
    public $controller;
    public $logId;
    public $itemId;

    public function __construct($controller = null, $logId = null, $itemId = null)
    {
        $this->controller = $controller;
        $this->logId = $logId;
        $this->itemId = $itemId;
    }

    public function log($msg, $action = null, $itemId = null, $level = self::LEVEL_INFO) {
        Log::log($msg, $this->controller, $action, $this->logId, $itemId ?? $this->itemId, $level);
    }

    public function debug($msg, $action = null, $itemId = null) {
        Log::log($msg, $this->controller, $action, $this->logId, $itemId ?? $this->itemId, Log::LEVEL_DEBUG);
    }

    public function warn($msg, $action = null, $itemId = null) {
        Log::log($msg, $this->controller, $action, $this->logId, $itemId ?? $this->itemId, Log::LEVEL_WARN);
    }

    public function error($msg, $action = null, $itemId = null) {
        Log::log($msg, $this->controller, $action, $this->logId, $itemId ?? $this->itemId, Log::LEVEL_ERROR);
    }

    public function fatal($msg, $action = null, $itemId = null) {
        Log::log($msg, $this->controller, $action, $this->logId, $itemId ?? $this->itemId, Log::LEVEL_FATAL);
    }
}