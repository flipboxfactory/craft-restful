<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\filters\transform;

use Craft;
use flipbox\craft\restful\helpers\TransformerHelper;
use flipbox\craft\restful\transformers\Errors;
use Flipbox\Transform\Factory;
use yii\base\Action;
use yii\base\ActionFilter;
use yii\base\Model;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\web\Link;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class TransformFilter extends ActionFilter
{
    /**
     * The default data transformer.  If a transformer cannot be resolved via an action mapping,
     * this transformer will be used.
     *
     * @var string|callable
     */
    public $transformer;

    /**
     * @var array this property defines the transformers for each action.
     * Each action that should only support one transformer.
     *
     * You can use `'*'` to stand for all actions. When an action is explicitly
     * specified, it takes precedence over the specification given by `'*'`.
     *
     * For example,
     *
     * ```php
     * [
     *   'create' => SomeClass::class,
     *   'update' => 'transformerHandle',
     *   'delete' => function() { return ['foo' => 'bar'] },
     *   '*' => SomeOtherClass::class,
     * ]
     * ```
     */
    public $actions = [];

    /**
     * @var string
     */
    public $fieldsParam = 'fields';

    /**
     * @var string
     */
    public $includesParam = 'includes';

    /**
     * @var string
     */
    public $excludesParam = 'excludes';

    /**
     * @var string the name of the envelope (e.g. `items`) for returning the resource objects in a collection.
     * This is used when serving a resource collection. When this is set and pagination is enabled, the serializer
     * will return a collection in the following format:
     *
     * ```php
     * [
     *     'data' => [...],  // assuming collectionEnvelope is "data"
     * ]
     * ```
     *
     * If this property is not set, the resource arrays will be directly returned without using envelope.
     * The pagination information as shown in `_links` and `_meta` can be accessed from the response HTTP headers.
     */
    public $collectionEnvelope = 'data';

    /**
     * @var callable a callback that will be called to determine if the transformer should be applied.
     * The signature of the callback should be as follows:
     *
     * ```php
     * function ($filter, $action, $data)
     * ```
     *
     * where `$filter` is this transformer filter, `$action` is the current [[Action|action]] object, and `$data` is
     * the data to be transformed.
     * The callback should return a boolean value indicating whether this transformer should be applied.
     */
    public $matchCallback;

    /**
     * Indicating whether to transform empty data
     *
     * @var bool
     */
    public $transformEmpty = false;

    /**
     * @var null|string|callable
     */
    public $error = Errors::class;

    /**
     * @var null|string|callable
     */
    public $pagination = null;

    /**
     * @var string the name of the HTTP header containing the information about total number of data items.
     * This is used when serving a resource collection with pagination.
     */
    public $totalCountHeader = 'X-Pagination-Total-Count';

    /**
     * @var string the name of the HTTP header containing the information about total number of pages of data.
     * This is used when serving a resource collection with pagination.
     */
    public $pageCountHeader = 'X-Pagination-Page-Count';

    /**
     * @var string the name of the HTTP header containing the information about the current page number (1-based).
     * This is used when serving a resource collection with pagination.
     */
    public $currentPageHeader = 'X-Pagination-Current-Page';

    /**
     * @var string the name of the HTTP header containing the information about the number of data items in each page.
     * This is used when serving a resource collection with pagination.
     */
    public $perPageHeader = 'X-Pagination-Per-Page';

    /**
     * @var string the name of the envelope (e.g. `_links`) for returning the links objects.
     * It takes effect only, if `collectionEnvelope` is set.
     * @since 2.0.4
     */
    public $linksEnvelope = 'links';

    /**
     * @var string the name of the envelope (e.g. `_meta`) for returning the pagination object.
     * It takes effect only, if `collectionEnvelope` is set.
     * @since 2.0.4
     */
    public $metaEnvelope = 'meta';

    /**
     * @param \yii\base\Action $action
     * @param mixed $result
     * @return array|mixed|null|DataProviderInterface
     */
    public function afterAction($action, $result)
    {
        if (!$this->shouldTransform($action, $result)) {
            return $result;
        }
        return $this->transform($result);
    }

    /*******************************************
     * TRANSFORM
     *******************************************/

    /**
     * @param $data
     * @return array|null
     */
    protected function transform($data)
    {
        if ($data instanceof Model && $data->hasErrors()) {
            return $this->transformModelErrors($data);
        } elseif ($data instanceof DataProviderInterface) {
            return $this->transformDataProvider($data);
        } elseif (!is_array($data)) {
            return $this->transformData($data);
        }
        return $data;
    }

    /**
     * Serializes the validation errors in a model.
     * @param Model $model
     * @return array the array representation of the errors
     */
    protected function transformModelErrors(Model $model): array
    {
        if (null === ($transformer = TransformerHelper::resolve($this->error))) {
            return [];
        };

        return Factory::item(
            $transformer,
            $model
        );
    }

    /**
     * Serializes a data provider.
     * @param DataProviderInterface $dataProvider
     * @return array the array representation of the data provider.
     */
    protected function transformDataProvider(DataProviderInterface $dataProvider)
    {
        if (Craft::$app->getRequest()->getIsHead()) {
            return null;
        }

        $result = $dataProvider;

        if (null !== ($transformer = TransformerHelper::resolve($this->transformer()))) {
            $data = Factory::transform($this->getTransformConfig())
                ->collection(
                    $transformer,
                    $dataProvider->getModels()
                );

            if ($this->collectionEnvelope === null) {
                $result = $data;
            } else {
                $result = [
                    $this->collectionEnvelope => $data,
                ];
            }
        };

        // Halt
        if ($result === null || $result instanceof DataProviderInterface) {
            return $result;
        }

        // Add headers
        if (false !== ($pagination = $dataProvider->getPagination())) {
            $this->addPaginationHeaders($pagination);
        }

        // apply pagination headers
        if ($this->collectionEnvelope === null) {
            return $result;
        }

        return $pagination ? array_merge($result, $this->transformPagination($pagination)) : $result;
    }

    /**
     * @param $data
     * @return array|null
     */
    protected function transformData($data)
    {
        if (Craft::$app->getRequest()->getIsHead()) {
            return null;
        }

        if (null === ($transformer = TransformerHelper::resolve($this->transformer()))) {
            return $data;
        };

        return Factory::transform($this->getTransformConfig())
            ->item(
                $transformer,
                $data
            );
    }


    /*******************************************
     * PAGINATION
     *******************************************/

    /**
     * Serializes a pagination into an array.
     * @param Pagination $pagination
     * @return array the array representation of the pagination
     * @see addPaginationHeaders()
     */
    protected function transformPagination(Pagination $pagination): array
    {
        $metaEnvelope = [];

        if (null !== ($transformer = TransformerHelper::resolve($this->pagination))) {
            $metaEnvelope = Factory::item(
                $transformer,
                $pagination
            );
        };

        return [
            $this->linksEnvelope => Link::serialize($pagination->getLinks(true)),
            $this->metaEnvelope => $metaEnvelope
        ];
    }

    /**
     * Adds HTTP headers about the pagination to the response.
     * @param Pagination $pagination
     */
    protected function addPaginationHeaders(Pagination $pagination)
    {
        $links = [];
        foreach ($pagination->getLinks(true) as $rel => $url) {
            $links[] = "<$url>; rel=$rel";
        }

        Craft::$app->getResponse()->getHeaders()
            ->set($this->totalCountHeader, $pagination->totalCount)
            ->set($this->pageCountHeader, $pagination->getPageCount())
            ->set($this->currentPageHeader, $pagination->getPage() + 1)
            ->set($this->perPageHeader, $pagination->pageSize)
            ->set('Link', implode(', ', $links));
    }


    /*******************************************
     * ACTION UTILITIES
     *******************************************/

    /**
     * Checks whether this filter should transform the specified action data.
     * @param Action $action the action to be performed
     * @param mixed $data the data to be transformed
     * @return bool `true` if the transformer should be applied, `false` if the transformer should be ignored
     */
    protected function shouldTransform($action, $data): bool
    {
        if ($this->matchData($data) &&
            $this->matchCustom($action, $data)) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed $data the data to be transformed
     * @return bool whether the transformer should be applied
     */
    protected function matchData($data)
    {
        return empty($data) && $this->transformEmpty !== true ? false : true;
    }

    /**
     * @param Action $action the action to be performed
     * @param mixed $data the data to be transformed
     * @return bool whether the transformer should be applied
     */
    protected function matchCustom($action, $data)
    {
        return empty($this->matchCallback) || call_user_func($this->matchCallback, $this, $action, $data);
    }


    /*******************************************
     * TRANSFORMER
     *******************************************/

    /**
     * @return callable|null
     */
    protected function transformer()
    {
        // The requested action
        $action = Craft::$app->requestedAction->id;

        // Default transformer
        $transformer = $this->transformer;

        // Look for definitions
        if (isset($this->actions[$action])) {
            $transformer = $this->actions[$action];
        } elseif (isset($this->actions['*'])) {
            $transformer = $this->actions['*'];
        }

        if (null === $transformer) {
            return null;
        }

        return $transformer;
    }

    /**
     * @return array
     */
    protected function getTransformConfig(): array
    {
        return [
            'includes' => $this->getRequestedIncludes(),
            'excludes' => $this->getRequestedExcludes(),
            'fields' => $this->getRequestedFields()
        ];
    }

    /**
     * @return array
     */
    protected function getRequestedFields(): array
    {
        return $this->normalizeRequest(
            Craft::$app->getRequest()->get($this->fieldsParam)
        );
    }

    /**
     * @return array
     */
    protected function getRequestedIncludes(): array
    {
        return $this->normalizeRequest(
            Craft::$app->getRequest()->get($this->includesParam)
        );
    }

    /**
     * @return array
     */
    protected function getRequestedExcludes(): array
    {
        return $this->normalizeRequest(
            Craft::$app->getRequest()->get($this->excludesParam)
        );
    }

    /**
     * @param $value
     * @return array
     */
    private function normalizeRequest($value): array
    {
        return is_string($value) ? preg_split('/\s*,\s*/', $value, -1, PREG_SPLIT_NO_EMPTY) : [];
    }
}
