@extends('website.layout')
@section('content')
<style>
    .shipping-options {
        display: flex;
        flex-direction: column; /* ensures each option on a new line */
        gap: 8px;
    }
    
    .shipping-option {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 15px; 
        border: 1px solid #ccc;
        border-radius: 8px;
        cursor: pointer;
        background-color: #f8f9fa; /* default gray */
        transition: background 0.2s ease, border 0.2s ease;
    }
    
    .shipping-option input[type="radio"] {
        accent-color: green; /* modern browsers: makes radio circle green */
    }
    
    .shipping-option.active {
        background-color: #28a745 !important; /* Bootstrap green */
        color: #fff;
        border-color: #28a745;
        font-weight: 600;
    }

</style>
    @php
        $lkFreeDeliveryCheckout = Cart::content()->contains(function ($item) {
            return $item->model && (int) $item->model->isFreeDelivery === 1;
        });
    @endphp
    @if(Cart::count() > 0)
    <section class="section-content padding-y bg slidetop">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <aside class="card mb-4">
                        <article class="card-body">
                            @if($lkFreeDeliveryCheckout)
                                <div class="lk-checkout-free-delivery" role="status"><i class="fa fa-truck" aria-hidden="true"></i> ফ্রী ডেলিভারি — এই অর্ডারে ডেলিভারি চার্জ ৳ ০</div>
                            @endif
                            <header class="mb-4">
                                <div class="lk-checkout-cart-count" role="status">Total cart items: {{ Cart::count() }}</div>
                                <p class="text-center" style="font-size: 16px;">অর্ডারটি কনফার্ম করতে আপনার নাম, ঠিকানা,
                                    মোবাইল নাম্বার, লিখে <span class="text-danger">অর্ডার কনফার্ম করুন</span> বাটনে
                                    ক্লিক করুন
                                </p>
                            </header>
                            <div class="row">
                                <div class="form-group col-sm-12">
                                    <label>আপনার নাম </label>
                                    <input onchange="saveInput()" type="text" id="customerName" placeholder="আপনার নাম লিখুন" class="form-control">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label>আপনার মোবাইল নম্বর </label>
                                    <input onchange="saveInput()" type="number" pattern="[0-9]*" id="customerPhone" placeholder="আপনার মোবাইল নম্বর লিখুন"
                                           class="form-control">
                                </div>
                                <div class="form-group col-sm-12">
                                    <label>আপনার সম্পূর্ণ ঠিকানা </label>
                                    <input onchange="saveInput()" type="text"  id="customerAddress" class="form-control"
                                           placeholder="আপনার ঠিকানা সম্পূর্ণ  লিখুন">
                                </div>
                                {{--
                                <div class="form-group col-sm-12">
                                    <label>Select Area </label>
                                    @php
                                    $shipingCharges = App\ShippingCharge::all();
                                    @endphp
                                    <select onchange="saveInput()" name="area" id="selectCourier" class="form-control">
                                       <option value=""> Select Area</option>
                                        @foreach($shipingCharges as $shipingCharge)
                                        <option value="{{$shipingCharge->charge}}"> {{$shipingCharge->name}} </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="form-group col-sm-12">
                                    <label class="form-label d-block">Select Area <span class="text-danger">*</span></label>
                                    @php
                                        $shipingCharges = App\ShippingCharge::all();
                                    @endphp
                                    @if ($shipingCharges->isNotEmpty())
                                        <div class="btn-group-toggle" data-toggle="buttons">
                                            @foreach ($shipingCharges as $index => $shipingCharge)
                                                <label class="btn btn-outline-primary {{ $index === 0 ? 'active' : '' }}">
                                                    <input 
                                                        type="radio" 
                                                        name="area" 
                                                        id="area-{{ $shipingCharge->id }}" 
                                                        value="{{ $shipingCharge->charge }}" 
                                                        autocomplete="off" 
                                                        {{ $index === 0 ? 'checked' : '' }}
                                                        required
                                                    >
                                                    {{ $shipingCharge->name }}
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <p>No shipping charges available.</p>
                                    @endif
                                </div>
                                --}}
                                <div class="form-group col-sm-12">
                                    <label class="form-label d-block">Select Area <span class="text-danger">*</span></label>
                                    @php
                                        $shipingCharges = App\ShippingCharge::all();
                                    @endphp
                                    @if ($shipingCharges->isNotEmpty())
                                        <div class="shipping-options">
                                            @foreach ($shipingCharges as $index => $shipingCharge)
                                                <label class="shipping-option {{ $index === 0 ? 'active' : '' }}">
                                                    <input 
                                                        type="radio" 
                                                        name="area" 
                                                        id="area-{{ $shipingCharge->id }}" 
                                                        value="{{ $shipingCharge->charge }}" 
                                                        {{ $index === 0 ? 'checked' : '' }}
                                                        required
                                                    >
                                                    {{ $shipingCharge->name }}
                                                </label>
                                            @endforeach
                                        </div>
                                    @else
                                        <p>No shipping charges available.</p>
                                    @endif
                                </div>

                                
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <button type="button" id="orderConfirm"
                                            class="btn btn-lg btn-info btn-base-1 btn-block btn-icon-left strong-500 hov-bounce hov-shaddow buy-now"
                                            style="font-size:20px !important;"> অর্ডার কনফার্ম করুন </button>
                                </div>
                            </div>
                        </article> <!-- card-body.// -->
                    </aside>
                </div>
                <div class="col-md-6 orderDetails">
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
                                           
                                        @foreach(Cart::content() as $item)
                                    
                                        <tr class="cart-item">
                                            <td class="product-image" style="display: flex; flex-direction: row-reverse;">
                                                <a href="#" >
                                                    <img class="lazyload" src="{{ \App\Support\PublicMedia::url('product/thumbnail/'.$item->model->productImage) }}" style="max-width: 50px">
                                                </a>
                                                <button href="#"  onclick="removeFromCart('{{ $item->rowId }}')" class="btn btn-danger btn-sm">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>

                                            <td class="product-name">
                                                
                                                <span class="d-block">{{ $item->model->productName }}</span>
                                                <small class="lk-cart-stock">Stock: {{ $item->model->isSoldOut() ? 0 : $item->model->availableStock() }} pcs</small>
                                                <?php if($item->options->colorName){ ?>
                                                    <small class="text-muted" >Color: <?php echo $item->options->colorName; ?>,</small>
                                                <?php } ?>
                                                <?php if($item->options->sizeName){ ?>
                                                    <small class="text-muted">Size: <?php echo $item->options->sizeName; ?>,</small>
                                                <?php } ?>
                                                <?php if($item->options->optionName){ ?>
                                                    <small class="text-muted">Option: <?php echo $item->options->optionName; ?></small>
                                                <?php } ?>
                                            </td>

                                            <td class="product-price">
                                                <span class="d-block">TK {{ $item->price }}</span>
                                            </td>

                                            <td class="product-quantity">
                                                <div class="input-group input-spinner">
                                                    <div class="input-group-prepend">
                                                        <button class="btn btn-light btn-number" type="button" data-type="plus" data-field="quantity[{{ $item->id }}]"> + </button>
                                                    </div>
                                                    <input type="text" name="quantity[{{ $item->id }}]" class="form-control input-number" placeholder="1" value="{{ $item->qty }}" min="1" max="{{ max(1, min(10, $item->model->availableStock())) }}" onchange="updateQuantity('{{ $item->rowId }}', this)">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-light btn-number" type="button" data-type="minus"  data-field="quantity[{{ $item->id }}]"> − </button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="product-total">
                                                <span>TK {{ number_format((float) $item->price * (int) $item->qty, 2) }}</span>
                                            </td>

                                        </tr>
                                        @endforeach
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
                                <dd class="col-sm-4 text-danger text-right"><strong>TK <?php echo $lkFreeDeliveryCheckout ? 0 : $_SESSION['delivery'] ?></strong></dd>

                                <dt class="col-sm-8">Total:</dt>
                                <dd class="col-sm-4 text-right"><strong class="h5 text-dark">TK <?php echo Cart::subtotal('0','','')+($lkFreeDeliveryCheckout ? 0 : $_SESSION['delivery']); ?></strong></dd>                            </dl>

                        </article>

                    </aside>
                </div>

            </div>
        </div>
    </section>
    

    @else
        <div class="container pb-5 mb-sm-4">
            <div class="pt-5">
                <div class="card py-3 mt-sm-3">
                    <div class="card-body text-center">
                        <h2 class="h4 pb-3">কোন প্রোডাক্ট নেই</h2>
                        <a class="btn btn-primary mt-3" href="{{url('/')}}">প্রোডাক্ট বাছাই করুন</a>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <input type="hidden" id="csrf-token" name="_token" value="{{ csrf_token() }}">
