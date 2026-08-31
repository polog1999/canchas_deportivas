SET search_path TO molicanchas;
SET client_encoding TO 'UTF8';

INSERT INTO menus (nombre, ruta, icono, orden, activo, creado_en, actualizado_en)
SELECT v.nombre, v.ruta, v.icono, v.orden, true, NOW(), NOW()
FROM (VALUES
  ('Reservar más', '/portal/reservar', 'fa-calendar-plus', 12)
) AS v(nombre, ruta, icono, orden)
WHERE NOT EXISTS (
  SELECT 1 FROM menus m WHERE m.nombre = v.nombre OR m.ruta = v.ruta
);

INSERT INTO menus_roles (rol_id, menu_id)
SELECT r.id, m.id
FROM roles r
CROSS JOIN menus m
WHERE r.activo = true
  AND m.ruta = '/portal/reservar'
  AND NOT EXISTS (
    SELECT 1 FROM menus_roles mr
    WHERE mr.rol_id = r.id AND mr.menu_id = m.id
  );

SELECT id, nombre, ruta, orden, activo
FROM menus
WHERE ruta = '/portal/reservar';
