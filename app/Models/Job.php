<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;


class Job extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'jobs';
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
        'placement_type_id',
        'start_month',
        'to_month'
    ];
    protected $appends = ['image_url', 'post_date_for', 'close_date_for', 'pos_title', 'sector_title', 'month'];
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
    // In your Job model (App\Models\Job)
    public function getMonthAttribute()
    {
        $month = "";

        if ($this->start_month) {
            $month = (string) $this->start_month;
        }

        if ($this->to_month) {
            $month .= $month ? ' - ' . (string) $this->to_month : (string) $this->to_month;
        }

        return $month;
    }


    public function getPostDateForAttribute()
    {
        return $this->post_date ? \Carbon\Carbon::parse($this->post_date)->format('Y/m/d') : null;
    }
    public function getCloseDateForAttribute()
    {
        return $this->close_date ? \Carbon\Carbon::parse($this->close_date)->format('Y/m/d') : null;
    }
    public function PlacementType()
    {
        return $this->belongsTo(PlacementType::class, 'placement_type_id', 'id');
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

    // public function categoryBlogs()
    // {
    //     return $this->hasMany(CategoryBlog::class, 'blog_id', 'id');
    // }

    // public function category()
    // {
    //     return $this->belongsToMany(Category::class, 'category_blogs', 'blog_id', 'category_id');
    // }

    // public function numView()
    // {
    //     return $this->hasOne(NumView::class, 'blog_id', 'id');
    // }
    // public function company()
    // {
    //     return $this->belongsTo(Company::class, 'company_id', 'id');
    // }
    // public function country()
    // {
    //     return $this->belongsTo(Country::class, 'country_id', 'id');
    // }
    // public function jobCategory()
    // {
    //     return $this->belongsTo(JobCategory::class, 'job_category_id', 'id');
    // }
    // public function location()
    // {
    //     return $this->belongsTo(Location::class, 'location_id', 'id');
    // }

    // public function savedJob()
    // {
    //     return $this->hasMany(SavedJob::class, 'job_id', 'id');
    // }

}
