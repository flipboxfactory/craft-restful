<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\rbac\rules;

use Craft;
use yii\rbac\Rule;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Admin extends Rule
{
    public $name = 'isAdmin';

    /**
     * @param int|string $user
     * @param \yii\rbac\Item $item
     * @param array $params
     * @return mixed
     */
    public function execute($user, $item, $params)
    {
        if (!$user || !$element = Craft::$app->getUsers()->getUserById($user)) {
            return false;
        }
        return $element->admin;
    }
}
