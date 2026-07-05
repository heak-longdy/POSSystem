<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobList extends Model
{
    use HasFactory, SoftDeletes;
    // protected $guarded = [];
    protected $fillable = [
        'post_date',
        'close_date',
        'user_id',
        'title',
        'salary_from',
        'job_des',
        'job_res',
        'job_requirement',
        'image',
        'status',
        'position_id',
        'sector_id',
        'number_of_day',
        'placement_type_id'
    ];
    protected $appends = ['image_url', 'post_date_for', 'close_date_for', 'pos_title','sector_title'];
    public function getImageUrlAttribute()
    {
        if ($this->image != null) {
            return url('file_manager' . $this->image);
        }
        return null;
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function getPostDateForAttribute()
    {
        return $this->post_date ? \Carbon\Carbon::parse($this->post_date)->format('Y/m/d') : null;
    }
    public function getCloseDateForAttribute()
    {
        return $this->close_date ? \Carbon\Carbon::parse($this->close_date)->format('Y/m/d') : null;
    }
    public function Position()
    {
        return $this->belongsTo(Position::class, 'position_id', 'id');
    }
    public function Sector()
    {
        return $this->belongsTo(Sector::class, 'sector_id', 'id');
    }
    public function getPosTitleAttribute()
    {
        if ($this->Position()) {
            return $this->Position?->title ?? "";
        }
    }
    public function getSectorTitleAttribute()
    {
        if ($this->Sector()) {
            return $this->Sector?->title ?? "";
        }
    }
}
