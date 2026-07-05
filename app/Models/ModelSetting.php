<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelSetting extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'isea_setting';
    protected $fillable = ['image', 'title', 'status', 'order', 'user','our_service_id','description'];
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
        return $this->created_at ? \Carbon\Carbon::parse($this->created_at)->format('d/M/Y h:i A') : null;
    }
    public function ModelSettingDe()
    {
        return $this->hasMany(ModelSettingDe::class, 'our_service_id');
    }
}
