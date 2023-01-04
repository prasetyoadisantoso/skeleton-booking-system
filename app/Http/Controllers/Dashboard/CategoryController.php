<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryFormRequest;
use App\Models\Category;
use App\Services\FileManagement;
use App\Services\GlobalVariable;
use App\Services\GlobalView;
use App\Services\ResponseFormatter;
use App\Services\Translations;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class CategoryController extends Controller
{
    protected $global_view, $global_variable, $translation, $dataTables, $responseFormatter, $fileManagement, $category;

    public function __construct(
        GlobalView $global_view,
        GlobalVariable $global_variable,
        Translations $translation,
        DataTables $dataTables,
        ResponseFormatter $responseFormatter,
        FileManagement $fileManagement,
        Category $category,
    ) {
        $this->middleware(['auth', 'verified', 'xss']);
        $this->middleware(['permission:blog-sidebar']);
        $this->middleware(['permission:category-index'])->only(['index', 'index_dt']);
        $this->middleware(['permission:category-create'])->only('create');
        $this->middleware(['permission:category-edit'])->only('edit');
        $this->middleware(['permission:category-store'])->only('store');
        $this->middleware(['permission:category-update'])->only('update');
        $this->middleware(['permission:category-destroy'])->only('destroy');
        $this->global_view = $global_view;
        $this->global_variable = $global_variable;
        $this->translation = $translation;
        $this->dataTables = $dataTables;
        $this->responseFormatter = $responseFormatter;
        $this->fileManagement = $fileManagement;
        $this->category = $category;
    }

    protected function boot()
    {
        // return $this->global_view->RenderView([
        // ]);
        return [
            // Global Variable
            $this->global_variable->TitlePage($this->translation->category['title']),
            $this->global_variable->SystemLanguage(),
            $this->global_variable->AuthUserName(),
            $this->global_variable->SystemName(),

            // Translations
            $this->translation->sidebar,
            $this->translation->button,
            $this->translation->category,
            $this->translation->notification,

            // Module
            $this->global_variable->ModuleType([
                'category-home',
                'category-form',
            ]),

            // Script
            $this->global_variable->ScriptType([
                'category-home-js',
                'category-form-js',
            ]),

            // Route Type
            $this->global_variable->RouteType('category.index'),
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return $this->fileManagement->Logging($this->responseFormatter->successResponse([
            $this->boot(),
            $this->global_variable->PageType('index'),
        ]));
        // return view('template.default.dashboard.blog.tag.home', array_merge(
        //     $this->global_variable->PageType('index'),
        // ));
    }

    public function index_dt()
    {
        $res = $this->dataTables->of($this->category->query())
            ->addColumn('name', function ($tag) {
                return $tag->name;
            })
            ->addColumn('slug', function ($tag) {
                return $tag->slug;
            })
            ->addColumn('parent', function ($tag) {
                return $tag->parent;
            })
            ->addColumn('action', function ($tag) {
                return $tag->id;
            })
            ->removeColumn('id')->addIndexColumn()->make('true');
        return $this->fileManagement->Logging($this->responseFormatter->successResponse($res));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $category_select = $this->category->query()->select(['id', 'name'])->get();
        return $this->fileManagement->Logging($this->responseFormatter->successResponse([
            $this->boot(),
            $this->global_variable->PageType('create'),
            'category_select' => $category_select,
        ]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CategoryFormRequest $request)
    {
        // Error Validation Message to Activity Log
        if (isset($request->validator) && $request->validator->fails()) {
            activity()->causedBy(Auth::user())->performedOn(new Category)->log($request->validator->messages());
        }

        $request->validated();
        $categorydata = $request->only(['name', 'slug', 'parent']);

        DB::beginTransaction();

        try {

            // Store Category Data
            $this->category->StoreCategory($categorydata);
            DB::commit();

            // Activity Log
            activity()->causedBy(Auth::user())->performedOn(new Category)->log($this->translation->category['messages']['store_success']);

            return $this->fileManagement->Logging($this->responseFormatter->successResponse([
                'success' => 'success',
                'title' => $this->translation->notification['success'],
                'content' => $this->translation->category['messages']['store_success'],
            ]));
            // return redirect()->route('tag.index')->with([
            //     'success' => 'success',
            //     'title' => $this->translation->notification['success'],
            //     'content' => $this->translation->category['messages']['store_success'],
            // ]);

        } catch (\Throwable$th) {
            DB::rollBack();
            $message = $th->getMessage();

            if (str_contains($th->getMessage(), 'Duplicate entry')) {
                $message = 'Duplicate entry';
            }

            // Activity Log
            activity()->causedBy(Auth::user())->performedOn(new Category)->log($message);

            return $this->fileManagement->Logging($this->responseFormatter->errorResponse([
                'error' => 'error',
                "title" => $this->translation->notification['error'],
                "content" => $message,
            ]));
            // return redirect()->route('category.create')->with([
            //     'error' => 'error',
            //     "title" => $this->translation->notification['error'],
            //     "content" => $message,
            // ]);
        }
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
        $this->boot();
        $categorydata = $this->category->GetCategoryById($id);

        return $this->fileManagement->Logging($this->responseFormatter->successResponse([
            $this->boot(),
            'category_name' => $categorydata->name,
            'category_slug' => $categorydata->slug,
            'category_parent' => $categorydata->parent,
        ]));
        // return view('template.default.dashboard.blog.tag.form', array_merge(
        //     $this->global_variable->PageType('edit'),
        //     [
        //         'category' => $categorydata,
        //     ]
        // ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CategoryFormRequest $request, $id)
    {
        // Error Validation Message to Activity Log
        if (isset($request->validator) && $request->validator->fails()) {
            activity()->causedBy(Auth::user())->performedOn(new Category)->log($request->validator->messages());
        }

        $request->validated();
        $categorydata = $request->only(['name', 'slug', 'parent']);

        DB::beginTransaction();
        try {
            // Update Tag
            $this->category->UpdateCategory($categorydata, $id);
            DB::commit();

            // Activity Log
            activity()->causedBy(Auth::user())->performedOn(new Category)->log($this->translation->category['messages']['update_success']);

            return $this->fileManagement->Logging($this->responseFormatter->successResponse([
                'success' => 'success',
                'title' => $this->translation->notification['success'],
                'content' => $this->translation->category['messages']['update_success'],
            ]));

            // return redirect()->route('tag.index')->with([
            //     'success' => 'success',
            //     'title' => $this->translation->notification['success'],
            //     'content' => $this->translation->meta['messages']['update_success'],
            // ]);
        } catch (\Throwable$th) {
            DB::rollBack();
            $message = $th->getMessage();

            if (str_contains($th->getMessage(), 'Duplicate entry')) {
                $message = 'Duplicate entry';
            }

            // Activity Log
            activity()->causedBy(Auth::user())->performedOn(new Category)->log($message);

            return $this->fileManagement->Logging($this->responseFormatter->errorResponse([
                'error' => 'error',
                "title" => $this->translation->notification['error'],
                "content" => $message,
            ]));

            // return redirect()->back()->with([
            //     'error' => 'error',
            //     "title" => $this->translation->notification['error'],
            //     "content" => $message,
            // ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {

            // Delete Tag
            $delete = $this->category->DeleteCategory($id);
            DB::commit();

            // check data deleted or not
            if ($delete == true) {
                $status = 'success';
            } else {
                $status = 'error';
            }

            // Activity Log
            activity()->causedBy(Auth::user())->performedOn(new Category)->log($this->translation->category['messages']['delete_success']);

            ///  Return response
            // return response()->json(['status' => $status]);
            return $this->fileManagement->Logging($this->responseFormatter->successResponse([
                'status' => $status,
                'title' => $this->translation->notification['success'],
                'content' => $this->translation->category['messages']['delete_success'],
            ]));
        } catch (\Throwable$th) {
            DB::rollback();
            $message = $th->getMessage();

            // Activity Log
            activity()->causedBy(Auth::user())->performedOn(new Category)->log($message);

            return $this->fileManagement->Logging($this->responseFormatter->errorResponse([
                'error' => 'error',
                "title" => $this->translation->notification['error'],
                "content" => $message,
            ]));

            // return redirect()->back()->with([
            //     'error' => 'error',
            //     "title" => $this->translation->notification['error'],
            //     "content" => $message,
            // ]);

        }
    }
}
