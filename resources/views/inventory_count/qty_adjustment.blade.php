@extends('layouts.app')
@section('title', __('inventory_count.add_adjustment'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('inventory_count.add_adjustment')
    </h1>
</section>
<section class="content">
    <div class="box box-primary">
        <div class="box-header">
            <p class="italic"><small>@lang('inventory_count.required_notes')</small></p>
        </div>
        <div class="box-body">
            <form method="POST" action="" accept-charset="UTF-8" id="adjustment-form" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('inventory_count.location') *</label>
                                    <select required id="location" name="location" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins">
                                        <option value="1">Shop 1</option>
                                        <option value="2">Shop 2</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>@lang('inventory_count.attach_document')</label>
                                    <input type="file" name="document" class="form-control" >
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <label>@lang('inventory_count.select_product')</label>
                                <div class="form-group">
                                    <input type="text" name="product_code_name" placeholder="@lang('inventory_count.search')" class="form-control" />
                                </div>
                            </div>
                        </div>
                        <div class="row mt-5">
                            <div class="col-md-12">
                                <h5>@lang('inventory_count.order_table') *</h5>
                                <div class="table-responsive mt-3">
                                    <table id="myTable" class="table table-hover order-list">
                                        <thead>
                                            <tr>
                                                <th>@lang('inventory_count.product_name')</th>
                                                <th>@lang('inventory_count.sku')</th>
                                                <th>@lang('inventory_count.upc_code')</th>
                                                <th>@lang('inventory_count.quantity')</th>
                                                <th><i class="fa fa-trash"></i></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Rich Dad Poor Dad</td>
                                                <td>02881990</td>
                                                <td>02881990</td>
                                                <td><input type="number" class="form-control qty" name="qty[]" value="35" required step="any" /></td>
                                                <td>
                                                    <button type="button" class="ibtnDel btn btn-md btn-danger">Delete</button>
                                                    <input type="hidden" class="product-code" value="02881990" />
                                                    <input type="hidden" class="product-id" name="product_id[]" value="1" />
                                                    <input type="hidden" class="product-code" name="product_code[]" value="02881990" />
                                                </td>
                                            <tr>
                                        </tbody>
                                        <tfoot class="tfoot active">
                                            <th colspan="2">@lang('inventory_count.total')</th>
                                            <th id="total-qty" colspan="2">0</th>
                                            <th><i class="dripicons-trash"></i></th>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <input type="hidden" name="total_qty" />
                                    <input type="hidden" name="item" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>@lang('inventory_count.note')</label>
                                    <textarea rows="5" class="form-control" name="note"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="submit" value="Submit" class="btn btn-primary" id="submit-button">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<!-- /.content -->
@stop
@section('javascript')
	<script src="{{ asset('js/inventory_count.js?v=' . $asset_v) }}"></script>
@endsection