<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\helpers;

use Craft;
use craft\helpers\ArrayHelper;
use yii\web\CompositeUrlRule;
use yii\web\Controller;
use yii\web\UrlRule;
use yii\web\UrlRuleInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class UrlRuleHelper
{
    /**
     * @param UrlRule $urlRule
     * @return null|Controller
     * @throws \yii\base\InvalidConfigException
     */
    public static function createController(UrlRule $urlRule)
    {
        if (!is_array($parts = Craft::$app->createController($urlRule->route))) {
            return null;
        }

        /** @var $controller Controller */
        list($controller, $action) = $parts;
        $controller->action = $controller->createAction($action);

        return $controller;
    }

    /**
     * @param array $urlRules
     * @return array
     */
    public static function pathMap(array $urlRules): array
    {
        $map = [];

        foreach (static::traverseAll($urlRules) as $urlRule) {
            $map = ArrayHelper::merge(
                $map,
                self::ruleMap(
                    trim(self::getTemplate($urlRule), '/'),
                    $urlRule
                )
            );
        }

        return $map;
    }

    /**
     * @param array $urlRules
     * @return array
     */
    public static function routeMap(array $urlRules): array
    {
        $map = [];

        foreach (static::traverseAll($urlRules) as $urlRule) {
            $map = ArrayHelper::merge(
                $map,
                self::ruleMap(
                    $urlRule->route,
                    $urlRule
                )
            );
        }

        return $map;
    }

    /**
     * @param array $urlRules
     * @return array
     */
    public static function traverseAll(array $urlRules)
    {
        $rules = [];

        foreach ($urlRules as $key => $urlRule) {
            $rules = array_merge(
                $rules,
                static::traverse($urlRule)
            );
        }

        return $rules;
    }

    /**
     * @param UrlRuleInterface $urlRule
     * @return array
     */
    public static function traverse(UrlRuleInterface $urlRule): array
    {
        if ($urlRule instanceof CompositeUrlRule) {
            return static::traverseChildren($urlRule);
        }

        if ($urlRule instanceof UrlRule) {
            return [static::createKey($urlRule) => $urlRule];
        }

        return [];
    }

    /**
     * @param CompositeUrlRule $rule
     * @return array
     */
    protected static function traverseChildren(CompositeUrlRule $rule): array
    {
        $rules = [];

        // Reflection method
        $method = new \ReflectionMethod(
            get_class($rule),
            'createRules'
        );

        // Enable access to protected method
        $method->setAccessible(true);

        // Iterate over each group of rules (grouped by controller id)
        foreach ($method->invoke($rule) as $controllerId => $controllerRules) {
            $rules = array_merge(
                $rules,
                static::traverseAll($controllerRules)
            );
        }

        return $rules;
    }

    /**
     * @param UrlRule $rule
     * @return string
     */
    public static function createKey(UrlRule $rule): string
    {
        $str = '';
        if ($rule->verb !== null) {
            $str .= implode(',', $rule->verb) . ' ';
        }
        if ($rule->host !== null && strrpos($rule->name, $rule->host) === false) {
            $str .= $rule->host . '/';
        }
        $str .= trim(self::getTemplate($rule), '/');

        if ($str === '') {
            return '/';
        }
        return $str;
    }

    /**
     * @param UrlRule $rule
     * @return string
     */
    public static function getTemplate(UrlRule $rule)
    {
        $prop = new \ReflectionProperty(
            get_class($rule),
            '_template'
        );
        $prop->setAccessible(true);

        return $prop->getValue($rule);
    }

    /**
     * @param $item
     * @param UrlRule $urlRule
     * @return array
     */
    private static function ruleMap($item, UrlRule $urlRule): array
    {
        $map = [];
        $ref = &$map;
        $route = explode('/', $item);
        foreach ($route as $key) {
            $ref[$key] = [];
            $ref = &$ref[$key];
        }

        $ref = [$urlRule];
        return $map;
    }
}
