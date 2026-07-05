<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'contacts';
    protected $fillable = [
        'email_1',
        'email_2', 
        'email_3',
        'email_4',
        'office_hour_kh',
        'office_hour_uk',
        'address_kh', 
        'address_uk',
        'phone_kh',
        'telegram',
        'facebook', 
        'link_in',
        'youtube',
        'instagram',
        'image', 
        'status',
        'user',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
  	protected $appends = ['image_url'];
    public function getImageUrlAttribute()
    {
        if ($this->image != null) {
            return url('file_manager' . $this->image);
        }
        return null;
    }
}
