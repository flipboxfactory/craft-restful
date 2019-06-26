<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\filters;

use flipbox\craft\restful\Restful;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Cors extends \yii\filters\Cors
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->cors = array_merge(
            $this->cors,
            Restful::getInstance()->getSettings()->getCORS()
        );

        Restful::info(['CORS Filter: ' => $this->cors], 'cors');
    }
}
