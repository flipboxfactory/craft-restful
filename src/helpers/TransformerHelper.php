<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\helpers;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\StringHelper;
use flipbox\craft\restful\Restful;
use Flipbox\Transform\Helpers\TransformerHelper as BaseTransformerHelper;
use yii\base\InvalidConfigException;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class TransformerHelper extends BaseTransformerHelper
{
    /**
     * @param $name
     * @return string
     */
    public static function assembleIdentifier($name)
    {
        if (!is_array($name)) {
            $name = ArrayHelper::toArray($name);
        }

        // Clean
        $name = array_filter($name);

        return StringHelper::toLowerCase(
            StringHelper::toString($name, ':')
        );
    }

    /**
     * @param $transformer
     * @return bool
     */
    public static function isTransformerConfig($transformer)
    {
        if (!is_array($transformer)) {
            return false;
        }

        if (null === ($class = ArrayHelper::getValue($transformer, 'class'))) {
            return false;
        }

        return static::isTransformerClass($class);
    }

    /**
     * @param $transformer
     * @return null|callable
     */
    public static function resolve($transformer)
    {
        if (null !== ($callable = parent::resolve($transformer))) {
            return $callable;
        }

        try {
            if (static::isTransformerConfig($transformer)) {
                return static::resolve(
                    Craft::createObject($transformer)
                );
            }
        } catch (InvalidConfigException $e) {
            Restful::warning("Invalid transformer configuration.");
        }

        return null;
    }
}
