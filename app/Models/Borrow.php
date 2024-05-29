<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    use HasFactory;

    protected $fillable =[
        'members_id',
        'books_id',
        'borrowed_at',
        'returned_at'
    ];

    public function members() 
    {
        return $this->belongsTo(Member::class);
    }

    public function books()
    {
        return $this->belongsTo(Book::class);
    }
}
