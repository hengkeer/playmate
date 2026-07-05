<?php

namespace App\Providers;

use App\Services\GeminiClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GeminiClient::class, function () {
            return new GeminiClient(
                apiKey: (string) config('gemini.api_key'),
                baseUrl: (string) config('gemini.base_url'),
                embeddingModel: (string) config('gemini.embedding_model'),
                chatModel: (string) config('gemini.chat_model'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
