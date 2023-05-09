<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\events;

use Craft;
use flipbox\craft\restful\Restful;
use yii\base\Event;
use yii\log\Logger;
use yii\web\Response;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ResponseLoggerHandler
{
    /**
     * @var int
     */
    public static $level = Logger::LEVEL_WARNING;

    /**
     * @var string
     */
    public static $category = 'application';

    /**
     * @var bool
     */
    public static $audit = false;

    /**
     * @param Event $event
     */
    public static function handle(Event $event)
    {
        /** @var Response $response */
        $response = $event->sender;

        $level = $event->data['level'] ?? static::$level;
        $category = $event->data['category'] ?? static::$category;
        $audit = $event->data['audit'] ?? static::$audit;

        Craft::getLogger()->log(
            sprintf(
                "Status Code = %s \n\n" .
                "Body = %s",
                $response->getStatusCode(),
                $response->content
            ),
            $level,
            self::loggerCategory($category, $audit)
        );
    }

    /**
     * The log categories
     *
     * @param string|null $category
     * @param bool $audit flag as an audit message.
     * @return string
     */
    protected static function loggerCategory(string $category = null, bool $audit = false): string
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $prefix = Restful::$category ? (Restful::$category . ($audit ? ':audit' : '')) : '';

        if (empty($category)) {
            return $prefix;
        }

        return ($prefix ? $prefix . ':' : '') . $category;
    }
}
