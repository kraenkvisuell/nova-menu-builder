<?php

namespace KraenkVisuell\MenuBuilder\Nova\Resources;

use Illuminate\Http\Request;
use KraenkVisuell\MenuBuilder\MenuBuilder;
use KraenkVisuell\MenuBuilder\Nova\Fields\MenuBuilderField;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Panel;
use Laravel\Nova\Resource;

class MenuResource extends Resource
{
    public static $model = \KraenkVisuell\MenuBuilder\Models\Menu::class;
    public static $search = ['name', 'slug'];
    public static $displayInNavigation = false;

    public function __construct($resource)
    {
        $this->resource = $resource;
        static::$model = MenuBuilder::getMenuClass();
    }

    public static function label()
    {
        return __('novaMenuBuilder.menuResourceLabel');
    }

    public static function singularLabel()
    {
        return __('novaMenuBuilder.menuResourceSingularLabel');
    }

    public static function uriKey()
    {
        return 'nova-menus';
    }

    public function title()
    {
        return $this->name.' ('.$this->slug.')';
    }

    public function fields(Request $request)
    {
        $menusTableName = MenuBuilder::getMenusTableName();
        $menuOptions = collect(MenuBuilder::getMenus())
            ->mapWithKeys(function ($menu, $key) {
                return [$key => $menu['name']];
            })
            ->toArray();

        $maxDepth = 10;
        if ($this->slug) {
            $maxDepth = MenuBuilder::getMenuConfig($this->slug)['max_depth'] ?? 10;
        }

        return [
            Text::make(__('novaMenuBuilder.nameFieldName'), 'name')
                ->sortable()
                ->rules('required', 'max:255'),

            Select::make(__('novaMenuBuilder.menuResourceSingularLabel'), 'slug')
                ->options($menuOptions)
                ->onlyOnForms()
                ->creationRules('required', 'max:255', "unique_menu:$menusTableName,slug,NULL,id")
                ->updateRules('required', 'max:255', "unique_menu:$menusTableName,slug,{{resourceId}},id"),

            Text::make(__('novaMenuBuilder.menuResourceSingularLabel'), 'slug', function ($key) {
                $menu = MenuBuilder::getMenus()[$key] ?? null;
                if (! $menu) {
                    return "<s>{$key}</s>";
                }

                return "<span class='whitespace-no-wrap'><b>{$menu['name']}</b> <i>({$key})</i></span>";
            })
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->asHtml(),

            Panel::make(__('novaMenuBuilder.menuItemsPanelName'), [
                MenuBuilderField::make('', 'menu_items')
                    ->hideWhenCreating()
                    ->maxDepth($maxDepth)
                    ->readonly(),
            ]),
        ];
    }
}
