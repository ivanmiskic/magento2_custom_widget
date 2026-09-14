<?php
/**
 * Copyright © 2026
 * @category Null
 * @package Null_Blueprint
 * @author Ivan Miskic
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'Null\\Blueprint\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $path = dirname(__DIR__, 2) . '/' . $relative . '.php';
    if (is_file($path)) {
        require $path;
    }
});
