<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;
use Webpatser\Uuid\Uuid;
use Spatie\Activitylog\LogOptions;

class Meta extends Model
{
    use HasFactory, HasTranslations, LogsActivity;

    public $primaryKey = 'id';

    public $incrementing = false;

    public $translatable = [
      'description', 'keyword'
    ];

    public $fillable = [
        'name', 'robot', 'description', 'keyword'
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->id = (string) Uuid::generate(4);
        });
    }

    public function GetMetaById($id)
    {
        return $this->query()->find($id)->first();
    }

    public function StoreMeta($data = null)
    {
        return $this->create([
            'name' => $data['name'],
            'robot' => $data['robot'],
            'description' => $data['description'],
            'keyword' => $data['keyword'],
        ]);
    }

    public function UpdateMeta($new_metadata, $id)
    {
        $metadata = $this->GetMetaById($id);
        $metadata->update($new_metadata);
    }

    public function DeleteMeta($id)
    {
        return $this->query()->find($id)->forceDelete();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

}
