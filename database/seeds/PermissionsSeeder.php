<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       // Insert some stuff
	DB::table('permissions')->insert(
		array([

			'name'  => 'users_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null

		],
		[

			'name'  => 'users_edit',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'record_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'users_delete',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'users_add',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[
			'name'  => 'permissions_edit',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'permissions_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],

		[
			'name'  => 'permissions_delete',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'permissions_add',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'products_delete',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'products_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'barcode_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'products_edit',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'products_add',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'expense_add',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'expense_delete',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'expense_edit',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'expense_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'transfer_delete',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'transfer_add',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'transfer_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'transfer_edit',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'adjustment_delete',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'adjustment_add',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'adjustment_edit',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'adjustment_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Sales_edit',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Sales_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Sales_delete',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Sales_add',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Purchases_edit',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Purchases_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Purchases_delete',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Purchases_add',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Quotations_edit',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Quotations_delete',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Quotations_add',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Quotations_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'payment_sales_delete',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'payment_sales_add',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'payment_sales_edit',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'payment_sales_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Purchase_Returns_delete',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Purchase_Returns_add',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Purchase_Returns_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Purchase_Returns_edit',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Sale_Returns_delete',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Sale_Returns_add',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Sale_Returns_edit',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Sale_Returns_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'payment_purchases_edit',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'payment_purchases_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'payment_purchases_delete',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'payment_purchases_add',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'payment_returns_edit',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'payment_returns_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'payment_returns_delete',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'payment_returns_add',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Customers_edit',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Customers_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Customers_delete',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Customers_add',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'unit',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'currency',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'category',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'backup',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'warehouse',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'brand',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'setting_system',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Warehouse_report',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Reports_quantity_alerts',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Reports_profit',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Reports_suppliers',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Reports_customers',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Reports_purchase',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Reports_sales',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Reports_payments_purchase_Return',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Reports_payments_Sale_Returns',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Reports_payments_Purchases',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Reports_payments_Sales',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Suppliers_delete',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Suppliers_add',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Suppliers_edit',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Suppliers_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Pos_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'product_import',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'customers_import',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Suppliers_import',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],

		//hrm
		[

			'name'  => 'view_employee',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'add_employee',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'edit_employee',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'delete_employee',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'company',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'department',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'designation',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'office_shift',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'attendance',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'leave',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'holiday',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Top_products',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Top_customers',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'shipment',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'users_report',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'stock_report',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Purchase_Receives_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
		[

			'name'  => 'Purchase_Receives_add',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
        ],
        [

			'name'  => 'warehouse_location',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
        [

			'name'  => 'Sales_Receipt_view',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],
        [

			'name'  => 'Sales_Receipt_add',
			'label' => null,
			'description' => null,
			'created_at' => null,
			'updated_at' => null,
			'deleted_at' => null
		],


		)
	);
	}



}
