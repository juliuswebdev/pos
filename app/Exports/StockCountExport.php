<?php

namespace App\Exports;

use App\Product;
use Auth;
use DB;
use App\CountHeader;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class StockCountExport implements FromCollection, WithHeadings, WithColumnWidths
{

 
    public $id;
    function __construct($id) {
        $this->id = $id;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        //
        $count_header = CountHeader::find($id);
        echo $count_header->count_type .'<br>';

        $products_query = DB::table('products')->where('business_id', Auth::user()->business_id);
        $products_query->leftJoin('variation_location_details', function($join) {
            $join->on('variation_location_details.product_id', '=', 'products.id');
        });

        if($count_header->count_sku_with_zero_stock_onhand == 1) {
            $products_query->whereNull('variation_location_details.qty_available');
        }

        if($count_header->count_type == 'partial') {
            $products_query->whereIn('products.category_id', [$count_header->categories])
                    ->orWhereIn('products.sub_category_id', [$count_header->sub_categories])
                    ->orWhereIn('products.brand_id', [$count_header->brands]);
        }

        $products = $products_query
            ->select([
                'products.sku AS sku',
                DB::raw('CONCAT("") AS upc_code'),
                DB::raw('CONCAT("") AS imei_or_serial'),
                'products.name AS product_name',
                DB::raw("( SELECT COALESCE(SUM(qty_available), 0) FROM variation_location_details WHERE product_id = products.id
                ) as expected"),
                DB::raw('CONCAT("") AS counted'),
                'products.category_id AS category',
                'products.sub_category_id AS sub_category',
                'products.brand_id AS brand'
            ])
        ->get();

        return $products;

    }

    /**
    * @return \Illuminate\Support\Heading
    */
    public function headings(): array
    {
        return ["SKU", "UPC CODE", "IMEI OR SERIAL", "PRODUCT NAME", "EXPECTED", "COUNTED"];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 170,
            'B' => 170,
            'C' => 1150,
            'D' => 170,
            'E' => 710            
        ];
    }
}
