<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopService extends Model
{
    use HasFactory;
    protected $table = 'shop_services';
    protected $fillable = ['shop_id','service_id','price','status','type','discount','from_date','to_date'];
    protected $casts = [
        'price' => 'double',
        'discount'=>'double',
        'commission' => 'double'
    ];
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
