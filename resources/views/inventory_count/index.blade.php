@extends('layouts.app')
@section('title', __('inventory_count.inventory_count'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('inventory_count.inventory_count')
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => __('inventory_count.all_inventory_counts')])
        @slot('tool')
            <div class="box-tools">
                <a class="btn btn-block btn-primary" href="#" data-toggle="modal" data-target="#create_count_stock">
                <i class="fa fa-plus"></i> @lang('inventory_count.count_inventory')</a>
            </div>
        @endslot
        <div class="table-responsive" style="min-height: 300px">
            <table class="table table-bordered table-striped ajax_view" id="inventory_count_table">
                <thead>
                    <tr>
                        <th>@lang('inventory_count.action')</th>
                        <th>@lang('inventory_count.date')</th>
                        <th>@lang('inventory_count.count_reference')</th>
                        <th>@lang('inventory_count.count_description')</th>
                        <th>@lang('inventory_count.location')</th>
                        <th>@lang('inventory_count.categories')</th>
                        <th>@lang('inventory_count.sub_categories')</th>
                        <th>@lang('inventory_count.brands')</th>
                        <th>@lang('inventory_count.type')</th>
                        <th>@lang('inventory_count.status')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

    <div id="create_count_stock" tabindex="-1" role="dialog" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ action([\App\Http\Controllers\InventoryCountController::class, 'store']) }}" accept-charset="UTF-8">
                {{ csrf_field() }}
             
                <div class="modal-header">
                    <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">@lang('inventory_count.count_inventory')</h4>
                </div>
                <div class="modal-body">
                    <p class="italic"><small>@lang('inventory_count.required_notes')</small></p>
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>@lang('inventory_count.location') *</label>
                            <select required name="business_location_id" id="business_location_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="@lang('inventory_count.select_location')">
                                <option selected disabled value="">@lang('inventory_count.select_location')</option>
                                @foreach($business_locations as $item)
                                    <option value="{{ $item->id }}">{{$item->name}} [{{$item->location_id}}]</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>@lang('inventory_count.count_type') *</label>
                            <select class="form-control" name="count_type" id="count_type">
                                <option value="entire_location">@lang('inventory_count.entire_location')</option>
                                <option value="partial">@lang('inventory_count.partial')</option>
                                <option value="mixed_skus">@lang('inventory_count.mixed_skus')</option>
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>@lang('inventory_count.batch_description') *</label>
                            <textarea name="description" rows="5" class="form-control" required></textarea>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>@lang('inventory_count.count_sku'): </label>
                            <label for="count_sku_with_zero_stock_onhand_1">@lang('inventory_count.yes')</label>
                            <input type="radio" name="count_sku_with_zero_stock_onhand" value="1" id="count_sku_with_zero_stock_onhand_1">
                            <label for="count_sku_with_zero_stock_onhand_2">@lang('inventory_count.no')</label>
                            <input type="radio" name="count_sku_with_zero_stock_onhand" value="0" id="count_sku_with_zero_stock_onhand_0" checked>
                        </div>
                        <div id="partial_area" style="display: none">
                            <div class="col-md-12 form-group">
                                <label>@lang('inventory_count.category')</label>
                                <select name="categories[]" id="categories" class="form-control select2-multiple" title="@lang('inventory_count.select_categories')" multiple>
                                    @foreach($categories as $item)
                                        <option value="{{ $item->id }}" data-cat_id="{{$item->id}}">{{$item->name}} [{{$item->short_code}}]</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 form-group">
                                <label>@lang('inventory_count.sub_category')</label>
                                <select name="sub_categories[]" id="sub_categories" class="form-control select2-multiple" title="Select Sub Category..." multiple>
                                </select>
                            </div>
                            <div class="col-md-12 form-group">
                                <label>@lang('inventory_count.brand')</label>
                                <select name="brands[]" id="brands" class="form-control select2-multiple" title="@lang('inventory_count.brands')" multiple>
                                    @foreach($brands as $item)
                                        <option value="{{ $item->id }}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="submit" value="Submit" class="btn btn-primary">
                    </div>
                </div>
            </form>
        </div>
        </div>
    </div>

    <div id="finalize" tabindex="-1" role="dialog" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="" accept-charset="UTF-8" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">@lang('inventory_count.count_inventory')</h4>
                    </div>
                    <div class="modal-body">
                        <p class="italic"><small>@lang('inventory_count.required_notes')</small></p>
                        <div class="form-group">
                            <label>Upload File *</label>
                            <input type="file" name="final_file" class="form-control"  accept=".csv">
                        </div>
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <ul>
                                    <li>{{ $errors->first('final_file'); }}</li>
                                </ul>
                            </div>
                        @endif
                        <div class="form-group">
                            <input type="submit" value="Submit" class="btn btn-primary">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="final_report" tabindex="-1" role="dialog" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="" accept-charset="UTF-8" enctype="multipart/form-data"><input name="_token" type="hidden">
                    <div class="modal-header">
                        <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">@lang('inventory_count.count_inventory')</h4>
                    </div>
                    <div class="modal-body">                        
                        <strong>@lang('inventory_count.date'): </strong>20/04/2023<br>
                        <strong>@lang('inventory_count.location'): </strong>Shop 1<br>
                        <strong>@lang('inventory_count.count_type'): </strong>Entire Location<br>
                        <span>
                            <strong>@lang("inventory_count.files"): </strong>
                            &nbsp;&nbsp;<a href="" class="btn btn-sm btn-primary"><i class="fa fa-download"></i> @lang("inventory_count.initial_file")</a>
                            &nbsp;&nbsp;<a href="" class="btn btn-sm btn-info"><i class="fa fa-download"></i> @lang("inventory_count.final_file")</a>
                        </span>
                        <br><br>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>@lang("inventory_count.sku")</th>
                                    <th>@lang("inventory_count.upc_code")</th>
                                    <th>@lang("inventory_count.imei_or_serial_numbers")</th>
                                    <th>@lang("inventory_count.product_name")</th>
                                    <th>@lang("inventory_count.expected")</th>
                                    <th>@lang("inventory_count.counted")</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>1</strong></td>
                                    <td>2881990</td>
                                    <td>2881990</td>
                                    <td>11111111</td>
                                    <td>test</td>
                                    <td>1</td>
                                    <td>2</td>
                                </tr>
                            </tbody>
                        </table>
                        <a class="btn btn-primary d-print-none" href="/inventory_count/1/qty-adjustment"><i class="fa fa-plus"></i> Add Adjustment</a></div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</section>
<!-- /.content -->
@stop
@section('javascript')
	<script src="{{ asset('js/inventory_count.js?v=' . $asset_v) }}"></script>
    
        @if($errors->count() > 0){
            @if($errors->first('final_file'))
            <script>
                $(function($){ $("#finalize").modal('show'); }) 
            </script>
            @endif
        @endif
   
@endsection