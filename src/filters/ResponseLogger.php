<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\filters;

use Craft;
use flipbox\craft\ember\filters\ActionTrait;
use flipbox\craft\restful\events\ResponseLoggerHandler;
use yii\base\Action;
use yii\base\ActionFilter;
use yii\base\Event;
use yii\helpers\Json;
use yii\log\Logger;
use yii\web\Response;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class ResponseLogger extends ActionFilter
{
    use ActionTrait;

    /**
     * @var int
     */
    public $level = Logger::LEVEL_WARNING;

    public $test = ['info' => ['200']];

    /**
     * @param Action $action
     * @param mixed $result
     * @return mixed
     */
    public function afterAction($action, $result)
    {
        if ($this->actionMatch($action->id)) {
            $this->addLogEvent($action);
        }

        return parent::afterAction($action, $result);
    }

    /**
     * @param Action $action
     */
    protected function addLogEvent(Action $action)
    {
        try {
            if (!$level = $this->findLogLevel($action->id)) {
                return;
            }

            Event::on(
                Response::class,
                Response::EVENT_AFTER_SEND,
                [
                    ResponseLoggerHandler::class,
                    'handle'
                ],
                [
                    'level' => $level
                ]
            );
        } catch (\Exception $e) {
            Craft::warning(
                sprintf(
                    "Exception caught while trying to set log level. Exception: [%s].",
                    (string)Json::encode([
                        'Trace' => $e->getTraceAsString(),
                        'File' => $e->getFile(),
                        'Line' => $e->getLine(),
                        'Code' => $e->getCode(),
                        'Message' => $e->getMessage()
                    ])
                ),
                __METHOD__
            );
        }
    }

    /**
     * @param string $action
     * @return null|int
     */
    protected function findLogLevel(string $action)
    {
        // Look for definitions
        if ($level = $this->findLogLevelFromAction($action)) {
            return $level;
        }

        return $this->level;
    }

    /**
     * @param string $action
     * @return null|int
     */
    protected function findLogLevelFromAction(string $action)
    {
        // Default format
        $messages = $this->findAction($action);

        if (!is_array($messages)) {
            return null;
        }

        return $this->resolveMessageStatusCode($messages);
    }

    /**
     * @param array $messages
     * @return int|null
     */
    protected function resolveMessageStatusCode(array $levels)
    {
        $statusCode = Craft::$app->getResponse()->getStatusCode();

        foreach ($levels as $level => $comparisons) {
            if ($comparisons === '*') {
                return (int)$level;
            }

            if (!is_array($comparisons)) {
                $comparisons = [$comparisons];
            }

            if ($this->matchAllComparisons($statusCode, $comparisons) === true) {
                return (int)$level;
            }
        }

        return null;
    }

    /**
     * The Status Code must match all comparisons
     *
     * @param int $statusCode
     * @param array $comparisons
     * @return bool
     */
    protected function matchAllComparisons(int $statusCode, array $comparisons): bool
    {
        $passes = !empty($comparisons);

        foreach ($comparisons as $comparison) {
            if ($comparison === '*') {
                continue;
            }
            if (!is_array($comparison)) {
                $comparison = [$comparison];
            }

            $version2 = $comparison[0] ?? 0;
            $operator = $comparison[1] ?? '=';

            if (!version_compare($statusCode, $version2, $operator)) {
                $passes = false;
            }
        }

        return $passes;
    }
}
