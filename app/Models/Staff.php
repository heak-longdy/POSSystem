<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staffs';

    protected $fillable = [
        'image',
        'name',
        'position_id',
        'phone_number',
        'email',
        'address',
        'status',
        'user',
    ];

    protected $appends = ['image_url', 'position_title'];

    public function getImageUrlAttribute()
    {
        if ($this->image != null) {
            return url('file_manager' . $this->image);
        }
        return null;
    }

    public function getPositionTitleAttribute()
    {
        return $this->position ? $this->position->title : null;
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id')->withTrashed();
    }
}
