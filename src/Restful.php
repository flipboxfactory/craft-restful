<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful;

use craft\base\Plugin;
use flipbox\craft\restful\models\Settings as SettingsModel;
use flipbox\craft\ember\modules\LoggerTrait;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method SettingsModel getSettings()
 */
class Restful extends Plugin
{
    use LoggerTrait;

    /**
     * @inheritdoc
     */
    protected static function getLogFileName(): string
    {
        return 'restful';
    }

    /**
     * @inheritdoc
     */
    public function createSettingsModel()
    {
        return new SettingsModel();
    }
}
