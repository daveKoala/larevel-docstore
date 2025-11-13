<?php

namespace App\Traits;

use Godruoyi\Snowflake\Snowflake;

trait HasSnowflakeGuid
{
    /**
     * Boot the trait.
     */
    protected static function bootHasSnowflakeGuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->guid)) {
                $snowflake = new Snowflake();
                $model->guid = (string) $snowflake->id();
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'guid';
    }

    /**
     * Find a model by its GUID.
     */
    public static function findByGuid(string $guid)
    {
        return static::where('guid', $guid)->first();
    }

    /**
     * Find a model by its GUID or fail.
     */
    public static function findByGuidOrFail(string $guid)
    {
        return static::where('guid', $guid)->firstOrFail();
    }
}
