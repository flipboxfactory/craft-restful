<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\data;

use flipbox\craft\restful\Restful;
use yii\data\Pagination;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait PaginationTrait
{
    /**
     * @param array $config
     * @return array
     */
    protected function paginationConfig(array $config = []): array
    {
        $settings = Restful::getInstance()->getSettings();
        return array_merge(
            [
                'class' => Pagination::class,
                'pageSizeParam' => $settings->pageSizeParam,
                'pageParam' => $settings->pageParam,
                'pageSizeLimit' => $settings->pageSizeLimit,
                'defaultPageSize' => $settings->defaultPageSize,
            ],
            $config
        );
    }
}
