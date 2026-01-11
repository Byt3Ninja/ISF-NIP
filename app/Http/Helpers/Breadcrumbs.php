<?php

use Illuminate\Support\Facades\Route;

if (! function_exists('breadcrumbs')) {
    function breadcrumbs()
    {
        $currentRoute = Route::currentRouteName();
        $navigation   = config('navigation');

        foreach ($navigation as $section) {
            foreach ($section['children'] as $child) {
                if ($child['route'] === $currentRoute) {
                    return [
                        'parent' => $section['title'],
                        'child'  => $child['title'],
                        'child_group' => isset($child['group']) ? $child['group'] : null,
                        'route'  => $child['route'],
                    ];
                }
            }
        }

        return null;
    }
}
