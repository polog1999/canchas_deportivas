<?php

namespace App\Models;

use App\Models\Concerns\HasSpanishTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Perfil extends Model
{
    use HasSpanishTimestamps;

    protected $table = 'perfiles';

    protected $fillable = [
        'usuario_id',
        'tipo_documento_id',
        'numero_documento',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'direccion',
        'ubigeo_departamento',
        'ubigeo_provincia',
        'ubigeo_distrito',
        'cod_contrib',
        'telefono',
    ];

    protected function casts(): array
    {
        return [
            'tipo_documento' => \App\Enums\DocumentType::class,
            'creado_en' => 'datetime',
            'actualizado_en' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function nombreCompleto(): string
    {
        return trim(implode(' ', array_filter([
            trim((string) $this->nombres),
            trim((string) $this->apellido_paterno),
            trim((string) $this->apellido_materno),
        ], fn ($v) => $v !== '')));
    }

    public function tipoDocumento(): BelongsTo{
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id');
    }
}
