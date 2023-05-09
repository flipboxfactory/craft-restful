<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\filters;

use craft\helpers\ArrayHelper;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 2.2.0
 */
trait AuditTrait
{
    /**
     * @var array this property defines the a mapping for each action.
     * For each action that should only support limited set of values
     * you add a value with the action id as array key and an array value of
     * allowed status codes (e.g. 'create' => true, 'delete' => false).
     * If an action is not defined the default action property will be used.
     *
     * You can use `'*'` to stand for all actions. When an action is explicitly
     * specified, it takes precedence over the specification given by `'*'`.
     *
     * For example,
     *
     * ```php
     * [
     *   'create' => true,
     *   'update' => false,
     *   '*' => true,
     * ]
     * ```
     */
    public $auditActions = [];

    /**
     * The default audit value
     *
     * @var bool
     */
    public $audit = false;

    /**
     * @param string $action
     * @return bool
     */
    protected function findAuditAction(string $action)
    {
        // Default
        $value = ArrayHelper::getValue($this->auditActions, '*', $this->audit);

        // Look by specific action
        if (isset($this->auditActions[$action])) {
            $value = $this->auditActions[$action];
        }

        return (boolean) $value;
    }
}
