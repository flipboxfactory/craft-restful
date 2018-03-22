<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/restful/license
 * @link       https://www.flipboxfactory.com/software/restful/
 */

namespace flipbox\craft\restful\modules;

use ArrayIterator;
use yii\base\Module;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
abstract class ApiModule extends Module
{
    /**
     * @param ArrayIterator $urlSegments
     * @return array
     */
    public function urlRules(ArrayIterator $urlSegments): array
    {
        $urlSegments->next();
        return array_filter(array_merge(
            $this->moduleUrlRules(),
            $this->childUrlRules($urlSegments)
        ));
    }

    /**
     * All url rules associated with this module and it's sub-modules.  This method allows
     * us to build a url map of all available endpoints and their supporting methods
     *
     * @return array
     */
    public function defineUrlRules(): array
    {
        return array_filter(array_merge(
            $this->moduleUrlRules(),
            $this->defineChildUrlRules()
        ));
    }

    /**
     * @return array
     */
    protected function moduleUrlRules(): array
    {
        return [];
    }

    /**
     * Child rules will load and traverse sub-modules.  This approach allows a modular approach to building
     * a RESTful API where the path consists of a series of modules.
     *
     * @param ArrayIterator $request
     * @return array
     */
    protected function childUrlRules(ArrayIterator $request): array
    {
        // The current
        $currentRouteSegment = $request->current();

        // Look for matching sub-module
        $module = $this->getModule($currentRouteSegment);

        // Check for valid sub-module
        if ($module === null || !$module instanceof ApiModule) {
            return [];
        }

        // Bump the request to the next matching segment
        $request->next();

        return $module->urlRules($request);
    }

    /**
     * Child rules will load and traverse sub-modules.  This approach allows a modular
     * approach to building a RESTful API where the path consists of a series of modules.
     *
     * @return array
     */
    protected function defineChildUrlRules(): array
    {
        $rules = [];

        // Get rules from sub-module
        foreach ($this->getModules() as $id => $module) {
            if (!$module instanceof Module) {
                $module = $this->getModule($id);
            }
            if ($module instanceof ApiModule) {
                $rules = array_merge(
                    $rules,
                    $module->defineUrlRules()
                );
            }
        }

        return $rules;
    }
}
