<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions'; // sesuaikan dengan nama tabelmu

    // Relasi ke model Book untuk mengambil judul buku
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id', 'id');
    }
}