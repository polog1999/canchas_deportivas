<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
    public function getCodContribuyente(
        string $tipo_documento,
        string $numero_documento
    ) {

        return DB::connection('oracle')
            ->table('smacarnom')
            ->whereRaw('TRIM(mcntipodi) = ?', [trim($tipo_documento)])
            ->whereRaw('TRIM(mcnnrodi) = ?', [trim($numero_documento)])
            ->value('mcncontrib');
    }
    public function  generarNumLiquidacion(string $grupo, string $codigo, string $codcontrib)
    {
        DB::connection('oracle')->statement("ALTER SESSION SET NLS_DATE_FORMAT = 'DD/MM/YYYY'");
        return DB::connection('oracle')->select(
            "select ds_valores.fu_digito_generar('1312',?,?,?,'ALQUILER CANCHA DEPORTIVA') AS liquidacion FROM DUAL",
            [trim($grupo), trim($codigo), trim($codcontrib)]
        );
    }

    public function  insertarEnOracle(string $grupo, string $codigo, string $codcontrib, float $monto, int $purchaseNumber, int $idTransaccion, Carbon $fechaYHoraPago, string $numeroLiquidacion, int $cantidadHoras)
    {
        $resContri = DB::connection('oracle')->selectOne(
            'SELECT MCNTIPODI, MCNNRODI, MCNAPEPAT,MCNAPEMAT, MCNNOMBRE FROM SMACARNOM WHERE MCNCONTRIB = :codContri',
            ['codContri' => $codcontrib]
        );

        // Mapeo seguro de variables evitando errores de trim(null)
        $codLote         = TRIM((string) $idTransaccion);
        $codGestrad      = $purchaseNumber;
        $tipoDocumento   = $resContri ? trim($resContri->mcntipodi) : null;
        $numDocumento    = $resContri ? trim($resContri->mcnnrodi) : null;
        $apePat          = $resContri ? trim($resContri->mcnapepat) : null;
        $apeMat          = $resContri ? trim($resContri->mcnapemat) : null;
        $nombres         = $resContri ? trim($resContri->mcnnombre) : null;
        $espCodigo       = DB::connection('oracle')->table('smaconceptod')->where('congrupo', $grupo)->where('concodigo', $codigo)->value('espcodigo');
        $montoTotal = $monto * $cantidadHoras;


        // Suponiendo que $fechaPago es una instancia de Carbon o un timestamp
        $fechaPago = $fechaYHoraPago->format('d/m/y'); // Fecha en formato DD/MM/AA
        $HoraPago  = $fechaYHoraPago->format('H:i:s'); // Hora en formato de 24 horas (o usa 'h:i:s A' si prefieres AM/PM)

        //==================== INSERCIÓN EN ORACLE CON TO_DATE ========================
        try {
            $pagoGestrad = DB::connection('oracle')->insert(
                "INSERT INTO DS_SIXTCL.PAGOS_GESTRAD (COD_LOTE, COD_GESTRAD, FECHAPAGO, HORAPAGO, TIPO_DOC, NRO_DOC, AP_APTERNO, AP_MATERNO, NOMBRES, ESPCODIGO, CONCODIGO, IMPORTE, CGONUMERO, MCNCONTRIB, ARCHIVO
                                ) VALUES (?, ?, TO_DATE(?, 'DD/MM/YY'), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,'ONLINE')",
                [
                    $codLote,
                    $codGestrad,
                    $fechaPago,   // Entra al primer TO_DATE
                    $HoraPago,    // Entra al segundo TO_DATE
                    $tipoDocumento,
                    $numDocumento,
                    $apePat,
                    $apeMat,
                    $nombres,
                    $espCodigo,
                    $codigo,
                    $montoTotal,
                    $numeroLiquidacion,
                    $codcontrib

                ]
            );
        } catch (\Exception $oracleException) {
            // LOG EXCLUSIVO DIARIO EN CASO DE FALLAR EL INSERT DE ORACLE
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/pagos_niubiz-' . date('Y-m-d') . '.log'),
            ])->error("ERROR INSERT ORACLE: Lote: {$codLote}, Liq: {$numeroLiquidacion}. Msg: " . $oracleException->getMessage());

            throw $oracleException;
        }

        $resPagoGestrad = DB::connection('oracle')->selectOne(
            "SELECT CGONUMERO, MCNCONTRIB,FECHAPAGO FROM DS_SIXTCL.PAGOS_GESTRAD WHERE TRIM(COD_LOTE) = ? AND TRIM(CGONUMERO) = ?",
            [trim($codLote), trim($numeroLiquidacion)]
        );


        $cgonumero = $resPagoGestrad->cgonumero;
        $mcncontrib = $resPagoGestrad->mcncontrib;
        $codofecha = $resPagoGestrad->fechapago;

        $resContador = DB::connection('oracle')->selectOne("SELECT 'C'||LPAD(SSEQ_CONCILIA_PAGO.nextval,11,'0') CONTADOR FROM DUAL");

        $codopemuncon = $resContador ? $resContador->contador : null;
        $codopemunpag = $resContador ? $resContador->contador : null;

        $tipo     = 'I';
        $flagdesc = 'N';

        try {
            DB::connection('oracle')->insert(
                "INSERT INTO SMVDESCTMP(CGONUMERO,CODCONTRI,ANYODEUD,FECOPE,CODOPEMUNCON,CODOPEMUNPAG,TIPO, FLAGDESC, LGPCODIGO, USERCODIGO)
                                             values(?, ?, TO_CHAR(Sysdate,'YYYY'), ?, ?, ?, ?, ?, 'B10',TRIM(user))",
                [$cgonumero, $mcncontrib, $codofecha, $codopemuncon, $codopemunpag, $tipo, $flagdesc]
            );
        } catch (\Exception $oracleException2) {
            // LOG EXCLUSIVO DIARIO EN CASO DE FALLAR EL SEGUNDO INSERT DE ORACLE
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/pagos_niubiz-' . date('Y-m-d') . '.log'),
            ])->error("ERROR INSERT SMVDESCTMP: Liq: {$cgonumero}. Msg: " . $oracleException2->getMessage());

            throw $oracleException2;
        }
    }
    public function actualizarCantidadConcepto(int $cantidadHoras, string $numeroLiquidacion)
    {
        return DB::connection('oracle')->update(
            "UPDATE smacgotri SET cgocanconcep = :cantidadHoras WHERE cgonumero = :numeroLiquidacion",
            [
                'cantidadHoras' => $cantidadHoras,
                'numeroLiquidacion' => trim($numeroLiquidacion),
            ]
        );
    }
}
