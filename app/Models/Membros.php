<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membros extends Model
{
    protected $table = 'tab_grupo_membros';
    protected $primaryKey = 'nr_sequencial';
    
    public $timestamps = false;
    protected $guarded = []; 

    public function pessoa(){
        return $this->belongsTo(Pessoa::class, 'nr_seq_pessoa', 'nr_sequencial');
    }
}