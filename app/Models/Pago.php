<?php

namespace App\Models;

use App\Models\Concerns\HasSpanishTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    use HasSpanishTimestamps;

    protected $table = 'pagos';

    protected $fillable = [
        'transaccion_id',
        'monto',
        'pagado_en',
        'acepto_terminos',
        'id_catalogos_tusne',
        'num_liquidacion',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'pagado_en' => 'datetime',
            'acepto_terminos' => 'boolean',
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    public function transaccion(): BelongsTo
    {
        return $this->belongsTo(Transaccion::class, 'transaccion_id');
    }

    public function catalogoTusne(): BelongsTo
    {
        return $this->belongsTo(CatalogoTusne::class, 'id_catalogos_tusne');
    }
}
