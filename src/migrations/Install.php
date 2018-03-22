<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\migrations;

use craft\db\Migration;
use flipbox\craft\rbac\migrations\Install as InstallRBAC;
use flipbox\craft\restful\Restful;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        (new InstallRBAC(Restful::getInstance()->getAuthManager()))
            ->safeUp();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        (new InstallRBAC(Restful::getInstance()->getAuthManager()))
            ->safeDown();

        return true;
    }
}
