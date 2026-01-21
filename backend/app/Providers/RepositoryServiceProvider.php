<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\RecordRepositoryInterface;
use App\Repositories\Contracts\DropdownOptionRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use App\Repositories\Eloquent\RecordRepository;
use App\Repositories\Eloquent\DropdownOptionRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(RecordRepositoryInterface::class, RecordRepository::class);
        $this->app->bind(DropdownOptionRepositoryInterface::class, DropdownOptionRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
