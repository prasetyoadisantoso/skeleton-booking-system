<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\SocialMedia;
use App\Services\GlobalView;
use Illuminate\Http\Request;
use App\Services\GlobalVariable;

class MainController extends Controller
{
    protected $global_view, $global_variable, $social_media;

    public function __construct(
        GlobalView $global_view,
        GlobalVariable $global_variable,
        SocialMedia $social_media,
    )
    {
        $this->global_view = $global_view;
        $this->global_variable = $global_variable;
        $this->social_media = $social_media;
    }

    protected function boot()
    {
        $this->global_view->RenderView([
            $this->global_variable->SiteLogo(),
        ]);
    }

    public function index()
    {
        $this->boot();
        return view('welcome', array_merge());
    }
}
