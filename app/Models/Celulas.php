<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Celulas extends Model
{
    protected $table = 'tab_celulas';
    protected $primaryKey = 'nr_sequencial';
    
    public $timestamps = false;
    protected $guarded = []; 

    public function presentes(){
        return $this->belongsTo(PessoasPresentes::class, 'nr_seq_celula', 'nr_sequencial');
    }
}