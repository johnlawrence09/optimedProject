<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\UserWarehouse;
use App\Exports\ProductsExport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\product_warehouse;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\PurchaseDetail;
use App\Models\SaleDetail;
use App\Models\Unit;
use App\Models\Warehouse;
use App\utils\helpers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use \Gumlet\ImageResize;


class ProductsController extends BaseController
{

    //------------ Get ALL Products --------------\\



    public function index(request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Product::class);
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;
        $helpers = new helpers();
        // Filter fields With Params to retrieve
        $columns = array(0 => 'name', 1 => 'category_id', 2 => 'brand_id', 3 => 'code');
        $param = array(0 => 'like', 1 => '=', 2 => '=', 3 => 'like');
        $data = array();

        $products = Product::with('unit', 'category', 'brand')
            ->where('deleted_at', '=', null);

        //Multiple Filter
        $Filtred = $helpers->filter($products, $columns, $param, $request)
        // Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('products.name', 'LIKE', "%{$request->search}%")
                        ->orWhere('products.code', 'LIKE', "%{$request->search}%")
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('category', function ($q) use ($request) {
                                $q->where('name', 'LIKE', "%{$request->search}%");
                            });
                        })
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('brand', function ($q) use ($request) {
                                $q->where('name', 'LIKE', "%{$request->search}%");
                            });
                        });
                });
            });
        $totalRows = $Filtred->count();
        if($perPage == "-1"){
            $perPage = $totalRows;
        }
        $products = $Filtred->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();


        foreach ($products as $product) {
            $item['id'] = $product->id;
            $item['code'] = $product->code;
            $item['name'] = $product->name;
            $item['category'] = $product['category']->name;
            $item['brand'] = $product['brand'] ? $product['brand']->name : 'N/D';
            $item['unit'] = $product['unit']->ShortName;
            $item['price'] = $product->price;

            $product_warehouse_data = product_warehouse::where('product_id', $product->id)
                ->where('deleted_at', '=', null)
                ->get();
            $total_qty = 0;
            foreach ($product_warehouse_data as $product_warehouse) {
                $total_qty += $product_warehouse->qte;
                $item['quantity'] = $total_qty;
            }

            $firstimage = explode(',', $product->image);
            $item['image'] = $firstimage[0];

            $data[] = $item;
        }

        //get warehouses assigned to user
        $user_auth = auth()->user();
        if($user_auth->is_all_warehouses){
            $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);
        }else{
            $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::where('deleted_at', '=', null)->whereIn('id', $warehouses_id)->get(['id', 'name']);
        }


        $categories = Category::where('deleted_at', null)->get(['id', 'name']);
        $brands = Brand::where('deleted_at', null)->get(['id', 'name']);
        return response()->json([
            'warehouses' => $warehouses,
            'categories' => $categories,
            'brands' => $brands,
            'products' => $data,
            'totalRows' => $totalRows,
        ]);
    }

    //-------------- Store new  Product  ---------------\\

    public function store(Request $request)
    {

        $this->authorizeForUser($request->user('api'), 'create', Product::class);

        $existingRecord = DB::table('products')
                            ->where('name', $request['name'])
                            ->where('note', $request['note'])
                            ->where('brand_id', $request['brand_id'])
                            ->Where('deleted_at', null)
                            ->count();

        if($existingRecord < 1) {

                $this->validate($request, [
                    'name' => 'required',
                    'note' => 'required',
                    'Type_barcode' => 'required',
                    'price' => 'required',
                    'category_id' => 'required',
                    'cost' => 'required',
                    'unit_id' => 'required',
                ], [
                    'code.unique' => 'This code already used. Generate Now',
                    'code.required' => 'This field is required',
                ]);

                \DB::transaction(function () use ($request) {

                    //-- Create New Product
                    $Product = new Product;

                    //-- Field Required
                    $Product->name = $request['name'];
                    $Product->Type_barcode = $request['Type_barcode'];
                    $Product->price = $request['price'];
                    $Product->category_id = $request['category_id'];
                    $Product->brand_id = $request['brand_id'];
                    $Product->TaxNet = $request['TaxNet'] ? $request['TaxNet'] : 0;
                    $Product->tax_method = $request['tax_method'];
                    $Product->note = $request['note'];
                    $Product->cost = $request['cost'];
                    $Product->unit_id = $request['unit_id'];
                    $Product->unit_sale_id = $request['unit_sale_id'];
                    $Product->unit_purchase_id = $request['unit_purchase_id'];
                    $Product->stock_alert = $request['stock_alert'] ? $request['stock_alert'] : 0;
                    $Product->is_variant = $request['is_variant'] == 'true' ? 1 : 0;
                    $Product->is_imei = $request['is_imei'] == 'true' ? 1 : 0;
                    $Product->is_expire = $request['is_expire'] == 'true' ? 1 : 0;
                    $Product->mos = $request['is_quotation'] == 'true' ? 1 : 0;
                    $Product->is_warranty = $request['is_warranty'] == 'true' ? 1 : 0;

                    if ($request['images']) {
                        $files = $request['images'];
                        foreach ($files as $file) {
                            $fileData = ImageResize::createFromString(base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $file['path'])));
                            $fileData->resize(200, 200);
                            $name = rand(11111111, 99999999) . $file['name'];
                            $path = public_path() . '/images/products/';
                            $success = file_put_contents($path . $name, $fileData);
                            $images[] = $name;
                        }
                        $filename = implode(",", $images);
                    } else {
                        $filename = 'no-image.png';
                    }

                    $Product->image = $filename;
                    $Product->save();

                    // Store Variants Product
                    if ($request['is_variant'] == 'true') {
                        foreach ($request['variants'] as $variant) {
                            $Product_variants_data[] = [
                                'product_id' => $Product->id,
                                'name' => $variant,
                            ];
                        }
                        ProductVariant::insert($Product_variants_data);
                    }

                    //--Store Product Warehouse
                    $warehouses = Warehouse::where('deleted_at', null)->pluck('id')->toArray();
                    if ($warehouses) {
                        $Product_variants = ProductVariant::where('product_id', $Product->id)
                            ->where('deleted_at', null)
                            ->get();

                        foreach ($warehouses as $warehouse) {
                            if ($request['is_variant'] == 'true') {
                                foreach ($Product_variants as $product_variant) {

                                    $product_warehouse[] = [
                                        'product_id' => $Product->id,
                                        'warehouse_id' => $warehouse,
                                        'product_variant_id' => $product_variant->id,
                                    ];
                                }
                            } else {
                                $product_warehouse[] = [
                                    'product_id' => $Product->id,
                                    'warehouse_id' => $warehouse,
                                ];
                            }
                        }


                        product_warehouse::insert($product_warehouse);
                    }
                    // dd($product_warehouse);

                }, 10);


                return response()->json(['exist' => false]);

        } else {
            return response()->json(['exist' => true]);
        }



    }

    //-------------- Update Product  ---------------\\
    //-----------------------------------------------\\

    public function update(Request $request, $id)
    {

        $existingRecord = DB::table('products')
        ->where('name', $request['name'])
        ->where('note', $request['note'])
        ->where('brand_id', $request['brand_id'])
        ->where('deleted_at', '=', NULL)
        ->Where('id','!=', $id )
        ->count();

    if($existingRecord < 1) {
                        $this->authorizeForUser($request->user('api'), 'update', Product::class);

                            $this->validate($request, [
                                'name' => 'required',
                                'Type_barcode' => 'required',
                                'price' => 'required',
                                'category_id' => 'required',
                                'cost' => 'required',
                                'unit_id' => 'required',
                            ], [
                                'code.unique' => 'This code already used. Generate Now',
                                'code.required' => 'This field is required',
                            ]);

                            \DB::transaction(function () use ($request, $id) {

                                $Product = Product::where('id', $id)
                                    ->where('deleted_at', '=', null)
                                    ->first();

                                //-- Update Product
                                $Product->name = $request['name'];
                                $Product->Type_barcode = $request['Type_barcode'];
                                $Product->price = $request['price'];
                                $Product->category_id = $request['category_id'];
                                $Product->brand_id = $request['brand_id'] == 'null' ?Null: $request['brand_id'];
                                $Product->TaxNet = $request['TaxNet'];
                                $Product->tax_method = $request['tax_method'];
                                $Product->note = $request['note'];
                                $Product->cost = $request['cost'];
                                $Product->unit_id = $request['unit_id'];
                                $Product->unit_sale_id = $request['unit_sale_id'] ? $request['unit_sale_id'] : $request['unit_id'];
                                $Product->unit_purchase_id = $request['unit_purchase_id'] ? $request['unit_purchase_id'] : $request['unit_id'];
                                $Product->stock_alert = $request['stock_alert'];
                                $Product->is_variant = $request['is_variant'] == 'true' ? 1 : 0;
                                $Product->is_imei = $request['is_imei'] == 'true' ? 1 : 0;
                                $Product->is_expire = $request['is_expire'] == 'true' ? 1 : 0;
                                $Product->mos = $request['is_quotation'] == 'true' ? 1 : 0;

                                // Store Variants Product
                                $oldVariants = ProductVariant::where('product_id', $id)
                                    ->where('deleted_at', null)
                                    ->get();

                                $warehouses = Warehouse::where('deleted_at', null)
                                    ->pluck('id')
                                    ->toArray();


                                if ($request['is_variant'] == 'true') {

                                    if ($oldVariants->isNotEmpty()) {
                                        $new_variants_id = [];
                                        $var = 'id';

                                        foreach ($request['variants'] as $new_id) {
                                            if (array_key_exists($var, $new_id)) {
                                                $new_variants_id[] = $new_id['id'];
                                            } else {
                                                $new_variants_id[] = 0;
                                            }
                                        }

                                        foreach ($oldVariants as $key => $value) {
                                            $old_variants_id[] = $value->id;

                                            // Delete Variant
                                            if (!in_array($old_variants_id[$key], $new_variants_id)) {
                                                $ProductVariant = ProductVariant::findOrFail($value->id);
                                                $ProductVariant->deleted_at = Carbon::now();
                                                $ProductVariant->save();

                                                $ProductWarehouse = product_warehouse::where('product_variant_id', $value->id)
                                                    ->update(['deleted_at' => Carbon::now()]);
                                            }
                                        }

                                        foreach ($request['variants'] as $key => $variant) {
                                            if (array_key_exists($var, $variant)) {

                                                $ProductVariantDT = new ProductVariant;

                                                //-- Field Required
                                                $ProductVariantDT->product_id = $variant['product_id'];
                                                $ProductVariantDT->name = $variant['text'];
                                                $ProductVariantDT->qty = $variant['qty'];
                                                $ProductVariantUP['product_id'] = $variant['product_id'];
                                                $ProductVariantUP['name'] = $variant['text'];
                                                $ProductVariantUP['qty'] = $variant['qty'];

                                            } else {
                                                $ProductVariantDT = new ProductVariant;

                                                //-- Field Required
                                                $ProductVariantDT->product_id = $id;
                                                $ProductVariantDT->name = $variant['text'];
                                                $ProductVariantDT->qty = 0.00;
                                                $ProductVariantUP['product_id'] = $id;
                                                $ProductVariantUP['name'] = $variant['text'];
                                                $ProductVariantUP['qty'] = 0.00;
                                            }

                                            if (!in_array($new_variants_id[$key], $old_variants_id)) {
                                                $ProductVariantDT->save();

                                                //--Store Product warehouse
                                                if ($warehouses) {
                                                    $product_warehouse= [];
                                                    foreach ($warehouses as $warehouse) {

                                                        $product_warehouse[] = [
                                                            'product_id' => $id,
                                                            'warehouse_id' => $warehouse,
                                                            'product_variant_id' => $ProductVariantDT->id,
                                                        ];

                                                    }
                                                    product_warehouse::insert($product_warehouse);
                                                }
                                            } else {
                                                ProductVariant::where('id', $variant['id'])->update($ProductVariantUP);
                                            }
                                        }

                                    } else {
                                        $ProducttWarehouse = product_warehouse::where('product_id', $id)
                                            ->update([
                                                'deleted_at' => Carbon::now(),
                                            ]);

                                        foreach ($request['variants'] as $variant) {
                                            $product_warehouse_DT = [];
                                            $ProductVarDT = new ProductVariant;

                                            //-- Field Required
                                            $ProductVarDT->product_id = $id;
                                            $ProductVarDT->name = $variant['text'];
                                            $ProductVarDT->save();

                                            //-- Store Product warehouse
                                            if ($warehouses) {
                                                foreach ($warehouses as $warehouse) {

                                                    $product_warehouse_DT[] = [
                                                        'product_id' => $id,
                                                        'warehouse_id' => $warehouse,
                                                        'product_variant_id' => $ProductVarDT->id,
                                                    ];
                                                }

                                                product_warehouse::insert($product_warehouse_DT);
                                            }
                                        }

                                    }
                                } else {
                                    if ($oldVariants->isNotEmpty()) {
                                        foreach ($oldVariants as $old_var) {
                                            $var_old = ProductVariant::where('product_id', $old_var['product_id'])
                                                ->where('deleted_at', null)
                                                ->first();
                                            $var_old->deleted_at = Carbon::now();
                                            $var_old->save();

                                            $ProducttWarehouse = product_warehouse::where('product_variant_id', $old_var['id'])
                                                ->update([
                                                    'deleted_at' => Carbon::now(),
                                                ]);
                                        }

                                        if ($warehouses) {
                                            foreach ($warehouses as $warehouse) {

                                                $product_warehouse[] = [
                                                    'product_id' => $id,
                                                    'warehouse_id' => $warehouse,
                                                    'product_variant_id' => null,
                                                ];

                                            }
                                            product_warehouse::insert($product_warehouse);
                                        }
                                    }
                                }

                                if ($request['images'] === null) {

                                    if ($Product->image !== null) {
                                        foreach (explode(',', $Product->image) as $img) {
                                            $pathIMG = public_path() . '/images/products/' . $img;
                                            if (file_exists($pathIMG)) {
                                                if ($img != 'no-image.png') {
                                                    @unlink($pathIMG);
                                                }
                                            }
                                        }
                                    }
                                    $filename = 'no-image.png';
                                } else {
                                    if ($Product->image !== null) {
                                        foreach (explode(',', $Product->image) as $img) {
                                            $pathIMG = public_path() . '/images/products/' . $img;
                                            if (file_exists($pathIMG)) {
                                                if ($img != 'no-image.png') {
                                                    @unlink($pathIMG);
                                                }
                                            }
                                        }
                                    }
                                    $files = $request['images'];
                                    foreach ($files as $file) {
                                        $fileData = ImageResize::createFromString(base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $file['path'])));
                                        $fileData->resize(200, 200);
                                        $name = rand(11111111, 99999999) . $file['name'];
                                        $path = public_path() . '/images/products/';
                                        $success = file_put_contents($path . $name, $fileData);
                                        $images[] = $name;
                                    }
                                    $filename = implode(",", $images);
                                }

                                $Product->image = $filename;
                                $Product->save();

                            }, 10);

                            return response()->json(['exist' => false]);



        } else {
            return response()->json(['exist' => true]);
        }

    }

    //-------------- Remove Product  ---------------\\
    //-----------------------------------------------\\

    public function destroy(Request $request, $id)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Product::class);

        \DB::transaction(function () use ($id) {

            $Product = Product::findOrFail($id);
            $Product->deleted_at = Carbon::now();
            $Product->save();

            foreach (explode(',', $Product->image) as $img) {
                $pathIMG = public_path() . '/images/products/' . $img;
                if (file_exists($pathIMG)) {
                    if ($img != 'no-image.png') {
                        @unlink($pathIMG);
                    }
                }
            }

            product_warehouse::where('product_id', $id)->update([
                'deleted_at' => Carbon::now(),
            ]);

            ProductVariant::where('product_id', $id)->update([
                'deleted_at' => Carbon::now(),
            ]);

        }, 10);

        return response()->json(['success' => true]);

    }

    //-------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Product::class);

        \DB::transaction(function () use ($request) {
            $selectedIds = $request->selectedIds;
            foreach ($selectedIds as $product_id) {

                $Product = Product::findOrFail($product_id);
                $Product->deleted_at = Carbon::now();
                $Product->save();

                foreach (explode(',', $Product->image) as $img) {
                    $pathIMG = public_path() . '/images/products/' . $img;
                    if (file_exists($pathIMG)) {
                        if ($img != 'no-image.png') {
                            @unlink($pathIMG);
                        }
                    }
                }

                product_warehouse::where('product_id', $product_id)->update([
                    'deleted_at' => Carbon::now(),
                ]);

                ProductVariant::where('product_id', $product_id)->update([
                    'deleted_at' => Carbon::now(),
                ]);
            }

        }, 10);

        return response()->json(['success' => true]);

    }

    //-------------- Export All Product to EXCEL  ---------------\\

    public function export_Excel(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Product::class);

        return Excel::download(new ProductsExport, 'List_Products.xlsx');
    }

    //--------------  Show Product Details ---------------\\

    public function Get_Products_Details(Request $request, $id)
    {

        $this->authorizeForUser($request->user('api'), 'view', Product::class);

        $Product = Product::where('deleted_at', '=', null)->findOrFail($id);
        //get warehouses assigned to user
        $user_auth = auth()->user();
        if($user_auth->is_all_warehouses){
            $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);
        }else{
            $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::where('deleted_at', '=', null)->whereIn('id', $warehouses_id)->get(['id', 'name']);
        }

        $item['id'] = $Product->id;
        $item['code'] = $Product->code;
        $item['Type_barcode'] = $Product->Type_barcode;
        $item['name'] = $Product->name;
        $item['note'] = $Product->note;
        $item['category'] = $Product['category']->name;
        $item['brand'] = $Product['brand'] ? $Product['brand']->name : 'N/D';
        $item['unit'] = $Product['unit']->ShortName;
        $item['price'] = $Product->price;
        $item['cost'] = $Product->cost;
        $item['stock_alert'] = $Product->stock_alert;
        $item['taxe'] = $Product->TaxNet;
        $item['tax_method'] = $Product->tax_method == '1' ? 'Exclusive' : 'Inclusive';

        if ($Product->is_variant) {
            $item['is_variant'] = 'yes';
            $productsVariants = ProductVariant::where('product_id', $id)
                ->where('deleted_at', null)
                ->get();
            foreach ($productsVariants as $variant) {
                $item['ProductVariant'][] = $variant->name;

                foreach ($warehouses as $warehouse) {
                    $product_warehouse = DB::table('product_warehouse')
                        ->where('product_id', $id)
                        ->where('deleted_at', '=', null)
                        ->where('warehouse_id', $warehouse->id)
                        ->where('product_variant_id', $variant->id)
                        ->select(DB::raw('SUM(product_warehouse.qte) AS sum'))
                        ->first();

                    $war_var['mag'] = $warehouse->name;
                    $war_var['variant'] = $variant->name;
                    $war_var['qte'] = $product_warehouse->sum;
                    $item['CountQTY_variants'][] = $war_var;
                }

            }
        } else {
            $item['is_variant'] = 'no';
            $item['CountQTY_variants'] = [];
        }

        foreach ($warehouses as $warehouse) {
            $product_warehouse_data = DB::table('product_warehouse')
                ->where('deleted_at', '=', null)
                ->where('product_id', $id)
                ->where('warehouse_id', $warehouse->id)
                ->select(DB::raw('SUM(product_warehouse.qte) AS sum'))
                ->first();

            $war['mag'] = $warehouse->name;
            $war['qte'] = $product_warehouse_data->sum;
            $item['CountQTY'][] = $war;
        }

        if ($Product->image != '') {
            foreach (explode(',', $Product->image) as $img) {
                $item['images'][] = $img;
            }
        }

        $data[] = $item;

        return response()->json($data[0]);

    }

    //------------ Get products By Warehouse -----------------\\

    public function Products_by_Warehouse_For_Purchase(request $request, $id)
    {
        $data = [];
        $product_warehouse_data = product_warehouse::with('warehouse', 'product', 'productVariant')
            ->where('warehouse_id', $id)
            ->where('deleted_at', '=', null)
            ->where(function ($query) use ($request) {
                if ($request->stock == '1') {
                    return $query->where('qte', '>', 0);
                }
            })
            ->get();
        foreach ($product_warehouse_data as $product_warehouse) {

            $existingItemIndex = null;
            foreach ($data as $index => $existingItem) {
                if ($existingItem['id'] === $product_warehouse->product_id && $existingItem['product_variant_id'] === $product_warehouse->product_variant_id) {
                    $existingItemIndex = $index;
                    break;
                }
            }
            if ($existingItemIndex !== null) {

                if ($product_warehouse['product']['unitSale']->operator == '/') {
                    $data[$existingItemIndex]['qte_sale'] += $product_warehouse->qte * $product_warehouse['product']['unitSale']->operator_value;
                    $price = $product_warehouse['product']->price / $product_warehouse['product']['unitSale']->operator_value;
                } else {
                    $data[$existingItemIndex]['qte_sale'] += $product_warehouse->qte / $product_warehouse['product']['unitSale']->operator_value;
                    $price = $product_warehouse['product']->price * $product_warehouse['product']['unitSale']->operator_value;
                }

                if ($product_warehouse['product']['unitPurchase']->operator == '/') {
                    $data[$existingItemIndex]['qte_purchase'] += round($product_warehouse->qte * $product_warehouse['product']['unitPurchase']->operator_value, 5);
                } else {
                    $data[$existingItemIndex]['qte_purchase'] += round($product_warehouse->qte / $product_warehouse['product']['unitPurchase']->operator_value, 5);
                }

                if ($product_warehouse['product']->TaxNet !== 0.0) {
                    //Exclusive
                    if ($product_warehouse['product']->tax_method == '1') {
                        $tax_price = $price * $product_warehouse['product']->TaxNet / 100;
                        $data[$existingItemIndex]['Net_price'] += $price + $tax_price;
                        // Inxclusive
                    } else {
                        $data[$existingItemIndex]['Net_price'] += $price;
                    }
                } else {
                    $data[$existingItemIndex]['Net_price'] += $price;
                }

                $data[$existingItemIndex]['qte'] += $product_warehouse->qte;
            } else {
                if ($product_warehouse->product_variant_id) {
                    $item['product_variant_id'] = $product_warehouse->product_variant_id;
                    $item['code'] = $product_warehouse['productVariant']->name . '-' . $product_warehouse['product']->code . ' ('. $product_warehouse['product']->name .')';;
                    $item['Variant'] = $product_warehouse['productVariant']->name;
                } else {
                    $item['product_variant_id'] = null;
                    $item['Variant'] = null;
                    $item['code'] = $product_warehouse['product']->code . ' ('. $product_warehouse['product']->name .')';;
                }

                $item['id'] = $product_warehouse->product_id;
                $item['name'] = $product_warehouse['product']->name;
                $item['barcode'] = $product_warehouse['product']->code;
                $item['Type_barcode'] = $product_warehouse['product']->Type_barcode;
                $firstimage = explode(',', $product_warehouse['product']->image);
                $item['image'] = $firstimage[0];
                $item['is_warranty'] = $product_warehouse['product']->is_warranty;

                if ($product_warehouse['product']['unitSale']->operator == '/') {
                    $item['qte_sale'] = $product_warehouse->qte * $product_warehouse['product']['unitSale']->operator_value;
                    $price = $product_warehouse['product']->price / $product_warehouse['product']['unitSale']->operator_value;
                } else {
                    $item['qte_sale'] = $product_warehouse->qte / $product_warehouse['product']['unitSale']->operator_value;
                    $price = $product_warehouse['product']->price * $product_warehouse['product']['unitSale']->operator_value;
                }

                if ($product_warehouse['product']['unitPurchase']->operator == '/') {
                    $item['qte_purchase'] = round($product_warehouse->qte * $product_warehouse['product']['unitPurchase']->operator_value, 5);
                } else {
                    $item['qte_purchase'] = round($product_warehouse->qte / $product_warehouse['product']['unitPurchase']->operator_value, 5);
                }

                $item['qte'] = $product_warehouse->qte;
                $item['unitSale'] = $product_warehouse['product']['unitSale']->ShortName;
                $item['unitPurchase'] = $product_warehouse['product']['unitPurchase']->ShortName;

                if ($product_warehouse['product']->TaxNet !== 0.0) {
                    //Exclusive
                    if ($product_warehouse['product']->tax_method == '1') {
                        $tax_price = $price * $product_warehouse['product']->TaxNet / 100;
                        $item['Net_price'] = $price + $tax_price;
                        // Inxclusive
                    } else {
                        $item['Net_price'] = $price;
                    }
                } else {
                    $item['Net_price'] = $price;
                }
                $data[] = $item;
            }

            // dd($data);

        }

        return response()->json($data);
    }

    public function Products_by_Warehouse_For_Purchase_Return(request $request, $id)
    {
        $data = [];
        $product_warehouse_data = product_warehouse::with('warehouse', 'product', 'productVariant')
            ->where('warehouse_id', $id)
            ->where('deleted_at', '=', null)
            ->where(function ($query) use ($request) {
                if ($request->stock == '1') {
                    return $query->where('qte', '>', 0);
                }
            })
            ->get();

        foreach ($product_warehouse_data as $product_warehouse) {

            $existingItemIndex = null;
            foreach ($data as $index => $existingItem) {
                if ($existingItem['id'] === $product_warehouse->product_id && $existingItem['product_variant_id'] === $product_warehouse->product_variant_id && $existingItem['expiration_date'] === $product_warehouse->expiration_date ){
                    $existingItemIndex = $index;
                    break;
                }
            }
            if ($existingItemIndex !== null) {

                if ($product_warehouse['product']['unitSale']->operator == '/') {
                    $data[$existingItemIndex]['qte_sale'] += $product_warehouse->qte * $product_warehouse['product']['unitSale']->operator_value;
                    $price = $product_warehouse['product']->price / $product_warehouse['product']['unitSale']->operator_value;
                } else {
                    $data[$existingItemIndex]['qte_sale'] += $product_warehouse->qte / $product_warehouse['product']['unitSale']->operator_value;
                    $price = $product_warehouse['product']->price * $product_warehouse['product']['unitSale']->operator_value;
                }

                if ($product_warehouse['product']['unitPurchase']->operator == '/') {
                    $data[$existingItemIndex]['qte_purchase'] += round($product_warehouse->qte * $product_warehouse['product']['unitPurchase']->operator_value, 5);
                } else {
                    $data[$existingItemIndex]['qte_purchase'] += round($product_warehouse->qte / $product_warehouse['product']['unitPurchase']->operator_value, 5);
                }

                if ($product_warehouse['product']->TaxNet !== 0.0) {
                    //Exclusive
                    if ($product_warehouse['product']->tax_method == '1') {
                        $tax_price = $price * $product_warehouse['product']->TaxNet / 100;
                        $data[$existingItemIndex]['Net_price'] += $price + $tax_price;
                        // Inxclusive
                    } else {
                        $data[$existingItemIndex]['Net_price'] += $price;
                    }
                } else {
                    $data[$existingItemIndex]['Net_price'] += $price;
                }

                $data[$existingItemIndex]['qte'] += $product_warehouse->qte;
            } else {
                if ($product_warehouse->product_variant_id) {
                    $item['product_variant_id'] = $product_warehouse->product_variant_id;
                    $item['code'] = $product_warehouse['productVariant']->name . '-' . $product_warehouse['product']->code . ' ('. $product_warehouse['product']->name .')';
                    $item['Variant'] = $product_warehouse['productVariant']->name;
                    $item['expiration_date'] = null;
                    if($product_warehouse->expiration_date) {
                        $item['code'] = $item['code'] . ' - ' . $product_warehouse->expiration_date;
                    }
                } else {
                    $item['product_variant_id'] = null;
                    $item['Variant'] = null;
                    $item['code'] = $product_warehouse['product']->code . ' ('. $product_warehouse['product']->name .')';
                    $item['expiration_date'] = null;
                    if($product_warehouse->expiration_date) {
                        $item['code'] = $item['code'] . ' - ' . $product_warehouse->expiration_date;
                    }
                }

                $item['id'] = $product_warehouse->product_id;
                $item['name'] = $product_warehouse['product']->name;
                $item['barcode'] = $product_warehouse['product']->code;
                $item['Type_barcode'] = $product_warehouse['product']->Type_barcode;
                $firstimage = explode(',', $product_warehouse['product']->image);
                $item['image'] = $firstimage[0];
                $item['expiration_date'] = $product_warehouse->expiration_date;
                $item['lot_number'] = $product_warehouse->lot_number;

                if ($product_warehouse['product']['unitSale']->operator == '/') {
                    $item['qte_sale'] = $product_warehouse->qte * $product_warehouse['product']['unitSale']->operator_value;
                    $price = $product_warehouse['product']->price / $product_warehouse['product']['unitSale']->operator_value;
                } else {
                    $item['qte_sale'] = $product_warehouse->qte / $product_warehouse['product']['unitSale']->operator_value;
                    $price = $product_warehouse['product']->price * $product_warehouse['product']['unitSale']->operator_value;
                }

                if ($product_warehouse['product']['unitPurchase']->operator == '/') {
                    $item['qte_purchase'] = round($product_warehouse->qte * $product_warehouse['product']['unitPurchase']->operator_value, 5);
                } else {
                    $item['qte_purchase'] = round($product_warehouse->qte / $product_warehouse['product']['unitPurchase']->operator_value, 5);
                }

                $item['qte'] = $product_warehouse->qte;
                $item['unitSale'] = $product_warehouse['product']['unitSale']->ShortName;
                $item['unitPurchase'] = $product_warehouse['product']['unitPurchase']->ShortName;

                if ($product_warehouse['product']->TaxNet !== 0.0) {
                    //Exclusive
                    if ($product_warehouse['product']->tax_method == '1') {
                        $tax_price = $price * $product_warehouse['product']->TaxNet / 100;
                        $item['Net_price'] = $price + $tax_price;
                        // Inxclusive
                    } else {
                        $item['Net_price'] = $price;
                    }
                } else {
                    $item['Net_price'] = $price;
                }
                $data[] = $item;
            }

            // dd($data);

        }

        return response()->json($data);
    }

    public function Products_by_Warehouse_For_Transfer(request $request, $id)
    {
        $data = [];
        $product_warehouse_data = product_warehouse::with('warehouse', 'product', 'productVariant')
            ->where('warehouse_id', $id)
            ->where('deleted_at', '=', null)
            ->where(function ($query) use ($request) {
                if ($request->stock == '1') {
                    return $query->where('qte', '>', 0);
                }
            })
            ->get();

        foreach ($product_warehouse_data as $product_warehouse) {

            $existingItemIndex = null;
            foreach ($data as $index => $existingItem) {
                if ($existingItem['id'] === $product_warehouse->product_id && $existingItem['product_variant_id'] === $product_warehouse->product_variant_id && $existingItem['expiration_date'] === $product_warehouse->expiration_date ){
                    $existingItemIndex = $index;
                    break;
                }
            }
            if ($existingItemIndex !== null) {

                if ($product_warehouse['product']['unitSale']->operator == '/') {
                    $data[$existingItemIndex]['qte_sale'] += $product_warehouse->qte * $product_warehouse['product']['unitSale']->operator_value;
                    $price = $product_warehouse['product']->price / $product_warehouse['product']['unitSale']->operator_value;
                } else {
                    $data[$existingItemIndex]['qte_sale'] += $product_warehouse->qte / $product_warehouse['product']['unitSale']->operator_value;
                    $price = $product_warehouse['product']->price * $product_warehouse['product']['unitSale']->operator_value;
                }

                if ($product_warehouse['product']['unitPurchase']->operator == '/') {
                    $data[$existingItemIndex]['qte_purchase'] += round($product_warehouse->qte * $product_warehouse['product']['unitPurchase']->operator_value, 5);
                } else {
                    $data[$existingItemIndex]['qte_purchase'] += round($product_warehouse->qte / $product_warehouse['product']['unitPurchase']->operator_value, 5);
                }

                if ($product_warehouse['product']->TaxNet !== 0.0) {
                    //Exclusive
                    if ($product_warehouse['product']->tax_method == '1') {
                        $tax_price = $price * $product_warehouse['product']->TaxNet / 100;
                        $data[$existingItemIndex]['Net_price'] += $price + $tax_price;
                        // Inxclusive
                    } else {
                        $data[$existingItemIndex]['Net_price'] += $price;
                    }
                } else {
                    $data[$existingItemIndex]['Net_price'] += $price;
                }

                $data[$existingItemIndex]['qte'] += $product_warehouse->qte;
            } else {
                if ($product_warehouse->product_variant_id) {
                    $item['product_variant_id'] = $product_warehouse->product_variant_id;
                    $item['code'] = $product_warehouse['productVariant']->name . '-' . $product_warehouse['product']->code . ' ('. $product_warehouse['product']->name .')';
                    $item['Variant'] = $product_warehouse['productVariant']->name;
                    if($product_warehouse->expiration_date) {
                        $item['code'] = $item['code'] . ' - ' . $product_warehouse->expiration_date;
                    }
                } else {
                    $item['product_variant_id'] = null;
                    $item['Variant'] = null;
                    $item['code'] = $product_warehouse['product']->code . ' ('. $product_warehouse['product']->name .')';
                    if($product_warehouse->expiration_date) {
                        $item['code'] = $item['code'] . ' - ' . $product_warehouse->expiration_date;
                    }
                }

                $item['id'] = $product_warehouse->product_id;
                $item['name'] = $product_warehouse['product']->name;
                $item['barcode'] = $product_warehouse['product']->code;
                $item['Type_barcode'] = $product_warehouse['product']->Type_barcode;
                $firstimage = explode(',', $product_warehouse['product']->image);
                $item['image'] = $firstimage[0];
                $item['expiration_date'] = $product_warehouse->expiration_date;
                $item['lot_number'] = $product_warehouse->lot_number;

                if ($product_warehouse['product']['unitSale']->operator == '/') {
                    $item['qte_sale'] = $product_warehouse->qte * $product_warehouse['product']['unitSale']->operator_value;
                    $price = $product_warehouse['product']->price / $product_warehouse['product']['unitSale']->operator_value;
                } else {
                    $item['qte_sale'] = $product_warehouse->qte / $product_warehouse['product']['unitSale']->operator_value;
                    $price = $product_warehouse['product']->price * $product_warehouse['product']['unitSale']->operator_value;
                }

                if ($product_warehouse['product']['unitPurchase']->operator == '/') {
                    $item['qte_purchase'] = round($product_warehouse->qte * $product_warehouse['product']['unitPurchase']->operator_value, 5);
                } else {
                    $item['qte_purchase'] = round($product_warehouse->qte / $product_warehouse['product']['unitPurchase']->operator_value, 5);
                }

                $item['qte'] = $product_warehouse->qte;
                $item['unitSale'] = $product_warehouse['product']['unitSale']->ShortName;
                $item['unitPurchase'] = $product_warehouse['product']['unitPurchase']->ShortName;

                if ($product_warehouse['product']->TaxNet !== 0.0) {
                    //Exclusive
                    if ($product_warehouse['product']->tax_method == '1') {
                        $tax_price = $price * $product_warehouse['product']->TaxNet / 100;
                        $item['Net_price'] = $price + $tax_price;
                        // Inxclusive
                    } else {
                        $item['Net_price'] = $price;
                    }
                } else {
                    $item['Net_price'] = $price;
                }
                $data[] = $item;
            }

            // dd($data);

        }

        return response()->json($data);
    }

    public function Products_by_Warehouse_For_Adjustment(request $request, $id)
    {
        $data = [];
        $product_warehouse_data = product_warehouse::with('warehouse', 'product', 'productVariant')
            ->where('warehouse_id', $id)
            ->where('deleted_at', '=', null)
            ->where(function ($query) use ($request) {
                if ($request->stock == '1') {
                    return $query->where('qte', '>', 0);
                }
            })
            ->get();

        foreach ($product_warehouse_data as $product_warehouse) {

            $existingItemIndex = null;
            foreach ($data as $index => $existingItem) {
                if ($existingItem['id'] === $product_warehouse->product_id && $existingItem['product_variant_id'] === $product_warehouse->product_variant_id && $existingItem['expiration_date'] === $product_warehouse->expiration_date ){
                    $existingItemIndex = $index;
                    break;
                }
            }
            if ($existingItemIndex !== null) {

                if ($product_warehouse['product']['unitSale']->operator == '/') {
                    $data[$existingItemIndex]['qte_sale'] += $product_warehouse->qte * $product_warehouse['product']['unitSale']->operator_value;
                    $price = $product_warehouse['product']->price / $product_warehouse['product']['unitSale']->operator_value;
                } else {
                    $data[$existingItemIndex]['qte_sale'] += $product_warehouse->qte / $product_warehouse['product']['unitSale']->operator_value;
                    $price = $product_warehouse['product']->price * $product_warehouse['product']['unitSale']->operator_value;
                }

                if ($product_warehouse['product']['unitPurchase']->operator == '/') {
                    $data[$existingItemIndex]['qte_purchase'] += round($product_warehouse->qte * $product_warehouse['product']['unitPurchase']->operator_value, 5);
                } else {
                    $data[$existingItemIndex]['qte_purchase'] += round($product_warehouse->qte / $product_warehouse['product']['unitPurchase']->operator_value, 5);
                }

                if ($product_warehouse['product']->TaxNet !== 0.0) {
                    //Exclusive
                    if ($product_warehouse['product']->tax_method == '1') {
                        $tax_price = $price * $product_warehouse['product']->TaxNet / 100;
                        $data[$existingItemIndex]['Net_price'] += $price + $tax_price;
                        // Inxclusive
                    } else {
                        $data[$existingItemIndex]['Net_price'] += $price;
                    }
                } else {
                    $data[$existingItemIndex]['Net_price'] += $price;
                }

                $data[$existingItemIndex]['qte'] += $product_warehouse->qte;
            } else {
                if ($product_warehouse->product_variant_id) {
                    $item['product_variant_id'] = $product_warehouse->product_variant_id;
                    $item['code'] = $product_warehouse['productVariant']->name . '-' . $product_warehouse['product']->code . ' ('. $product_warehouse['product']->name .')';
                    $item['Variant'] = $product_warehouse['productVariant']->name;
                    if($product_warehouse->expiration_date) {
                        $item['code'] = $item['code'] . ' - ' . $product_warehouse->expiration_date;
                    }
                } else {
                    $item['product_variant_id'] = null;
                    $item['Variant'] = null;
                    $item['code'] = $product_warehouse['product']->code . ' ('. $product_warehouse['product']->name .')';
                    if($product_warehouse->expiration_date) {
                        $item['code'] = $item['code'] . ' - ' . $product_warehouse->expiration_date;
                    }
                }

                $item['id'] = $product_warehouse->product_id;
                $item['name'] = $product_warehouse['product']->name;
                $item['barcode'] = $product_warehouse['product']->code;
                $item['Type_barcode'] = $product_warehouse['product']->Type_barcode;
                $firstimage = explode(',', $product_warehouse['product']->image);
                $item['image'] = $firstimage[0];
                $item['expiration_date'] = $product_warehouse->expiration_date;
                $item['lot_number'] = $product_warehouse->lot_number;

                if ($product_warehouse['product']['unitSale']->operator == '/') {
                    $item['qte_sale'] = $product_warehouse->qte * $product_warehouse['product']['unitSale']->operator_value;
                    $price = $product_warehouse['product']->price / $product_warehouse['product']['unitSale']->operator_value;
                } else {
                    $item['qte_sale'] = $product_warehouse->qte / $product_warehouse['product']['unitSale']->operator_value;
                    $price = $product_warehouse['product']->price * $product_warehouse['product']['unitSale']->operator_value;
                }

                if ($product_warehouse['product']['unitPurchase']->operator == '/') {
                    $item['qte_purchase'] = round($product_warehouse->qte * $product_warehouse['product']['unitPurchase']->operator_value, 5);
                } else {
                    $item['qte_purchase'] = round($product_warehouse->qte / $product_warehouse['product']['unitPurchase']->operator_value, 5);
                }

                $item['qte'] = $product_warehouse->qte;
                $item['unitSale'] = $product_warehouse['product']['unitSale']->ShortName;
                $item['unitPurchase'] = $product_warehouse['product']['unitPurchase']->ShortName;

                if ($product_warehouse['product']->TaxNet !== 0.0) {
                    //Exclusive
                    if ($product_warehouse['product']->tax_method == '1') {
                        $tax_price = $price * $product_warehouse['product']->TaxNet / 100;
                        $item['Net_price'] = $price + $tax_price;
                        // Inxclusive
                    } else {
                        $item['Net_price'] = $price;
                    }
                } else {
                    $item['Net_price'] = $price;
                }
                $data[] = $item;
            }

            // dd($data);

        }

        return response()->json($data);
    }

    //------------ Get products By Warehouse -----------------\\

    public function Products_by_Warehouse(request $request, $id)
    {
        $data = [];
        $product_warehouse_data = product_warehouse::with('warehouse', 'product', 'productVariant')
            ->where('warehouse_id', $id)
            ->where('deleted_at', '=', null)
            ->where(function ($query) use ($request) {
                if ($request->stock == '1') {
                    return $query->where('qte', '>', 0);
                }
            })
            ->get();


        foreach ($product_warehouse_data as $product_warehouse) {

            if ($product_warehouse->product_variant_id) {
                $item['product_variant_id'] = $product_warehouse->product_variant_id;
                $item['code'] = $product_warehouse['productVariant']->name . '-' . $product_warehouse['product']->code;
                $item['Variant'] = $product_warehouse['productVariant']->name;
            } else {
                $item['product_variant_id'] = null;
                $item['Variant'] = null;
                $item['code'] = $product_warehouse['product']->code;
            }

            $item['id'] = $product_warehouse->product_id;
            $item['name'] = $product_warehouse['product']->name;
            $item['barcode'] = $product_warehouse['product']->code;
            $item['Type_barcode'] = $product_warehouse['product']->Type_barcode;
            $firstimage = explode(',', $product_warehouse['product']->image);
            $item['image'] = $firstimage[0];
            $item['expiration_date'] = $product_warehouse->expiration_date;

            if ($product_warehouse['product']['unitSale']->operator == '/') {
                $item['qte_sale'] = $product_warehouse->qte * $product_warehouse['product']['unitSale']->operator_value;
                $price = $product_warehouse['product']->price / $product_warehouse['product']['unitSale']->operator_value;
            } else {
                $item['qte_sale'] = $product_warehouse->qte / $product_warehouse['product']['unitSale']->operator_value;
                $price = $product_warehouse['product']->price * $product_warehouse['product']['unitSale']->operator_value;
            }

            if ($product_warehouse['product']['unitPurchase']->operator == '/') {
                $item['qte_purchase'] = round($product_warehouse->qte * $product_warehouse['product']['unitPurchase']->operator_value, 5);
            } else {
                $item['qte_purchase'] = round($product_warehouse->qte / $product_warehouse['product']['unitPurchase']->operator_value, 5);
            }

            $item['qte'] = $product_warehouse->qte;
            $item['unitSale'] = $product_warehouse['product']['unitSale']->ShortName;
            $item['unitPurchase'] = $product_warehouse['product']['unitPurchase']->ShortName;

            if ($product_warehouse['product']->TaxNet !== 0.0) {
                //Exclusive
                if ($product_warehouse['product']->tax_method == '1') {
                    $tax_price = $price * $product_warehouse['product']->TaxNet / 100;
                    $item['Net_price'] = $price + $tax_price;
                    // Inxclusive
                } else {
                    $item['Net_price'] = $price;
                }
            } else {
                $item['Net_price'] = $price;
            }

            $data[] = $item;


        }

        return response()->json($data);
    }

     //------------ Get products By Warehouse -----------------\\

     public function Products_by_Warehouse_For_Purchase_Receive(Request $request, $id)
     {
        $data = [];
        $purchase = Purchase::find($id);
        $product_ids = PurchaseDetail::where('is_receive', 0)->where('purchase_id', $id)->pluck('product_id');

        $product_warehouse_data = product_warehouse::with('warehouse', 'product', 'productVariant')
            ->whereIn('product_id', $product_ids)
            ->where('deleted_at', '=', null)
            ->where(function ($query) use ($request) {
                 if ($request->stock == '1') {
                     return $query->where('qte', '>', 0);
                 }
             })->get();

        $purchase_details = PurchaseDetail::where('is_receive', 0)
            ->where('purchase_id', $purchase->id)
            ->with('product', 'productVariant')
            ->get();

        foreach ($purchase_details as $purchase_detail) {
            if($purchase_detail->product_variant_id) {
                $item['product_variant_id'] = $purchase_detail->product_variant_id;
                $item['code'] = $purchase_detail['productVariant']->name . '-' . $purchase_detail['product']->code;
                $item['Variant'] = $purchase_detail['productVariant']->name;
            }else{
                $item['product_variant_id'] = null;
                $item['code'] = $purchase_detail['product']->code;
                $item['Variant'] = null;
            }
            $item['purchase_detail_id'] = $purchase_detail->id;
            $item['id'] = $purchase_detail->product_id;
            $item['name'] = $purchase_detail['product']->name;
            $item['barcode'] = $purchase_detail['product']->code;
            $item['is_expire'] = $purchase_detail['product']->is_expire;
            $item['Type_barcode'] = $purchase_detail['product']->Type_barcode;
            $item['quantity_balance'] = $purchase_detail->quantity - $purchase_detail->quantity_receive;
            $item['order_quantity'] = $purchase_detail->quantity;
            $firstimage = explode(',', $purchase_detail['product']->image);
            $item['image'] = $firstimage[0];

            if ($purchase_detail['product']['unitSale']->operator == '/') {
                $item['qte_sale'] = $purchase_detail->quantity_receive * $purchase_detail['product']['unitSale']->operator_value;
                $price = $purchase_detail['product']->price / $purchase_detail['product']['unitSale']->operator_value;
            } else {
                $item['qte_sale'] = $purchase_detail->quantity_receive / $purchase_detail['product']['unitSale']->operator_value;
                $price = $purchase_detail['product']->price * $purchase_detail['product']['unitSale']->operator_value;
            }

            if ($purchase_detail['product']['unitPurchase']->operator == '/') {
                $item['qte_purchase'] = round($purchase_detail->quantity_receive * $purchase_detail['product']['unitPurchase']->operator_value, 5);
            } else {
                $item['qte_purchase'] = round($purchase_detail->quantity_receive / $purchase_detail['product']['unitPurchase']->operator_value, 5);
            }

            $item['qte'] = $purchase_detail->quantity_receive;
            $item['unitSale'] = $purchase_detail['product']['unitSale']->ShortName;
            $item['unitPurchase'] = $purchase_detail['product']['unitPurchase']->ShortName;

            if ($purchase_detail['product']->TaxNet !== 0.0) {
                //Exclusive
                if ($purchase_detail['product']->tax_method == '1') {
                    $tax_price = $price * $purchase_detail['product']->TaxNet / 100;
                    $item['Net_price'] = $price + $tax_price;
                    // Inxclusive
                } else {
                    $item['Net_price'] = $price;
                }
            } else {
                $item['Net_price'] = $price;
            }

            $data[] = $item;
        }


        return response()->json([
            'warehouse_id'  => $purchase->warehouse_id,
            'provider_id'   => $purchase->provider_id,
            'products'      => $data
        ]);
     }

         //------------ Get products By Warehouse - Sales -----------------\\

         public function Products_by_Sales(Request $request, $id)
         {
            $data = [];
            $sale = Sale::find($id);
            $product_ids = SaleDetail::where('is_receive', 0)->where('sale_id', $id)->pluck('product_id');


            $product_warehouse_data = product_warehouse::with('warehouse', 'product', 'productVariant')
                ->whereIn('product_id', $product_ids)
                ->where('deleted_at', '=', null)
                ->where(function ($query) use ($request) {
                     if ($request->stock == '1') {
                         return $query->where('qte', '>', 0);
                     }
                 })->get();


            $sale_details = SaleDetail::where('is_receive', 0)
                ->where('sale_id', $sale->id)
                ->with('product.ProductWarehouse', 'productVariant')
                ->get();


            foreach ($sale_details as $sale_detail) {

                if($sale_detail->product_variant_id) {
                    $item['product_variant_id'] = $sale_detail->product_variant_id;
                    $item['code'] = $sale_detail['productVariant']->name . '-' . $sale_detail['product']->code;
                    $item['Variant'] = $sale_detail['productVariant']->name;
                }else{
                    $item['product_variant_id'] = null;
                    $item['code'] = $sale_detail['product']->code;
                    $item['Variant'] = null;
                }

                $item['sale_detail_id'] = $sale_detail->id;
                $item['id'] = $sale_detail->product_id;
                $item['name'] = $sale_detail['product']->name;
                $item['barcode'] = $sale_detail['product']->code;
                $item['is_expire'] = $sale_detail['product']->is_expire;
                $item['Type_barcode'] = $sale_detail['product']->Type_barcode;
                $item['quantity_balance'] = $sale_detail->quantity - $sale_detail->quantity_receive;
                $item['order_quantity'] = $sale_detail->quantity;
                $firstimage = explode(',', $sale_detail['product']->image);
                $item['image'] = $firstimage[0];


                if ($sale_detail['product']['unitSale']->operator == '/') {
                    $item['qte_sale'] = $sale_detail->quantity_receive * $sale_detail['product']['unitSale']->operator_value;
                    $price = $sale_detail['product']->price / $sale_detail['product']['unitSale']->operator_value;
                } else {
                    $item['qte_sale'] = $sale_detail->quantity_receive / $sale_detail['product']['unitSale']->operator_value;
                    $price = $sale_detail['product']->price * $sale_detail['product']['unitSale']->operator_value;
                }

                if ($sale_detail['product']['unitPurchase']->operator == '/') {
                    $item['qte_purchase'] = round($sale_detail->quantity_receive * $sale_detail['product']['unitPurchase']->operator_value, 5);
                } else {
                    $item['qte_purchase'] = round($sale_detail->quantity_receive / $sale_detail['product']['unitPurchase']->operator_value, 5);
                }

                foreach($sale_detail['product']['ProductWarehouse'] as $prodWarehouse) {
                    $item['expiraton'] = $prodWarehouse->expiration_date;
                }

                $item['qte'] = $sale_detail->quantity_receive;
                $item['unitSale'] = $sale_detail['product']['unitSale']->ShortName;
                $item['unitPurchase'] = $sale_detail['product']['unitPurchase']->ShortName;

                if ($sale_detail['product']->TaxNet !== 0.0) {
                    //Exclusive
                    if ($sale_detail['product']->tax_method == '1') {
                        $tax_price = $price * $sale_detail['product']->TaxNet / 100;
                        $item['Net_price'] = $price + $tax_price;
                        // Inxclusive
                    } else {
                        $item['Net_price'] = $price;
                    }
                } else {
                    $item['Net_price'] = $price;
                }

                $data[] = $item;


            }
            return response()->json([
                'warehouse_id'  => $sale->warehouse_id,
                'provider_id'   => $sale->client_id,
                'products'      => $data
            ]);
         }



    //------------ Get product By ID -----------------\\

    public function show($id)
    {

        $Product_data = Product::with('unit','ProductWarehouse')
            ->where('id', $id)
            ->where('deleted_at', '=', null)
            ->first();



        $data = [];
        $item['id'] = $Product_data['id'];
        $item['name'] = $Product_data['name'];
        $item['Type_barcode'] = $Product_data['Type_barcode'];
        $item['unit_id'] = $Product_data['unit']->id;
        $item['unit'] = $Product_data['unit']->ShortName;
        $item['purchase_unit_id'] = $Product_data['unitPurchase']->id;
        $item['unitPurchase'] = $Product_data['unitPurchase']->ShortName;
        $item['sale_unit_id'] = $Product_data['unitSale']->id;
        $item['unitSale'] = $Product_data['unitSale']->ShortName;
        $item['tax_method'] = $Product_data['tax_method'];
        $item['tax_percent'] = $Product_data['TaxNet'];

        $item['is_imei'] = $Product_data['is_imei'];
        $item['is_expire'] = $Product_data['is_expire'];
        $item['is_warranty'] = $Product_data['is_warranty'];

        if ($Product_data['unitSale']->operator == '/') {
            $price = $Product_data['price'] / $Product_data['unitSale']->operator_value;

        } else {
            $price = $Product_data['price'] * $Product_data['unitSale']->operator_value;
        }

        if ($Product_data['unitPurchase']->operator == '/') {
            $cost = $Product_data['cost'] / $Product_data['unitPurchase']->operator_value;
        } else {
            $cost = $Product_data['cost'] * $Product_data['unitPurchase']->operator_value;
        }

        $item['Unit_cost'] = $cost;
        $item['fix_cost'] = $Product_data['cost'];
        $item['Unit_price'] = $price;
        $item['fix_price'] = $Product_data['price'];

        if ($Product_data->TaxNet !== 0.0) {
            //Exclusive
            if ($Product_data['tax_method'] == '1') {
                $tax_price = $price * $Product_data['TaxNet'] / 100;
                $tax_cost = $cost * $Product_data['TaxNet'] / 100;

                $item['Total_cost'] = $cost + $tax_cost;
                $item['Total_price'] = $price + $tax_price;
                $item['Net_cost'] = $cost;
                $item['Net_price'] = $price;
                $item['tax_price'] = $tax_price;
                $item['tax_cost'] = $tax_cost;

                // Inxclusive
            } else {
                $item['Total_cost'] = $cost;
                $item['Total_price'] = $price;
                $item['Net_cost'] = $cost / (($Product_data['TaxNet'] / 100) + 1);
                $item['Net_price'] = $price / (($Product_data['TaxNet'] / 100) + 1);
                $item['tax_cost'] = $item['Total_cost'] - $item['Net_cost'];
                $item['tax_price'] = $item['Total_price'] - $item['Net_price'];
            }
        } else {
            $item['Total_cost'] = $cost;
            $item['Total_price'] = $price;
            $item['Net_cost'] = $cost;
            $item['Net_price'] = $price;
            $item['tax_price'] = 0;
            $item['tax_cost'] = 0;
        }

        foreach($Product_data['ProductWarehouse'] as $productExpired) {
            $item['Expiration_date'] = $productExpired->expiration_date;
            $item['lot_number'] = $productExpired->lot_number;

       }

        $data[] = $item;


        // Show Product in the table
        // dd($data);

        return response()->json($data[0]);
    }

    //--------------  Product Quantity Alerts ---------------\\

    public function Products_Alert(request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Stock_Alerts', Product::class);

        $product_warehouse_data = product_warehouse::with('warehouse', 'product', 'productVariant')
            ->join('products', 'product_warehouse.product_id', '=', 'products.id')
            ->whereRaw('qte <= stock_alert')
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('warehouse'), function ($query) use ($request) {
                    return $query->where('warehouse_id', $request->warehouse);
                });
            })->where('product_warehouse.deleted_at', null)->get();

        $data = [];

        if ($product_warehouse_data->isNotEmpty()) {

            foreach ($product_warehouse_data as $product_warehouse) {
                if ($product_warehouse->qte <= $product_warehouse['product']->stock_alert) {
                    if ($product_warehouse->product_variant_id !== null) {
                        $item['code'] = $product_warehouse['productVariant']->name . '-' . $product_warehouse['product']->code;
                    } else {
                        $item['code'] = $product_warehouse['product']->code;
                    }
                    $item['quantity'] = $product_warehouse->qte;
                    $item['name'] = $product_warehouse['product']->name;
                    $item['warehouse'] = $product_warehouse['warehouse']->name;
                    $item['stock_alert'] = $product_warehouse['product']->stock_alert;
                    $data[] = $item;
                }
            }
        }

        $perPage = $request->limit; // How many items do you want to display.
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $collection = collect($data);
        // Get only the items you need using array_slice
        $data_collection = $collection->slice($offSet, $perPage)->values();

        $products = new LengthAwarePaginator($data_collection, count($data), $perPage, Paginator::resolveCurrentPage(), array('path' => Paginator::resolveCurrentPath()));

         //get warehouses assigned to user
         $user_auth = auth()->user();
         if($user_auth->is_all_warehouses){
             $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);
         }else{
             $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
             $warehouses = Warehouse::where('deleted_at', '=', null)->whereIn('id', $warehouses_id)->get(['id', 'name']);
         }

        return response()->json([
            'products' => $products,
            'warehouses' => $warehouses,
        ]);
    }

    //---------------- Show Form Create Product ---------------\\

    public function create(Request $request)
    {

        $this->authorizeForUser($request->user('api'), 'create', Product::class);

        $categories = Category::where('deleted_at', null)->get(['id', 'name']);
        $brands = Brand::where('deleted_at', null)->get(['id', 'name']);
        $units = Unit::where('deleted_at', null)->where('base_unit', null)->get();
        return response()->json([
            'categories' => $categories,
            'brands' => $brands,
            'units' => $units,
        ]);

    }

    //---------------- Show Elements Barcode ---------------\\

    public function Get_element_barcode(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'barcode', Product::class);

         //get warehouses assigned to user
         $user_auth = auth()->user();
         if($user_auth->is_all_warehouses){
             $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);
         }else{
             $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
             $warehouses = Warehouse::where('deleted_at', '=', null)->whereIn('id', $warehouses_id)->get(['id', 'name']);
         }

        return response()->json(['warehouses' => $warehouses]);

    }

    //---------------- Show Form Edit Product ---------------\\

    public function edit(Request $request, $id)
    {

        $this->authorizeForUser($request->user('api'), 'update', Product::class);

        $Product = Product::where('deleted_at', '=', null)->findOrFail($id);

        $item['id'] = $Product->id;
        $item['code'] = $Product->code;
        $item['Type_barcode'] = $Product->Type_barcode;
        $item['name'] = $Product->name;
        if ($Product->category_id) {
            if (Category::where('id', $Product->category_id)
                ->where('deleted_at', '=', null)
                ->first()) {
                $item['category_id'] = $Product->category_id;
            } else {
                $item['category_id'] = '';
            }
        } else {
            $item['category_id'] = '';
        }

        if ($Product->brand_id) {
            if (Brand::where('id', $Product->brand_id)
                ->where('deleted_at', '=', null)
                ->first()) {
                $item['brand_id'] = $Product->brand_id;
            } else {
                $item['brand_id'] = '';
            }
        } else {
            $item['brand_id'] = '';
        }

        if ($Product->unit_id) {
            if (Unit::where('id', $Product->unit_id)
                ->where('deleted_at', '=', null)
                ->first()) {
                $item['unit_id'] = $Product->unit_id;
            } else {
                $item['unit_id'] = '';
            }

            if (Unit::where('id', $Product->unit_sale_id)
                ->where('deleted_at', '=', null)
                ->first()) {
                $item['unit_sale_id'] = $Product->unit_sale_id;
            } else {
                $item['unit_sale_id'] = '';
            }

            if (Unit::where('id', $Product->unit_purchase_id)
                ->where('deleted_at', '=', null)
                ->first()) {
                $item['unit_purchase_id'] = $Product->unit_purchase_id;
            } else {
                $item['unit_purchase_id'] = '';
            }

        } else {
            $item['unit_id'] = '';
        }

        $item['tax_method'] = $Product->tax_method;
        $item['price'] = $Product->price;
        $item['cost'] = $Product->cost;
        $item['stock_alert'] = $Product->stock_alert;
        $item['TaxNet'] = $Product->TaxNet;
        $item['note'] = $Product->note ? $Product->note : '';
        $item['images'] = [];
        if ($Product->image != '' && $Product->image != 'no-image.png') {
            foreach (explode(',', $Product->image) as $img) {
                $path = public_path() . '/images/products/' . $img;
                if (file_exists($path)) {
                    $itemImg['name'] = $img;
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $itemImg['path'] = 'data:image/' . $type . ';base64,' . base64_encode($data);

                    $item['images'][] = $itemImg;
                }
            }
        } else {
            $item['images'] = [];
        }
        if ($Product->is_variant) {
            $item['is_variant'] = true;
            $productsVariants = ProductVariant::where('product_id', $id)
                ->where('deleted_at', null)
                ->get();
            foreach ($productsVariants as $variant) {
                $variant_item['id'] = $variant->id;
                $variant_item['text'] = $variant->name;
                $variant_item['qty'] = $variant->qty;
                $variant_item['product_id'] = $variant->product_id;
                $item['ProductVariant'][] = $variant_item;
            }
        } else {
            $item['is_variant'] = false;
            $item['ProductVariant'] = [];
        }

        $item['is_imei'] = $Product->is_imei?true:false;
        $item['is_expire'] = $Product->is_expire?true:false;
        $item['is_quotation'] = $Product->mos?true:false;
        $item['is_warranty'] = $Product->is_warranty?true:false;

        $data = $item;
        $categories = Category::where('deleted_at', null)->get(['id', 'name']);
        $brands = Brand::where('deleted_at', null)->get(['id', 'name']);

        $product_units = Unit::where('id', $Product->unit_id)
                              ->orWhere('base_unit', $Product->unit_id)
                              ->where('deleted_at', null)
                              ->get();


        $units = Unit::where('deleted_at', null)
            ->where('base_unit', null)
            ->get();


        return response()->json([
            'product' => $data,
            'categories' => $categories,
            'brands' => $brands,
            'units' => $units,
            'units_sub' => $product_units,
        ]);

    }

    // import Products
    public function import_products(Request $request)
    {
        try {
            \DB::transaction(function () use ($request) {
                $file_upload = $request->file('products');
                $ext = pathinfo($file_upload->getClientOriginalName(), PATHINFO_EXTENSION);
                if ($ext != 'csv') {
                    return response()->json([
                        'msg' => 'must be in csv format',
                        'status' => false,
                    ]);
                } else {
                    $data = array();
                    $rowcount = 0;
                    if (($handle = fopen($file_upload, "r")) !== false) {

                        $max_line_length = defined('MAX_LINE_LENGTH') ? MAX_LINE_LENGTH : 10000;
                        $header = fgetcsv($handle, $max_line_length);
                        $header_colcount = count($header);
                        while (($row = fgetcsv($handle, $max_line_length)) !== false) {
                            $row_colcount = count($row);
                            if ($row_colcount == $header_colcount) {
                                $entry = array_combine($header, $row);
                                $data[] = $entry;
                            } else {
                                return null;
                            }
                            $rowcount++;
                        }
                        fclose($handle);
                    } else {
                        return null;
                    }


                    $warehouses = Warehouse::where('deleted_at', null)->pluck('id')->toArray();

                    //-- Create New Product
                    foreach ($data as $key => $value) {
                        $category = Category::firstOrCreate(['name' => $value['category']]);
                        $category_id = $category->id;

                        $unit = Unit::where(['ShortName' => $value['unit']])
                            ->orWhere(['name' => $value['unit']])->first();
                        $unit_id = $unit->id;

                        if ($value['brand'] != 'N/A' && $value['brand'] != '') {
                            $brand = Brand::firstOrCreate(['name' => $value['brand']]);
                            $brand_id = $brand->id;
                        } else {
                            $brand_id = null;
                        }
                        $Product = new Product;
                        $Product->name = $value['name'] == '' ? null : $value['name'];
                        $Product->code = $value['code'] == '' ? '11111111' : $value['code'];
                        $Product->Type_barcode = 'CODE128';
                        $Product->price = $value['price'];
                        $Product->cost = $value['cost'];
                        $Product->category_id = $category_id;
                        $Product->brand_id = $brand_id;
                        $Product->TaxNet = 0;
                        $Product->tax_method = 1;
                        $Product->note = $value['note'] ? $value['note'] : '';
                        $Product->unit_id = $unit_id;
                        $Product->unit_sale_id = $unit_id;
                        $Product->unit_purchase_id = $unit_id;
                        $Product->stock_alert = $value['stock_alert'] ? $value['stock_alert'] : 0;
                        $Product->is_variant = 0;
                        $Product->image = 'no-image.png';
                        $Product->save();

                        if ($warehouses) {
                            foreach ($warehouses as $warehouse) {
                                $product_warehouse[] = [
                                    'product_id' => $Product->id,
                                    'warehouse_id' => $warehouse,
                                ];
                            }
                        }
                    }
                    if ($warehouses) {
                        product_warehouse::insert($product_warehouse);
                    }
                }
            }, 10);
            return response()->json([
                'status' => true,
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'msg' => 'error',
                'errors' => $e->errors(),
            ]);
        }

    }

    // Generate_random_code
    public function generate_random_code($value_code)
    {
        if($value_code == ''){
            $gen_code = substr(number_format(time() * mt_rand(), 0, '', ''), 0, 8);
            $this->check_code_exist($gen_code);
        }else{
            $this->check_code_exist($value_code);
        }
    }


    // check_code_exist
    public function check_code_exist($code)
    {
        $check_code = Product::where('code', $code)->first();
        if ($check_code) {
            $this->generate_random_code($code);
        } else {
            return $code;
        }



    }




}
