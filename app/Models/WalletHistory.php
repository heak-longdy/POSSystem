<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalletHistory extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'wallet_histories';
    protected $fillable = ['shop_id', 'amount', 'status', 'image', 'type'];
    protected $appends = [
        'created_date',
        'image_url'
    ];
    protected $casts = [
        'amount' => 'double',
    ];
    public function getImageUrlAttribute()
    {
        if ($this->image != null) {
            return url('file_manager' . $this->image);
        }
        return null;
    }
    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barber_id', 'id');
    }
    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }
    public function getCreatedDateAttribute()
    {
        return $this->status_date ? Carbon::parse($this->status_date)->format('d/M/Y h:i A') : null;
    }
}
