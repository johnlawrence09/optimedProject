<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\utils\helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use DB;

class CategorieController extends BaseController
{

    //-------------- Get All Categories ---------------\\

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Category::class);
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;
        $helpers = new helpers();

        $categories = Category::where('deleted_at', '=', null)

        // Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('name', 'LIKE', "%{$request->search}%")
                        ->orWhere('code', 'LIKE', "%{$request->search}%");
                });
            });
        $totalRows = $categories->count();
        if($perPage == "-1"){
            $perPage = $totalRows;
        }
        $categories = $categories->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        return response()->json([
            'categories' => $categories,
            'totalRows' => $totalRows,
        ]);
    }

    //-------------- Store New Category ---------------\\

    public function store(Request $request)
    {

        $this->authorizeForUser($request->user('api'), 'create', Category::class);

        $existingRecord = DB::table('categories')
        ->where('name', $request['name'])
        ->Where('deleted_at', null)
        ->count();

        if($existingRecord < 1) {
            request()->validate([
                'name' => 'required'
            ]);

            $url = '';
            if($request->file('img')) {
                $file           = $request->file('img');
                $file_folder    = 'images/Category';
                $file_path      = Storage::disk('s3')->put($file_folder, $file);

                Storage::disk('s3')->setVisibility($file_path, 'public');
                $url            = Storage::disk('s3')->url($file_path);
            } else {
                $url = 'no-image.png';
            }

            Category::create([
                'name' => $request['name'],
                'image' => $url
            ]);
            return response()->json(['exist' => false]);
        } else {
            return response()->json(['exist' => true]);
        }

    }

     //------------ function show -----------\\

    public function show($id){
        //

    }

    //-------------- Update Category ---------------\\

    public function update(Request $request, $id)
    {

        $this->authorizeForUser($request->user('api'), 'update', Category::class);

        $existingRecord = DB::table('categories')
            ->where('name', $request['name'])
            ->where('deleted_at', '=', NULL)
            ->where('id', '!=', $id)
            ->count();

        if($existingRecord < 1) {
            request()->validate([
                'name' => 'required',
            ]);

            Category::whereId($id)->update([
                'name' => $request['name'],
            ]);
            return response()->json(['exist' => false]);
        }else {
            return response()->json(['exist' => true]);
        }



    }

    //-------------- Remove Category ---------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Category::class);

        Category::whereId($id)->update([
            'deleted_at' => Carbon::now(),
        ]);
        return response()->json(['success' => true]);
    }

    //-------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Category::class);
        $selectedIds = $request->selectedIds;

        foreach ($selectedIds as $category_id) {
            Category::whereId($category_id)->update([
                'deleted_at' => Carbon::now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

}
