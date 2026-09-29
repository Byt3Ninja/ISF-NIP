<?php

use Illuminate\Support\Facades\Route;

if (! function_exists('breadcrumbs')) {
    function breadcrumbs()
    {
        $currentRoute = Route::currentRouteName();
        $navigation   = config('navigation')();

        foreach ($navigation as $item) {
            if (!isset($item['children']) || !is_array($item['children'])) {
                if (isset($item['url']) && request()?->is($item['url'])) {
                    return [
                        'parent' => null,
                        'child'  => $item['title'],
                        'child_group' => null,
                        'route'  => $item['url'],
                        'group_route' => null,
                    ];
                }
            }

            else {
                foreach ($item['children'] as $child) {
                    if ($child['route'] === $currentRoute) {
                        return [
                            'parent' => $item['title'],
                            'child'  => $child['title'],
                            'child_group' => isset($child['group']) ? $child['group'] : null,
                            'route'  => $child['route'],
                            'group_route' => isset($child['group_route']) ? $child['group_route'] : null,
                            'hide_parent' => isset($child['hide_parent']) ? $child['hide_parent'] : false
                        ];
                    }
                }
            }

        }

        return null;
    }
}
