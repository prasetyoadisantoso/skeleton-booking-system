<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Services\GlobalView;
use App\Services\GlobalVariable;
use App\Services\Translations;
use Yajra\DataTables\DataTables;
use App\Services\ResponseFormatter;
use App\Services\FileManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Meta;
use App\Models\Canonical;

class PostController extends Controller
{
    protected $global_view, $global_variable, $translation, $dataTables, $responseFormatter, $fileManagement, $post, $category, $tag, $meta, $canonical;

    public function __construct(
        GlobalView $global_view,
        GlobalVariable $global_variable,
        Translations $translation,
        DataTables $dataTables,
        ResponseFormatter $responseFormatter,
        FileManagement $fileManagement,
        Post $post,
        Category $category,
        Tag $tag,
        Meta $meta,
        Canonical $canonical,
    ) {
        $this->middleware(['auth', 'verified', 'xss']);
        $this->middleware(['permission:blog-sidebar']);
        $this->middleware(['permission:post-index'])->only(['index', 'index_dt']);
        $this->middleware(['permission:post-create'])->only('create');
        $this->middleware(['permission:post-edit'])->only('edit');
        $this->middleware(['permission:post-store'])->only('store');
        $this->middleware(['permission:post-update'])->only('update');
        $this->middleware(['permission:post-destroy'])->only('destroy');
        $this->global_view = $global_view;
        $this->global_variable = $global_variable;
        $this->translation = $translation;
        $this->dataTables = $dataTables;
        $this->responseFormatter = $responseFormatter;
        $this->fileManagement = $fileManagement;
        $this->post = $post;
        $this->category = $category;
        $this->tag = $tag;
        $this->meta = $meta;
        $this->canonical = $canonical;
    }

    protected function boot()
    {
        return $this->global_view->RenderView([

            // Global Variable
            $this->global_variable->TitlePage($this->translation->post['title']),
            $this->global_variable->SystemLanguage(),
            $this->global_variable->AuthUserName(),
            $this->global_variable->SystemName(),

            // Translations
            $this->translation->sidebar,
            $this->translation->button,
            $this->translation->post,
            $this->translation->notification,

            // Module
            $this->global_variable->ModuleType([
                'post-home',
                'post-form',
            ]),

            // Script
            $this->global_variable->ScriptType([
                'post-home-js',
                'post-form-js',
            ]),

            // Route Type
            $this->global_variable->RouteType('post.index'),
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->boot();
        return view('template.default.dashboard.blog.post.home', array_merge(
            $this->global_variable->PageType('index'),
        ));
    }

    public function index_dt()
    {
        $res = $this->dataTables->of($this->post->query())
            ->addColumn('title', function ($post) {
                return $post->title;
            })
            ->addColumn('image', function ($post) {
                return Storage::url($post->feature_image);
            })
            ->addColumn('publish', function ($post) {
                return $post->created_at->format('M d, Y');
            })
            ->addColumn('action', function ($post) {
                return $post->id;
            })
            ->removeColumn('id')->addIndexColumn()->make('true');
        return $res;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->boot();
        $category_select = $this->category->query()->get();
        $tag_select = $this->tag->query()->get();
        $meta_select = $this->meta->query()->get();
        $canonical_select = $this->canonical->query()->get();
        return view('template.default.dashboard.blog.post.form', array_merge(
            $this->global_variable->PageType('create'),
            [
                'category_select' => $category_select,
                'tag_select' => $tag_select,
                'meta_select' => $meta_select,
                'canonical_select' => $canonical_select
            ]
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}