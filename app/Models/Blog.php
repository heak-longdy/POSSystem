<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'blogs';
    protected $fillable = ['user', 'title', 'des', 'image', 'ads_image' ,'slug', 'status', 'post_date'
    ];
    
    protected $appends = ['image_url','created_date'];
    public function getImageUrlAttribute()
    {
        if ($this->image != null) {
            return url('file_manager' . $this->image);
        }
        return null;
    }
    public function getCreatedDateAttribute()
    {
        return $this->created_at ? \Carbon\Carbon::parse($this->created_at)->format('Y/m/d h:i A') : null;
    }
    // public function advPosition()
    // {
    //     return $this->hasOne(AdvPosition::class, 'id', 'adv_position_id');
    // }
    // public function user()
    // {
    //     return $this->hasOne(User::class, 'id', 'user_id');
    // }

    // public function categoryBlogs()
    // {
    //     return $this->hasMany(CategoryBlog::class, 'blog_id', 'id');
    // }

    // public function category()
    // {
    //     return $this->belongsToMany(Category::class, 'category_blogs', 'blog_id', 'category_id');
    // }

    // public function viewer()
    // {
    //     return $this->hasOne(Viewers::class, 'type_id', 'id')->where('type', 'blog');
    // }
}
