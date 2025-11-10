<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HobbyModel extends Model {
    use HasFactory;
    public $table      = 'egc_hobbies';
    public $primaryKey = 'sno';
    //public $timestamps = false;

    protected $fillable = [
        'hobby_name',
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'status',
    ];
}