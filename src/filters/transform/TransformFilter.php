<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\filters\transform;

use Craft;
use flipbox\craft\restful\Restful;
use flipbox\flux\filters\TransformFilter as BaseTransformFilter;
use Flipbox\Transform\Factory;
use yii\base\Model;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\web\Link;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class TransformFilter extends BaseTransformFilter
{
    /**
     * The pagination transformer identifier
     */
    const PAGINATION_IDENTIFIER = 'pagination';

    /**
     * The error transformer identifier
     */
    const ERROR_IDENTIFIER = 'error';

    /**
     * @var null|string|callable
     */
    public $error = self::ERROR_IDENTIFIER;

    /**
     * @var null|string|callable
     */
    public $pagination = self::PAGINATION_IDENTIFIER;

    /**
     * The scope that transformers are registered under.
     *
     * @var
     */
    public $scope = Restful::FLUX_SCOPE;

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
     * Serializes a data provider.
     * @param DataProviderInterface $dataProvider
     * @return array the array representation of the data provider.
     */
    protected function transformDataProvider(DataProviderInterface $dataProvider)
    {
        // Get existing data
        $result = parent::transformDataProvider($dataProvider);

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
     * Serializes a pagination into an array.
     * @param Pagination $pagination
     * @return array the array representation of the pagination
     * @see addPaginationHeaders()
     */
    protected function transformPagination(Pagination $pagination): array
    {
        $metaEnvelope = [];

        if (null !== ($transformer = $this->resolveTransformer($this->pagination))) {
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
     * Serializes the validation errors in a model.
     * @param Model $model
     * @return array the array representation of the errors
     */
    protected function transformModelErrors(Model $model): array
    {
        if (null === ($transformer = $this->resolveTransformer($this->error))) {
            return [];
        };

        return Factory::item(
            $transformer,
            $model
        );
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
}
