<?php

namespace App\Eloquents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @property integer $id
 */
class Answer extends Model
{
    public function details()
    {
        return DB::table('answer_details')
            ->where('answer_id', $this->id)
            ->get();
    }
}
