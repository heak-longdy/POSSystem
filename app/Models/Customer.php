<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'customers';
    protected $fillable = ['name', 'ordering', 'phone', 'address', 'profile', 'password', 'status', 'total_point','user'];

    protected $appends = ['image_url'];
    public function getImageUrlAttribute()
    {
        if ($this->profile != null) {
            $path = str_starts_with($this->profile, '/') ? $this->profile : '/' . $this->profile;
            return url('file_manager' . $path);
        }
        return null;
    }
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user')->withTrashed();
    }

    public function scopeForUser(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?? auth()->user();

        if (!$user || $user->hasFullDataAccess()) {
            return $query;
        }

        return $query->where('customers.user', (string) $user->id);
    }

    public function isOwnedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->hasFullDataAccess()) {
            return true;
        }

        return (string) $this->user === (string) $user->id;
    }
}
