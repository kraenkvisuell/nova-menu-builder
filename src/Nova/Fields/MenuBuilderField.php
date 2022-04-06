<?php

namespace KraenkVisuell\MenuBuilder\Nova\Fields;

use KraenkVisuell\MenuBuilder\MenuBuilder;
use KraenkVisuell\MenuBuilder\Models\Menu;
use Laravel\Nova\Fields\Field;

class MenuBuilderField extends Field
{
    public $component = 'menu-builder-field';

    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        $this->withMeta([
            'locales' => MenuBuilder::getLocales(),
            'maxDepth' => 10,
            'menuCount' => Menu::count(),
        ]);
    }

    public function maxDepth($maxDepth = 10)
    {
        return $this->withMeta(['maxDepth' => $maxDepth]);
    }
}
