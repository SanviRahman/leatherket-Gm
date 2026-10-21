<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Notification;
use App\Order;
use App\OrderProducts;
use App\Ip;
use App\Color;
use App\Option;
use App\Size;
use App\Product;
use App\Campaign;
use App\ShippingCharge;
use App\User;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Setting;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        session_start();
        error_reporting(0);
        $charge = ShippingCharge::first();
        $_SESSION['delivery'] =  0;
        // if(!$_SESSION['delivery']){
        //   // $_SESSION['delivery'] =  $$charge->charge??0;
        //     $_SESSION['delivery'] =  0;
        // }

        return view('website.checkout');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        // Validate the incoming request with custom messages for product ID
        try {
            $request->validate([
                'id' => 'required|exists:products,id',
            ], [
                'id.required' => 'Product ID is required.',
                'id.exists' => 'The selected product does not exist.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->validator->errors()
            ]);
        }
        $product = Product::visible()->with('stockRecord')->findOrFail($request->id);
        $qty = filter_var($request->input('quantity', 1), FILTER_VALIDATE_INT);
        if ($qty === false || $qty < 1) {
            return response()->json(['status' => 'error', 'message' => 'Please select a valid quantity.'], 422);
        }
        if ($product->isSoldOut()) {
            return response()->json(['status' => 'error', 'message' => 'This product is sold out.'], 422);
        }
        // Stock may be shared across variants. Count all existing cart lines for the product.
        $alreadyInCart = Cart::content()->filter(function ($item) use ($product) {
            return (int) $item->id === (int) $product->id;
        })->sum('qty');
        if ($qty + $alreadyInCart > $product->availableStock()) {
            return response()->json(['status' => 'error', 'message' => 'Only ' . $product->availableStock() . ' item(s) are in stock.'], 422);
        }
    
        // Prepare conditional validation rules and messages for color and size
        $rules = [];
        $messages = [];
    
        if ($product->colors->isNotEmpty()) {
            $rules['color_id'] = 'required|exists:colors,id';
            $messages['color_id.required'] = 'Please select a color.';
            $messages['color_id.exists'] = 'The selected color is invalid.';
        }
    
        if ($product->sizes->isNotEmpty()) {
            $rules['size_id'] = 'required|exists:sizes,id';
            $messages['size_id.required'] = 'Please select a size.';
            $messages['size_id.exists'] = 'The selected size is invalid.';
        }
        if ($product->options->isNotEmpty()) {
            $rules['option_id'] = 'required|exists:options,id';
            $messages['option_id.required'] = 'Please select a option.';
            $messages['option_id.exists'] = 'The selected option is invalid.';
        }
    
        // Perform the additional validation for color and size
        if (!empty($rules)) {
            try {
                $request->validate($rules, $messages);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->validator->errors()->first(),
                    'errors' => $e->validator->errors()
                ]);
            }
        }
         // Retrieve selected color and size names if applicable
        if ($request->color_id) {
            $color = Color::find($request->color_id);
        }
        $price = $product->price();
        if ($request->size_id) {
            $size = Size::find($request->size_id);
        }
        if ($request->option_id) {
            $option = Option::find($request->option_id);
            $price = DB::table('option_product')->where('product_id', $product->id)->where('option_id', $option->id)->value('price');
        }
    
        // Add product to the cart with color and size if applicable
        Cart::add([
            'id' => $product->id,
            'name' => $product->productName,
            'qty' => $qty,
            'isFreeDelivery' => $product->isFreeDelivery,
            'price' => $price,
            'options' => [
                'colorName' => $color->colorName ?? null,
                'sizeName' => $size->sizeName ?? null,
                'optionName' => $option->optionName ?? null,
            ]
        ])->associate(Product::class);
    
        // Return success response with cart content
        return response()->json([
            'status' => 'success',
            'message' => 'Product added to cart successfully.',
            'cart' => Cart::content()
        ]);
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        Cart::remove($id);
        $response['reload'] = 'true';
        if (Cart::count() > 0 ){
            $response['reload'] = 'false';
        }
        $response['status'] = 'success';
        $response['message'] = 'Successfully Add Product';
        return response()->json($response, 201);
    }

    public function mini_cart()
    {
        $response['count'] = Cart::count();
        $response['data'] = Cart::content();
        return response()->json($response, 200);
    }
    public function miniCart()
    {
        return view('partials.mini_cart');
    
    }

    public function updateQuantity(Request $request)
    {
        session_start();
        if($request->quantity > 0){
            Cart::update($request->key, $request->quantity);
        }
         // Calculate delivery discount if any product has free delivery
        $freeDeliveryDiscount = 0;
        foreach (Cart::content() as $item) {
            if( $item->model->isFreeDelivery) {
                $freeDeliveryDiscount = $_SESSION['delivery']; // discount equals delivery charge
                break; // only need to apply once
            }
        }
        ?>
        <aside class="card">
            <article class="card-body">
                <header class="mb-4">
                    <h4 class="card-title" style="font-size: 16px;">আপনার অর্ডার</h4>
                </header>
                <div class="row">
                    <div class="table-responsive bg-white">
                        <table class="table border-bottom">
                            <thead>
                            <tr>
                                <th class="product-image">Image</th>
                                <th class="product-name">Product</th>
                                <th class="product-price">Price</th>
                                <th class="product-quanity">Quantity</th>
                                <th class="product-total">Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach(Cart::content() as $item) {  ?>
                                <tr class="cart-item">
                                    <td class="product-image" style="display: flex; flex-direction: row-reverse;">
                                        <a href="#" >
                                            <img class="lazyload" src="<?php echo \App\Support\PublicMedia::url('product/thumbnail/'.$item->model->productImage) ?>" style="max-width: 50px">
                                        </a>
                                        <button href="#"  onclick="removeFromCart('<?php echo $item->rowId ?>')" class="btn btn-danger btn-sm">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>

                                    <td class="product-name">
                                        <span class="d-block"><?php echo $item->model->productName ?></span>
                                           
                                        <?php if($item->options->colorName){ ?>
                                            <small class="text-muted">Color: <?php echo $item->options->colorName; ?></small>
                                        <?php } ?>
                                        <?php if($item->options->colorName){ ?>
                                            <small class="text-muted">Size: <?php echo $item->options->sizeName; ?></small>
                                        <?php } ?>
                                        <?php if($item->options->optionName){ ?>
                                            <small class="text-muted">Option: <?php echo $item->options->optionName; ?></small>
                                        <?php } ?>
                                    </td>

                                    <td class="product-price">
                                        <span class="d-block">TK <?php echo $item->price ?></span>
                                    </td>

                                    <td class="product-quantity">
                                        <div class="input-group input-spinner">
                                            <div class="input-group-prepend">
                                                <button class="btn btn-light btn-number" type="button" data-type="plus" data-field="quantity[<?php echo $item->id ?>]"> + </button>
                                            </div>
                                            <input type="text" name="quantity[<?php echo $item->id ?>]" class="form-control input-number" placeholder="1" value="<?php echo $item->qty ?>" min="1" max="10" onchange="updateQuantity('<?php echo $item->rowId ?>', this)">
                                            <div class="input-group-append">
                                                <button class="btn btn-light btn-number" type="button" data-type="minus"  data-field="quantity[<?php echo $item->id ?>]"> − </button>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="product-total">
                                        <span>TK <?php echo Cart::subtotal('0','','')?></span>
                                    </td>

                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>
            <article class="card-body border-top">
                <dl class="row">
                    <dt class="col-sm-8">Subtotal: </dt>
                    <dd class="col-sm-4 text-right"><strong>TK <?php echo Cart::total('0') ?></strong></dd>

                    <dt class="col-sm-8">Delivery charge: </dt>
                    <dd class="col-sm-4 text-danger text-right"><strong>TK <?php echo $_SESSION['delivery'] ?></strong></dd>
                    
                    <dt class="col-sm-8">Discount: </dt>
                    <dd class="col-sm-4 text-success text-right"><strong>- TK <?php echo $freeDeliveryDiscount ?></strong></dd>

                    <dt class="col-sm-8">Total Taka:</dt>
                    <dd class="col-sm-4 text-right"><strong class="h5 text-dark">  <?php echo Cart::subtotal('0','','') - $freeDeliveryDiscount + $_SESSION['delivery']; ?></strong></dd>
                </dl>

            </article>
            <script type="text/javascript">
                cartQuantityInitialize();
            </script>

        </aside>
    <?php }

    public function updateDeliveryCharge(Request $request)
    {
        session_start();
        $_SESSION['delivery'] = $request->selectCourier;

    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'customerPhone' => ['required', 'regex:/^01[3-9][0-9]{8}$/'],
            'customerName' => 'nullable|string|max:150',
            'customerAddress' => 'nullable|string|max:1000',
            'selectCourier' => 'required|numeric|min:0',
        ]);

        if (Cart::count() < 1) {
            return response()->json(['status' => 'error', 'message' => 'Your cart is empty.'], 422);
        }

        $sessionId = session('checkout_session');
        $ipAddress = $request->ip();
        $blockHours = (float) Setting::get('order_blocking_hour');
        $recentOrder = Order::where('session_id', $sessionId)
            ->where('status', '!=', 'Incomplete')->latest()->first();

        if ($blockHours > 0 && $recentOrder && now()->lt($recentOrder->created_at->copy()->addMinutes($blockHours * 60))) {
            return response()->json(['status' => 'blocked', 'message' => 'You already placed an order recently. Please try later.'], 201);
        }
        if (Ip::where('ip_address', $ipAddress)->exists()) {
            return response()->json(['status' => 'blocked', 'message' => 'You Can not place any order at this moment'], 201);
        }

        $user = DB::table('users')->where('status', 'Active')
            ->where('role_id', 3)->inRandomOrder()->first();
        $user = $user ?: User::find(1);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Order processing is unavailable.'], 422);
        }

        // Only clear the cart AFTER the database transaction commits.
        // Stock check, order rows, customer, and reservation succeed or fail together.
        try {
            $orderId = DB::transaction(function () use ($request, $sessionId, $ipAddress, $user) {
                $cartItems = Cart::content();
                $freeDelivery = false;
                $requestedQuantities = [];
                foreach ($cartItems as $item) {
                    // Resolve the live product for the final inventory decision.
                    $product = Product::find($item->id);
                    if (!$product || $product->status !== 'Active' || (int) $product->isStockOut === 1) {
                        throw new \RuntimeException('A product in your cart is no longer available.');
                    }
                    if ((int) $item->qty < 1) {
                        throw new \RuntimeException('Invalid cart quantity.');
                    }
                    $requestedQuantities[$product->id] = ($requestedQuantities[$product->id] ?? 0) + (int) $item->qty;
                    if ((int) $product->isFreeDelivery === 1) {
                        $freeDelivery = true;
                    }
                }

                // Recheck current stock before saving a pending order, without
                // reserving/deducting anything. Final stock deduction happens on Completed.
                foreach ($requestedQuantities as $productId => $quantity) {
                    $liveProduct = Product::visible()->with('stockRecord')->findOrFail($productId);
                    if ($liveProduct->isSoldOut() || $quantity > $liveProduct->availableStock()) {
                        throw new \RuntimeException('Not enough stock for ' . $liveProduct->productName . '. Please adjust your cart.');
                    }
                }

                // Draft orders never reserve stock. Delete drafts only inside the transaction.
                $draftOrders = Order::where('session_id', $sessionId)
                    ->where('status', 'Incomplete')->lockForUpdate()->get();
                foreach ($draftOrders as $draft) {
                    OrderProducts::where('order_id', $draft->id)->forceDelete();
                    Customer::where('order_id', $draft->id)->forceDelete();
                    $draft->forceDelete();
                }

                $deliveryCharge = $freeDelivery ? 0 : (float) $request->selectCourier;
                $order = new Order();
                $order->ip_address = $ipAddress;
                $order->invoiceID = $this->uniqueID();
                $order->store_id = 1;
                $order->session_id = $sessionId;
                $order->status = 'Processing';
                $order->deliveryCharge = $deliveryCharge;
                $order->orderDate = date('d-m-Y, h:i A');
                $order->subTotal = (float) Cart::subtotal(2, '.', '') + $deliveryCharge;
                $order->user_id = $user->id;
                $order->save();

                $customer = new Customer();
                $customer->order_id = $order->id;
                $customer->customerName = $request->customerName ?: '-';
                $customer->customerPhone = $request->customerPhone;
                $customer->customerAddress = $request->customerAddress ?: '-';
                $customer->save();

                foreach ($cartItems as $item) {
                    $product = Product::visible()->findOrFail($item->id);
                    $line = new OrderProducts();
                    $line->order_id = $order->id;
                    $line->product_id = $product->id;
                    $line->productCode = $product->productCode;
                    $line->productName = $product->productName;
                    $line->quantity = (int) $item->qty;
                    $line->productPrice = $item->price;
                    $line->colorName = $item->options->colorName ?? null;
                    $line->sizeName = $item->options->sizeName ?? null;
                    $line->optionName = $item->options->optionName ?? null;
                    $line->save();
                }

                // Stock is reduced only when an order is marked Completed.
                // Do NOT allocate/deduct inventory on checkout submission.

                $notification = new Notification();
                $notification->order_id = $order->id;
                $notification->notificaton = '#SD' . $order->id . ' Order Has Been Created by ' . $user->name;
                $notification->user_id = $user->id;
                $notification->save();

                return $order->id;
            }, 3);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        Cart::destroy();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully Placed Order',
            'link' => url('/checkout/order-received/' . $orderId),
        ], 201);
    }

    public function saveOrderInput(Request $request){
        
        $phone = $request->customerPhone;

        // Regex for Bangladeshi numbers: 01XXXXXXXXX (11 digits)
        if (!preg_match('/^01[3-9][0-9]{8}$/', $phone)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Bangladeshi phone number'
            ]); 
        }

        $sessionId = session('checkout_session');
        
        $blockHours = (float) Setting::get('order_blocking_hour');
        
        $order = Order::where('session_id', $sessionId)
            ->where('status', '!=', 'Incomplete')
            ->latest()
            ->first();
        if ($blockHours > 0 && $order) {
            $nextAllowedTime = $order->created_at
                ->copy()
                ->addMinutes($blockHours * 60); // supports decimal hours
        
            if (now()->lt($nextAllowedTime)) {
                return response()->json([
                    'status' => 'blocked',
                    'message' => 'You already placed an order recently. Please try later.'
                ], 201);
            }
        }   
        
        if(Cart::count() <= 0) {
            return response()->json(['status' => 'Your shopping empty']);
        }
        
        $ipAddress = $request->ip();
        
        if(Ip::where('ip_address',$ipAddress)->first()){
            $response['status'] = 'blocked';
            $response['message'] = 'You Can not place any order at this moment';
            
            return response()->json($response, 201);
        }
        $user = DB::table('users')->where([
            ['status', 'like', 'Active'],
            ['role_id', '=', '3']
        ])->inRandomOrder()->first();
        if (!$user) {
            $user = User::find(1);
        }
        
        
        $order = Order::orderBy('id','desc')->where('session_id',$sessionId)->where('status','Incomplete')->first();
        
        if(!$order){
            
            $order = new Order();
            $order->ip_address = $ipAddress;
            $order->invoiceID = $this->uniqueID();
            $order->store_id = 1;
            $order->status = 'Incomplete';
            $order->session_id = $sessionId;
            $order->deliveryCharge = $request->selectCourier;
            $order->orderDate = date('d-m-y, h:i A');
            $order->subTotal = Cart::subtotal('0','','') +$request->selectCourier;
            $order->user_id = $user->id;
            $order->save(); 
        }else{
           
            $order->ip_address = $ipAddress;
            $order->invoiceID = $this->uniqueID();
            $order->store_id = 1;
            $order->status = 'Incomplete';
            $order->session_id = $sessionId;
            $order->deliveryCharge = $request->selectCourier;
            $order->orderDate = date('d-m-y, h:i A');
            $order->subTotal = Cart::subtotal('0','','') +$request->selectCourier;
            $order->user_id = $user->id;
            $order->save(); 
        }
       
        if($order->id){
            $customer = Customer::orderBy('id','desc')->where('session_id',$sessionId)->first();
             
            if(!$customer){
                
                $customer = new Customer();
                $customer->session_id = $sessionId;
                $customer->order_id = $order->id;
                $customer->customerName = $request->customerName??'-';
                $customer->customerPhone = $request->customerPhone;
                $customer->customerAddress = $request->customerAddress??'-';
                $customer->save();
                
                
            }else{
                
                $customer->order_id = $order->id;
                $customer->customerName = $request->customerName??'-';
                $customer->customerPhone = $request->customerPhone;
                $customer->customerAddress = $request->customerAddress??'-';
                $customer->save();
                
            }
          
            OrderProducts::where('order_id', $order->id)->forceDelete();
            foreach(Cart::content() as $item) {
                $orderProducts = new OrderProducts();
                $orderProducts->order_id = $order->id;
                $orderProducts->product_id = $item->model->id;
                $orderProducts->productCode = $item->model->productCode;
                $orderProducts->productName = $item->model->productName;
                $orderProducts->quantity = $item->qty;
                $orderProducts->productPrice = $item->price;
                // Include colorName and sizeName from the cart options
                $orderProducts->colorName = $item->options->colorName ?? null;
                $orderProducts->sizeName = $item->options->sizeName ?? null;
                $orderProducts->optionName = $item->options->optionName ?? null;
                $orderProducts->save();
            }
         
        }
        return response()->json(['status' => 'saved']);
    }

    public function orderRecived($id)
    {
        $order = Order::find($id);
        $customer = Customer::where('order_id',$id)->first();
        $orderProducts = OrderProducts::where('order_id',$id)->get();
        return view('website.thankyou', compact(['order','orderProducts','customer']));

    }
    public function uniqueID()
    {
        $lastOrder = Order::latest('id')->first();
        if ($lastOrder) {
            $orderID = $lastOrder->id + 1;
        } else {
            $orderID = 1;
        }

        return 'BB-' . $orderID;
    }
    public function updateCartOptions(Request $request)
    {
        if ($request->has('key')) {
            $cartItem = Cart::get($request->key);
    
            if ($cartItem) {
                $existingOptions = $cartItem->options->toArray();
                
                // Extract new values from the request
                $colorName = $request->input('colorName', $existingOptions['colorName']?? null);
                $sizeName = $request->input('sizeName', $existingOptions['sizeName'] ?? null);
                $optionName = $request->input('optionName', $existingOptions['optionName'] ?? null);
    
                // Update cart item with merged options (keeping existing ones if not overwritten)
                Cart::update($cartItem->rowId, [
                    'options' => array_merge($existingOptions, [
                        'colorName' => $colorName,
                        'sizeName' => $sizeName,
                        'optionName' => $optionName,
                    ])
                ]);
            }
        }
    
        return $this->renderCart();
    }

    /**
     * Add or remove one product from the current campaign selection.
     * Only products assigned to the requested campaign can be selected.
     */
    public function updateCampaignProduct(Request $request)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $request->validate([
            'campaign_id' => 'required|integer|exists:campaigns,id',
            'product_id' => 'required|integer|exists:products,id',
            'selected' => 'required|in:0,1',
        ]);

        $campaign = Campaign::with(['products.options', 'products.colors', 'products.sizes', 'product.options', 'product.colors', 'product.sizes'])
            ->findOrFail((int) $request->input('campaign_id'));

        $campaignProducts = $campaign->products;
        if ($campaignProducts->isEmpty() && $campaign->product) {
            $campaignProducts = collect([$campaign->product]);
        }

        $productId = (int) $request->input('product_id');
        $campaignProduct = $campaignProducts->firstWhere('id', $productId);

        if (!$campaignProduct || $campaignProduct->status !== 'Active') {
            return response()->json([
                'status' => 'error',
                'message' => 'The selected product is not part of this campaign.',
            ], 422);
        }

        $selected = (string) $request->input('selected') === '1';
        $existingCartItem = Cart::content()->first(function ($item) use ($productId) {
            return (int) $item->id === $productId;
        });

        if ($selected && !$existingCartItem) {
            $selectedOption = $campaignProduct->options->first();
            $selectedColor = $campaignProduct->colors->first();
            $selectedSize = $campaignProduct->sizes->first();
            $price = $campaignProduct->price();
            $optionName = null;
            $optionId = null;
            $colorName = null;
            $colorId = null;
            $sizeName = null;

            if ($selectedOption) {
                $optionPrice = $selectedOption->pivot->price;
                if ($optionPrice !== null && is_numeric($optionPrice)) {
                    $price = (float) $optionPrice;
                }

                $optionName = $selectedOption->optionName;
                $optionId = $selectedOption->id;
            }

            if ($selectedColor) {
                $colorName = $selectedColor->colorName;
                $colorId = $selectedColor->id;
            }

            if ($selectedSize) {
                $sizeName = $selectedSize->sizeName;
            }

            $campaignPosition = $campaignProducts->search(function ($product) use ($productId) {
                return (int) $product->id === $productId;
            });
            $campaignPosition = $campaignPosition === false ? 1 : $campaignPosition + 1;

            Cart::add([
                'id' => $campaignProduct->id,
                'name' => $campaignProduct->productName,
                'qty' => 1,
                'price' => $price,
                'options' => [
                    'colorName' => $colorName,
                    'colorId' => $colorId,
                    'sizeName' => $sizeName,
                    'optionName' => $optionName,
                    'optionId' => $optionId,
                    'campaignPosition' => $campaignPosition,
                ],
            ])->associate(Product::class);
        }

        if (!$selected && $existingCartItem) {
            // Keep at least one product selected so campaign checkout can never
            // be submitted with an empty cart.
            if (Cart::content()->count() <= 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'At least one campaign product must remain selected.',
                ], 422);
            }

            Cart::remove($existingCartItem->rowId);
        }

        return response()->json([
            'status' => 'success',
            'selected' => $selected,
            'html' => $this->renderCampaignCart(),
        ]);
    }

    /**
     * Campaign-only quantity update. Kept separate so the existing checkout
     * cart rendering and behavior remain unchanged.
     */
    public function updateCampaignQuantity(Request $request)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if ($request->filled('key') && (int) $request->input('quantity', 0) > 0) {
            Cart::update($request->input('key'), (int) $request->input('quantity'));
        }

        return $this->renderCampaignCart();
    }

    /**
     * Update an option-price selection for one campaign cart item.
     * The selected option must actually belong to that product.
     */
    public function updateCampaignOption(Request $request)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $request->validate([
            'key' => 'required|string',
            'option_id' => 'required|integer|exists:options,id',
        ]);

        $cartItem = Cart::get($request->input('key'));
        $product = Product::with('options')->findOrFail($cartItem->id);
        $option = $product->options->firstWhere('id', (int) $request->input('option_id'));

        if (!$option) {
            return response()->json([
                'status' => 'error',
                'message' => 'The selected option is not available for this product.',
            ], 422);
        }

        $existingOptions = $cartItem->options->toArray();
        $optionPrice = $option->pivot->price;
        $price = ($optionPrice !== null && is_numeric($optionPrice))
            ? (float) $optionPrice
            : (float) $product->price();

        Cart::update($cartItem->rowId, [
            'price' => $price,
            'options' => array_merge($existingOptions, [
                'optionName' => $option->optionName,
                'optionId' => (int) $option->id,
            ]),
        ]);

        return $this->renderCampaignCart();
    }

    /**
     * Update the selected color for one campaign cart item.
     * The color must belong to the product, so a forged color ID cannot be
     * attached to an unrelated campaign product.
     */
    public function updateCampaignColor(Request $request)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $request->validate([
            'key' => 'required|string',
            'color_id' => 'required|integer|exists:colors,id',
        ]);

        $cartItem = Cart::get($request->input('key'));

        if (!$cartItem) {
            return response()->json([
                'status' => 'error',
                'message' => 'The campaign cart item could not be found.',
            ], 404);
        }

        $product = Product::with('colors')->findOrFail($cartItem->id);
        $color = $product->colors->firstWhere('id', (int) $request->input('color_id'));

        if (!$color) {
            return response()->json([
                'status' => 'error',
                'message' => 'The selected color is not available for this product.',
            ], 422);
        }

        $existingOptions = $cartItem->options->toArray();

        Cart::update($cartItem->rowId, [
            'options' => array_merge($existingOptions, [
                'colorName' => $color->colorName,
                'colorId' => (int) $color->id,
            ]),
        ]);

        return $this->renderCampaignCart();
    }

    /**
     * Render only the campaign order summary so AJAX changes do not alter
     * the normal checkout page or its existing cart markup.
     */
    protected function renderCampaignCart()
    {
        return view('website.partials.campaign_order_details')->render();
    }

    protected function renderCart()
    {
        ob_start(); // Start output buffering
        ?>
        <aside class="card">
            <article class="card-body">
                <header class="mb-4">
                    <h4 class="card-title" style="font-size: 16px;">আপনার অর্ডার</h4>
                </header>
                <div class="row">
                    <div class="table-responsive bg-white">
                        <table class="table border-bottom">
                            <thead>
                                <tr>
                                    <th class="product-image">Image</th>
                                    <th class="product-name">Product</th>
                                    <th class="product-price">Price</th>
                                    <th class="product-quantity">Quantity</th>
                                    <th class="product-total">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (Cart::content() as $item){ ?>
                                    <tr class="cart-item">
                                        <td class="product-image" style="display: flex; flex-direction: row-reverse;">
                                            <a href="#">
                                                <img class="lazyload" src="<?php echo \App\Support\PublicMedia::url('product/thumbnail/' . $item->model->productImage); ?>" style="max-width: 50px">
                                            </a>
                                            <button onclick="removeFromCart('<?php echo $item->rowId; ?>')" class="btn btn-danger btn-sm">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
    
                                        <td class="product-name">
                                            <span class="d-block"><?php echo $item->model->productName; ?></span>
                                            <?php if ($item->options->colorName){ ?>
                                                <small class="text-muted">Color: <?php echo $item->options->colorName; ?>,</small>
                                            <?php } ?>
                                            <?php if ($item->options->sizeName){ ?>
                                                <small class="text-muted">Size: <?php echo $item->options->sizeName; ?>,</small>
                                            <?php } ?>
                                            <?php if($item->options->optionName){ ?>
                                                <small class="text-muted">Option: <?php echo $item->options->optionName; ?></small>
                                            <?php } ?>
    
                                            
                                        </td>
    
                                        <td class="product-price">
                                            <span class="d-block">TK <?php echo $item->model->price(); ?></span>
                                        </td>
    
                                        <td class="product-quantity">
                                            <div class="input-group input-spinner">
                                                <div class="input-group-prepend">
                                                    <button class="btn btn-light btn-number" type="button" data-type="plus" data-field="quantity[<?php echo $item->id; ?>]"> + </button>
                                                </div>
                                                <input type="text" name="quantity[<?php echo $item->id; ?>]" class="form-control input-number" value="<?php echo $item->qty; ?>" min="1" max="10" onchange="updateQuantity('<?php echo $item->rowId; ?>', this)">
                                                <div class="input-group-append">
                                                    <button class="btn btn-light btn-number" type="button" data-type="minus" data-field="quantity[<?php echo $item->id; ?>]"> − </button>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="product-total">
                                            <span>TK <?php echo $item->total(); ?></span>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </article>
            <article class="card-body border-top">
                <dl class="row">
                    <dt class="col-sm-8">Subtotal:</dt>
                    <dd class="col-sm-4 text-right"><strong>TK <?php echo Cart::subtotal(); ?></strong></dd>
    
                    <dt class="col-sm-8">Delivery charge:</dt>
                    <dd class="col-sm-4 text-danger text-right"><strong>TK <?php echo session('delivery', 0); ?></strong></dd>
    
                    <dt class="col-sm-8">Total:</dt>
                    <dd class="col-sm-4 text-right"><strong class="h5 text-dark">TK <?php echo Cart::subtotal() + session('delivery', 0); ?></strong></dd>
                </dl>
            </article>
            <script type="text/javascript">
                cartQuantityInitialize();
            </script>
        </aside>
        <?php
        return ob_get_clean(); // Return the buffered content
    }
}
