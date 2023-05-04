@php
	$is_mobile = isMobile();
@endphp
<div class="row">
	<div class="pos-form-actions">
		<div class="col-md-12">
			@if($is_mobile)
				<div class="col-md-12 text-right">
					<b>@lang('sale.total_payable'):</b>
					<input type="hidden" name="final_total" 
												id="final_total_input" value=0>
					<span id="total_payable" class="text-success lead text-bold text-right">0</span>
				</div>
			@endif
			<button type="button" class="@if($is_mobile) col-xs-6 @endif btn bg-info text-white btn-default btn-flat @if($pos_settings['disable_draft'] != 0) hide @endif" id="pos-draft" @if(!empty($only_payment)) disabled @endif><i class="fas fa-edit"></i> @lang('sale.draft')</button>
			<button type="button" class="btn btn-default bg-yellow btn-flat @if($is_mobile) col-xs-6 @endif" id="pos-quotation" @if(!empty($only_payment)) disabled @endif><i class="fas fa-edit"></i> @lang('lang_v1.quotation')</button>

			@if(empty($pos_settings['disable_suspend']))
				<button type="button" 
				class="@if($is_mobile) col-xs-6 @endif btn bg-red btn-default btn-flat no-print pos-express-finalize" 
				data-pay_method="suspend"
				title="@lang('lang_v1.tooltip_suspend')" @if(!empty($only_payment)) disabled @endif>
				<i class="fas fa-pause" aria-hidden="true"></i>
				@lang('lang_v1.suspend')
				</button>
			@endif

			@if(empty($pos_settings['disable_credit_sale_button']))
				<input type="hidden" name="is_credit_sale" value="0" id="is_credit_sale">
				<button type="button" 
				class="btn bg-purple btn-default btn-flat no-print pos-express-finalize @if($is_mobile) col-xs-6 @endif" 
				data-pay_method="credit_sale"
				title="@lang('lang_v1.tooltip_credit_sale')" @if(!empty($only_payment)) disabled @endif>
					<i class="fas fa-check" aria-hidden="true"></i> @lang('lang_v1.credit_sale')
				</button>
			@endif
			<button type="button" 
				class="btn bg-maroon btn-default btn-flat no-print @if(!empty($pos_settings['disable_suspend'])) @endif pos-express-finalize @if(!array_key_exists('card', $payment_types)) hide @endif @if($is_mobile) col-xs-6 @endif" 
				data-pay_method="card"
				title="@lang('lang_v1.tooltip_express_checkout_card')" >
				<i class="fas fa-credit-card" aria-hidden="true"></i> @lang('lang_v1.express_checkout_card')
			</button>

			<button type="button" class="btn bg-navy btn-default @if(!$is_mobile) @endif btn-flat no-print @if($pos_settings['disable_pay_checkout'] != 0) hide @endif @if($is_mobile) col-xs-6 @endif" id="pos-finalize" title="@lang('lang_v1.tooltip_checkout_multi_pay')"><i class="fas fa-money-check-alt" aria-hidden="true"></i> @lang('lang_v1.checkout_multi_pay') </button>
			@if(($pos_settings['pay_safe_key_id']))
			<script src="{{ asset('ingenico/dist/connectsdk.min.js') }}"></script>
			<script>
			var session = new connectsdk.Session({
    clientSessionId : "47e9dc332ca24273818be2a46072e006",
    customerId : "9991-0d93d6a0e18443bd871c89ec6d38a873",
    clientApiUrl : "https://clientapi.com",
    assetUrl : "https://asset.com"
});
				var paymentDetails = { "totalAmount" : 16195, // in cents
    "countryCode" : "NL",
    "locale" : "nl_NL", // as specified in the config center
    "isRecurring" : false, // set if recurring
    "currency" : "EUR" // set currency, see dropdown
    };

session.getBasicPaymentItems(paymentDetails, true).then(function (basicPaymentItems) {
    // The promise has fulfilled, basicPaymentItems is an instance of BasicPaymentItems

    // this is an array of BasicPaymentproduct objects
    basicPaymentItems.basicPaymentItems;

    // this is an array of AccountOnFile objects
    basicPaymentItems.accountsOnFile;

    // This returns the Visa BasicPaymentProduct object in this PaymentProducts object if it exists.
    var paymentProduct = basicPaymentItems.paymentProductById["1"];

    // The raw JSON that the server returned and that was used to fill this object
    basicPaymentItems.json;

}, function() {
    // The promise failed, inform the user what happened.
    
});
			</script>
			<button type="button" class="btn btn-default">PaySafe</button>
			@endif
			<button type="button" class="btn btn-success @if(!$is_mobile) @endif btn-flat no-print @if($pos_settings['disable_express_checkout'] != 0 || !array_key_exists('cash', $payment_types)) hide @endif pos-express-finalize @if($is_mobile) col-xs-6 @endif" data-pay_method="cash" title="@lang('tooltip.express_checkout')"> <i class="fas fa-money-bill-alt" aria-hidden="true"></i> @lang('lang_v1.express_checkout_cash')</button>

			@if(empty($edit))
				<button type="button" class="btn btn-danger btn-flat @if($is_mobile) col-xs-6 @else btn-xs @endif" id="pos-cancel"> <i class="fas fa-window-close"></i> @lang('sale.cancel')</button>
			@else
				<button type="button" class="btn btn-danger btn-flat hide @if($is_mobile) col-xs-6 @else btn-xs @endif" id="pos-delete" @if(!empty($only_payment)) disabled @endif> <i class="fas fa-trash-alt"></i> @lang('messages.delete')</button>
			@endif

			@if(!$is_mobile)
			<div class="bg-navy pos-total text-white">
			<span class="text">@lang('sale.total_payable')</span>
			<input type="hidden" name="final_total" 
										id="final_total_input" value=0>
			<span id="total_payable" class="number">0</span>
			</div>
			@endif

			@if(!isset($pos_settings['hide_recent_trans']) || $pos_settings['hide_recent_trans'] == 0)
			<button type="button" class="pull-right btn btn-primary btn-flat @if($is_mobile) col-xs-6 @endif" data-toggle="modal" data-target="#recent_transactions_modal" id="recent-transactions"> <i class="fas fa-clock"></i> @lang('lang_v1.recent_transactions')</button>
			@endif

			
			
		</div>
	</div>
</div>
@if(isset($transaction))
	@include('sale_pos.partials.edit_discount_modal', ['sales_discount' => $transaction->discount_amount, 'discount_type' => $transaction->discount_type, 'rp_redeemed' => $transaction->rp_redeemed, 'rp_redeemed_amount' => $transaction->rp_redeemed_amount, 'max_available' => !empty($redeem_details['points']) ? $redeem_details['points'] : 0])
@else
	@include('sale_pos.partials.edit_discount_modal', ['sales_discount' => $business_details->default_sales_discount, 'discount_type' => 'percentage', 'rp_redeemed' => 0, 'rp_redeemed_amount' => 0, 'max_available' => 0])
@endif

@if(isset($transaction))
	@include('sale_pos.partials.edit_order_tax_modal', ['selected_tax' => $transaction->tax_id])
@else
	@include('sale_pos.partials.edit_order_tax_modal', ['selected_tax' => $business_details->default_sales_tax])
@endif

@include('sale_pos.partials.edit_shipping_modal')