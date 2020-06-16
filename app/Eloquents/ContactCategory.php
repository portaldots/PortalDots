<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

class ContactCategory extends Model
{
    protected $fillable = [
        'name',
        'email',
    ];
}
