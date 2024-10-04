<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupos extends Model
{
    protected $table = 'tab_grupos';
    protected $primaryKey = 'nr_sequencial';
    
    public $timestamps = false;
    protected $guarded = []; 

    public function membros(){
        return $this->hasMany(Membros::class, 'nr_seq_grupo', 'nr_sequencial');
    }

}