<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PessoasPresentes extends Model
{
    protected $table = 'tab_celula_presentes';
    protected $primaryKey = 'nr_sequencial';
    
    public $timestamps = false;
    protected $guarded = []; 

}