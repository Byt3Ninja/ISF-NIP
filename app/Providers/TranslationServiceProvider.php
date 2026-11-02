<?php

namespace App\Providers;

use App\Translation\OverlayFileLoader;
use Illuminate\Translation\TranslationServiceProvider as BaseTranslationServiceProvider;
use ReflectionClass;

class TranslationServiceProvider extends BaseTranslationServiceProvider
{
    /**
     * Use the overlay loader so LOCALIZATION_PATH can replace wording per deployment.
     *
     * @return void
     */
    protected function registerLoader()
    {
        // Same paths as Laravel's own loader (its __DIR__.'/lang' plus the app's lang path).
        $frameworkLangPath = dirname((new ReflectionClass(BaseTranslationServiceProvider::class))->getFileName()).'/lang';

        $this->app->singleton('translation.loader', function ($app) use ($frameworkLangPath) {
            return new OverlayFileLoader(
                $app['files'],
                [$frameworkLangPath, $app['path.lang']],
                $app['config']->get('app.localization_path')
            );
        });
    }
}
