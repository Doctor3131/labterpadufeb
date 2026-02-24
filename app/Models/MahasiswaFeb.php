<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MahasiswaFeb extends Model
{
    protected $table = 'mahasiswa_feb';
    protected $primaryKey = 'nim';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nim',
        'nama',
        'prodi',
    ];
}
