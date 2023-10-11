<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\product_warehouse;
use App\Models\ProductVariant;
use App\Models\Provider;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\PurchaseReceive;
use App\Models\PurchaseReceiveDetail;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\UserWarehouse;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\utils\helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class PurchaseReceiveController extends Controller
{
    public function index(Request $request)
    {
        // $this->authorizeForUser($request->user('api'), 'view', PurchaseReceive::class);
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = \Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;
        $helpers = new helpers();
        // Filter fields With Params to retrieve
        $param = array(
            0 => 'like',
            1 => 'like',
            2 => '=',
            3 => 'like',
            4 => '=',
            5 => '=',
        );
        $columns = array(
            0 => 'Ref',
            1 => 'statut',
            2 => 'provider_id',
            3 => 'payment_statut',
            4 => 'warehouse_id',
            5 => 'date',
        );
        $data = array();
        $total = 0;

        // Check If User Has Permission View  All Records
        $Purchases = PurchaseReceive::with('facture', 'provider', 'warehouse', 'purchase')
            ->where('deleted_at', '=', null)
            ->where(function ($query) use ($view_records) {
                if (!$view_records) {
                    return $query->where('user_id', '=', Auth::user()->id);
                }
            });

        //Multiple Filter
        $Filtred = $helpers->filter($Purchases, $columns, $param, $request)
        // Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->where('Ref', 'LIKE', "%{$request->search}%")
                        ->orWhere('statut', 'LIKE', "%{$request->search}%")
                        ->orWhere('GrandTotal', $request->search)
                        ->orWhere('payment_statut', 'like', "$request->search")
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('provider', function ($q) use ($request) {
                                $q->where('name', 'LIKE', "%{$request->search}%");
                            });
                        })
                        ->orWhere(function ($query) use ($request) {
                            return $query->whereHas('warehouse', function ($q) use ($request) {
                                $q->where('name', 'LIKE', "%{$request->search}%");
                            });
                        });
                });
            });

       

        $totalRows = $Filtred->count();
        if($perPage == "-1"){
            $perPage = $totalRows;
        }
      
        $Purchases = $Filtred->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        foreach ($Purchases as $Purchase) {

            $item['id'] = $Purchase->id;
            $item['date'] = $Purchase->date;
            $item['Ref'] = $Purchase->Ref;
            $item['warehouse_name'] = $Purchase['warehouse']->name;
            $item['discount'] = $Purchase->discount;
            $item['shipping'] = $Purchase->shipping;
            $item['statut'] = $Purchase->statut;
            $item['provider_id'] = $Purchase['provider']->id;
            $item['provider_name'] = $Purchase['provider']->name;
            $item['provider_email'] = $Purchase['provider']->email;
            $item['provider_tele'] = $Purchase['provider']->phone;
            $item['provider_code'] = $Purchase['provider']->code;
            $item['provider_adr'] = $Purchase['provider']->adresse;
            $item['GrandTotal'] = number_format($Purchase->GrandTotal, 2, '.', '');
            $item['paid_amount'] = number_format($Purchase->paid_amount, 2, '.', '');
            $item['due'] = number_format($item['GrandTotal'] - $item['paid_amount'], 2, '.', '');
            $item['payment_status'] = $Purchase->payment_statut;
            $item['purchase_id'] = $Purchase->purchase_id;
            $item['purchase_ref'] = $Purchase->purchase->Ref;

            $data[] = $item;
        }

        $suppliers = Provider::where('deleted_at', '=', null)->get(['id', 'name']);

         //get warehouses assigned to user
         $user_auth = auth()->user();
         if($user_auth->is_all_warehouses){
             $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);
         }else{
             $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
             $warehouses = Warehouse::where('deleted_at', '=', null)->whereIn('id', $warehouses_id)->get(['id', 'name']);
         } 

        return response()->json([
            'totalRows' => $totalRows,
            'purchases' => $data,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
        ]);
    }

    public function create(Request $request)
    {

        // $this->authorizeForUser($request->user('api'), 'create', Purchase::class);

         //get warehouses assigned to user
         $user_auth = auth()->user();
       
         if($user_auth->is_all_warehouses){
             $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);
         }else{
             $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
             $warehouses = Warehouse::where('deleted_at', '=', null)->whereIn('id', $warehouses_id)->get(['id', 'name']);
         } 

        $purchases = Purchase::whereIn('statut', ['ordered', 'partial'])->get(['id', 'Ref']);

        $suppliers = Provider::where('deleted_at', '=', null)->get(['id', 'name']);

        $warehouse_locations = WarehouseLocation::where('deleted_at', '=', null)->get(['id', 'name']);

        return response()->json([
            'warehouses' => $warehouses,
            'suppliers' => $suppliers,
            'purchases' => $purchases,
            'warehouse_locations' => $warehouse_locations
        ]);
    }

     //------ Store new Purchase -------------\\

    public function store(Request $request)
    {
    //  $this->authorizeForUser($request->user('api'), 'create', Purchase::class);

        request()->validate([
            'supplier_id' => 'required',
            'warehouse_id' => 'required',
        ]);

        

        \DB::transaction(function () use ($request) {
            $order = new PurchaseReceive;

            $order->date = $request->date;
            $order->purchase_id = $request->purchase_id;
            $order->provider_id = $request->supplier_id;
            $order->GrandTotal = $request->GrandTotal;
            $order->warehouse_id = $request->warehouse_id;
            $order->tax_rate = $request->tax_rate;
            $order->TaxNet = $request->TaxNet;
            $order->discount = $request->discount;
            $order->shipping = $request->shipping;
            $order->statut = 'received';
            $order->payment_statut = 'unpaid';
            $order->notes = $request->notes;
            $order->user_id = Auth::user()->id;

            $order->save();

            $data = $request['details'];
            foreach ($data as $key => $value) {
                $unit = Unit::where('id', $value['purchase_unit_id'])->first();
                $orderDetails[] = [
                'purchase_receive_id' => $order->id,
                'quantity' => $value['quantity'],
                'cost' => $value['Unit_cost'],
                'purchase_unit_id' =>  $value['purchase_unit_id'],
                'TaxNet' => $value['tax_percent'],
                'tax_method' => $value['tax_method'],
                'discount' => $value['discount'],
                'discount_method' => $value['discount_Method'],
                'product_id' => $value['product_id'],
                'product_variant_id' => $value['product_variant_id'],
                'total' => $value['subtotal'],
                'imei_number' => $value['imei_number'],
                'expiration_date' => $value['expiration_date'],
                'lot_number' =>  $value['lot_number'],
            ];
                if ($order->statut == "received") {
                    $purchase_detail = PurchaseDetail::find($value['purchase_detail_id']);

                    if($purchase_detail->quantity > $purchase_detail->quantity_receive) {
                    $purchase_detail->quantity_receive = $purchase_detail->quantity_receive + $value['quantity'];
                    if($purchase_detail->quantity_receive == $purchase_detail->quantity) {
                        $purchase_detail->is_receive = 1;
                    }
                    $purchase_detail->save();
                    }
                    if ($value['product_variant_id'] !== null) {
                        if($value['expiration_date'] !== null) {
                            $product_warehouse = product_warehouse::where('deleted_at', '=', null)
                                ->where('warehouse_id', $order->warehouse_id)
                                ->where('product_id', $value['product_id'])
                                ->where('product_variant_id', $value['product_variant_id'])
                                ->whereDate('expiration_date', Carbon::parse($value['expiration_date'])->format('Y-m-d'))
                                ->first();
                            if(!$product_warehouse) {
                                $product_warehouse = product_warehouse::create([
                                    'product_id' => $value['product_id'],
                                    'warehouse_id' => $order->warehouse_id,
                                    'product_variant_id' => $value['product_variant_id'],
                                    'qte' => 0,
                                    'expiration_date' => $value['expiration_date'],
                                    'lot_number' => $value['lot_number'],
                                ]);
                            }
                        } else {
                            $product_warehouse = product_warehouse::where('deleted_at', '=', null)
                                ->where('warehouse_id', $order->warehouse_id)
                                ->where('product_id', $value['product_id'])
                                ->where('product_variant_id', $value['product_variant_id'])
                                ->first();
                        }
                        

                        if ($unit && $product_warehouse) {
                            if ($unit->operator == '/') {
                                $product_warehouse->qte += $value['quantity'] / $unit->operator_value;
                            } else {
                                $product_warehouse->qte += $value['quantity'] * $unit->operator_value;
                            }
                            $product_warehouse->save();
                        }

                    } else {
                        
                        if($value['expiration_date'] !== null) {
                        $product_warehouse = product_warehouse::where('deleted_at', '=', null)
                            ->where('warehouse_id', $order->warehouse_id)
                            ->where('product_id', $value['product_id'])
                            ->whereDate('expiration_date', Carbon::parse($value['expiration_date'])->format('Y-m-d'))
                            ->first();
                        if(!$product_warehouse) {
                            $product_warehouse = product_warehouse::create([
                                'product_id' => $value['product_id'],
                                'warehouse_id' => $order->warehouse_id,
                                'product_variant_id' => $value['product_variant_id'],
                                'qte' => 0,
                                'expiration_date' => $value['expiration_date'],
                                'lot_number' => $value['lot_number'],
                            ]);
                        }
                        }else {
                        $product_warehouse = product_warehouse::where('deleted_at', '=', null)
                            ->where('warehouse_id', $order->warehouse_id)
                            ->where('product_id', $value['product_id'])
                            ->first();
                        }


                        if ($unit && $product_warehouse) {
                            if ($unit->operator == '/') {
                                $product_warehouse->qte += $value['quantity'] / $unit->operator_value;
                            } else {
                                $product_warehouse->qte += $value['quantity'] * $unit->operator_value;
                            }
                            $product_warehouse->save();
                        }
                    }
                }
            }
            PurchaseReceiveDetail::insert($orderDetails);

            $purchase = Purchase::where('id', $order->purchase_id)
            ->with(['details' => function ($query) {
                // Add a query for the 'details' relationship here
                $query->where('is_receive', 0);
            }])
            ->first();
            if(count($purchase->details)) {
                $purchase->statut = 'partial';
                $purchase->save();
            }else{
                $purchase->statut = 'received';
                $purchase->save();
            }

        }, 10);

        return response()->json(['success' => true, 'message' => 'Purchase Receipt Created !!']);
    }

    public function edit(Request $request, $id)
    {

        // $this->authorizeForUser($request->user('api'), 'update', Purchase::class);
        // $role = Auth::user()->roles()->first();
        // $view_records = Role::findOrFail($role->id)->inRole('record_view');
        $Purchase_data = PurchaseReceive::with('details.product.unitPurchase')
            ->where('deleted_at', '=', null)
            ->findOrFail($id);
        $details = array();
        // Check If User Has Permission view All Records
        // if (!$view_records) {
        //     // Check If User->id === Purchase->id
        //     $this->authorizeForUser($request->user('api'), 'check_record', $Purchase_data);
        // }
        if($Purchase_data->purchase_id){
            if(Purchase::where('id', $Purchase_data->purchase_id)->where('deleted_at', null)->first()) {
                $purchase['purchase_id'] = $Purchase_data->purchase_id;
            } else {
                $purchase['purchase_id'] = '';
            }
        } else {
            $purchase['purchase_id'] = '';
        }

        if ($Purchase_data->provider_id) {
            if (Provider::where('id', $Purchase_data->provider_id)->where('deleted_at', '=', null)->first()) {
                $purchase['supplier_id'] = $Purchase_data->provider_id;
            } else {
                $purchase['supplier_id'] = '';
            }
        } else {
            $purchase['supplier_id'] = '';
        }

        if ($Purchase_data->warehouse_id) {
            if (Warehouse::where('id', $Purchase_data->warehouse_id)->where('deleted_at', '=', null)->first()) {
                $purchase['warehouse_id'] = $Purchase_data->warehouse_id;
            } else {
                $purchase['warehouse_id'] = '';
            }
        } else {
            $purchase['warehouse_id'] = '';
        }

        $purchase['date'] = $Purchase_data->date;
        $purchase['tax_rate'] = $Purchase_data->tax_rate;
        $purchase['TaxNet'] = $Purchase_data->TaxNet;
        $purchase['discount'] = $Purchase_data->discount;
        $purchase['shipping'] = $Purchase_data->shipping;
        $purchase['statut'] = $Purchase_data->statut;
        $purchase['notes'] = $Purchase_data->notes;

        $detail_id = 0;
        foreach ($Purchase_data['details'] as $detail) {

            //-------check if detail has purchase_unit_id Or Null
            if($detail->purchase_unit_id !== null){
                $unit = Unit::where('id', $detail->purchase_unit_id)->first();
                $data['no_unit'] = 1;
            }else{
                $product_unit_purchase_id = Product::with('unitPurchase')
                ->where('id', $detail->product_id)
                ->first();
                $unit = Unit::where('id', $product_unit_purchase_id['unitPurchase']->id)->first();
                $data['no_unit'] = 0;
            }

            if ($detail->product_variant_id) {
                $item_product = product_warehouse::where('product_id', $detail->product_id)
                    ->where('deleted_at', '=', null)
                    ->where('product_variant_id', $detail->product_variant_id)
                    ->where('warehouse_id', $Purchase_data->warehouse_id)
                    ->first();

                $productsVariants = ProductVariant::where('product_id', $detail->product_id)
                    ->where('id', $detail->product_variant_id)->first();

                $item_product ? $data['del'] = 0 : $data['del'] = 1;
                $data['code'] = $productsVariants->name . '-' . $detail['product']['code'];
                $data['product_variant_id'] = $detail->product_variant_id;                

                if ($unit && $unit->operator == '/') {
                    $data['stock'] = $item_product ? $item_product->qte * $unit->operator_value : 0;
                } else if ($unit && $unit->operator == '*') {
                    $data['stock'] = $item_product ? $item_product->qte / $unit->operator_value : 0;
                } else {
                    $data['stock'] = 0;
                }

            } else {
                $item_product = product_warehouse::where('product_id', $detail->product_id)
                    ->where('deleted_at', '=', null)->where('product_variant_id', '=', null)
                    ->where('warehouse_id', $Purchase_data->warehouse_id)->first();

                $item_product ? $data['del'] = 0 : $data['del'] = 1;
                $data['product_variant_id'] = null;
                $data['code'] = $detail['product']['code'];
             

                if ($unit && $unit->operator == '/') {
                    $data['stock'] = $item_product ? $item_product->qte * $unit->operator_value : 0;
                } else if ($unit && $unit->operator == '*') {
                    $data['stock'] = $item_product ? $item_product->qte / $unit->operator_value : 0;
                } else {
                    $data['stock'] = 0;
                }

            }

            $data['id'] = $detail->id;
            $data['name'] = $detail['product']['name'];
            $data['detail_id'] = $detail_id += 1;
            $data['quantity'] = $detail->quantity;
            $data['product_id'] = $detail->product_id;
            $data['unitPurchase'] = $unit->ShortName;
            $data['purchase_unit_id'] = $unit->id;
            $data['expiration_date'] = $detail->expiration_date;
            $data['lot_number'] = $detail->lot_number;

            $data['is_imei'] = $detail['product']['is_imei'];
            $data['imei_number'] = $detail->imei_number;

            if ($detail->discount_method == '2') {
                $data['DiscountNet'] = $detail->discount;
            } else {
                $data['DiscountNet'] = $detail->cost * $detail->discount / 100;
            }

            $tax_cost = $detail->TaxNet * (($detail->cost - $data['DiscountNet']) / 100);
            $data['Unit_cost'] = $detail->cost;
            $data['tax_percent'] = $detail->TaxNet;
            $data['tax_method'] = $detail->tax_method;
            $data['discount'] = $detail->discount;
            $data['discount_Method'] = $detail->discount_method;

            if ($detail->tax_method == '1') {
                $data['Net_cost'] = $detail->cost - $data['DiscountNet'];
                $data['taxe'] = $tax_cost;
                $data['subtotal'] = ($data['Net_cost'] * $data['quantity']) + ($tax_cost * $data['quantity']);
            } else {
                $data['Net_cost'] = ($detail->cost - $data['DiscountNet']) / (($detail->TaxNet / 100) + 1);
                $data['taxe'] = $detail->cost - $data['Net_cost'] - $data['DiscountNet'];
                $data['subtotal'] = ($data['Net_cost'] * $data['quantity']) + ($tax_cost * $data['quantity']);
            }

            $details[] = $data;
        }

        // get warehouses assigned to user
        $user_auth = auth()->user();

        $purchases = Purchase::where('id', $Purchase_data->purchase_id)->get(['id', 'Ref']);

        if($user_auth->is_all_warehouses){
            $warehouses = Warehouse::where('deleted_at', '=', null)->get(['id', 'name']);
        }else{
            $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::where('deleted_at', '=', null)->whereIn('id', $warehouses_id)->get(['id', 'name']);
        }
     
        
        $suppliers = Provider::where('deleted_at', '=', null)->get(['id', 'name']);


        return response()->json([
            'details' => $details,
            'purchase' => $purchase,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
            'purchases' => $purchases
        ]);
    }
 
    public function show(Request $request, $id)
    {

        // $this->authorizeForUser($request->user('api'), 'view', Purchase::class);
        $role = Auth::user()->roles()->first();
        $view_records = Role::findOrFail($role->id)->inRole('record_view');
        $purchase = PurchaseReceive::with('details.product.unitPurchase', 'purchase')
            ->where('deleted_at', '=', null)
            ->findOrFail($id);

        $details = array();

        // Check If User Has Permission view All Records
        // if (!$view_records) {
        //     // Check If User->id === purchase->id
        //     $this->authorizeForUser($request->user('api'), 'check_record', $purchase);
        // }

        $purchase_data['Ref'] = $purchase->Ref;
        $purchase_data['purchase_id'] = $purchase->purchase_id;
        $purchase_data['date'] = $purchase->date;
        $purchase_data['statut'] = $purchase->statut;
        $purchase_data['note'] = $purchase->notes;
        $purchase_data['discount'] = $purchase->discount;
        $purchase_data['shipping'] = $purchase->shipping;
        $purchase_data['tax_rate'] = $purchase->tax_rate;
        $purchase_data['TaxNet'] = $purchase->TaxNet;
        $purchase_data['supplier_name'] = $purchase['provider']->name;
        $purchase_data['supplier_email'] = $purchase['provider']->email;
        $purchase_data['supplier_phone'] = $purchase['provider']->phone;
        $purchase_data['supplier_adr'] = $purchase['provider']->adresse;
        $purchase_data['warehouse'] = $purchase['warehouse']->name;
        $purchase_data['GrandTotal'] = number_format($purchase->GrandTotal, 2, '.', '');
        $purchase_data['paid_amount'] = number_format($purchase->paid_amount, 2, '.', '');
        $purchase_data['due'] = number_format($purchase_data['GrandTotal'] - $purchase_data['paid_amount'], 2, '.', '');
        $purchase_data['payment_status'] = $purchase->payment_statut;
        $purchase_data['purchase'] = $purchase->purchase;

        foreach ($purchase['details'] as $detail) {

             //-------check if detail has purchase_unit_id Or Null
             if($detail->purchase_unit_id !== null){
                $unit = Unit::where('id', $detail->purchase_unit_id)->first();
            }else{
                $product_unit_purchase_id = Product::with('unitPurchase')
                ->where('id', $detail->product_id)
                ->first();
                $unit = Unit::where('id', $product_unit_purchase_id['unitPurchase']->id)->first();
            }

            if ($detail->product_variant_id) {

                $productsVariants = ProductVariant::where('product_id', $detail->product_id)
                    ->where('id', $detail->product_variant_id)->first();

                $data['code'] = $productsVariants->name . '-' . $detail['product']['code'];
           
            } else {
                $data['code'] = $detail['product']['code'];
            }
            
            $data['expiration_date'] = $detail->expiration_date ? $detail->expiration_date : 'N/A';
            $data['quantity'] = $detail->quantity;
            $data['total'] = $detail->total;
            $data['name'] = $detail['product']['name'];
            $data['cost'] = $detail->cost;
            $data['cost'] = $detail->cost;
            $data['unit_purchase'] = $unit->ShortName;

            if ($detail->discount_method == '2') {
                $data['DiscountNet'] = $detail->discount;
            } else {
                $data['DiscountNet'] = $detail->cost * $detail->discount / 100;
            }

            $tax_cost = $detail->TaxNet * (($detail->cost - $data['DiscountNet']) / 100);
            $data['Unit_cost'] = $detail->cost;
            $data['discount'] = $detail->discount;

            if ($detail->tax_method == '1') {

                $data['Net_cost'] = $detail->cost - $data['DiscountNet'];
                $data['taxe'] = $tax_cost;
            } else {
                $data['Net_cost'] = ($detail->cost - $data['DiscountNet']) / (($detail->TaxNet / 100) + 1);
                $data['taxe'] = $detail->cost - $data['Net_cost'] - $data['DiscountNet'];
            }

            $data['is_imei'] = $detail['product']['is_imei'];
            $data['imei_number'] = $detail->imei_number;

            $details[] = $data;
        }

        $company = Setting::where('deleted_at', '=', null)->first();

        return response()->json([
            'details' => $details,
            'purchase' => $purchase_data,
            'company' => $company,
        ]);

    }

    public function Purchase_Receive_pdf(Request $request, $id)
    {
        $details = array();
        $helpers = new helpers();
        $Purchase_data = PurchaseReceive::with('details.product.unitPurchase')
            ->where('deleted_at', '=', null)
            ->findOrFail($id);

        $purchase['supplier_name'] = $Purchase_data['provider']->name;
        $purchase['supplier_phone'] = $Purchase_data['provider']->phone;
        $purchase['supplier_adr'] = $Purchase_data['provider']->adresse;
        $purchase['supplier_email'] = $Purchase_data['provider']->email;
        $purchase['TaxNet'] = number_format($Purchase_data->TaxNet, 2, '.', '');
        $purchase['discount'] = number_format($Purchase_data->discount, 2, '.', '');
        $purchase['shipping'] = number_format($Purchase_data->shipping, 2, '.', '');
        $purchase['statut'] = $Purchase_data->statut;
        $purchase['Ref'] = $Purchase_data->Ref;
        $purchase['date'] = $Purchase_data->date;
        $purchase['GrandTotal'] = number_format($Purchase_data->GrandTotal, 2, '.', '');
        $purchase['paid_amount'] = number_format($Purchase_data->paid_amount, 2, '.', '');
        $purchase['due'] = number_format($purchase['GrandTotal'] - $purchase['paid_amount'], 2, '.', '');
        $purchase['payment_status'] = $Purchase_data->payment_statut;

        $detail_id = 0;
        foreach ($Purchase_data['details'] as $detail) {

            //-------check if detail has purchase_unit_id Or Null
            if($detail->purchase_unit_id !== null){
                $unit = Unit::where('id', $detail->purchase_unit_id)->first();
            }else{
                $product_unit_purchase_id = Product::with('unitPurchase')
                ->where('id', $detail->product_id)
                ->first();
                $unit = Unit::where('id', $product_unit_purchase_id['unitPurchase']->id)->first();
            }

            if ($detail->product_variant_id) {

                $productsVariants = ProductVariant::where('product_id', $detail->product_id)
                    ->where('id', $detail->product_variant_id)->first();

                $data['code'] = $productsVariants->name . '-' . $detail['product']['code'];
            } else {
                $data['code'] = $detail['product']['code'];
            }

                $data['detail_id'] = $detail_id += 1;
                $data['quantity'] = number_format($detail->quantity, 2, '.', '');
                $data['total'] = number_format($detail->total, 2, '.', '');
                $data['name'] = $detail['product']['name'];
                $data['unit_purchase'] = $unit->ShortName;
                $data['cost'] = number_format($detail->cost, 2, '.', '');

            if ($detail->discount_method == '2') {
                $data['DiscountNet'] = number_format($detail->discount, 2, '.', '');
            } else {
                $data['DiscountNet'] = number_format($detail->cost * $detail->discount / 100, 2, '.', '');
            }

            $tax_cost = $detail->TaxNet * (($detail->cost - $data['DiscountNet']) / 100);
            $data['Unit_cost'] = number_format($detail->cost, 2, '.', '');
            $data['discount'] = number_format($detail->discount, 2, '.', '');

            if ($detail->tax_method == '1') {

                $data['Net_cost'] = $detail->cost - $data['DiscountNet'];
                $data['taxe'] = number_format($tax_cost, 2, '.', '');
            } else {
                $data['Net_cost'] = ($detail->cost - $data['DiscountNet']) / (($detail->TaxNet / 100) + 1);
                $data['taxe'] = number_format($detail->cost - $data['Net_cost'] - $data['DiscountNet'], 2, '.', '');
            }

            $data['is_imei'] = $detail['product']['is_imei'];
            $data['imei_number'] = $detail->imei_number;

            $details[] = $data;
        }

        $settings = Setting::where('deleted_at', '=', null)->first();
        $symbol = $helpers->Get_Currency_Code();

        $pdf = \PDF::loadView('pdf.purchase_pdf', [
            'symbol' => $symbol,
            'setting' => $settings,
            'purchase' => $purchase,
            'details' => $details,
        ]);
        return $pdf->download('Purchase.pdf');

    }
}
