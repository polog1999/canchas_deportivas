SET search_path TO molicanchas;
SET client_encoding TO 'UTF8';

CREATE TABLE IF NOT EXISTS sliders (
    id serial4 NOT NULL,
    titulo varchar(255) NOT NULL,
    texto_boton varchar(100) NOT NULL DEFAULT 'Reservar cancha',
    enlace_boton varchar(255) NOT NULL DEFAULT '#gridSedes',
    imagen varchar NULL,
    orden int4 NOT NULL DEFAULT 0,
    activo bool NOT NULL DEFAULT true,
    creado_en timestamp NOT NULL DEFAULT NOW(),
    actualizado_en timestamp NOT NULL DEFAULT NOW(),
    CONSTRAINT sliders_pkey PRIMARY KEY (id)
);

INSERT INTO menus (nombre, ruta, icono, orden, activo, creado_en, actualizado_en)
SELECT v.nombre, v.ruta, v.icono, v.orden, true, NOW(), NOW()
FROM (VALUES
  ('Slider', '/portal/slider', 'fa-images', 6)
) AS v(nombre, ruta, icono, orden)
WHERE NOT EXISTS (
  SELECT 1 FROM menus m WHERE m.nombre = v.nombre OR m.ruta = v.ruta
);

INSERT INTO menus_roles (rol_id, menu_id)
SELECT r.id, m.id
FROM roles r
CROSS JOIN menus m
WHERE r.nombre = 'admin'
  AND m.ruta = '/portal/slider'
  AND NOT EXISTS (
    SELECT 1 FROM menus_roles mr
    WHERE mr.rol_id = r.id AND mr.menu_id = m.id
  );

SELECT id, nombre, ruta, orden FROM menus WHERE ruta = '/portal/slider';
