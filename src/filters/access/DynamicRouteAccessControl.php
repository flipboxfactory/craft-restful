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
class DynamicRouteAccessControl extends AbstractDynamicAccessControl
{
    /**
     * @var bool
     */
    public $enforceMethod = false;

    /**
     * @param Controller $controller
     * @return string
     */
    protected function getPermissionName(Controller $controller): string
    {
        $method = null;

        if ($this->enforceMethod === true) {
            $method = Craft::$app->getRequest()->getMethod();
        }
        return RBACHelper::assembleItemNameFromController($controller, $method);
    }
}
