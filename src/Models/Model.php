<?php


namespace MGGFLOW\LVMSVC\Models;


use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    const CREATED_AT = 'created_at';
    const UPDATED_AT = false;
    public $incrementing = true;
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    protected $dateFormat = 'U';

    protected $guarded = [];

    protected $hidden = [];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->customTimestamps) {
                $model->setCreatedAt($model->freshTimestamp()->format($model->getDateFormat()));
            }
        });

        static::updating(function ($model) {
            if ($model->customTimestamps) {
                $model->setUpdatedAt($model->freshTimestamp()->format($model->getDateFormat()));
            }
        });
    }
}
