<?php
/**
 * JASS Logistics - Custom Logger
 * Handles logging errors to a file instead of exposing them to the user.
 */

class Logger {
    private static $logFile = __DIR__ . '/../logs/errors.log';

    public static function log($message, $level = 'ERROR') {
        $date = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        
        // Ensure logs directory exists
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $formattedMessage = "[$date] [$level] [IP: $ip] $message" . PHP_EOL;
        file_put_contents(self::$logFile, $formattedMessage, FILE_APPEND);
    }
}
?>
