<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sector extends Model
{
    use HasFactory,SoftDeletes;
    protected $table = 'sectors';
    protected $fillable = [
                            'position_id',
                            'image', 
                            'title', 
                            'status', 
                            'order', 
                            'user'
                        ];
    protected $appends = ['image_url','created_date','pos_title'];

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
    public function Position(){
        return $this->hasOne(Position::class, 'id','position_id');
    }
    public function getPosTitleAttribute(){
        if($this->Position()){
            return $this->Position?->title ?? "";
        }
    }
}
