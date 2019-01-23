<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\filters\transform;

use Craft;
use craft\web\User;
use yii\di\Instance;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class AccessTransformFilter extends TransformFilter
{
    /**
     * The user object representing the authentication status or the ID of the
     * user application component.  This can also be a configuration array for creating the object or you can set it
     * to `false` to explicitly switch this component support off for the filter.
     *
     * @var User|array|string|false
     */
    public $user = 'user';

    /**
     * The default configuration of access rules. Individual rule configurations
     * specified via [[rules]] will take precedence when the same property of the rule is configured.
     *
     * @var array
     */
    public $ruleConfig = [
        'class' => TransformRule::class
    ];

    /**
     * A list of transform rule objects or configuration arrays for creating the rule objects.
     * If a rule is specified via a configuration array, it will be merged with [[ruleConfig]] first
     * before it is used for creating the rule object.
     *
     * @var array
     * @see ruleConfig
     *
     * @var TransformRule[]
     */
    public $rules = [];

    /**
     * Initializes the [[rules]] array by instantiating rule objects from configurations.
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if ($this->user !== false) {
            $this->user = Instance::ensure($this->user, User::class);
        }
        foreach ($this->rules as $i => $rule) {
            if (null !== ($rule = $this->resolveRule($rule))) {
                $this->rules[$i] = $rule;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {
        $user = $this->user;

        foreach ($this->rules as $rule) {
            if ($rule->matches($action, $user)) {
                return $rule->transform($result);
            }
        }

        return parent::afterAction($action, $result);
    }

    /**
     * @param $rule
     * @return TransformFilter
     * @throws \yii\base\InvalidConfigException
     */
    protected function resolveRule($rule)
    {
        if ($rule instanceof TransformRule) {
            return $rule;
        }

        if (is_string($rule)) {
            $rule = ['class' => $rule];
        }

        if (!is_array($rule)) {
            $rule = [$rule];
        }

        /** @var TransformRule $rule */
        $rule = Craft::createObject(
            array_merge(
                $this->ruleConfig,
                [
                    'transformer' => $this->transformer
                ],
                $rule
            )
        );

        return $rule instanceof TransformRule ? $rule : null;
    }
}
