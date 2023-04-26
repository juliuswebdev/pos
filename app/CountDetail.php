<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'count_header_id',
        'sku',
        'upc',
        'frozen_quantity',
        'count_quantity',
    ];

    protected $table = 'count_detail';
}
