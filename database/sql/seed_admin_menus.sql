SET search_path TO molicanchas;
SET client_encoding TO 'UTF8';

INSERT INTO menus (nombre, ruta, icono, orden, activo, creado_en, actualizado_en)
SELECT v.nombre, v.ruta, v.icono, v.orden, true, NOW(), NOW()
FROM (VALUES
  ('Roles y Menús', '/portal/roles-menus', 'fa-shield', 5),
  ('Estructura de Menús', '/portal/menus', 'fa-sitemap', 6)
) AS v(nombre, ruta, icono, orden)
WHERE NOT EXISTS (
  SELECT 1 FROM menus m WHERE m.nombre = v.nombre
);

INSERT INTO menus_roles (rol_id, menu_id)
SELECT r.id, m.id
FROM roles r
CROSS JOIN menus m
WHERE r.nombre = 'admin'
  AND m.ruta IN ('/portal/roles-menus', '/portal/menus')
  AND NOT EXISTS (
    SELECT 1 FROM menus_roles mr
    WHERE mr.rol_id = r.id AND mr.menu_id = m.id
  );

SELECT id, nombre, ruta, orden FROM menus ORDER BY orden, id;
