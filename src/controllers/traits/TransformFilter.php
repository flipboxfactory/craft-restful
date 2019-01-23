<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\controllers\traits;

use craft\helpers\ArrayHelper;
use flipbox\craft\restful\filters\transform\TransformFilter as RestfulTransformFilter;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @deprecated
 */
trait TransformFilter
{
    /**
     * An array of transformers in the format of 'action' => 'transformer'
     *
     * @return array
     */
    protected function transformers(): array
    {
        return [];
    }

    /**
     * @param array $params
     * @return array
     */
    protected function transformFilter(array $params = []): array
    {
        return [
            'transform' => array_filter(ArrayHelper::merge(
                [
                    'class' => RestfulTransformFilter::class,
                    'actions' => $this->transformers()
                ],
                $params
            ))
        ];
    }
}
