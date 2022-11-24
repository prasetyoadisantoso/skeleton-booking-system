<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\MetaFormRequest;
use App\Services\FileManagement;
use App\Services\GlobalVariable;
use App\Services\GlobalView;
use App\Services\ResponseFormatter;
use App\Services\Translations;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
Use App\Models\Meta;
use Illuminate\Support\Facades\DB;

class MetaController extends Controller
{
    protected $global_view, $global_variable, $translation, $dataTables, $responseFormatter, $fileManagement, $meta;

    public function __construct(
        GlobalView $global_view,
        GlobalVariable $global_variable,
        Translations $translation,
        DataTables $dataTables,
        ResponseFormatter $responseFormatter,
        FileManagement $fileManagement,
        Meta $meta,
    )
    {
        $this->middleware(['permission:setting-sidebar']);
        $this->global_view = $global_view;
        $this->global_variable = $global_variable;
        $this->translation = $translation;
        $this->dataTables = $dataTables;
        $this->responseFormatter = $responseFormatter;
        $this->fileManagement = $fileManagement;
        $this->meta = $meta;
    }

    protected function boot()
    {
        return $this->global_view->RenderView([
            $this->global_variable->TitlePage($this->translation->meta['title']),
            $this->global_variable->SystemLanguage(),
            $this->global_variable->AuthUserName(),
            $this->global_variable->SystemName(),

            // Translations
            $this->translation->sidebar,
            $this->translation->button,
            $this->translation->meta,
            $this->translation->notification,

            // Module
            $this->global_variable->ModuleType([
                'meta-home',
                'meta-form'
            ]),

            // Script
            $this->global_variable->ScriptType([
                'meta-home-js',
                'meta-form-js'
            ]),

            // Route Type
            $this->global_variable->RouteType('meta.index'),
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
        return view('template.default.dashboard.settings.meta.home', array_merge(
            $this->global_variable->PageType('index'),
        ));
    }

    public function index_dt()
    {
        return $this->dataTables->of($this->meta->query())
        ->addColumn('name', function($meta){
            return $meta->name;
        })
        ->addColumn('robot', function($meta){
            return $meta->robot;
        })
        ->addColumn('description', function($meta){
            return $meta->name;
        })
        ->addColumn('keyword', function($meta){
            return $meta->keyword;
        })
        ->addColumn('action', function ($meta) {
            return $meta->id;
        })
        ->removeColumn('id')->addIndexColumn()->make('true');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->boot();
        return view('template.default.dashboard.settings.meta.form', array_merge(
            $this->global_variable->PageType('create'),
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MetaFormRequest $request)
    {
        $request->validated();
        $metadata = $request->only(['name', 'robot', 'description', 'keyword']);

        DB::beginTransaction();
        try {
            $this->meta->StoreMeta($metadata);
            DB::commit();
            return redirect()->route('meta.index')->with([
                'success' => 'success',
                'title' => $this->translation->notification['success'],
                'content' => $this->translation->roles['messages']['update_success'],
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            $message = $th->getMessage();

            if (str_contains($th->getMessage(), 'Duplicate entry')) {
                $message = 'Duplicate entry';
            }

            return redirect()->route('role.create')->with([
                'error' => 'error',
                "title" => $this->translation->notification['error'],
                "content" => $message,
            ]);
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
        $metadata = $this->meta->GetMetaById($id);
        return $this->fileManagement->Logging($this->responseFormatter->successResponse($metadata)->getContent());
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
        $metadata = $this->meta->GetMetaById($id);
        return view('template.default.dashboard.settings.meta.form', array_merge(
            $this->global_variable->PageType('edit'),
            [
                'meta' => $metadata,
            ]
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(MetaFormRequest $request, $id)
    {
        $request->validated();
        $metadata = $request->only(['name', 'robot', 'description', 'keyword']);

        DB::beginTransaction();
        try {$this->meta->UpdateMeta($metadata, $id);
            DB::commit();
            return redirect()->route('meta.index')->with([
                'success' => 'success',
                'title' => $this->translation->notification['success'],
                'content' => $this->translation->meta['messages']['update_success'],
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            $message = $th->getMessage();

            if (str_contains($th->getMessage(), 'Duplicate entry')) {
                $message = 'Duplicate entry';
            }

            return redirect()->back()->with([
                'error' => 'error',
                "title" => $this->translation->notification['error'],
                "content" => $message,
            ]);
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
            $delete = $this->meta->DeleteMeta($id);
            DB::commit();

            // check data deleted or not
            if ($delete == true) {
                $status = 'success';
            } else {
                $status = 'error';
            }

            ///  Return response
            return response()->json(['status' => $status]);
        } catch (\Throwable$th) {
            DB::rollback();
            $message = $th->getMessage();
            return redirect()->back()->with([
                'error' => 'error',
                "title" => $this->translation->notification['error'],
                "content" => $message
            ]);

        }
    }
}
