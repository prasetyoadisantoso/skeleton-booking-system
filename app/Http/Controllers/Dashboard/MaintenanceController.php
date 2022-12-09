<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\FileManagement;
use App\Services\GlobalVariable;
use App\Services\GlobalView;
use App\Services\ResponseFormatter;
use App\Services\Translations;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class MaintenanceController extends Controller
{

    protected $global_view, $global_variable, $translation, $responseFormatter, $fileManagement;

    public function __construct(
        ResponseFormatter $responseFormatter,
        FileManagement $fileManagement,
        GlobalVariable $global_variable,
        GlobalView $global_view,
        Translations $translation,
    ) {
        $this->middleware(['auth', 'verified', 'xss']);
        $this->middleware(['permission:system-sidebar']);
        $this->middleware(['permission:maintenance-index']);

        $this->responseFormatter = $responseFormatter;
        $this->global_variable = $global_variable;
        $this->global_view = $global_view;
        $this->translation = $translation;
        $this->fileManagement = $fileManagement;
    }

    protected function boot()
    {
        // Render to View
        $this->global_view->RenderView([

            // Global Variable
            $this->global_variable->TitlePage($this->translation->maintenance['title']),
            $this->global_variable->SystemLanguage(),
            $this->global_variable->AuthUserName(),
            $this->global_variable->SystemName(),
            $this->global_variable->SiteLogo(),

            // Translations
            $this->translation->sidebar,
            $this->translation->button,
            $this->translation->notification,
            $this->translation->maintenance,

            // Module
            $this->global_variable->ModuleType([
                'maintenance-home',
            ]),

            // Script
            $this->global_variable->ScriptType([
                'maintenance-js',
            ]),
        ]);
    }

    public function index()
    {
        $this->boot();
        return view('template.default.dashboard.system.maintenance.home', array_merge(
            $this->global_variable->PageType('index'
        )));
    }

    public function event_clear()
    {
        try {
            Artisan::call('event:clear');
            activity()->causedBy(Auth::user())->log($this->translation->maintenance['messages']['action_success']);
            return redirect()->back()->with([
                'success' => 'success',
                'title' => $this->translation->notification['success'],
                'content' => $this->translation->maintenance['messages']['action_success'],
            ]);
        } catch (\Throwable$th) {
            $message = $th->getMessage();
            activity()->causedBy(Auth::user())->log($message);
            return redirect()->back()->with([
                'error' => 'error',
                "title" => $this->translation->notification['error'],
                "content" => $message,
            ]);
        }
    }

    public function view_clear()
    {
        try {
            Artisan::call('view:clear');
            activity()->causedBy(Auth::user())->log($this->translation->maintenance['messages']['action_success']);
            return redirect()->back()->with([
                'success' => 'success',
                'title' => $this->translation->notification['success'],
                'content' => $this->translation->maintenance['messages']['action_success'],
            ]);
        } catch (\Throwable$th) {
            $message = $th->getMessage();
            activity()->causedBy(Auth::user())->log($message);
            return redirect()->back()->with([
                'error' => 'error',
                "title" => $this->translation->notification['error'],
                "content" => $message,
            ]);
        }
    }

    public function cache_clear()
    {
        try {
            Artisan::call('cache:clear');
            activity()->causedBy(Auth::user())->log($this->translation->maintenance['messages']['action_success']);
            return redirect()->back()->with([
                'success' => 'success',
                'title' => $this->translation->notification['success'],
                'content' => $this->translation->maintenance['messages']['action_success'],
            ]);
        } catch (\Throwable$th) {
            $message = $th->getMessage();
            activity()->causedBy(Auth::user())->log($message);
            return redirect()->back()->with([
                'error' => 'error',
                "title" => $this->translation->notification['error'],
                "content" => $message,
            ]);
        }
    }

    public function config_clear()
    {
        try {
            Artisan::call('config:clear');
            activity()->causedBy(Auth::user())->log($this->translation->maintenance['messages']['action_success']);
            return redirect()->back()->with([
                'success' => 'success',
                'title' => $this->translation->notification['success'],
                'content' => $this->translation->maintenance['messages']['action_success'],
            ]);
        } catch (\Throwable$th) {
            $message = $th->getMessage();
            activity()->causedBy(Auth::user())->log($message);
            return redirect()->back()->with([
                'error' => 'error',
                "title" => $this->translation->notification['error'],
                "content" => $message,
            ]);
        }
    }

    public function route_clear()
    {
        try {
            Artisan::call('route:clear');
            activity()->causedBy(Auth::user())->log($this->translation->maintenance['messages']['action_success']);
            return redirect()->back()->with([
                'success' => 'success',
                'title' => $this->translation->notification['success'],
                'content' => $this->translation->maintenance['messages']['action_success'],
            ]);
        } catch (\Throwable$th) {
            $message = $th->getMessage();
            activity()->causedBy(Auth::user())->log($message);
            return redirect()->back()->with([
                'error' => 'error',
                "title" => $this->translation->notification['error'],
                "content" => $message,
            ]);
        }
    }

    public function compile_clear()
    {
        try {
            Artisan::call('clear-compiled');
            activity()->causedBy(Auth::user())->log($this->translation->maintenance['messages']['action_success']);
            return redirect()->back()->with([
                'success' => 'success',
                'title' => $this->translation->notification['success'],
                'content' => $this->translation->maintenance['messages']['action_success'],
            ]);
        } catch (\Throwable$th) {
            $message = $th->getMessage();
            activity()->causedBy(Auth::user())->log($message);
            return redirect()->back()->with([
                'error' => 'error',
                "title" => $this->translation->notification['error'],
                "content" => $message,
            ]);
        }
    }

    public function optimize_clear()
    {
        try {
            Artisan::call('optimize:clear');
            activity()->causedBy(Auth::user())->log($this->translation->maintenance['messages']['action_success']);
            return redirect()->back()->with([
                'success' => 'success',
                'title' => $this->translation->notification['success'],
                'content' => $this->translation->maintenance['messages']['action_success'],
            ]);
        } catch (\Throwable$th) {
            $message = $th->getMessage();
            activity()->causedBy(Auth::user())->log($message);
            return redirect()->back()->with([
                'error' => 'error',
                "title" => $this->translation->notification['error'],
                "content" => $message,
            ]);
        }
    }
}
