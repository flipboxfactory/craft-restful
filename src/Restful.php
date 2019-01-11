<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful;

use Craft;
use craft\base\Plugin;
use flipbox\craft\rbac\DbManager;
use flipbox\craft\restful\models\Settings as SettingsModel;
use flipbox\craft\ember\modules\LoggerTrait;
use flipbox\flux\events\RegisterScopesEvent;
use flipbox\flux\Flux;
use yii\base\Event;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @method SettingsModel getSettings()
 *
 * @property DbManager $authManager
 */
class Restful extends Plugin
{
    use LoggerTrait;

    /**
     * The transformer scope
     */
    const FLUX_SCOPE = 'rest';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Components
        $this->setComponents([
            'authManager' => [
                "class" => DbManager::class,
                "itemTable" => "{{%restful_rbac_item}}",
                "itemChildTable" => "{{%restful_rbac_item_child}}",
                "assignmentTable" => "{{%restful_rbac_assignment}}",
                "ruleTable" => "{{%restful_rbac_rule}}"
            ]
        ]);

        Event::on(
            Flux::class,
            Flux::EVENT_REGISTER_SCOPES,
            function (RegisterScopesEvent $event) {
                $event->scopes[] = static::FLUX_SCOPE;
            }
        );
    }

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

    /**
     * Ensure our dependencies are installed
     *
     * @return bool
     * @throws \Throwable
     * @throws \craft\errors\InvalidPluginException
     */
    public function beforeInstall(): bool
    {
        if (!Craft::$app->getPlugins()->getPlugin('flux')) {
            Craft::$app->getPlugins()->installPlugin('flux');
        }

        return parent::beforeInstall();
    }

    /*******************************************
     * SERVICES
     *******************************************/

    /**
     * @noinspection PhpDocMissingThrowsInspection
     * @return DbManager
     */
    public function getAuthManager(): DbManager
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->get('authManager');
    }
}
