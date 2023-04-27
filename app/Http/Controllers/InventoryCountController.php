<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\CountHeader;
use App\CountDetail;
use App\CountFrozenInventoryBalance;
use App\BusinessLocation;
use App\Category;
use App\Brands;
use App\Product;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockCountExport;
use App\Imports\CountDetailImport;

use Datatables;
use Auth;
use DB;


class InventoryCountController extends Controller
{
    //

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function inventory_count()
    {
        if (! auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }
        $request = request();
        if (request()->ajax()) {
 
            $inventory_count = CountHeader::where('count_header.business_id', Auth::user()->business_id)
                ->leftJoin('business_locations', function($join){
                    $join->on('count_header.business_location_id', '=', 'business_locations.id');
                })
                ->select([
                    'count_header.id AS id',
                    'count_header.final_file AS final_file',
                    'count_header.created_at AS date',
                    'count_header.count_reference',
                    'count_header.description AS count_description',
                    DB::raw('CONCAT( business_locations.name, " [", business_locations.location_id, "]") AS location'),
                    DB::raw('( 
                        SELECT GROUP_CONCAT(c.name) FROM categories as c WHERE FIND_IN_SET(c.id, count_header.categories)
                    ) 
                    AS categories '),
                    DB::raw('( 
                        SELECT GROUP_CONCAT(c.name) FROM categories as c WHERE FIND_IN_SET(c.id, count_header.sub_categories)
                    ) 
                    AS sub_categories '),
                    DB::raw('( 
                        SELECT GROUP_CONCAT(c.name) FROM brands as c WHERE FIND_IN_SET(c.id, count_header.brands)
                    ) 
                    AS brands '),
                    'count_header.count_type AS type',
                    'count_header.status'
                ]);
            return Datatables::of($inventory_count)
                ->addColumn(
                    'action',
                    function($row) {
                        $html = '<div class="btn-group" class="inventory_count_dropdown">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false">Actions<span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button>
                            <ul class="action dropdown-menu dropdown-menu-left" role="menu">';

                            if($row->status == 1) {
                                $html .=  '<li>
                                    <a href="#" data-toggle="modal" data-target="#freeze_confirm" class="action_modal_form" data-action="'.action('App\Http\Controllers\InventoryCountController@freeze_count', [$row->id]).'">
                                        <i class="fa fa-file" aria-hidden="true" style="margin-right: 10px"></i>'.__("inventory_count.freeze_count").'
                                    </a>
                                </li>';
                            } else {
                                $html .=  '<li class="disabled">
                                    <a href="#">
                                        <i class="fa fa-file" aria-hidden="true" style="margin-right: 10px"></i>'.__("inventory_count.freeze_count").'
                                    </a>
                                </li>';  
                            }

                            if($row->status == 2) {
                            $html .=  '<li>
                                    <a class="download_inventory_count" href="'.action('App\Http\Controllers\InventoryCountController@download_initial_file', [$row->id]).'" data-count_reference="'.$row->count_reference.'">
                                        <i class="fa fa-download" aria-hidden="true"></i>'.__("inventory_count.download_count").'
                                    </a>
                                </li>
                                <li>
                                    <a href="#" data-toggle="modal" data-target="#upload_count" class="action_modal_form" data-action="'.action('App\Http\Controllers\InventoryCountController@upload_final_file', [$row->id]).'">
                                        <i class="fa fa-upload" aria-hidden="true" style="margin-right: 10px"></i>'.__("inventory_count.upload_count").'
                                    </a>
                                </li>';
                            } else {
                                $html .=  '<li class="disabled">
                                    <a href="#">
                                        <i class="fa fa-download" aria-hidden="true"></i>'.__("inventory_count.download_count").'
                                    </a>
                                </li>
                                <li class="disabled">
                                    <a href="#">
                                        <i class="fa fa-upload" aria-hidden="true" style="margin-right: 10px"></i>'.__("inventory_count.upload_count").'
                                    </a>
                                </li>';
                            }

                            if($row->final_file) {
                            $html .=  '
                                    <li class="divider"></li>
                                    <li>
                                        <a href="/uploads/csv/'.$row->final_file.'">
                                            <i class="fa fa-download" aria-hidden="true"></i>'.__("inventory_count.initial_file").'
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" data-toggle="modal" data-target="#final_report">
                                            <i class="fa fa-file" aria-hidden="true"></i>'.__("inventory_count.final_report").'
                                        </a>
                                    </li>';
                            }
                            $html .=  '</ul>
                            </div>';
                    return $html;
                    })

                ->editColumn(
                    'type',
                    function($row) {
                        $text = 'N/A';
                        $type = 'default';
                        if($row->type == 'entire_location') {
                            $type = 'info';
                            $text = 'Entire Location';
                        } else if ($row->type == 'partial') {
                            $type = 'primary';
                            $text = 'Partial';
                        } else if ($row->type == 'mixed_skus') {
                            $type = 'warning';
                            $text = 'Mixed SKUs';
                        }
                        return '<span class="btn btn-xs btn-'.$type.'">'.$text.'</span>';
                    }
                )
                ->editColumn(
                    'status',
                    function($row) {
                        $text = 'N/A';
                        if($row->status == 1) {
                            $text = 'Batch Created';
                        } else if ($row->status == 2) {
                            $text = 'Frozen';
                        } else if ($row->status == 3) {
                            $text = 'Count Updated';
                        } else if ($row->status == 4) {
                            $text = 'Posted';
                        }
                        return $text;
                    }
                )
                ->filter(function ($instance) use ($request) {
                    if ($request->get('status')) {
                        $instance->where('status', $request->get('status'));
                    }
                })
                ->rawColumns(['action', 'type'])
                ->make(true);
        }

        $business_locations = BusinessLocation::where('business_id',  Auth::user()->business_id)
                            ->select(['id', 'name', 'location_id'])
                            ->get();

        $categories = Category::where('business_id',  Auth::user()->business_id)
                        ->where('parent_id' , 0)
                        ->select(['id', 'name', 'short_code'])
                        ->get();

        $brands = Brands::where('business_id',  Auth::user()->business_id)
                        ->select(['id', 'name'])
                        ->get();

        return view('inventory_count.index', [
            'business_locations' => $business_locations,
            'categories' => $categories,
            'brands' => $brands
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {
        $count_header = CountHeader::create([
            'count_reference' => 'sc-'.date("Ymd-his"),
            'business_id' => Auth::user()->business_id,
            'business_location_id' => $request['business_location_id'],
            'description' => $request['description'],
            'count_type' => $request['count_type'],
            'count_sku_with_zero_stock_onhand' => $request['count_sku_with_zero_stock_onhand'],
            'frozen_count' => 0,
            'status' =>  1,
            'categories' => ($request['count_type'] == 'partial') ? $request['categories'] ? implode(',', $request['categories']) : '' : null,
            'sub_categories' => ($request['count_type'] == 'partial') ? $request['sub_categories'] ? implode(',', $request['sub_categories']) : '' : null,
            'brands' => ($request['count_type'] == 'partial') ? $request['brands'] ? implode(',', $request['brands']): '' : null,
            'created_by' => Auth::user()->id,
            'user_froze_count' => 0,
            'user_posted_count' => 0
        ]);

        if($count_header->count_type == 'partial' || $count_header->count_type == 'entire_location' ) {

            $products_query = $this->filter_products($count_header);

            $products = $products_query->select([
                DB::raw('CONCAT("'.$count_header->id.'") AS count_header_id'),
                'products.sku AS sku',
                DB::raw('CONCAT("") AS upc'),
                DB::raw('CURRENT_TIMESTAMP() AS created_at')
            ]);

            CountDetail::insertUsing([
                'count_header_id',
                'sku',
                'upc',
                'created_at'
            ],$products);


        }
        return redirect()->back();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function freeze_count($id)
    {
        // Freeze count
        $count_header = CountHeader::find($id);
        $count_header->status = 2;
        $count_header->user_froze_count = Auth::user()->id;
        $count_header->save();

        if($count_header->count_type == 'partial' || $count_header->count_type == 'entire_location') {
            $products_query = $this->filter_products($count_header);

            $products = $products_query->select([
                DB::raw('CONCAT("'.$count_header->id.'") AS count_header_id'),
                'products.sku AS sku',
                DB::raw('CONCAT("") AS upc'),
                DB::raw("( SELECT COALESCE(SUM(qty_available), 0) FROM variation_location_details WHERE product_id = products.id
                    ) as frozen_quantity"),
                DB::raw('CURRENT_TIMESTAMP() AS created_at'),
                DB::raw('CURRENT_TIMESTAMP() AS updated_at')
            ]);

            CountFrozenInventoryBalance::insertUsing([
                'count_header_id',
                'sku',
                'upc',
                'frozen_quantity',
                'created_at',
                'updated_at'
            ],$products);

            foreach($products->get() as $item) {
                CountDetail::where('count_header_id', $item->count_header_id)->where('sku', $item->sku)->update(['frozen_quantity' => $item->frozen_quantity]);
            }
        }
        
        return redirect()->back();
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function qty_adjustment($id) 
    {
        return view('inventory_count.qty_adjustment');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function download_initial_file($id) 
    {
        
        $count_header = CountHeader::find($id);
        $file_name = $count_header->count_reference ?? 'sc-'.$id;
        return Excel::download(new StockCountExport($this->filter_products($count_header), $count_header->count_type), $file_name.'.csv');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function upload_final_file(Request $request, $id)
    {

        $request->validate([
            "final_file" => 'required|mimetypes:text/csv,text/plain,application/csv,text/comma-separated-values,text/anytext,application/octet-stream,application/txt'
        ]);

        if ($request->hasFile('final_file')) {
            $file = $request->file('final_file');
            $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $fileName = 'sc-'.date("Ymd-his") . "." . $file->getClientOriginalExtension();
            $file->storeAs('csv', $fileName, 'local');
            $csv = $fileName;

            $count_header = CountHeader::find($id);
            $count_header->final_file = $csv;
            $count_header->status = 3;
            $count_header->save();

        }

        Excel::import(new CountDetailImport($id, $count_header->count_type),request()->file('final_file'));
        return back();

    }

        /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
    */
    public function filter_products($count_header)
    {
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

        return $products_query;
    }

    
}
