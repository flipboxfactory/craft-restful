<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\transformers;

use yii\base\Model;

class Errors
{
    /**
     * @param Model $data
     * @return array
     */
    public function __invoke(Model $data)
    {
        return $this->transform($data);
    }

    /**
     * @param Model $model
     * @return array
     */
    private function transform(Model $model): array
    {
        $errors = [];
        foreach ($model->getErrors() as $key => $error) {
            $errors[] = [
                'key' => $key,
                'errors' => array_merge(
                    $errors[$key] ?? [],
                    $error
                )
            ];
        }

        return $errors;
    }
}
