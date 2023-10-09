<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\product_warehouse;
use App\Models\ProductVariant;
use App\Models\Client;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\SaleReceive;
use App\Models\SaleReceiveDetail;
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

        $purchases = Sale::whereIn('statut', ['ordered', 'partial'])->get(['id', 'Ref']);

        $suppliers = Client::where('deleted_at', '=', null)->get(['id', 'name']);

        $warehouse_locations = WarehouseLocation::where('deleted_at', '=', null)->get(['id', 'name']);

        return response()->json([
            'warehouses' => $warehouses,
            'suppliers' => $suppliers,
            'purchases' => $purchases,
            'warehouse_locations' => $warehouse_locations
        ]);
    }

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



}
