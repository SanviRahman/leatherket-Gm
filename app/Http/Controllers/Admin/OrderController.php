<?php

namespace App\Http\Controllers\Admin;

use App\City;
use App\Courier;
use App\Customer;
use App\Http\Controllers\Controller;
use App\Invoice;
use App\Notification;
use App\Order;
use App\OrderProducts;
use App\Payment; 
use App\PaymentType;
use App\Product;
use App\Setting;
use App\Stock;
use App\Services\OrderStockService;
use App\Store;
use App\User;
use App\Zone;
use App\PaymentCompelte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Yajra\Datatables\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    // Show Orders Page
    public function index()
    {
        $status = 'all';
        return view('admin.order.index', compact('status'));
    }

    // Create Order Page
    public function create()
    {
        $unique = $this->uniqueID();
        $couriers = Courier::all();
        return view('admin.order.create', compact('unique', 'couriers'));
    }

    // Order Store
    public function store(Request $request)
    {
        $order = new Order();
        $order->invoiceID = $this->uniqueID();
        $order->store_id = $request['data']['storeID'];
        $order->subTotal = $request['data']['total'];
        $order->deliveryCharge = $request['data']['deliveryCharge'];
        $order->discountCharge = $request['data']['discountCharge'];
        $order->payment_type_id = $request['data']['paymentTypeID'];
        $order->payment_id = $request['data']['paymentID'];
        $order->paymentAmount = $request['data']['paymentAmount'];
        $order->paymentAgentNumber = $request['data']['paymentAgentNumber'];
        $order->orderDate = $request['data']['orderDate'];
        $order->courier_id = $request['data']['courierID'];
        $order->city_id = $request['data']['cityID'];
        $order->zone_id = $request['data']['zoneID'];
        $products = $request['data']['products'];
        $order->user_id = Auth::id();
        $order->is_custom_order = 1;
        // Manual orders must enter the Processing queue immediately.
        $order->status = 'Processing';
        $result = $order->save();
        if ($result) {
            $customer = new Customer();
            $customer->order_id = $order->id;
            $customer->customerName = $request['data']['customerName'];
            $customer->customerPhone = $request['data']['customerPhone'];
            $customer->customerAddress = $request['data']['customerAddress'];
            $customer->save();
            foreach ($products as $product) {
                $orderProducts = new OrderProducts();
                $orderProducts->order_id = $order->id;
                $orderProducts->product_id = $product['productID'];
                $orderProducts->productCode = $product['productCode'];
                $orderProducts->productName = $product['productName'];
                $orderProducts->quantity = $product['productQuantity'];
                $orderProducts->productPrice = $product['productPrice'];
                $orderProducts->save();
            }

            $notification = new Notification();
            $notification->order_id = $order->id;
            $notification->notificaton = '#BB-' . $order->id . ' Order Has Been Created by ' . Auth::user()->name;
            $notification->user_id = Auth::id();
            $notification->save();

            if ($request['data']['paymentID'] != '' && $request['data']['paymentID'] != '') {
                $paymentComplete = new PaymentCompelte();
                $paymentComplete->order_id = $order->id;
                $paymentComplete->payment_type_id = $request['data']['paymentTypeID'];
                $paymentComplete->payment_id = $request['data']['paymentID'];
                $paymentComplete->trid = $request['data']['paymentAgentNumber'];
                $paymentComplete->date = date('d-m-y');
                $paymentComplete->userID = Auth::id();
                $paymentComplete->save();
            }
            $response['status'] = 'success';
            $response['message'] = 'Successfully Add Order';
        } else {
            Customer::where('order_id', '=', $order->id)->delete();
            OrderProducts::where('order_id', '=', $order->id)->delete();
            Notification::where('order_id', '=', $order->id)->delete();
            Order::where('id', '=', $order->id)->delete();
            $response['status'] = 'failed';
            $response['message'] = 'Unsuccessful to Add Order';
        }
        return json_encode($response);
        die();
    }

    public function show(Request $request)
    {
        $columns = $request->input('columns');
        $status  = $request->input('status');
    
        $orders = DB::table('orders')
            ->leftJoin('customers', 'orders.id', '=', 'customers.order_id')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('cities', 'orders.city_id', '=', 'cities.id')
            ->leftJoin('zones', 'orders.zone_id', '=', 'zones.id')
            ->leftJoin('couriers', 'orders.courier_id', '=', 'couriers.id')
            ->whereNull('orders.deleted_at')
            ->select(
    'orders.id', 'orders.invoiceID', 'orders.web_ID', 'orders.subTotal',
    'orders.status', 'orders.orderDate', 'orders.deliveryDate',
    'orders.courier_id', 'orders.user_id', 'orders.memo',
    'orders.note', 'orders.consignment_id', 'orders.is_custom_order',
    'customers.customerName', 'customers.customerPhone', 'customers.customerAddress',
    'couriers.courierName', 'cities.cityName', 'zones.zoneName',
    'users.name as name'
);

        // Fraud-check columns were added later. Keep the order list usable even
        // before the production migration is run, then read persisted values
        // automatically once the columns exist.
        if (Schema::hasColumn('orders', 'fraud_success_count')) {
            $orders->addSelect('orders.fraud_success_count');
        } else {
            $orders->addSelect(DB::raw('NULL as fraud_success_count'));
        }

        if (Schema::hasColumn('orders', 'fraud_fail_count')) {
            $orders->addSelect('orders.fraud_fail_count');
        } else {
            $orders->addSelect(DB::raw('NULL as fraud_fail_count'));
        }
    
        // ========================
        // STATUS FILTER
        // ========================
        if ($status == 'Pending Invoiced') {
            $orders->whereIn('orders.status', ['Completed', 'Pending Invoiced']);
        } elseif ($status == 'Customer On Hold') {
            $orders->whereIn('orders.status', ['Delivered', 'Customer On Hold']);
        } elseif ($status == 'Delivered') {
            $orders->whereIn('orders.status', [
                'Delivered',
                'Customer Confirm',
                'Customer On Hold',
                'Request to Return'
            ]);
        } elseif ($status != 'All') {
            $orders->where('orders.status', $status);
        }
    
        // ========================
        // SEARCH FILTERS
        // ========================
        if (!empty($columns[1]['search']['value'])) {
            $search = $columns[1]['search']['value'];
            $orders->where(function ($q) use ($search) {
                $q->where('orders.invoiceID', 'like', "%$search%")
                  ->orWhere('orders.web_ID', 'like', "%$search%");
            });
        }
    
        if (!empty($columns[2]['search']['value'])) {
            $orders->where('customers.customerPhone', 'like', '%' . $columns[2]['search']['value'] . '%');
        }
    
        if (!empty($columns[5]['search']['value'])) {
            $orders->where('orders.courier_id', $columns[5]['search']['value']);
        }
    
        if (!empty($columns[6]['search']['value'])) {
            $value = $columns[6]['search']['value'];
    
            if ($status == 'Delivered') {
                $orders->where('orders.deliveryDate', 'like', "%$value%");
            } else {
                $orders->where('orders.orderDate', 'like', "%$value%");
            }
        }
    
        if (!empty($columns[8]['search']['value'])) {
            $orders->where('orders.memo', 'like', "%{$columns[8]['search']['value']}%");
        }
    
        if (!empty($columns[9]['search']['value'])) {
            $orders->where('orders.user_id', $columns[9]['search']['value']);
        }
    
        // ========================
        // DUPLICATE PHONE CACHE (OPTIMIZED)
        // ========================
        $duplicatePhones = DB::table('orders')
            ->join('customers', 'orders.id', '=', 'customers.order_id')
            ->select('customers.customerPhone')
            ->groupBy('customers.customerPhone')
            ->havingRaw('COUNT(customers.customerPhone) > 1')
            ->pluck('customers.customerPhone')
            ->toArray();
    
        // ========================
        // DATATABLE RESPONSE
        // ========================
        return DataTables::of($orders->latest('orders.id'))
    
            ->addColumn('customerInfo', function ($order) use ($duplicatePhones) {
    
                $highlight   = in_array($order->customerPhone, $duplicatePhones) ? 'highlight' : '';
                $customOrder = $order->is_custom_order == 1 ? 'custom' : '';
    
                $fraudResult = '';
                if ($order->fraud_success_count !== null || $order->fraud_fail_count !== null) {
                    $fraudResult = '<span class="badge badge-success mr-1">Success: ' . (int) $order->fraud_success_count . '</span>'
                        . '<span class="badge badge-danger">Fail: ' . (int) $order->fraud_fail_count . '</span>';
                }

                return '<div class="'.$highlight.' '.$customOrder.'">'
                    . $order->customerName . '<br>'
                    . $order->customerPhone . '<br>'
                    . $order->customerAddress . '<br>'
                    . 'Order Note: ' . $order->note . '<br>'
                    . '<button class="btn btn-info btn-xs fraud-check-button"
                        data-phone="'.$order->customerPhone.'"
                        data-order-id="'.$order->id.'"
                        onclick="openFraudCheckModal(this)">Check</button>'
                    . '<div class="fraud-check-inline-result mt-1">' . $fraudResult . '</div>'
                . '</div>';
            })
    
            ->addColumn('invoice', function ($order) {
                return $order->invoiceID . '<br>' . $order->web_ID;
            })
    
            ->addColumn('products', function ($order) {
                return $this->getProductsDetails($order->id);
            })
    
            ->addColumn('notification', function ($order) {
                return $this->getNotificationDetails($order->id);
            })
    
            ->addColumn('action', function ($order) {
    
                $edit = "<a href='javascript:void(0);' data-id='{$order->id}' class='action-icon btn-edit'>
                            <i class='fas fa-edit'></i>
                         </a>";
    
                if (Auth::user()->role_id < 2) {
                    $delete = "<a href='javascript:void(0);' data-id='{$order->id}' class='action-icon btn-delete'>
                                <i class='fas fa-trash-alt'></i>
                               </a>";
    
                    return $edit . $delete;
                }
    
                return $edit;
            })
    
            ->addColumn('statusButton', function ($order) use ($status) {
    
                if ($status == 'Paid') {
                    return '<span class="badge bg-soft-success text-success">Paid</span>';
                }
    
                if ($status == 'Return') {
                    return '<span class="badge bg-soft-danger text-danger">Return</span>';
                }
    
                if ($status == 'Lost') {
                    return '<span class="badge bg-soft-danger text-danger">Lost</span>';
                }
    
                if ($status == 'Pending Invoiced') {
                    return $this->statusList('Pending Invoiced', $order->id);
                }
    
                return $this->statusList($order->status, $order->id);
            })
    
            ->editColumn('courierName', function ($order) {
    
                if ($order->courierName) {
                    return $order->courierName . ' - ' . $order->consignment_id;
                }
    
                return 'Not Selected';
            })
    
            ->escapeColumns([])
            ->make(true);
    }

    public function getProductsDetails($orderID)
    {
        $products = DB::table('order_products')->select('order_products.*')->where('order_id', '=', $orderID)->get();
        $orderProducts = '';
        foreach ($products as $product) {
            //$orderProducts = $orderProducts . $product->quantity . ' x ' . $product->productName . '<br>';
             // Start building the order product string
            $orderProducts .= $product->quantity . ' x ' . $product->productName;
            
            // Include color if available
            if (!empty($product->colorName)) {
                $orderProducts .= ' (Color: ' . $product->colorName . ')';
            }
        
            // Include size if available
            if (!empty($product->sizeName)) {
                $orderProducts .= ' (Size: ' . $product->sizeName . ')';
            }
            // Include option if available
            if (!empty($product->optionName)) {
                $orderProducts .= ' (Option: ' . $product->optionName . ')';
            }
        
            // Add a line break for each product
            $orderProducts .= '<br>';
        }
        return rtrim($orderProducts, '<br>');
    }


    public function getNotificationDetails($orderID)
    {
        $notification = Notification::query()->where('order_id', '=', $orderID)->latest('id')->get()->first();
        if($notification){
            return $notification->notificaton;
        }else{
            return 'Order Has Been Created';
        }
        
    }


    // Edit Single Order
    public function edit($id)
    {
        $orders = DB::table('orders')
            ->select('orders.*', 'customers.customerName', 'customers.customerPhone', 'customers.customerAddress', 'couriers.courierName', 'cities.cityName', 'zones.zoneName', 'users.name', 'stores.*', 'payment_types.paymentTypeName', 'payments.paymentNumber')
            ->leftJoin('customers', 'orders.id', '=', 'customers.order_id')
            ->leftJoin('couriers', 'orders.courier_id', '=', 'couriers.id')
            ->leftJoin('payment_types', 'orders.payment_type_id', '=', 'payment_types.id')
            ->leftJoin('payments', 'orders.payment_id', '=', 'payments.id')
            ->leftJoin('cities', 'orders.city_id', '=', 'cities.id')
            ->leftJoin('zones', 'orders.zone_id', '=', 'zones.id')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->leftJoin('stores', 'orders.store_id', '=', 'stores.id')
            ->where('orders.id', '=', $id)->get()->first();
        $products = DB::table('order_products')->where('order_id', '=', $id)->get();
        $orders->products = $products;
        $orders->id = $id;
        return view('admin.order.edit')->with('order', $orders);
    }

    // Update Order
    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        $order->store_id = $request['data']['storeID'];
        $order->subTotal = $request['data']['total'];
        $oldAmount = $order->paymentAmount;
        $newAmount = $request['data']['paymentAmount'];
        $order->memo = $request['data']['memo'];
        $order->deliveryCharge = $request['data']['deliveryCharge'];
        $order->discountCharge = $request['data']['discountCharge'];
        $order->payment_type_id = $request['data']['paymentTypeID'];
        $order->payment_id = $request['data']['paymentID'];
        $order->paymentAmount = $request['data']['paymentAmount'];
        $order->paymentAgentNumber = $request['data']['paymentAgentNumber'];
        //$order->orderDate = $request['data']['orderDate'];
        if (!empty($request['data']['deliveryDate'])) {
            $order->deliveryDate = $request['data']['deliveryDate'];
        }
        if (!empty($request['data']['completeDate'])) {
            $order->completeDate = $request['data']['completeDate'];
        }
        $order->courier_id = $request['data']['courierID'];
        $order->note = $request['data']['orderNote'];
        $order->city_id = $request['data']['cityID'];
        $order->zone_id = $request['data']['zoneID'];
        $products = $request['data']['products'];


        $result = $order->update();
        if ($result) {
            $customer = Customer::where('order_id', '=', $id)->first();
            $customer->customerName = $request['data']['customerName'];
            $customer->customerPhone = $request['data']['customerPhone'];
            $customer->customerAddress = $request['data']['customerAddress'];
            $customer->update();
            
            // Soft delete the order products related to the order ID
            OrderProducts::where('order_id', $id)->delete();
            
            // Now fetch the old soft-deleted products for that order
            $oldOrderProducts = OrderProducts::onlyTrashed()->where('order_id', $id)->get();
            
            // Force delete the old soft-deleted products
            foreach ($oldOrderProducts as $oldProduct) {
                $oldProduct->forceDelete();
            }
            
            foreach ($products as $product) {
                $orderProducts = new OrderProducts();
                $orderProducts->order_id = $id;
                $orderProducts->product_id = $product['productID'];
                $orderProducts->productCode = $product['productCode'];
                $orderProducts->productName = $product['productName'];
                $orderProducts->quantity = $product['productQuantity'];
                
                $orderProducts->colorName = $product['productColor']??null;
                $orderProducts->sizeName = $product['productSize']??null;
                $orderProducts->optionName = $product['productOption']??null;
                
                $orderProducts->productPrice = $product['productPrice'];
                $orderProducts->save();
            }
            $notification = new Notification();
            $notification->order_id = $order->id;
            $notification->notificaton = Auth::user()->name . ' Update Order Details';
            $notification->user_id = Auth::id();
            $notification->save();
            $paymentComplete = PaymentCompelte::where('order_id', $order->id)->first();
            if ($paymentComplete) {
                $paymentComplete->payment_type_id = $request['data']['paymentTypeID'];
                $paymentComplete->payment_id = $request['data']['paymentID'];
                if ($newAmount != $oldAmount) {
                    $paymentComplete->amount = $request['data']['paymentAmount'];
                    $paymentComplete->date = date('d-m-y');
                }
                $paymentComplete->trid = $request['data']['paymentAgentNumber'];
                $paymentComplete->userID = Auth::id();
                $paymentComplete->update();
            } else {
                $paymentComplete = new PaymentCompelte();
                $paymentComplete->order_id = $order->id;
                $paymentComplete->payment_type_id = $request['data']['paymentTypeID'];
                $paymentComplete->payment_id = $request['data']['paymentID'];
                $paymentComplete->amount = $request['data']['paymentAmount'];
                $paymentComplete->trid = $request['data']['paymentAgentNumber'];
                $paymentComplete->date = date('d-m-y');
                $paymentComplete->userID = Auth::id();
                $paymentComplete->save();
            }
            $response['status'] = 'success';
            $response['message'] = 'Successfully Update Order';
        } else {
            $response['status'] = 'failed';
            $response['message'] = 'Unsuccessful to Update Order';
        }
        return json_encode($response);
    }

    // Delete Single Order
    public function destroy($id)
    {
        $result = Order::find($id)->delete();
        if ($result) {
            Customer::query()->where('order_id', '=', $id)->delete();
            OrderProducts::query()->where('order_id', '=', $id)->delete();
            $response['status'] = 'success';
            $response['message'] = 'Successfully Delete Order';
        } else {
            $response['status'] = 'failed';
            $response['message'] = 'Unsuccessful to Delete Order';
        }
        return json_encode($response);
    }

    // Show Order By status
    public function ordersByStatus($status)
    {
        $users = DB::table('users')->where([
            ['status', 'like', 'Active'],
            ['role_id', '=', '3']
        ])->inRandomOrder()->get();
        if ($status == 'Pending Invoiced' || $status == 'Invoiced' || $status == 'Stock Out') {
            return view('admin.order.invoiced', compact('status', 'users'));
        }
        if ($status == 'Delivered' || $status == 'Customer Confirm' || $status == 'Customer On Hold' || $status == 'Request to Return' || $status == 'Paid' || $status == 'Return' || $status == 'Lost') {
            return view('admin.order.delivered', compact('status', 'users'));
        } else {
            return view('admin.order.index', compact('status', 'users'));
        }

    }
    // get Users
    public function users(Request $request)
    {
        if (isset($request['q'])) {
            $$users = User::query()->where([
                ['name', 'like', '%' . $request['q'] . '%'],
                ['status', 'like', 'Active']
            ])->get();
        } else {
            $users = User::query()->where('status', 'like', 'Active')->get();
        }
        $user = array();
        foreach ($users as $item) {
            $user[] = array(
                "id" => $item['id'],
                "text" => $item['name']
            );
        }
        return json_encode($user);
        die();
    }

    // Get Products
    public function product(Request $request)
    {
        if (isset($request['q'])) {
            $products = Product::query()->where('productName', 'like', '%' . $request['q'] . '%')->orderBy('created_at', 'desc')->get();
        } else {
            $products = Product::orderBy('created_at', 'desc')->get();
        }
        $product = array();
        foreach ($products as $item) {
            if (App::environment('local')) {
                $item['productImage'] = url('/product/' . $item['productImage']);
            } else {
                $item['productImage'] = \App\Support\PublicMedia::url('product/' . $item['productImage']);
            }

            $product[] = array(
                "id" => $item['id'],
                "text" => $item->productName,
                "image" => $item->productImage,
                "productCode" => $item->productCode,
                "productPrice" => $item->price()
            );
        }
        $data['data'] = $product;
        return json_encode($data);
        die();
    }

    // get Stores
    public function stores(Request $request)
    {
        if (isset($request['q'])) {
            $stores = Store::query()->where([
                ['storeName', 'like', '%' . $request['q'] . '%'],
                ['status', 'like', 'Active']
            ])->get();
        } else {
            $stores = Store::query()->where('status', 'like', 'Active')->get();
        }
        $store = array();
        foreach ($stores as $item) {
            $store[] = array(
                "id" => $item['id'],
                "text" => $item['storeName']
            );
        }
        return json_encode($store);
        die();
    }

    // Get Payment Type
    public function paymenttype(Request $request)
    {
        if (isset($request['q'])) {
            $paymentTypes = PaymentType::query()->where([
                ['paymentTypeName', 'like', '%' . $request['q'] . '%'],
                ['status', 'like', 'Active']
            ])->get();
        } else {
            $paymentTypes = PaymentType::query()->where('status', 'like', 'Active')->get();
        }
        $paymentType = array();
        foreach ($paymentTypes as $item) {
            $paymentType[] = array(
                "id" => $item['id'],
                "text" => $item['paymentTypeName']
            );
        }
        return json_encode($paymentType);
    }

    // Get Payment Number
    public function paymentnumber(Request $request)
    {
        if (isset($request['q']) && $request['paymentTypeID']) {
            $payments = Payment::query()->where([
                ['paymentNumber', 'like', '%' . $request['q'] . '%'],
                ['status', 'like', 'Active'],
                ['payment_type_id', '=', $request['paymentTypeID']]
            ])->get();
        } else {
            $payments = Payment::query()->where([
                ['status', 'like', 'Active'],
                ['payment_type_id', '=', $request['paymentTypeID']]
            ])->get();
        }
        $payment = array();
        foreach ($payments as $item) {
            $payment[] = array(
                "id" => $item['id'],
                "text" => $item['paymentNumber']
            );
        }
        return json_encode($payment);
    }

    // Get Courier
    public function courier(Request $request)
    {
        if (isset($request['q'])) {
            $couriers = Courier::query()->where([
                ['courierName', 'like', '%' . $request['q'] . '%'],
                ['status', 'like', 'Active']
            ])->get();
        } else {
            $couriers = Courier::query()->where('status', 'like', 'Active')->get();
        }
        $courier = array();
        foreach ($couriers as $item) {
            $courier[] = array(
                "id" => $item['id'],
                "text" => $item['courierName']
            );
        }
        return json_encode($courier);
    }

    // Get City
    public function city(Request $request)
    {
        if (isset($request['q']) && $request['courierID']) {
            $cites = City::query()->where([
                ['cityName', 'like', '%' . $request['q'] . '%'],
                ['status', 'like', 'Active'],
                ['courier_id', '=', $request['courierID']]
            ])->get();
        } else {
            $cites = City::query()->where([
                ['status', 'like', 'Active'],
                ['courier_id', '=', $request['courierID']]
            ])->get();
        }
        $city = array();
        foreach ($cites as $item) {
            $city[] = array(
                "id" => $item['id'],
                "text" => $item['cityName']
            );
        }
        return json_encode($city);
    }

    // Get Zone
    public function zone(Request $request)
    {
        if (isset($request['q'])) {
            $zones = Zone::query()->where([
                ['zoneName', 'like', '%' . $request['q'] . '%'],
                ['courier_id', '=', $request['courierID']],
                ['status', 'like', 'Active'],
                ['city_id', '=', $request['cityID']]
            ])->get();
        } else {
            $zones = Zone::query()->where([
                ['courier_id', '=', $request['courierID']],
                ['city_id', '=', $request['cityID']],
                ['status', 'like', 'Active']
            ])->get();
        }
        $zone = array();
        foreach ($zones as $item) {
            $zone[] = array(
                "id" => $item['id'],
                "text" => $item['zoneName']
            );
        }
        return json_encode($zone);
    }

    // All Status List
    public function statusList($status, $id)
    {
        $allStatus = array(
            'order' => array(
                "Incomplete" => array(
                    "name" => "Incomplete",
                    "icon" => "fe-tag",
                    "color" => "bg-danger"
                ),
                "Processing" => array(
                    "name" => "Processing",
                    "icon" => "fe-tag",
                    "color" => "bg-primary"
                ),
                "Customer Confirm" => array(
                    "name" => "Customer Confirm",
                    "icon" => "fe-check-circle",
                    "color" => "bg-success"
                ),
                "On Hold" => array(
                    "name" => "On Hold",
                    "icon" => "far fa-stop-circle",
                    "color" => "bg-warning"
                ),
                "Payment Pending" => array(
                    "name" => "Payment Pending",
                    "icon" => "fe-tag",
                    "color" => "bg-info"
                ),
                "Canceled" => array(
                    "name" => "Canceled",
                    "icon" => "fe-trash-2",
                    "color" => "bg-danger"
                ),
                "Completed" => array(
                    "name" => "Completed",
                    "icon" => "fe-check-circle",
                    "color" => "bg-success"
                )
            ),
            'invoice' => array(
                "Pending Invoiced" => array(
                    "name" => "Pending Invoiced",
                    "color" => "bg-primary"
                ),
                "Invoiced" => array(
                    "name" => "Invoiced",
                    "color" => "bg-warning"
                ),
                "Stock Out" => array(
                    "name" => "Stock Out",
                    "color" => "bg-info"
                ),
                "Canceled" => array(
                    "name" => "Canceled",
                    "color" => "bg-info"
                ),
                "Delivered" => array(
                    "name" => "Delivered",
                    "color" => "bg-info"
                )
            ),
            'delivered' => array(
                "Delivered" => array(
                    "name" => "Delivered",
                    "color" => "bg-primary"
                ),
                "Customer On Hold" => array(
                    "name" => "Customer On Hold",
                    "color" => "bg-warning"
                ),
                "Customer Confirm" => array(
                    "name" => "Customer Confirm",
                    "color" => "bg-warning"
                ),
                 "Request to Return" => array(
                    "name" => "Request to Return",
                    "color" => "bg-warning"
                ),
                "Paid" => array(
                    "name" => "Paid",
                    "color" => "bg-info"
                ),
                "Return" => array(
                    "name" => "Return",
                    "color" => "bg-danger"
                ),
                "Lost" => array(
                    "name" => "Lost",
                    "color" => "bg-danger"
                )
            )
        );

        $temp = 'order';
        foreach ($allStatus as $key => $value) {
            foreach ($value as $kes => $val) {
                if ($kes == $status) {
                    $temp = $key;
                }
            }
        }
        $args = $allStatus[$temp];
        $html = '';
        foreach ($args as $value) {
            if ($args[$status]['name'] != $value['name']) {
                $html = $html . "<a class='dropdown-item btn-status' data-id='" . $id . "' data-status='" . $value['name'] . "' href='#'>" . $value['name'] . "</a>";
            }
        }
        $response = "<div class='btn-group dropdown'>
            <a href='javascript: void(0);'  class='table-action-btn dropdown-toggle arrow-none btn " . $args[$status]['color'] . " btn-xs' data-toggle='dropdown' aria-expanded='false'>" . $args[$status]['name'] . " <i class='mdi mdi-chevron-down'></i></a>
            <div class='dropdown-menu dropdown-menu-right'>
            " . $html . "
            </div>
        </div>";

        return $response;
    }

    //
    public function view(Request $request)
    {

    }

    // Create Invoice ID
    public function uniqueID()
    {
        // Include soft-deleted rows and the highest historical BB-* number.
        // This prevents a restored/legacy database from reusing an old invoice locally.
        $lastOrderId = (int) DB::table('orders')->max('id');
        $lastInvoiceNo = (int) DB::table('orders')
            ->where('invoiceID', 'like', 'BB-%')
            ->selectRaw('MAX(CAST(SUBSTRING(invoiceID, 4) AS UNSIGNED)) as max_invoice_no')
            ->value('max_invoice_no');

        $next = max($lastOrderId, $lastInvoiceNo) + 1;

        while (DB::table('orders')->where('invoiceID', 'BB-' . $next)->exists()) {
            $next++;
        }

        return 'BB-' . $next;
    }

    // Order Sync
    public function orderSync(Request $request)
    {
        $stores = Store::query()->where('status', 'like', 'Active')->get();
        // dd($stores);

        $orderCount = 0;
        foreach ($stores as $store) {

            $syncOrders = json_decode($this->getOrders($store->storeUrl));

            foreach ($syncOrders as $syncOrder) {

                $orderExist = Order::query()->where([
                    ['web_ID', '=', $syncOrder->wp_id],
                    ['store_id', '=', $store->id]
                ])->get()->first();

                if (!$orderExist) {
                    $user = DB::table('users')->where([
                        ['status', 'like', 'Active'],
                        ['role_id', '=', '3']
                    ])->inRandomOrder()->first();

                    if (!$user) {
                        $user_id = 1;
                    } else {
                        $user_id = $user->id;
                    }
                    
                    
                    $order = new Order();
                    $order->invoiceID = $this->uniqueID();
                    $order->web_ID = $syncOrder->wp_id;
                    $order->subTotal = $syncOrder->total;
                    $order->orderDate = date('d-m-y');
                    $order->user_id = $user_id;
                    $order->store_id = $store->id;

                    if(isset($syncOrder->deliveryCharge)){
                        $order->deliveryCharge = $syncOrder->deliveryCharge;
                    }else{
                        $order->deliveryCharge = 100;
                    }
                    
                    $result = $order->save();
                    $products = $syncOrder->products;
                    if ($result) {
                        $customer = new Customer();
                        $customer->order_id = $order->id;
                        $customer->customerName = $syncOrder->customer->first_name;
                        $customer->customerPhone = $syncOrder->customer->phone;
                        $customer->customerAddress = $syncOrder->customer->address_1;
                        $customer->save();
                        foreach ($products as $product) {
                            $orderProducts = new OrderProducts();
                            $productExist = Product::query()->where('productCode', 'like', $product->sku)->get()->first();
                            if ($productExist) {
                                $orderProducts->order_id = $order->id;
                                $orderProducts->product_id = $productExist->id;
                                $orderProducts->productCode = $product->sku;
                                $orderProducts->productName = $product->product_name;
                                $orderProducts->quantity = $product->quantity;
                                $orderProducts->productPrice = $product->price;
                                $orderProducts->save();
                            } else {
                                $this->productSync();
                                $productExist = Product::query()->where('productCode', 'like', $product->sku)->get()->first();
                                $orderProducts->order_id = $order->id;
                                $orderProducts->product_id = $productExist->id;
                                $orderProducts->productCode = $product->sku;
                                $orderProducts->productName = $product->product_name;
                                $orderProducts->quantity = $product->quantity;
                                $orderProducts->productPrice = $product->price;
                                $orderProducts->save();
                            }
                        }
                        $notification = new Notification();
                        $notification->order_id = $order->id;
                        $notification->notificaton = 'Order Has Been Created';
                        $notification->user_id = $user_id;
                        $notification->save();
                    }
                    $orderCount++;
                } else {
                    //echo 'Exist';
                }
            }

        }

        if ($orderCount > 0) {
            $response['status'] = 'success';
            $response['orders'] = $orderCount;
        } else {
            $response['status'] = 'failed';
            $response['orders'] = $orderCount;
        }
        return json_encode($response);
    }

    // Get Orders from website
    public function getOrders($url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url . "/wp-json/inventory/v1/order/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));
        return curl_exec($curl);
    }

    // Delete All Orders
    public function deleteAll(Request $request)
    {
        $ids = $request['ids'];
        if ($ids) {
            foreach ($ids as $id) {
                if (Auth::id() == 1) {
                    Order::query()->truncate();
                    Customer::query()->truncate();
                    OrderProducts::query()->truncate();
                    Notification::query()->truncate();
                } else {
                    $result = Order::find($id)->delete();
                    if ($result) {
                        Customer::query()->where('order_id', '=', $id)->delete();
                        OrderProducts::query()->where('order_id', '=', $id)->delete();
                        Notification::query()->where('order_id', '=', $id)->delete();
                    }
                }
            }
            $response['status'] = 'success';
            $response['message'] = 'Successfully Delete Order';
        } else {
            $response['status'] = 'failed';
            $response['message'] = 'Unsuccessful to Delete Order';
        }
        return json_encode($response);
    }

    // Assign Order to a user
    public function assign(Request $request)
    {
        $user_id = $request['user_id'];
        $ids = $request['ids'];
        if ($ids) {
            foreach ($ids as $id) {
                $order = Order::find($id);
                $order->user_id = $user_id;
                $order->save();
                $notification = new Notification();
                $user = User::find($user_id);
                $notification->order_id = $id;
                $notification->notificaton = Auth::user()->name . ' Successfully Assign #BB-' . $id . ' Order to ' . $user->name;
                $notification->user_id = Auth::id();
                $notification->save();
            }
            $response['status'] = 'success';
            $response['message'] = 'Successfully Assign User to this Order';
        } else {
            $response['status'] = 'failed';
            $response['message'] = 'Unsuccessful to Assign User to this Order';
        }
        return json_encode($response);
    }

    // Change Single order Status
    public function status(Request $request)
    {
        $request->validate(['id' => 'required|integer', 'status' => 'required|string|max:100']);
        $status = (string) $request->input('status');
        try {
            DB::transaction(function () use ($request, $status) {
                $order = Order::whereKey($request->input('id'))->lockForUpdate()->firstOrFail();
                if (!$order->courier_id && !in_array($status, ['Canceled', 'On Hold', 'Payment Pending', 'Customer Confirm'], true)) {
                    throw new \RuntimeException('Please Update order courier and try again !');
                }
                app(OrderStockService::class)->onStatusChange($order, $status);
                if ($status === 'Completed') {
                    $order->orderDate = date('d-m-y');
                }
                if ($status === 'Delivered') {
                    $order->deliveryDate = date('d-m-y h:i A');
                }
                if (in_array($status, ['Paid', 'Return'], true)) {
                    $order->completeDate = date('d-m-y');
                }
                $order->status = $status;
                $order->save();

                $notification = new Notification();
                $notification->order_id = $order->id;
                $notification->notificaton = Auth::user()->name . ' Successfully Update #BB-' . $order->id . ' Order status to ' . $status;
                $notification->user_id = Auth::id();
                $notification->save();
            }, 3);
            return json_encode(['status' => 'success', 'message' => 'Successfully Update Status to ' . $status]);
        } catch (\RuntimeException $e) {
            return json_encode(['status' => 'failed', 'message' => $e->getMessage()]);
        }
    }

    public function changeStatusByCheckbox(Request $request)
    {
        $request->validate([
            'status' => 'required|string|max:100',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);
        $status = (string) $request->input('status');
        try {
            DB::transaction(function () use ($request, $status) {
                foreach (array_unique($request->input('ids')) as $id) {
                    $order = Order::whereKey($id)->lockForUpdate()->firstOrFail();
                    app(OrderStockService::class)->onStatusChange($order, $status);
                    if ($status === 'Delivered') {
                        $order->deliveryDate = date('d-m-y h:i A');
                    }
                    if (in_array($status, ['Paid', 'Return'], true)) {
                        $order->completeDate = date('d-m-y');
                    }
                    $order->status = $status;
                    $order->save();
                    $notification = new Notification();
                    $notification->order_id = $order->id;
                    $notification->notificaton = Auth::user()->name . ' Successfully Update #BB-' . $order->id . ' Order status to ' . $status;
                    $notification->user_id = Auth::id();
                    $notification->save();
                }
            }, 3);
            return json_encode(['status' => 'success', 'message' => 'Successfully Updated Order Status']);
        } catch (\RuntimeException $e) {
            return json_encode(['status' => 'failed', 'message' => $e->getMessage()]);
        }
    }

    //
    public function pendingInvoiced()
    {
        $status = 'all';
        return view('admin.order.index', compact('status'));
    }

    // Product Sync if Not exist
    public function productSync()
    {
        $stores = Store::query()->where('status', 'like', 'Active')->get();
        $orderCount = 0;
        foreach ($stores as $store) {
            $syncProducts = json_decode($this->getProducts($store->storeUrl));
            foreach ($syncProducts as $syncProduct) {
                $LocalProduct = Product::where('productCode', 'like', $syncProduct->sku)->get()->first();
                if (!$LocalProduct && $syncProduct->price) {
                    $image = $syncProduct->image;
                    $imageName = uniqid() . '.jpg';
                    $img = public_path('product/') . $imageName;
                    file_put_contents($img, $this->curl_get_file_contents($image));
                    $newProduct = new Product();
                    $newProduct->productCode = $syncProduct->sku;
                    $newProduct->productName = $syncProduct->name;
                    $newProduct->productPrice = $syncProduct->price;
                    $newProduct->productImage = $imageName;
                    $newProduct->save();
                    $orderCount++;
                }
            }

        }
        if ($orderCount > 0) {
            $response['status'] = 'success';
            $response['products'] = $orderCount;
        } else {
            $response['status'] = 'failed';
            $response['products'] = $orderCount;
        }
        return json_encode($response);
    }

    // Get Products From website
    public function getProducts($url)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url . "/wp-json/inventory/v1/products/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));
        return curl_exec($curl);
    }

    // Get Note of order
    public function getNotes(Request $request)
    {
        $order_id = $request['id'];
        $notification = Notification::query()->where('order_id', '=', $order_id)->latest()->get();
        $notification['data'] = $notification->map(function ($notification) {
            $user = DB::table('users')->select('users.name')->where('id', '=', $notification->user_id)->get()->first();
            $notification->name = $user->name;
            $notification->date = $this->time_ago_in_php($notification->created_at);
            return $notification;
        });
        return json_encode($notification);

    }

    // Update Note of Order
    public function updateNotes(Request $request)
    {
        $id = $request['id'];
        $note = $request['note'];
        $notification = new Notification();
        $notification->order_id = $id;
        $notification->notificaton = $note;
        $notification->user_id = Auth::id();
        $request = $notification->save();

        if ($request) {
            $response['status'] = 'success';
            $response['message'] = 'Successfully to Update Order note';
        } else {
            $response['status'] = 'failed';
            $response['message'] = 'Unsuccessful to Update Order note';
        }
        return json_encode($response);
        die();

    }

    // Change time to facebook Style
    public function time_ago_in_php($timestamp)
    {

        date_default_timezone_set("Asia/Dhaka");
        $time_ago = strtotime($timestamp);
        $current_time = time();
        $time_difference = $current_time - $time_ago;
        $seconds = $time_difference;

        $minutes = round($seconds / 60); // value 60 is seconds
        $hours = round($seconds / 3600); //value 3600 is 60 minutes * 60 sec
        $days = round($seconds / 86400); //86400 = 24 * 60 * 60;
        $weeks = round($seconds / 604800); // 7*24*60*60;
        $months = round($seconds / 2629440); //((365+365+365+365+366)/5/12)*24*60*60
        $years = round($seconds / 31553280); //(365+365+365+365+366)/5 * 24 * 60 * 60

        if ($seconds <= 60) {

            return "Just Now";
        } else if ($minutes <= 60) {

            if ($minutes == 1) {

                return "one minute ago";
            } else {

                return "$minutes minutes ago";
            }
        } else if ($hours <= 24) {

            if ($hours == 1) {

                return "an hour ago";
            } else {

                return "$hours hrs ago";
            }
        } else if ($days <= 7) {

            if ($days == 1) {

                return "yesterday";
            } else {

                return "$days days ago";
            }
        } else if ($weeks <= 4.3) {

            if ($weeks == 1) {

                return "a week ago";
            } else {

                return "$weeks weeks ago";
            }
        } else if ($months <= 12) {

            if ($months == 1) {

                return "a month ago";
            } else {

                return "$months months ago";
            }
        } else {

            if ($years == 1) {

                return "one year ago";
            } else {

                return "$years years ago";
            }
        }
    }

    // Get Old Orders
    public function oldOrders(Request $request)
    {
        $order_id = $request['id'];
        $customer = Customer::query()->where('order_id', '=', $order_id)->get()->first();
        $orders = DB::table('orders')
            ->select('orders.*', 'customers.*')
            ->leftJoin('customers', 'orders.id', '=', 'customers.order_id')
            ->where([
                ['customers.order_id', '!=', $order_id],
                ['customers.customerPhone', 'like', $customer->customerPhone]
            ])->get();
        $order['data'] = $orders->map(function ($order) {
            $products = DB::table('order_products')->select('order_products.*')->where('order_id', '=', $order->id)->get();
            $orderProducts = '';
            foreach ($products as $product) {
                $orderProducts = $orderProducts . $product->quantity . ' x ' . $product->productName . '<br>';
            }
            $order->products = rtrim($orderProducts, '<br>');
            return $order;
        });
        return json_encode($order);

//        return $orders;
    }

    // Get Status Wise order Count
    public function countOrders()
    {
        $response['all'] = DB::table('orders')->count();
        $response['incomplete'] = DB::table('orders')->where('status', 'like', 'Incomplete')->count();
        $response['processing'] = DB::table('orders')->where('status', 'like', 'Processing')->count();
        $response['pendingPayment'] = DB::table('orders')->where('status', 'like', 'Payment Pending')->count();
        $response['onHold'] = DB::table('orders')->where('status', 'like', 'On Hold')->count();
        $response['canceled'] = DB::table('orders')->where('status', 'like', 'Canceled')->count();
        $response['completed'] = DB::table('orders')->where('status', 'like', 'Completed')->count();
        $response['pendingInvoiced'] = DB::table('orders')->where('status', 'like', 'Completed')->orWhere('orders.status', 'like', 'Pending Invoiced')->count();
        $response['invoiced'] = DB::table('orders')->where('status', 'like', 'Invoiced')->count();
        $response['stockOut'] = DB::table('orders')->where('status', 'like', 'Stock Out')->count();
        $response['delivered'] = DB::table('orders')->whereIn('orders.status', ['Delivered', 'Customer Confirm','Customer On Hold','Request to Return'])->count();
        $response['customerOnHold'] = DB::table('orders')->whereIn('orders.status', ['Delivered', 'Customer On Hold'])->count();
        $response['customerConfirm'] = DB::table('orders')->where('status', 'like', 'Customer Confirm')->count();
        $response['requestToReturn'] = DB::table('orders')->where('status', 'like', 'Request to Return')->count();
        $response['paid'] = DB::table('orders')->where('status', 'like', 'Paid')->count();
        $response['return'] = DB::table('orders')->where('status', 'like', 'Return')->count();
        $response['lost'] = DB::table('orders')->where('status', 'like', 'Lost')->count();
        $response['status'] = 'success';
        return json_encode($response);
    }

    // Invoice Display
    public function storeInvoice(Request $request)
    {
        $ids = serialize($request['ids']);
        $invoice = new Invoice();
        $invoice->order_id = $ids;
        $result = $invoice->save();
        if ($result) {
            $response['status'] = 'success';
            $response['link'] = url('admin/order/invoice/') . '/' . $invoice->id;
        } else {
            $response['status'] = 'failed';
            $response['message'] = 'Unsuccessful to Add Order';
        }
        return json_encode($response);
        die();
    }

    public function viewInvoice($id)
    {
        $invoice = Invoice::find($id);
        return view('admin.order.print', compact('invoice'));

    }

    public function curl_get_file_contents($URL)
    {
        $c = curl_init();
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $URL);
        $contents = curl_exec($c);
        curl_close($c);
        if ($contents) return $contents;
        else return FALSE;
    }

    public function getSotreUrl($id)
    {
        $store = Store::find($id);
        return $store->storeUrl;
    }

    public function sendNumber(Request $request)
    {
        $settings = Setting::get('sms_content');
        $url = "http://66.45.237.70/api.php";
        $customerPhone = $request['customerPhone'];
        $invoiceID = $request['invoiceID'];
        $paymentTypeID = $request['paymentTypeID'];
        $orderID = $request['orderID'];
        $storeURL = $this->getSotreUrl($request['storeID']);
        $paymentID = $request['paymentID'];
        $replaceTag = ["{ID}", "{method}", "{number}", "{site_url}", "{invoiceID}"];
        $tag = [$orderID, $paymentTypeID, $paymentID, $storeURL, $invoiceID];
        $text = str_replace($replaceTag, $tag, $settings);

        $data = array(
            'username' =>  Setting::get('sms_username'),
            'password' =>  Setting::get('sms_password'),
            'number' => "$customerPhone",
            'message' => "$text"
        );
        
 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $smsresult = curl_exec($ch);
        $p = explode("|", $smsresult);
        $sendstatus = $p[0];
        if ($sendstatus == '1101') {
            $notification = new Notification();
            $notification->order_id = $orderID;
            $notification->notificaton = Auth::user()->name . ' Send Sms for ' . $paymentTypeID . ' payment on ' . $paymentID . ' For ' . $orderID . ' Order';
            $notification->user_id = Auth::id();
            $notification->save();
            $settings = Setting::find(1);
            $settings->value = $settings->value + 1;
            $settings->update();
            $response['status'] = 'success';
            $response['message'] = 'Successfully Send SMS';
        } else {
            $response['status'] = 'failed';
            $response['message'] = 'Unsuccessful to Send SMS';
        }
        return json_encode($response);
        die();
    }

    public function sendMemoNumbers(Request $request)
    {

    }


    public function memoUpdate(Request $request)
    {
        $order = Order::find($request->id);
        if ($order->status != 'Paid' && $order->status != 'Return' && $order->status != 'Lost') {
            $order->memo = $request->memo;
            $result = $order->update();
            if ($result) {
                $notification = new Notification();
                $notification->order_id = $request->id;
                $notification->notificaton = Auth::user()->name . ' Update #BB-' . $request->id . ' Order Memo To ' . $request->memo;
                $notification->user_id = Auth::id();
                $notification->save();
                $response['status'] = 'success';
                $response['message'] = 'Successfully Updated Order Memo';
            } else {
                $response['status'] = 'failed';
                $response['message'] = 'Unsuccessful to Updated Order Memo';
            }
        } else {
            $response['status'] = 'failed';
            $response['message'] = 'Unsuccessful to Updated Order Memo';
        }
        return json_encode($response);
        die();
    }
    
    public function trashList (){
        $trashedOrders = Order::onlyTrashed()->get();
        return view('admin.order.trash', compact('trashedOrders'));
    }
    public function restore($id)
    {
        // Retrieve the soft-deleted order using the ID
        $order = Order::onlyTrashed()->findOrFail($id);
    
        // Retrieve the soft-deleted customer associated with the order
        $customer = Customer::onlyTrashed()->where('order_id', $order->id)->first();
        if ($customer) {
            $customer->restore();
        }
    
        // Retrieve the soft-deleted order products associated with the order
        $orderProducts = OrderProducts::onlyTrashed()->where('order_id', $order->id)->get();
        foreach ($orderProducts as $orderProduct) {
            $orderProduct->restore();
        }
    
        // Restore the order
        $order->restore();
    
        return redirect()->route('admin.orderTrashList')->with('message', 'Order and associated customer and products restored successfully.');
    }


    // Method to permanently delete a trashed order
    public function forceDelete($id)
    {
        // Retrieve the soft-deleted order using the ID
        $order = Order::onlyTrashed()->findOrFail($id);
    
        // Retrieve the soft-deleted customer associated with the order
        $customer = Customer::onlyTrashed()->where('order_id', $order->id)->first();
        if ($customer) {
            $customer->forceDelete();
        }
    
        // Retrieve the soft-deleted order products associated with the order
        $orderProducts = OrderProducts::onlyTrashed()->where('order_id', $order->id)->get();
        foreach ($orderProducts as $orderProduct) {
            $orderProduct->forceDelete();
        }
    
        // Force delete the order
        $order->forceDelete();
    
        return redirect()->route('admin.orderTrashList')->with('message', 'Order and associated customer and products deleted permanently.');
    }
    public function emptyTrash(){
        
        // Retrieve all soft-deleted orders
        $orders = Order::onlyTrashed()->get();
        
        foreach ($orders as $order) {
            // Retrieve the soft-deleted customer associated with the order
            $customer = Customer::onlyTrashed()->where('order_id', $order->id)->first();
            if ($customer) {
                $customer->forceDelete();
            }
            
            // Retrieve the soft-deleted order products associated with the order
            $orderProducts = OrderProducts::onlyTrashed()->where('order_id', $order->id)->get();
            foreach ($orderProducts as $orderProduct) {
                $orderProduct->forceDelete();
            }
            
            // Force delete the order
            $order->forceDelete();
        }
        
        return redirect()->route('admin.orderTrashList')->with('message', 'Order and associated customer and products deleted permanently.');
    }
    public function deleteSelected(Request $request){
        $ids = $request->query('ids');
        $orders = Order::whereIn('id', explode(',', $ids))->get();
        foreach($orders as $order){
            Customer::query()->where('order_id', '=', $order->id)->delete();
            OrderProducts::query()->where('order_id', '=', $order->id)->delete();
            $order->delete();
        }
        $response['status'] = 'success';
        $response['message'] = 'Successfully Delete Order';
        return response()->json($response);
    }
     /** Convert an acknowledged courier shipment into Completed + stock deduction. */
    private function finalizeCourierShipment(Order $order, int $courierId, $consignmentId, $trackingCode): ?string
    {
        try {
            app(OrderStockService::class)->completeCourierOrder(
                $order, $courierId, $consignmentId, $trackingCode
            );
            return null;
        } catch (\RuntimeException $exception) {
            return $exception->getMessage();
        }
    }

    public function import(Request $request){
        $type = $request->query('type');
        $ids = $request->query('ids');
        $orders = Order::whereIn('id', explode(',', $ids))->get();
        if ($type == 'steadfast') {

            $api_key    = env('STEADFAST_API_KEY');
            $secret_key = env('STEADFAST_SECRET_KEY');
            $base_url   = rtrim(env('STEADFAST_BASE_URL', 'https://portal.packzy.com/api/v1'), '/');

            if (!$api_key || !$secret_key) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'SteadFast API credentials are missing.',
                ], 500);
            }

            $results        = [];
            $successResults = [];
            $data           = [];
            $orderMap       = [];
            $successCount   = 0;
            $queuedStockQuantities = [];

            // Do not depend only on a legacy hard-coded courier ID.
            $steadfastCourierId = Courier::where('courierName', 'like', '%Stead%')->value('id') ?: 29;

            foreach ($orders as $order) {
                // Already synced locally: never send the same parcel again.
                if (!empty($order->consignment_id) || !empty($order->tracking_code)) {
                    $stockError = $this->finalizeCourierShipment(
                        $order, (int) $steadfastCourierId, $order->consignment_id, $order->tracking_code
                    );
                    if ($stockError !== null) {
                        $results[] = ['order_id' => $order->id, 'invoice' => $order->invoiceID,
                            'error_code' => 'COURIER_STOCK_SYNC', 'message' => $stockError];
                        continue;
                    }
                    $successCount++;
                    $successResults[] = [
                        'order_id' => $order->id,
                        'invoice' => $order->invoiceID,
                        'message' => 'Order already synced with SteadFast.',
                        'status' => 'success',
                        'consignment_id' => $order->consignment_id,
                        'tracking_code' => $order->tracking_code,
                    ];
                    continue;
                }

                $customer = Customer::where('order_id', $order->id)->first();
                $products = OrderProducts::where('order_id', $order->id)->get();

                if (!$customer) {
                    $results[] = [
                        'order_id' => $order->id,
                        'invoice' => $order->invoiceID,
                        'error' => 'Customer information not found.',
                    ];
                    continue;
                }

                $description = '';
                $qty = 0;

                foreach ($products as $product) {
                    $description .= $product->productName . ' (' . $product->productPrice . 'X' . $product->quantity . '), ';
                    $qty += $product->quantity;
                }

                $phone = preg_replace('/[^0-9]/', '', (string) $customer->customerPhone);
                if (strlen($phone) !== 11) {
                    $results[] = [
                        'order_id' => $order->id,
                        'invoice' => $order->invoiceID,
                        'error' => 'SteadFast recipient phone must contain exactly 11 digits.',
                    ];
                    continue;
                }

                $address = trim((string) $customer->customerAddress);
                if ($address === '') {
                    $results[] = [
                        'order_id' => $order->id,
                        'invoice' => $order->invoiceID,
                        'error' => 'SteadFast recipient address is empty.',
                    ];
                    continue;
                }

                // Prevent sending parcels whose quantities cannot be fulfilled.
                // Also consider other orders queued in this SteadFast batch.
                try {
                    $needed = app(OrderStockService::class)->checkAvailability(
                        $order, $queuedStockQuantities
                    );
                } catch (\RuntimeException $exception) {
                    $results[] = [
                        'order_id' => $order->id,
                        'invoice' => $order->invoiceID,
                        'error_code' => 'INSUFFICIENT_STOCK',
                        'message' => $exception->getMessage(),
                    ];
                    continue;
                }
                foreach ($needed as $productId => $requiredQty) {
                    $queuedStockQuantities[$productId] =
                        (int) ($queuedStockQuantities[$productId] ?? 0) + $requiredQty;
                }

                $invoice = (string) $order->invoiceID;

                $data[] = [
                    'invoice'           => $invoice,
                    'recipient_name'    => substr((string) $customer->customerName, 0, 100),
                    'recipient_address' => substr($address, 0, 250),
                    'recipient_phone'   => $phone,
                    'cod_amount'        => max(0, (float) $order->subTotal),
                    'note'              => substr($description, 0, 200),
                    'item_description'  => substr($description, 0, 200),
                    'total_lot'         => max(1, (int) $qty),
                    'delivery_type'     => 0,
                ];

                $orderMap[$invoice] = $order;
            }

            $chunks = array_chunk($data, 500);

            foreach ($chunks as $chunk) {
                $ch = curl_init();

                // This matches the working mentor implementation and SteadFast bulk API format:
                // the outer JSON contains a `data` field whose value is a JSON-encoded array.
                $payload = [
                    'data' => json_encode(array_values($chunk)),
                ];

                curl_setopt_array($ch, [
                    CURLOPT_URL => $base_url . '/create_order/bulk-order',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($payload),
                    CURLOPT_HTTPHEADER => [
                        'Api-Key: ' . $api_key,
                        'Secret-Key: ' . $secret_key,
                        'Content-Type: application/json',
                        'Accept: application/json',
                    ],
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_CONNECTTIMEOUT => 10,
                ]);

                $response = curl_exec($ch);
                $curlError = curl_error($ch);
                $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($curlError) {
                    $results[] = [
                        'error' => $curlError,
                        'http_code' => $httpCode,
                    ];
                    continue;
                }

                $resData = json_decode($response, true);

                if (!is_array($resData)) {
                    $results[] = [
                        'error' => 'Invalid SteadFast response.',
                        'http_code' => $httpCode,
                        'raw' => $response,
                    ];
                    continue;
                }

                // Working SteadFast bulk response is normally { status, message, data: [...] }.
                // Keep direct-array support too, so old/new API response shapes both work.
                if (isset($resData['data']) && is_array($resData['data'])) {
                    $responseItems = $resData['data'];
                } elseif (isset($resData[0]) && is_array($resData[0])) {
                    $responseItems = $resData;
                } elseif (isset($resData['invoice'])) {
                    $responseItems = [$resData];
                } else {
                    $results[] = [
                        'error' => 'Unexpected SteadFast bulk response structure.',
                        'http_code' => $httpCode,
                        'response' => $resData,
                    ];
                    continue;
                }

                foreach ($responseItems as $res) {
                    if (!is_array($res)) {
                        continue;
                    }

                    $invoice = isset($res['invoice']) ? (string) $res['invoice'] : null;

                    if (!$invoice || !isset($orderMap[$invoice])) {
                        $results[] = [
                            'invoice' => $invoice,
                            'error' => 'SteadFast response could not be matched with a local order.',
                            'response' => $res,
                        ];
                        continue;
                    }

                    $order = $orderMap[$invoice];
                    $itemStatus = strtolower(trim((string) ($res['status'] ?? '')));
                    $consignmentId = $res['consignment_id'] ?? null;
                    $trackingCode = $res['tracking_code'] ?? null;
                    $isSuccess = $itemStatus === 'success' || !empty($consignmentId) || !empty($trackingCode);

                    if ($isSuccess) {
                        $stockError = $this->finalizeCourierShipment(
                            $order, (int) $steadfastCourierId, $consignmentId, $trackingCode
                        );
                        if ($stockError !== null) {
                            $results[] = ['order_id' => $order->id, 'invoice' => $invoice,
                                'error_code' => 'COURIER_STOCK_SYNC', 'message' => $stockError,
                                'consignment_id' => $consignmentId, 'tracking_code' => $trackingCode];
                            continue;
                        }
                        $successCount++;
                        $successResults[] = [
                            'order_id' => $order->id,
                            'invoice' => $invoice,
                            'message' => $res['message'] ?? 'Order Created Successfully',
                            'status' => 'success',
                            'consignment_id' => $consignmentId,
                            'tracking_code' => $trackingCode,
                            'response' => $res,
                        ];
                        continue;
                    }

                    $apiError = $res['error'] ?? ($res['errors'] ?? null);
                    $errorText = is_array($apiError) ? json_encode($apiError) : (string) $apiError;
                    $isDuplicate = stripos($errorText, 'THIS_INVOICE_ALREADY_EXISTS') !== false;

                    if ($isDuplicate) {
                        /*
                         * SteadFast requires invoice values to be unique in the merchant account.
                         * Old/restored databases can legitimately reuse a local BB-* invoice that
                         * already exists remotely. First try to recover identifiers from the old
                         * invoice. If SteadFast only returns delivery_status (no identifiers), retry
                         * ONCE with a deterministic order-specific courier invoice. This keeps the
                         * retry idempotent for this local order and prevents endless new parcels.
                         */
                        $statusCh = curl_init();
                        curl_setopt_array($statusCh, [
                            CURLOPT_URL => $base_url . '/status_by_invoice/' . rawurlencode($invoice),
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_HTTPHEADER => [
                                'Api-Key: ' . $api_key,
                                'Secret-Key: ' . $secret_key,
                                'Content-Type: application/json',
                                'Accept: application/json',
                            ],
                            CURLOPT_TIMEOUT => 30,
                            CURLOPT_CONNECTTIMEOUT => 10,
                        ]);

                        $statusResponse = curl_exec($statusCh);
                        $statusError = curl_error($statusCh);
                        curl_close($statusCh);

                        $statusData = $statusError ? null : json_decode($statusResponse, true);

                        $recoveredCid = null;
                        $recoveredTracking = null;
                        if (is_array($statusData)) {
                            $recoveredCid = $statusData['consignment_id']
                                ?? ($statusData['data']['consignment_id'] ?? null)
                                ?? ($statusData['consignment']['consignment_id'] ?? null);
                            $recoveredTracking = $statusData['tracking_code']
                                ?? ($statusData['data']['tracking_code'] ?? null)
                                ?? ($statusData['consignment']['tracking_code'] ?? null);
                        }

                        if (!empty($recoveredCid) || !empty($recoveredTracking)) {
                            $stockError = $this->finalizeCourierShipment(
                                $order, (int) $steadfastCourierId, $recoveredCid, $recoveredTracking
                            );
                            if ($stockError !== null) {
                                $results[] = ['order_id' => $order->id, 'invoice' => $invoice,
                                    'error_code' => 'COURIER_STOCK_SYNC', 'message' => $stockError,
                                    'consignment_id' => $recoveredCid, 'tracking_code' => $recoveredTracking];
                                continue;
                            }
                            $successCount++;
                            $successResults[] = [
                                'order_id' => $order->id,
                                'invoice' => $invoice,
                                'steadfast_invoice' => $invoice,
                                'message' => 'Existing SteadFast order recovered successfully.',
                                'status' => 'success',
                                'consignment_id' => $recoveredCid,
                                'tracking_code' => $recoveredTracking,
                                'response' => $statusData,
                            ];
                            continue;
                        }

                        // Find the original payload item for this local order.
                        $retryItem = null;
                        foreach ($chunk as $chunkItem) {
                            if ((string) ($chunkItem['invoice'] ?? '') === $invoice) {
                                $retryItem = $chunkItem;
                                break;
                            }
                        }

                        if (!$retryItem) {
                            $results[] = [
                                'order_id' => $order->id,
                                'invoice' => $invoice,
                                'error_code' => 'STEADFAST_RETRY_PAYLOAD_NOT_FOUND',
                                'message' => 'Could not rebuild the SteadFast payload for duplicate-invoice retry.',
                                'original_response' => $res,
                            ];
                            continue;
                        }

                        // Deterministic, API-safe, order-specific fallback invoice.
                        // Example: local BB-4 for order #27 -> SF-27-BB-4.
                        $safeLocalInvoice = preg_replace('/[^A-Za-z0-9_-]/', '-', $invoice);
                        $retryInvoice = 'SF-' . $order->id . '-' . trim($safeLocalInvoice, '-_');
                        $retryInvoice = substr($retryInvoice, 0, 90);
                        $retryItem['invoice'] = $retryInvoice;

                        $retryCh = curl_init();
                        $retryPayload = [
                            'data' => json_encode([$retryItem]),
                        ];

                        curl_setopt_array($retryCh, [
                            CURLOPT_URL => $base_url . '/create_order/bulk-order',
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_POST => true,
                            CURLOPT_POSTFIELDS => json_encode($retryPayload),
                            CURLOPT_HTTPHEADER => [
                                'Api-Key: ' . $api_key,
                                'Secret-Key: ' . $secret_key,
                                'Content-Type: application/json',
                                'Accept: application/json',
                            ],
                            CURLOPT_TIMEOUT => 60,
                            CURLOPT_CONNECTTIMEOUT => 10,
                        ]);

                        $retryResponse = curl_exec($retryCh);
                        $retryCurlError = curl_error($retryCh);
                        $retryHttpCode = (int) curl_getinfo($retryCh, CURLINFO_HTTP_CODE);
                        curl_close($retryCh);

                        if ($retryCurlError) {
                            $results[] = [
                                'order_id' => $order->id,
                                'invoice' => $invoice,
                                'steadfast_invoice' => $retryInvoice,
                                'error_code' => 'STEADFAST_RETRY_CURL_ERROR',
                                'message' => $retryCurlError,
                                'http_code' => $retryHttpCode,
                            ];
                            continue;
                        }

                        $retryData = json_decode($retryResponse, true);
                        $retryItems = [];
                        if (is_array($retryData)) {
                            if (isset($retryData['data']) && is_array($retryData['data'])) {
                                $retryItems = $retryData['data'];
                            } elseif (isset($retryData[0]) && is_array($retryData[0])) {
                                $retryItems = $retryData;
                            } elseif (isset($retryData['invoice'])) {
                                $retryItems = [$retryData];
                            }
                        }

                        $retryResult = $retryItems[0] ?? null;
                        if (is_array($retryResult)) {
                            $retryStatus = strtolower(trim((string) ($retryResult['status'] ?? '')));
                            $retryCid = $retryResult['consignment_id'] ?? null;
                            $retryTracking = $retryResult['tracking_code'] ?? null;
                            $retrySuccess = $retryStatus === 'success' || !empty($retryCid) || !empty($retryTracking);

                            if ($retrySuccess) {
                                $stockError = $this->finalizeCourierShipment(
                                    $order, (int) $steadfastCourierId, $retryCid, $retryTracking
                                );
                                if ($stockError !== null) {
                                    $results[] = ['order_id' => $order->id, 'invoice' => $invoice,
                                        'error_code' => 'COURIER_STOCK_SYNC', 'message' => $stockError,
                                        'consignment_id' => $retryCid, 'tracking_code' => $retryTracking];
                                    continue;
                                }
                                $successCount++;
                                $successResults[] = [
                                    'order_id' => $order->id,
                                    'invoice' => $invoice,
                                    'steadfast_invoice' => $retryInvoice,
                                    'message' => $retryResult['message'] ?? 'Order Created Successfully',
                                    'status' => 'success',
                                    'duplicate_invoice_retried' => true,
                                    'consignment_id' => $retryCid,
                                    'tracking_code' => $retryTracking,
                                    'response' => $retryResult,
                                ];
                                continue;
                            }
                        }

                        $results[] = [
                            'order_id' => $order->id,
                            'invoice' => $invoice,
                            'steadfast_invoice' => $retryInvoice,
                            'error_code' => 'STEADFAST_DUPLICATE_RETRY_FAILED',
                            'message' => 'Original invoice already existed in SteadFast and the safe order-specific retry did not succeed.',
                            'steadfast_status' => $statusData,
                            'original_response' => $res,
                            'retry_response' => $retryData,
                            'retry_http_code' => $retryHttpCode,
                        ];
                        continue;
                    }

                    $results[] = [
                        'order_id' => $order->id,
                        'invoice' => $invoice,
                        'error' => $res,
                    ];
                }
            }

            $failedCount = count($results);
            $responseStatus = $failedCount > 0
                ? ($successCount > 0 ? 'partial_success' : 'failed')
                : ($successCount > 0 ? 'success' : 'failed');

            return response()->json([
                'status' => $responseStatus,
                'message' => $responseStatus === 'success'
                    ? 'Order(s) sent to SteadFast successfully.'
                    : ($responseStatus === 'partial_success'
                        ? 'Some orders were sent to SteadFast, but some failed.'
                        : 'No order was sent to SteadFast.'),
                'success' => $successCount,
                'failed' => $failedCount,
                'results' => $successResults,
                'errors' => $results,
            ]);
        }
        else {
            $accessToken = $this->getPathaoAccessToken();
            if (!$accessToken || isset($accessToken['error'])) {
                return response()->json(['error' => 'Unable to get Pathao access token'], 500);
            }
        
            $baseUrl = env('PATHAO_SANDBOX', false) 
                ? 'https://courier-api-sandbox.pathao.com' 
                : 'https://api-hermes.pathao.com';
        
            $results = [];

            // Resolve Pathao from the actual couriers table instead of relying on a
            // hard-coded ID. Courier IDs can differ between local/live/restored DBs.
            $pathaoCourier = Courier::where('courierName', 'like', '%Pathao%')->first();
            if (!$pathaoCourier) {
                $pathaoCourier = new Courier();
                $pathaoCourier->courierName = 'Pathao';
                $pathaoCourier->hasCity = 'on';
                $pathaoCourier->hasZone = 'on';
                $pathaoCourier->courierCharge = '0';
                $pathaoCourier->status = 'Active';
                $pathaoCourier->save();
            }
            $pathaoCourierId = $pathaoCourier->id;
        
            foreach ($orders as $order) {
                // Existing tracking identifies a remote shipment: reconcile, never re-send.
                if (!empty($order->consignment_id) || !empty($order->tracking_code)) {
                    $stockError = $this->finalizeCourierShipment(
                        $order, (int) $pathaoCourierId, $order->consignment_id, $order->tracking_code
                    );
                    $results[] = [
                        'order_id' => $order->id,
                        'response' => $stockError === null
                            ? ['type' => 'success', 'local_update' => 'Already synced; stock reconciled.']
                            : ['type' => 'error', 'local_update_error' => $stockError],
                    ];
                    continue;
                }
                try {
                    app(OrderStockService::class)->checkAvailability($order);
                } catch (\RuntimeException $exception) {
                    $results[] = ['order_id' => $order->id, 'response' => [
                        'type' => 'error', 'error' => $exception->getMessage(),
                    ]];
                    continue;
                }

                $customer  = Customer::where('order_id', $order->id)->first();
                $products  = OrderProducts::where('order_id', $order->id)->get();
                if (!$customer) {
                    $results[] = ['order_id' => $order->id, 'response' => [
                        'type' => 'error', 'error' => 'Customer information not found.',
                    ]];
                    continue;
                }
        
                $description = '';
                $quantity    = 0;
                $amount      = 0;
        
                foreach ($products as $product) {
                    $description .= "{$product->productName} ({$product->productPrice}X{$product->quantity}), ";
                    $quantity   += $product->quantity;
                    $amount     += $product->quantity * $product->productPrice;
                }
        
                $payloadOrder = [
                    "store_id"            => env('PATHAO_STORE_ID'),
                    "merchant_order_id"   => $order->invoiceID,
                    "recipient_name"      => $customer->customerName,
                    "recipient_phone"     => $customer->customerPhone,
                    "recipient_address"   => $customer->customerAddress,
                    "delivery_type"       => 48,
                    "item_type"           => 2,
                    "special_instruction" => $order->note ?? '',
                    "item_quantity"       => $quantity ?: 1,
                    "item_weight"         => $order->weight ?: 0.5,
                    "item_description"    => $description ?: '',
                    "amount_to_collect"   => $order->subTotal ?: 0
                ];
        
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $baseUrl . '/aladdin/api/v1/orders');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payloadOrder));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $accessToken
                ]);
        
                $orderResponse = curl_exec($ch);
                $orderError    = curl_error($ch);
                curl_close($ch);
        
                $resData = $orderError ? ['error' => $orderError] : json_decode($orderResponse, true);
        
                // Update local order if success
                if (isset($resData['type']) && $resData['type'] == 'success') {
                    $stockError = $this->finalizeCourierShipment(
                        $order, (int) $pathaoCourierId,
                        $resData['data']['consignment_id'] ?? null,
                        $resData['data']['merchant_order_id'] ?? null
                    );
                    if ($stockError !== null) {
                        $resData['local_update_error'] = $stockError;
                        $resData['type'] = 'error';
                    } else {
                        $resData['local_update'] = 'Order Completed and stock deducted successfully';
                    }
                }
        
                $results[] = [
                    'order_id' => $order->id,
                    'response' => $resData
                ];
            }
        
            return response()->json($results);
        }

        return redirect()->back();
    }
    public function getPathaoAccessToken()
    {
        $base_url = env('PATHAO_SANDBOX', false) 
            ? 'https://courier-api-sandbox.pathao.com' 
            : 'https://api-hermes.pathao.com';
    
        $payload = [
            'client_id'     => env('PATHAO_CLIENT_ID'),
            'client_secret' => env('PATHAO_CLIENT_SECRET'),
            'grant_type'    => 'password',
            'username'      => env('PATHAO_USERNAME'),
            'password'      => env('PATHAO_PASSWORD'),
        ];
       
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $base_url . '/aladdin/api/v1/issue-token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
    
        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);
    
        if ($error) {
            return ['error' => $error];
        }
    
        $data = json_decode($response, true);
    
        if (isset($data['access_token'])) {
            return $data['access_token']; // You can store this in DB or cache
        }
    
        return $data; // Return full API response if no token
    }

    public function export(Request $request)
    {
        $type = $request->query('type');
        $ids = $request->query('ids');
        $orders = Order::whereIn('id', explode(',', $ids))->get();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        if($type == 'steadfast'){
            
                // Steadfast
            $sheet->setCellValue('A1', 'Invoice');
            $sheet->setCellValue('B1', 'Name');
            $sheet->setCellValue('C1', 'Address');
            $sheet->setCellValue('D1', 'Phone');
            $sheet->setCellValue('E1', 'Amount');
            $sheet->setCellValue('F1', 'Note');
            $sheet->setCellValue('G1', 'Contact Name');
            $sheet->setCellValue('H1', 'Contact Phone');
            
            
            $rowNumber = 1;
            
            foreach ($orders as $order) {
                $rowNumber++;
                $customer = Customer::where('order_id',$order->id)->first();
                $store = Store::find($order->store_id);
                $products = OrderProducts::where('order_id',$order->id)->get();
                
                $sheet->setCellValue('A' . $rowNumber, $order->invoiceID);
                $sheet->setCellValue('B' . $rowNumber, $customer->customerName);
                $sheet->setCellValue('C' . $rowNumber, $customer->customerAddress);
                $sheet->setCellValue('D' . $rowNumber, $customer->customerPhone);
                $sheet->setCellValue('E' . $rowNumber, $order->subTotal);
                $description = '';
                $quantity = 0;
                $amount = 0;
                foreach($products as $product){
                   $description = $description.$product->productName.' ('.$product->productPrice.'X'.$product->quantity.') ,';
                   $quantity = $quantity + $product->quantity;
                   $amount += $product->quantity * $product->productPrice;
                }
                $sheet->setCellValue('F' . $rowNumber, $description);
                $sheet->setCellValue('G' . $rowNumber, $customer->customerName);
                $sheet->setCellValue('H' . $rowNumber, $customer->customerPhone);
                
            }
            
            
            // Create a Writer and output the file
            $writer = new Xlsx($spreadsheet);
            $fileName = 'SteadfsastExport_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
            
        }elseif($type == 'pathao'){
            $sheet->setCellValue('A1', 'ItemType');
            $sheet->setCellValue('B1', 'StoreName');
            $sheet->setCellValue('C1', 'MarchentOrderId');
            $sheet->setCellValue('D1', 'RecipientName');
            $sheet->setCellValue('E1', 'RecipientPhone');
            $sheet->setCellValue('F1', 'RecipientCity');
            $sheet->setCellValue('G1', 'RecipientZone');
            $sheet->setCellValue('H1', 'RecipientArea');
            $sheet->setCellValue('I1', 'RecipientAddress');
            $sheet->setCellValue('J1', 'AmountToCollect');
            $sheet->setCellValue('K1', 'ItemQuantity');
            $sheet->setCellValue('L1', 'ItemWeight');
            $sheet->setCellValue('M1', 'ItemDesc');
            $sheet->setCellValue('N1', 'SpecialInstruction');
            
            $rowNumber = 1;
            
            foreach ($orders as $order) {
                $rowNumber++;
                $customer = Customer::where('order_id',$order->id)->first();
                $store = Store::find($order->store_id);
                $products = OrderProducts::where('order_id',$order->id)->get();
                
                $sheet->setCellValue('A' . $rowNumber, 'parcel');
                $sheet->setCellValue('B' . $rowNumber, $store->storeName);
                $sheet->setCellValue('C' . $rowNumber, $order->invoiceID);
                $sheet->setCellValue('D' . $rowNumber, $customer->customerName);
                $sheet->setCellValue('E' . $rowNumber, $customer->customerPhone);
                $sheet->setCellValue('I' . $rowNumber, $customer->customerAddress);
                $description = '';
                $quantity = 0;
                $amount = 0;
                foreach($products as $product){
                   $description = $description.$product->productName.' ('.$product->productPrice.'X'.$product->quantity.') ,';
                   $quantity = $quantity + $product->quantity;
                   $amount += $product->quantity * $product->productPrice;
                }
                $sheet->setCellValue('J' . $rowNumber, $order->subTotal);
                $sheet->setCellValue('K' . $rowNumber, $quantity);
                $sheet->setCellValue('L' . $rowNumber, 0.5);
                $sheet->setCellValue('M' . $rowNumber, $description);
                
                
            }
            
            
            // Create a Writer and output the file
            $writer = new Xlsx($spreadsheet);
            $fileName = 'PathaoExport_' . now()->format('Y_m_d_H_i_s') . '.xlsx'; 
        }
        elseif($type == 'redex'){
            $sheet->setCellValue('A1', 'Invoice');
            $sheet->setCellValue('B1', 'Customer Name');
            $sheet->setCellValue('C1', 'Contact No.');
            $sheet->setCellValue('D1', 'Customer Address');
            $sheet->setCellValue('E1', 'District');
            $sheet->setCellValue('F1', 'Area');
            $sheet->setCellValue('G1', 'Area ID');
            $sheet->setCellValue('H1', 'Division');
            $sheet->setCellValue('I1', 'Products');
            $sheet->setCellValue('J1', 'Price');
            $sheet->setCellValue('K1', 'Weight(g)');
            $sheet->setCellValue('L1', 'Instruction');
            $sheet->setCellValue('M1', 'Product Selling Price');
            $sheet->setCellValue('N1', 'Seller Name');
            $sheet->setCellValue('O1', 'Seller Phone');
          
            
            $rowNumber = 1;
            
            foreach ($orders as $order) {
                $rowNumber++;
                $customer = Customer::where('order_id',$order->id)->first();
                $store = Store::find($order->store_id);
                $products = OrderProducts::where('order_id',$order->id)->get();
                
                $sheet->setCellValue('A' . $rowNumber, $order->invoiceID);
                $sheet->setCellValue('B' . $rowNumber, $customer->customerName);
                $sheet->setCellValue('C' . $rowNumber, $customer->customerPhone);
                $sheet->setCellValue('D' . $rowNumber, $customer->customerAddress);
                
                
                $description = '';
                $quantity = 0;
                $amount = 0;
                foreach($products as $product){
                   $description = $description.$product->productName.' ('.$product->productPrice.'X'.$product->quantity.') ,';
                   $quantity = $quantity + $product->quantity;
                   $amount += $product->quantity * $product->productPrice;
                }
                $sheet->setCellValue('I' . $rowNumber, $description);
                $sheet->setCellValue('J' . $rowNumber, $order->subTotal);
                $sheet->setCellValue('K' . $rowNumber, 500);
                $sheet->setCellValue('M' . $rowNumber, $amount);
                $sheet->setCellValue('N' . $rowNumber, '');
                $sheet->setCellValue('O' . $rowNumber, '');
    
                
            }
            
            
            // Create a Writer and output the file
            $writer = new Xlsx($spreadsheet);
            $fileName = 'RedxExport_' . now()->format('Y_m_d_H_i_s') . '.xlsx'; 
        }
        else{
              // Set the header row
            $sheet->setCellValue('A1', 'Store Name');
            $sheet->setCellValue('B1', 'Order Id');
            $sheet->setCellValue('C1', 'Customer Name');
            $sheet->setCellValue('D1', 'Customer Phone No');
            $sheet->setCellValue('E1', 'Customer Address');
            $sheet->setCellValue('F1', 'Item Description');
            $sheet->setCellValue('G1', 'Item Quentity');
            $sheet->setCellValue('H1', 'Order Amount');
            $sheet->setCellValue('I1', 'Delivery Charge');
            $sheet->setCellValue('J1', 'Total');
          
            
            $rowNumber = 1;
            
            foreach ($orders as $order) {
                $rowNumber++;
                $customer = Customer::where('order_id',$order->id)->first();
                $store = Store::find($order->store_id);
                $products = OrderProducts::where('order_id',$order->id)->get();
                
                $sheet->setCellValue('A' . $rowNumber, $store->storeName);
                $sheet->setCellValue('B' . $rowNumber, $order->invoiceID);
                $sheet->setCellValue('C' . $rowNumber, $customer->customerName);
                $sheet->setCellValue('D' . $rowNumber, $customer->customerPhone);
                $sheet->setCellValue('E' . $rowNumber, $customer->customerAddress);
                $description = '';
                $quantity = 0;
                $amount = 0;
                foreach($products as $product){
                   $description = $description.$product->productName.' ('.$product->productPrice.'X'.$product->quantity.') ,';
                   $quantity = $quantity + $product->quantity;
                   $amount += $product->quantity * $product->productPrice;
                }
                $sheet->setCellValue('F' . $rowNumber, $description);
                $sheet->setCellValue('G' . $rowNumber, $quantity);
                $sheet->setCellValue('H' . $rowNumber, $amount);
                $sheet->setCellValue('I' . $rowNumber, $order->deliveryCharge);
                $sheet->setCellValue('J' . $rowNumber, $order->subTotal);
    
                
            }
            
            
            // Create a Writer and output the file
            $writer = new Xlsx($spreadsheet);
            $fileName = 'orders_' . now()->format('Y_m_d_H_i_s') . '.xlsx'; 
        }
        
       

        // Set headers for the download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        // Write the file to output
        $writer->save('php://output');
        exit;
        // You can filter the data based on the selected IDs
        //return Excel::download(new OrdersExport($ids), 'orders.xlsx');
    }
}
