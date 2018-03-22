<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\rbac\rules;

use craft\helpers\ArrayHelper;
use yii\rbac\Rule;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Owner extends Rule
{
    public $name = 'isOwner';

    /**
     * @param int|string $user
     * @param \yii\rbac\Item $item
     * @param array $params
     * @return bool
     */
    public function execute($user, $item, $params)
    {
        // User
        if (ArrayHelper::keyExists('user', $params)) {
            if (!$userId = ArrayHelper::getValue($params, 'user', null)) {
                return false;
            }

            return $userId == $user;
        }

        // Entry Author
        if (ArrayHelper::keyExists('entry', $params)) {
            if (!$entryId = ArrayHelper::getValue($params, 'entry', null)) {
                return false;
            }

            if (!$entry = \Craft::$app->getEntries()->getEntryById($entryId)) {
                return false;
            }

            return $entry->authorId == $user;
        }

        return false;
    }
}
