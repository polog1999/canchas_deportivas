SET search_path TO molicanchas;

-- Opcional: migrar datos de usuarios.nombre a perfiles antes de borrar la columna
INSERT INTO perfiles (usuario_id, nombres, apellido_paterno, apellido_materno, creado_en, actualizado_en)
SELECT u.id,
       COALESCE(NULLIF(TRIM(u.nombre), ''), u.usuario),
       '',
       '',
       NOW(),
       NOW()
FROM usuarios u
WHERE NOT EXISTS (
    SELECT 1 FROM perfiles p WHERE p.usuario_id = u.id
)
AND EXISTS (
    SELECT 1
    FROM information_schema.columns
    WHERE table_schema = 'molicanchas'
      AND table_name = 'usuarios'
      AND column_name = 'nombre'
);

ALTER TABLE usuarios DROP COLUMN IF EXISTS nombre;
