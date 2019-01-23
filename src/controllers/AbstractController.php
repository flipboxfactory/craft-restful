<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\controllers;

use craft\helpers\ArrayHelper;
use flipbox\craft\rest\Controller;
use flipbox\craft\restful\Restful;
use yii\filters\AccessControl;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
abstract class AbstractController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                'authenticator' => [
                    'authMethods' => Restful::getInstance()->getSettings()->getAuthMethods()
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'except' => [
                        'options',
                        'head'
                    ]
                ]
            ]
        );
    }
}
