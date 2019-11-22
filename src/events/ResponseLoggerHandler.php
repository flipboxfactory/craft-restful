<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\events;

use Craft;
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
     * @param Event $event
     */
    public static function handle(Event $event)
    {
        /** @var Response $response */
        $response = $event->sender;

        Craft::getLogger()->log(
            sprintf(
                "Status Code = %s \n\n" .
                "Body = %s",
                $response->getStatusCode(),
                $response->content
            ),
            $event->data['level'] ?? static::$level,
            $event->data['category'] ?? static::$category
        );
    }
}
