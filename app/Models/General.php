<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

class General extends Model
{
    use HasFactory , HasTranslations;

    public $incrementing = false;

    public $translatable = [
        'site_tagline',
        'copyright',
        'cookies_concern'
    ];

    protected $primaryKey = 'id';

    protected $fillable = [
        'site_title',
        'site_tagline',
        'site_logo',
        'site_favicon',
        'url_address',
        'copyright',
        'cookies_concern'
    ];

    public function UpdateSiteDescription($data)
    {
        $current_data = $this->query()->find($data['id']);
        return $current_data->update($data);
    }

    public function UpdateSiteLogoFavicon($data)
    {
        $current_data = $this->query()->find($data['id']);
        if (isset($data['site_logo'])) {
            // Delete image file
            Storage::delete('/public' . '/' . $current_data->site_logo);
        }

        if (isset($data['site_favicon'])) {
            // Delete image file
            Storage::delete('/public' . '/' . $current_data->site_favicon);
        }

        return $current_data->update($data);
    }

}