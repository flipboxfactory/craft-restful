<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\models;

use craft\base\Model;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Settings extends Model
{
    /**
     * @var string
     */
    public $pageSizeParam = 'limit';

    /**
     * @var string
     */
    public $pageParam = 'page';

    /**
     * @var array
     */
    public $pageSizeLimit = [0, 1, 50, 100, 200, 500, 1000];

    /**
     * @var int
     */
    public $defaultPageSize = 50;

    /**
     * @var array
     */
    private $authMethods = [];

    /**
     * @var array
     */
    private $cors = [];

    /**
     * @param array $cors
     * @return $this
     */
    public function setCORS(array $cors = [])
    {
        $this->cors = $cors;
        return $this;
    }

    /**
     * @return array
     */
    public function getCORS(): array
    {
        return $this->cors;
    }

    /**
     * @return array
     */
    public function getAuthMethods(): array
    {
        return $this->authMethods;
    }

    /**
     * @param array $authMethods
     * @return $this
     */
    public function setAuthMethods(array $authMethods = [])
    {
        $this->authMethods = $authMethods;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return array_merge(
            parent::attributes(),
            [
                'authMethods',
                'cors'
            ]
        );
    }
}
