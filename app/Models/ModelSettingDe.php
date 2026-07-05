<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelSettingDe extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'isea_setting_de';
    protected $fillable = ['our_service_id','image', 'title','description', 'status', 'order', 'user'];
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
}
