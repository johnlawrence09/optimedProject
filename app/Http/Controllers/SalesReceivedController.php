<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\product_warehouse;
use App\Models\ProductVariant;
use App\Models\Client;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SalesReceive;
use App\Models\SalesReceiveDetail;
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

class SalesReceivedController extends Controller
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
      $Sales = SaleReceive::with('facture', 'provider', 'warehouse', 'sale')
      ->where('deleted_at', '=', null)
      ->where(function ($query) use ($view_records) {
          if (!$view_records) {
              return $query->where('user_id', '=', Auth::user()->id);
          }
      });

      //Multiple Filter
      $Filtred = $helpers->filter($Sales, $columns, $param, $request)
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
        
          $Sales = $Filtred->offset($offSet)
              ->limit($perPage)
              ->orderBy($order, $dir)
              ->get();
          
              foreach ($Sales as $Sale) {

                $item['id'] = $Sale->id;
                $item['date'] = $Sale->date;
                $item['Ref'] = $Sale->Ref;
                $item['warehouse_name'] = $Sale['warehouse']->name;
                $item['discount'] = $Sale->discount;
                $item['shipping'] = $Sale->shipping;
                $item['statut'] = $Sale->statut;
                $item['provider_id'] = $Sale['provider']->id;
                $item['provider_name'] = $Sale['provider']->name;
                $item['provider_email'] = $Sale['provider']->email;
                $item['provider_tele'] = $Sale['provider']->phone;
                $item['provider_code'] = $Sale['provider']->code;
                $item['provider_adr'] = $Sale['provider']->adresse;
                $item['GrandTotal'] = number_format($Sale->GrandTotal, 2, '.', '');
                $item['paid_amount'] = number_format($Sale->paid_amount, 2, '.', '');
                $item['due'] = number_format($item['GrandTotal'] - $item['paid_amount'], 2, '.', '');
                $item['payment_status'] = $Sale->payment_statut;
                $item['sales_id'] = $Sale->sales_id;
                $item['sales_ref'] = $Sale->sale->Ref;
    
                $data[] = $item;
                
            }

        $customers = Client::where('deleted_at', '=', null)->get(['id', 'name']);

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
            'sales' => $data,
            'customers' => $customers,
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

        $sales = Sale::whereIn('statut', ['ordered', 'partial'])->whereNull('deleted_at')->get(['id', 'Ref']);
       

        $client = Client::where('deleted_at', '=', null)->get(['id', 'name']);
        
        $warehouse_locations = WarehouseLocation::where('deleted_at', '=', null)->get(['id', 'name']);

        return response()->json([
            'warehouses' => $warehouses,
            'suppliers' => $client,
            'purchases' => $sales,
            'warehouse_locations' => $warehouse_locations
        ]);
    }

    public function store(Request $request)
    {

    //  $this->authorizeForUser($request->user('api'), 'create', Purchase::class);
        request()->validate([
            'client_id' => 'required',
            'warehouse_id' => 'required',
        ]);

        

        \DB::transaction(function () use ($request) {
            $order = new SalesReceive;

            $order->date = $request->date;
            $order->sale_id = $request->sale_id;
            $order->client_id = $request->client_id;
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
                $unit = Unit::where('id', $value['sale_unit_id'])->first();
                $orderDetails[] = [
                'sales_receive_id' => $order->id,
                'quantity' => $value['quantity'],
                'cost' => $value['Unit_cost'],
                'sale_unit_id' =>  $value['sale_unit_id'],
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
                    $sale_detail = SaleDetail::find($value['sale_detail_id']);
                    if($sale_detail->quantity > $sale_detail->quantity_receive) {
                    $sale_detail->quantity_receive = $sale_detail->quantity_receive + $value['quantity'];
                    if($sale_detail->quantity_receive == $sale_detail->quantity) {
                        $sale_detail->is_receive = 1;
                    }
                    $sale_detail->save();
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
                                $product_warehouse->qte -= $value['quantity'] / $unit->operator_value;
                            } else {
                                $product_warehouse->qte -= $value['quantity'] * $unit->operator_value;
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
                                $product_warehouse->qte -= $value['quantity'] / $unit->operator_value;
                            } else {
                                $product_warehouse->qte -= $value['quantity'] * $unit->operator_value;
                            }
                            $product_warehouse->save();
                        }
                    }
                }
            }
            
            SalesReceiveDetail::insert($orderDetails);
           
            $sale = Sale::where('id', $order->sale_id)
            ->with(['details' => function ($query) {
                // Add a query for the 'details' relationship here
                $query->where('is_receive', 0);
            }])
            ->first();
            if(count($sale->details)) {
                $sale->statut = 'partial';
                $sale->save();
            }else{
                $sale->statut = 'received';
                $sale->save();
            }

        }, 10);

        return response()->json(['success' => true, 'message' => 'Sales Receipt Created !!']);
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
        $sale = SalesReceive::with('details.product.unitPurchase', 'sale')
            ->where('deleted_at', '=', null)
            ->findOrFail($id);
        $details = array();
  
       
        
        // Check If User Has Permission view All Records
        // if (!$view_records) {
        //     // Check If User->id === purchase->id
        //     $this->authorizeForUser($request->user('api'), 'check_record', $purchase);
        // }

        $sale_data['Ref'] = $sale->Ref;
        $sale_data['sale_id'] = $sale->sale_id;
        $sale_data['date'] = $sale->date;
        $sale_data['statut'] = $sale->statut;
        $sale_data['note'] = $sale->notes;
        $sale_data['discount'] = $sale->discount;
        $sale_data['shipping'] = $sale->shipping;
        $sale_data['tax_rate'] = $sale->tax_rate;
        $sale_data['TaxNet'] = $sale->TaxNet;
        $sale_data['client_name'] = $sale['client']->name;
        $sale_data['client_email'] = $sale['client']->email;
        $sale_data['client_phone'] = $sale['client']->phone;
        $sale_data['client_adr'] = $sale['client']->adresse;
        $sale_data['warehouse'] = $sale['warehouse']->name;
        $sale_data['GrandTotal'] = number_format($sale->GrandTotal, 2, '.', '');
        $sale_data['paid_amount'] = number_format($sale->paid_amount, 2, '.', '');
        $sale_data['due'] = number_format($sale_data['GrandTotal'] - $sale_data['paid_amount'], 2, '.', '');
        $sale_data['payment_status'] = $sale->payment_statut;
        $sale_data['sale'] = $sale->sale;

        foreach ($sale['details'] as $detail) {

             //-------check if detail has purchase_unit_id Or Null
             if($detail->sale_unit_id !== null){
                $unit = Unit::where('id', $detail->sale_unit_id)->first();
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
            $data['unit_sale'] = $unit->ShortName;

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
            'sale' => $sale_data,
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
