<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\data;

use flipbox\craft\restful\Restful;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait PaginationTrait
{
    /**
     * @return array
     */
    protected function paginationConfig(): array
    {
        $settings = Restful::getInstance()->getSettings();
        return [
            'pageSizeParam' => $settings->pageSizeParam,
            'pageParam' => $settings->pageParam,
            'pageSizeLimit' => $settings->pageSizeLimit
        ];
    }
}