@endsection

@push('js')
    <script>
        $(document).ready(function () {
            //updateQuantity(0,0);
            // $('#selectCourier').on('change',function (e) {
            //     var selectCourier = +$('#selectCourier option:selected').val();
            //     $.ajax({
            //         type: "get",
            //         url: "{{url('/updateDeliveryCharge')}}",
            //         data: {
            //             'selectCourier':selectCourier,
            //             '_token': '{{ csrf_token() }}'
            //         },
            //         success: function () {
            //             updateQuantity(0,0);
            //         }
            //     });
            // });
            $('input[name="area"]').on('change', function () {
                var selectedC = $('input[name="area"]:checked'); // Get the checked radio button
                var selectCV = selectedC.val(); // Get the value of the checked radio button
                $('.shipping-option').removeClass('active');
                $(this).closest('.shipping-option').addClass('active');
                    $.ajax({
                        type: "get",
                        url: "{{url('/updateDeliveryCharge')}}",
                        data: {
                            'selectCourier': selectCV,
                            '_token': '{{ csrf_token() }}'
                        },
                        success: function () {
                            updateQuantity(0, 0); // Call the updateQuantity function upon successful response
                        }
                });
            });
            $('input[name="area"]:checked').trigger('change');
            
            $(document).on("click", "#orderConfirm", function () {
                constantValue = 0;
                var bdPhoneRegex = /^(013|014|015|016|017|018|019)\d{8}$/;
                var customerName = $('#customerName');
                var customerAddress = $('#customerAddress');
                var customerPhone = $('#customerPhone');
                //var selectCourier = $('#selectCourier option:selected');
                
                var selectCourier = $('input[name="area"]:checked');
                //var selectCourier = selectedCourier.val(); // Get the value of the checked radio button
                
                
                if (!customerName.val()) {
                    customerName.addClass("has-error");
                    constantValue = 1;
                    showFrontendAlert('error', 'Invalied Customenr Name!');
                }
                if (!customerAddress.val()) {
                    customerAddress.addClass("has-error");
                    constantValue = 1;
                    showFrontendAlert('error', 'Invalied Customer Address!');
                }
                if (!customerPhone.val()) {
                    customerPhone.addClass("has-error");
                    constantValue = 1;
                    showFrontendAlert('error', 'Invalied Phone Number!');
                }
                if (!bdPhoneRegex.test(customerPhone.val())) {
                    customerPhone.addClass("has-error");
                    constantValue = 1;
                    showFrontendAlert('error', 'Invalied Phone Number!');
                }
                // Log the whole element
                console.log(selectCourier);
                
                // Log its value
                console.log(selectCourier.val());
                if (selectCourier.val() === '') {
                    selectCourier.addClass("has-error");
                    showFrontendAlert('error', 'Unsuccessful to Place order');
                    constantValue = 1;
                }
                console.log("Customer Name:", customerName.val());
                console.log("Customer Address:", customerAddress.val());
                console.log("Customer Phone:", customerPhone.val());
                console.log("Is Phone Valid:", bdPhoneRegex.test(customerPhone.val()));
                console.log("Selected Courier:", selectCourier.val(), "-", selectCourier.text());
               
                if (!selectCourier) {
                    
                    // If no radio button is selected, handle the error
                    $('input[name="area"]').closest('.form-group').addClass("has-error");
                    showFrontendAlert('error', 'Please Aelect Area');
                    constantValue = 1;
                }
                if (constantValue === 1) {
                    $('html, body').animate(  {  scrollTop: $('body').position().top  },  500   );
                } else {
                    $.ajax({
                        type: "post",
                        url: "{{url('/placeOrder')}}",
                        data: {
                            'customerName': customerName.val(),
                            'customerAddress': customerAddress.val(),
                            'customerPhone': customerPhone.val(),
                            'selectCourier': selectCourier.val(),
                            '_token': '{{ csrf_token() }}'
                        },
                        success: function (data) {
                            if(data['status'] === 'success'){
                                showFrontendAlert('success', 'Successfully Place order');
                                window.location.href = data['link'];

                            }else if(data['status'] === 'blocked'){
                                showFrontendAlert('error', data['message']);
                            }
                            else{
                                showFrontendAlert('error', data['message'] || 'Unsuccessful to Place order');

                            }
                        },
                        error: function (xhr) {
                            showFrontendAlert('error', (xhr.responseJSON && xhr.responseJSON.message) || 'Unable to place order. Please check stock.');
                        }
                    });
                }
            });
        });
    </script>
    <script>
        window.dataLayer = window.dataLayer || [];
    
        dataLayer.push({
            event: 'begin_checkout',
            value: "{{Cart::subtotal()}}", // Pass total cart value for the checkout
            currency: "BDT", // Set currency (BDT for Bangladesh)
            ecommerce: {
                checkout: {
                    actionField: {
                        step: 1, // This represents the step number (1 = checkout page)
                        option: 'Standard Checkout' // You can use this for different checkout options if applicable
                    },
                    products: [
                        @foreach(Cart::content() as $item)
                        {
                            id: "{{ $item->id }}",
                            name: "{{ $item->model->productName }}",
                            price: "{{ $item->productSalePrice }}",
                            quantity: {{ $item->qty }},
                            category: "{{ $item->model->category->name ?? 'Uncategorized' }}",
                            variant: "{{ $item->options->colorName ?? 'No color' }}",
                        },
                        @endforeach
                    ]
                }
            },
            user_data: {
                email: "{{ Auth::check() ? Auth::user()->email : '' }}",
                phone: "{{ Auth::check() ? Auth::user()->phone : '' }}"
            }
        });
    
        // Facebook Pixel Tracking for begin_checkout
        fbq('track', 'AddToCart', {
            content_ids: [
                @foreach(Cart::content() as $item)
                    "{{ $item->id }}",
                @endforeach
            ],
            content_name: "Checkout",
            content_category: "Products",
            value: "{{ Cart::subtotal() }}",
            currency: "BDT"
        });
    
    </script>
<script>

function saveInput() {
    let customerPhone = document.querySelector('#customerPhone')?.value;
    let customerName = document.querySelector('#customerName')?.value;
    let customerAddress = document.querySelector('#customerAddress')?.value;
    let selectCourier = document.querySelector('#selectCourier')?.value;
    let csrfToken = document.querySelector('#csrf-token')?.value;

    fetch("{{ url('/save-input') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            customerPhone,
            customerName,
            customerAddress,
            selectCourier
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response:', data);
        // Show success message or handle response
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
@endpush