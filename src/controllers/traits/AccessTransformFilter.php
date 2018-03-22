<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\controllers\traits;

use craft\helpers\ArrayHelper;
use flipbox\craft\restful\filters\transform\AccessTransformFilter as AccessTransformFilterClass;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait AccessTransformFilter
{
    use TransformFilter;

    /**
     * An array of transformers in the format of 'action' => 'transformer'
     *
     * @return array
     */
    protected function transformerRules(): array
    {
        return [];
    }

    /**
     * @param array $params
     * @return array
     */
    protected function accessTransformFilter(array $params = []): array
    {
        return $this->transformFilter(array_filter(
            ArrayHelper::merge(
                [
                    'class' => AccessTransformFilterClass::class,
                    'rules' => $this->transformerRules()
                ],
                $params
            )
        ));
    }
}
