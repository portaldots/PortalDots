<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

class ContactEmail extends Model
{
    protected $fillable = [
        'name',
        'email',
    ];
}
