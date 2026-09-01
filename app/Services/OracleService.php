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
    public function  getCodContribuyente(string $numero_documento){
        return DB::connection('oracle')->table('smacarnom')->where('mcndni', $numero_documento)->value('mcncontrib');
    }
    public function  generarNumLiquidacion(string $grupo, string $codigo, string $codcontrib){
        DB::connection('oracle')->statement("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY'");
        return DB::connection('oracle')->select(
                    "select ds_valores.fu_digito_generar('1312',?,?,?,'ALQUILER CANCHA DEPORTIVA') AS liquidacion FROM DUAL",
                    [trim($grupo), trim($codigo), trim($codcontrib)]
                );
    }
}
