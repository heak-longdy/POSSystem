<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormQueue extends Model
{
    use HasFactory;
    protected $table = 'FromQueues';
    protected $fillable = [
                            'user',
                            'name',
                            'email',
                            'subject',
                            'mailTo',
                            'files',
                            'dataJson',
                            'type',
                            'status'
                        ];
}
