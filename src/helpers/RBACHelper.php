<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\helpers;

use craft\db\Migration;
use craft\helpers\StringHelper;
use yii\base\Action;
use yii\rbac\Permission;
use yii\base\Controller;
use yii\web\UrlRule;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class RBACHelper extends Migration
{
    /**
     * The glue when constructing from an array
     */
    const ITEM_GLUE = '.';

    /**
     * The glue when re-constructing from an array
     */
    const ROUTE_GLUE = '/';

    /**
     * @param string $uri
     * @param string $method
     * @return string
     */
    public static function assembleItemNameFromUri(string $uri, string $method): string
    {
        return static::assembleItemNameFromRoute($uri, $method);
    }

    /**
     * @param UrlRule $rule
     * @param string|null $method
     * @return Permission
     * @throws \yii\base\InvalidConfigException
     */
    public static function createPermissionFromUrlRule(UrlRule $rule, string $method = null): Permission
    {
        return new Permission([
            'name' => static::assembleItemNameFromUrlRule($rule, $method),
            'description' => static::assembleItemDescriptionFromUrlRule($rule, $method)
        ]);
    }

    /**
     * @param UrlRule $rule
     * @param string|null $method
     * @return string
     */
    public static function assembleItemNameFromUrlRule(UrlRule $rule, string $method = null): string
    {
        return static::assembleItemNameFromRoute($rule->route, $method);
    }

    /**
     * @param Action $action
     * @param string|null $method
     * @return string
     */
    public static function assembleItemNameFromAction(Action $action, string $method = null): string
    {
        return static::assembleItemNameFromController($action->controller, $method);
    }

    /**
     * @param Controller $controller
     * @param string|null $method
     * @return string
     */
    public static function assembleItemNameFromController(Controller $controller, string $method = null): string
    {
        return static::assembleItemNameFromRoute($controller->getRoute(), $method);
    }

    /**
     * @param string $route
     * @param string|null $method
     * @return string
     */
    public static function assembleItemNameFromRoute(string $route, string $method = null): string
    {
        $routeArray = explode(static::ROUTE_GLUE, trim($route, '/'));

        if ($method !== null) {
            $routeArray[] = strtoupper($method);
        }

        return StringHelper::toString(
            $routeArray,
            static::ITEM_GLUE
        );
    }


    /**
     * @param UrlRule $rule
     * @param string|null $method
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public static function assembleItemDescriptionFromUrlRule(UrlRule $rule, string $method = null): string
    {
        $description = UrlRuleHelper::getTemplate($rule);

        if ($method !== null) {
            return $description . ' ' . strtoupper($method);
        }

        if ($controller = UrlRuleHelper::createController($rule)) {
            return $description . ' ' . strtoupper($controller->action->id);
        }

        return $description;
    }

    /**
     * @param $action
     * @param $resource
     * @return string
     */
    public static function assembleItemNameFromResource($resource, $action = null): string
    {
        if (null === $action) {
            return $resource;
        }
        return $resource . self::ITEM_GLUE . $action;
    }

    /**
     * @param $action
     * @param $resource
     * @return string
     */
    public static function assembleItemDescriptionFromResource($resource, $action = null): string
    {
        if (null === $action) {
            return $resource;
        }

        return $resource . ' - ' . StringHelper::toUpperCase($action);
    }
}
