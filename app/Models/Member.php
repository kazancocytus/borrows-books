<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    public function borrow()
    {
        return $this->hasMany(Borrow::class, 'members_id');
    }

    public function is_penalized()
    {
        return $this->is_penalized && $this->is_penalized->isFuture();
    }
}
