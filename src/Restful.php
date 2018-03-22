<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful;

use Craft;
use craft\base\Plugin;
use craft\web\twig\variables\CraftVariable;
use flipbox\craft\rbac\DbManager;
use flipbox\craft\restful\models\Settings as SettingsModel;
use flipbox\craft\restful\web\twig\variables\Restful as RestfulVariable;
use yii\base\Event;
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
     * The transformer scope
     */
    const TRANSFORMER_SCOPE = 'rest';

    /**
     * @inheritdoc
     */
    public function createSettingsModel()
    {
        return new SettingsModel();
    }

    /**
     * Ensure our dependencies are installed
     *
     * @return bool
     * @throws \Throwable
     * @throws \craft\errors\InvalidPluginException
     */
    public function beforeInstall(): bool
    {
        if (!Craft::$app->getPlugins()->getPlugin('flux')) {
            Craft::$app->getPlugins()->installPlugin('flux');
        }

        return parent::beforeInstall();
    }


    /*******************************************
     * SERVICES
     *******************************************/

    /**
     * @return DbManager
     */
    public function getAuthManager(): DbManager
    {
        return $this->get('authManager');
    }


    /*******************************************
     * LOGGING
     *******************************************/

    /**
     * Logs a trace message.
     * Trace messages are logged mainly for development purpose to see
     * the execution work flow of some code.
     * @param string $message the message to be logged.
     * @param string $category the category of the message.
     */
    public static function trace($message, string $category = null)
    {
        Craft::getLogger()->log($message, Logger::LEVEL_TRACE, self::normalizeCategory($category));
    }

    /**
     * Logs an error message.
     * An error message is typically logged when an unrecoverable error occurs
     * during the execution of an application.
     * @param string $message the message to be logged.
     * @param string $category the category of the message.
     */
    public static function error($message, string $category = null)
    {
        Craft::getLogger()->log($message, Logger::LEVEL_ERROR, self::normalizeCategory($category));
    }

    /**
     * Logs a warning message.
     * A warning message is typically logged when an error occurs while the execution
     * can still continue.
     * @param string $message the message to be logged.
     * @param string $category the category of the message.
     */
    public static function warning($message, string $category = null)
    {
        Craft::getLogger()->log($message, Logger::LEVEL_WARNING, self::normalizeCategory($category));
    }

    /**
     * Logs an informative message.
     * An informative message is typically logged by an application to keep record of
     * something important (e.g. an administrator logs in).
     * @param string $message the message to be logged.
     * @param string $category the category of the message.
     */
    public static function info($message, string $category = null)
    {
        Craft::getLogger()->log($message, Logger::LEVEL_INFO, self::normalizeCategory($category));
    }

    /**
     * @param string|null $category
     * @return string
     */
    private static function normalizeCategory(string $category = null)
    {
        $normalizedCategory = 'Restful';

        if ($category === null) {
            return $normalizedCategory;
        }

        return $normalizedCategory . ': ' . $category;
    }
}
