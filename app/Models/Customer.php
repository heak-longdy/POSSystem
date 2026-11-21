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
    protected $fillable = ['code', 'name', 'ordering', 'phone', 'address', 'latitude', 'longitude', 'profile', 'image_1', 'image_2', 'password', 'status', 'total_point', 'user', 'payment_restriction'];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected $appends = ['image_url', 'image_1_url', 'image_2_url', 'google_maps_url'];
    public function getGoogleMapsUrlAttribute(): ?string
    {
        $lat = $this->attributes['latitude'] ?? null;
        $lng = $this->attributes['longitude'] ?? null;
        if ($lat !== null && $lng !== null && $lat !== '' && $lng !== '') {
            return "https://www.google.com/maps?q={$lat},{$lng}";
        }
        return null;
    }

    public function hasLocation(): bool
    {
        $lat = $this->attributes['latitude'] ?? null;
        $lng = $this->attributes['longitude'] ?? null;
        return $lat !== null && $lng !== null && $lat !== '' && $lng !== '';
    }
    public function getImageUrlAttribute()
    {
        if ($this->profile != null) {
            $path = str_starts_with($this->profile, '/') ? $this->profile : '/' . $this->profile;
            return url('file_manager' . $path);
        }
        return null;
    }

    public function getImage1UrlAttribute()
    {
        if ($this->image_1 != null) {
            $path = str_starts_with($this->image_1, '/') ? $this->image_1 : '/' . $this->image_1;
            return url('file_manager' . $path);
        }
        return null;
    }

    public function getImage2UrlAttribute()
    {
        if ($this->image_2 != null) {
            $path = str_starts_with($this->image_2, '/') ? $this->image_2 : '/' . $this->image_2;
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

    public function isFullPaymentRestricted(): bool
    {
        return strtoupper((string) ($this->payment_restriction ?? 'N')) === 'Y';
    }
}
