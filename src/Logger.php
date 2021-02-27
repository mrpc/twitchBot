<?php
#mrpc/Logger.php

namespace mrpc;

/**
 * Logger class
 */
class Logger {

    public static $logPath = 'var';

    /**
     * Log something
     * @param string $log
     * @param string $file
     * @param string $ext
     */
    public static function log($log, $file = 'history', $ext = "log")
    {
        $dir = dirname(__DIR__);
        if (!file_exists($dir . '/' . self::$logPath)) {
            @mkdir($dir . '/' . self::$logPath);
            @chmod($dir . '/' . self::$logPath, 0777);
        }
        if (!file_exists($dir . '/' . self::$logPath . '/' . 'logs')) {
            @mkdir($dir . '/' . self::$logPath . '/' . 'logs');
            @chmod($dir . '/' . self::$logPath . '/' . 'logs', 0777);
        }
        $file = $file . '.' . $ext;
        $handle = @fopen(
            $dir . '/' . self::$logPath . '/' . 'logs' . '/' . $file, "a+"
        );
        @fwrite(
            $handle, "["
            . date('d/m/Y H:i', time() + 25200)
            . "] " . $log . "\r\n"
        );
        @fclose($handle);
    }


}