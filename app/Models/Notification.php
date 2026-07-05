<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'notifications';
    protected $fillable = [
        'title',
        'description',
        'image',
        'type',
        'is_read_member',
        'is_read_garage',
        'booking_id',
        'member_id',
        'garage_id',
        'user_id',
        'status',
        'type_send',
    ];
    protected $casts = [
        'is_read_member' => 'integer',
        'is_read_garage' => 'integer',
        'booking_id' => 'integer',
        'member_id' => 'integer',
        'garage_id' => 'integer',
        'user_id' => 'integer',
        'status'    => 'integer',
    ];
    protected $appends = ['image_url'];
    public function getImageUrlAttribute()
    {
        if ($this->image != null) {
            return url('file_manager' . $this->image);
        }
        return null;
    }
    public function member()
    {
        return $this->belongsTo(User::class, 'member_id', 'id');
    }
    public function garage()
    {
        return $this->belongsTo(User::class, 'garage_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
