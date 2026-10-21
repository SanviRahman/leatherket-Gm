@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="card-box">
                <h4 class="header-title mb-4">Website Settings</h4>
                <div class="row">
                    <div class="col-sm-3">
                        <div class="nav flex-column nav-pills nav-pills-tab" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            <a class="nav-link active show mb-1" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="false">General</a>
                            <a class="nav-link mb-1" id="v-pills-profile-tab" data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false">  Site Logo</a>
                            <a class="nav-link mb-1" id="v-pills-messages-tab" data-toggle="pill" href="#v-pills-messages" role="tab" aria-controls="v-pills-messages" aria-selected="false"> Footer</a>
                            <a class="nav-link mb-1 " id="v-pills-settings-tab" data-toggle="pill" href="#v-pills-settings" role="tab" aria-controls="v-pills-settings" aria-selected="false"> Social Links</a>
                            <a class="nav-link mb-1 " id="v-pills-settings-tab" data-toggle="pill" href="#v-pills-analytics" role="tab" aria-controls="v-pills-settings" aria-selected="false"> Analytics</a>
                            <a class="nav-link mb-1 " id="v-pills-settings-tab" data-toggle="pill" href="#v-pills-payments" role="tab" aria-controls="v-pills-settings" aria-selected="false"> Payments</a>
                            <a class="nav-link mb-1" data-toggle="pill" href="#v-pills-collections" role="tab">Shop by Collection</a>
                            <a class="nav-link mb-1 " id="v-pills-settings-tab" data-toggle="pill" href="#v-pills-product" role="tab" aria-controls="v-pills-settings" aria-selected="false"> Products</a>
                            <a class="nav-link mb-1 " id="v-pills-settings-tab" data-toggle="pill" href="#v-pills-pathao-api" role="tab" aria-controls="v-pills-settings" aria-selected="false"> Pathao API</a>
                            <a class="nav-link mb-1 " id="v-pills-settings-tab" data-toggle="pill" href="#v-pills-checkout" role="tab" aria-controls="v-pills-settings" aria-selected="false"> Checkout</a>
                        </div>
                    </div> <!-- end col-->
                    <div class="col-sm-9">
                        <div class="tab-content pt-0">
                            <div class="tab-pane fade active show" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                                @include('admin.settings.includes.general')
                            </div>
                            <div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                                @include('admin.settings.includes.logo')
                            </div>
                            <div class="tab-pane fade" id="v-pills-messages" role="tabpanel" aria-labelledby="v-pills-messages-tab">
                                @include('admin.settings.includes.footer_seo')
                            </div>
                            <div class="tab-pane fade  show" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                                @include('admin.settings.includes.social_links')
                            </div>
                            <div class="tab-pane fade  show" id="v-pills-analytics" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                                @include('admin.settings.includes.analytics')
                            </div>
                            <div class="tab-pane fade  show" id="v-pills-payments" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                                @include('admin.settings.includes.payments')
                            </div>
                            <div class="tab-pane fade" id="v-pills-collections" role="tabpanel">
                                @include('admin.settings.includes.shop_by_collection')
                            </div>
                            <div class="tab-pane fade  show" id="v-pills-product" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                                @include('admin.settings.includes.product')
                            </div>
                            <div class="tab-pane fade  show" id="v-pills-pathao-api" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                                @include('admin.settings.includes.pathao_api')
                            </div>
                            <div class="tab-pane fade  show" id="v-pills-checkout" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                                @include('admin.settings.includes.checkout')
                            </div>
                            

                        </div>
                    </div> <!-- end col-->
                </div> <!-- end row-->

            </div>
        </div> <!-- end col -->
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function(){
            var activeTab = new URLSearchParams(window.location.search).get('tab');
            if (activeTab === 'collections') {
                $('a[href="#v-pills-collections"]').tab('show');
            } else if (activeTab === 'footer') {
                $('a[href="#v-pills-messages"]').tab('show');
            }
        });
    </script>
@endpush

