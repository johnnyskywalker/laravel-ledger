<?php

namespace App\Providers;

use DB;
use Log;
use Schema;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        // 修正 table 字串長度
        Schema::defaultStringLength(191);

        // 將每一句 SQL 紀錄在 log
        DB::listen(function ($query) {
            // Laravel 5.4 改成以下結構
            $bindings  = $query->bindings;
            $exec_time = $query->time;
            $query     = $query->sql;

            // 整理 binding 格式
            foreach ($bindings as $i => $binding) {
                if ($binding instanceof \DateTime) {
                    $bindings[$i] = $binding->format('Y-m-d H:i:s');
                } elseif (is_string($binding)) {
                    $bindings[$i] = str_replace("'", "\\'", $binding);
                }
            }

            // now we create full SQL query - in case of failure, we log this
            $query    = str_replace(['%', '?', "\n"], ['%%', "'%s'", ' '], $query);
            $full_sql = vsprintf($query, $bindings);
            $exec_time = number_format(($exec_time / 1000.0), 4);

            Log::info(
                $full_sql,
                [$exec_time] // 單位轉成秒 (second)
            );
        });

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
