<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use flipbox\craft\restful\models\Settings as SettingsModel;
use yii\log\Logger;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method SettingsModel getSettings()
 */
class Restful extends Plugin
{
    /**
     * @var string
     */
    public static string $category = 'restful';

    /**
     * @inheritdoc
     */
    protected static function getLogFileName(): string
    {
        return self::$category;
    }

    /**
     * @inheritdoc
     */
    public function createSettingsModel(): ?Model
    {
        return new SettingsModel();
    }

    /**
     * The log categories
     *
     * @param string|null $category
     * @param bool $audit flag as an audit message.
     * @return string
     */
    protected static function loggerCategory(string|null $category = null, bool $audit = false): string
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $prefix = static::$category ? (static::$category . ($audit ? ':audit' : '')) : '';

        if (empty($category)) {
            return $prefix;
        }

        return ($prefix ? $prefix . ':' : '') . $category;
    }

    /**
     * Logs a debug message.
     * Trace messages are logged mainly for development purpose to see
     * the execution work flow of some code. This method will only log
     * a message when the application is in debug mode.
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array.
     * @param string $category the category of the message.
     * @param bool $audit flag as an audit message.
     * @since 2.0.0
     */
    public static function debug($message, $category = 'general', bool $audit = false)
    {
        Craft::getLogger()->log($message, Logger::LEVEL_TRACE, static::loggerCategory($category, $audit));
    }

    /**
     * Logs an error message.
     * An error message is typically logged when an unrecoverable error occurs
     * during the execution of an application.
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array.
     * @param string $category the category of the message.
     * @param bool $audit flag as an audit message.
     * @since 2.0.0
     */
    public static function error($message, $category = 'general', bool $audit = false)
    {
        Craft::getLogger()->log($message, Logger::LEVEL_ERROR, static::loggerCategory($category, $audit));
    }

    /**
     * Logs a warning message.
     * A warning message is typically logged when an error occurs while the execution
     * can still continue.
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array.
     * @param string $category the category of the message.
     * @param bool $audit flag as an audit message.
     * @since 2.0.0
     */
    public static function warning($message, $category = 'general', bool $audit = false)
    {
        Craft::getLogger()->log($message, Logger::LEVEL_WARNING, static::loggerCategory($category, $audit));
    }

    /**
     * Logs an informative message.
     * An informative message is typically logged by an application to keep record of
     * something important (e.g. an administrator logs in).
     * @param string|array $message the message to be logged. This can be a simple string or a more
     * complex data structure, such as array.
     * @param string $category the category of the message.
     * @param bool $audit flag as an audit message.
     * @since 2.0.0
     */
    public static function info($message, $category = 'general', bool $audit = false)
    {
        Craft::getLogger()->log($message, Logger::LEVEL_INFO, static::loggerCategory($category, $audit));
    }
}
