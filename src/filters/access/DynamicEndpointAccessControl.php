<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\filters\access;

use Craft;
use flipbox\craft\restful\helpers\RBACHelper;
use yii\base\Controller;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class DynamicEndpointAccessControl extends AbstractDynamicAccessControl
{
    /**
     * @param Controller $controller
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function getPermissionName(Controller $controller): string
    {
        $request = Craft::$app->getRequest();

        return RBACHelper::assembleItemNameFromUri(
            $request->getUrl(),
            $request->getMethod()
        );
    }
}
