<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class OracleService
{

    public function getDistritos()
    {

        $distritos = DB::connection('oracle')->table('smadistrito')->get();
        return $distritos;
    }
    public function getTusnePorCodigo(string $grupo, string $codigo)
    {
        $tusne = DB::connection('oracle')->table('smaconceptod')->where('congrupo', $grupo)->where('concodigo', $codigo)->first();
        return $tusne;
    }
     public function getMontoTusne(string $grupo, string $codigo)
    {
        $tusne = DB::connection('oracle')->table('smaconceptod')->where('congrupo', $grupo)->where('concodigo', $codigo)->first('conmonto');
        return $tusne;
    }
    public function  getCodContibuyente(string $numero_documento){
        return DB::connection('oracle')->table('smacarnom')->where('mcndni', $numero_documento)->value('mcncontrib');
    }
}
