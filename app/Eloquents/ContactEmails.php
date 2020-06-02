<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;

class ContactEmails extends Model
{
    protected $fillable = [
        'name',
        'email',
    ];
}
