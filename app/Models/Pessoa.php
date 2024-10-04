<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pessoa extends Model
{
    protected $table = 'tab_pessoas';
    protected $primaryKey = 'nr_sequencial';
    protected $guarded = []; 

    public $timestamps = false;
}