<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceSetting extends Model
{
    use HasFactory;

    protected $table = 'invoice_settings';

    protected $fillable = [
        'prefix',
        'separator',
        'date_format',
        'digit_length',
        'start_number',
        'reset_cycle',
    ];

    /**
     * Retrieve the active setting or create default.
     */
    public static function getActive(): self
    {
        return static::firstOrCreate([], [
            'prefix'       => 'NO',
            'separator'    => '-',
            'date_format'  => 'none',
            'digit_length' => 4,
            'start_number' => 1,
            'reset_cycle'  => 'never',
        ]);
    }
}
