<?php

namespace App\Http\Controllers;

use App\Models\Mapping;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\utils\helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MappingsController extends Controller
{
     //------------ GET ALL Mappings -----------\\

     public function index(Request $request)
     {
         // How many items do you want to display.
         $perPage = $request->limit;
         $pageStart = \Request::get('page', 1);
         // Start displaying items from this number;
         $offSet = ($pageStart * $perPage) - $perPage;
         $order = $request->SortField;
         $dir = $request->SortType;
         $helpers = new helpers();

         $mappings = Mapping::with(['product', 'warehouse', 'warehouse_location'])->where('deleted_at', '=', null)

         // Search With Multiple Param
             ->where(function ($query) use ($request) {
                 return $query->when($request->filled('search'), function ($query) use ($request) {
                     return $query->orWhereHas('product', function ($query) use ($request) {
                        $query->where('name', 'LIKE', "%{$request->search}%")
                        ->orWhere('code', 'LIKE', "%{$request->search}%");
                    })->orWhereHas('warehouse', function ($query) use ($request) {
                        $query->where('name', 'LIKE', "%{$request->search}%");
                    })->orWhereHas('warehouse_location', function ($query) use ($request) {
                        $query->where('name', 'LIKE', "%{$request->search}%");
                    });
                 });
             });
         $totalRows = $mappings->count();
         $products = Product::where('deleted_at', '=', null)->get();
         $warehouses = Warehouse::where('deleted_at', '=', null)->get();
         $warehouse_locations = WarehouseLocation::where('deleted_at', '=', null)->get();
         if($perPage == "-1"){
             $perPage = $totalRows;
         }
         $mappings = $mappings->offset($offSet)
             ->limit($perPage)
             ->orderBy($order, $dir)
             ->get();

         return response()->json([
             'mappings' => $mappings,
             'totalRows' => $totalRows,
             'products' => $products,
             'warehouses' => $warehouses,
             'warehouse_locations' => $warehouse_locations,
         ]);

     }

     //---------------- STORE NEW Mapping -------------\\
     public function store(Request $request)
     {


         $existingRecord = DB::table('mappings')
         ->where('product_id', $request['product_id'])
         ->where('warehouse_id', $request['warehouse_id'])
         ->where('warehouse_location_id', $request['warehouse_location_id'])
         ->Where('deleted_at', null)
         ->count();


         if($existingRecord < 1) {
             request()->validate([
                 'product_id' => 'required',
                 'warehouse_id' => 'required',
                 'warehouse_location_id' => 'required',
             ]);

             DB::transaction(function () use ($request) {

                 $Mapping = new Mapping;

                 $Mapping->product_id = $request['product_id'];
                 $Mapping->warehouse_id = $request['warehouse_id'];
                 $Mapping->warehouse_location_id = $request['warehouse_location_id'];
                 $Mapping->save();

             }, 10);

             return response()->json(['exist' => false]);
         } else {
             return response()->json(['exist' => true]);
         }


     }

     //------------ function show -----------\\

     public function show($id){
        //

    }

    //---------------- UPDATE Brand -------------\\

    public function update(Request $request, $id)
    {

        $this->authorizeForUser($request->user('api'), 'update', Brand::class);

        $existingRecord = DB::table('mappings')
        ->where('product_id', $request['product_id'])
        ->where('warehouse_id', $request['warehouse_id'])
        ->where('warehouse_location_id', $request['warehouse_location_id'])
        ->where('deleted_at', '=', NULL)
        ->where('id', '!=', $id)
        ->count();

        if($existingRecord < 1) {
           request()->validate([
               'product_id' => 'required',
               'warehouse_id' => 'required',
               'warehouse_location_id' => 'required',
           ]);
           DB::transaction(function () use ($request, $id) {
               $Brand = Mapping::findOrFail($id);
               $currentImage = $Brand->image;


               Mapping::whereId($id)->update([
                   'product_id' => $request['product_id'],
                   'warehouse_id' => $request['warehouse_id'],
                   'warehouse_location_id' => $request['warehouse_location_id'],
               ]);

           }, 10);

           return response()->json(['exist' => false]);
        } else {
           return response()->json(['exist' => true]);
        }


    }

    //------------ Delete Brand -----------\\

    public function destroy(Request $request, $id)
    {

        Mapping::whereId($id)->update([
            'deleted_at' => Carbon::now(),
        ]);
        return response()->json(['success' => true]);
    }

    //-------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {

        $selectedIds = $request->selectedIds;
        foreach ($selectedIds as $mapping_id) {
            Mapping::whereId($mapping_id)->update([
                'deleted_at' => Carbon::now(),
            ]);
        }
        return response()->json(['success' => true]);

    }

}
