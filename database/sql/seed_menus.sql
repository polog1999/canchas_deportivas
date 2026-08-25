SET search_path TO molicanchas;

-- ruta = link/path libre (ej. /portal/users), no el nombre interno de Laravel
INSERT INTO menus (nombre, ruta, icono, orden, activo, creado_en, actualizado_en)
SELECT v.nombre, v.ruta, v.icono, v.orden, true, NOW(), NOW()
FROM (VALUES
  ('Canchas', '/portal/courts', 'fa-futbol', 1),
  ('Sedes', '/portal/locations', 'fa-location-dot', 2),
  ('Tusne', '/portal/tusne-catalog', 'fa-list', 3),
  ('Usuarios', '/portal/users', 'fa-users', 4),
  ('Roles y Menús', '/portal/roles-menus', 'fa-shield', 5),
  ('Estructura de Menús', '/portal/menus', 'fa-sitemap', 6)
) AS v(nombre, ruta, icono, orden)
WHERE NOT EXISTS (
  SELECT 1 FROM menus m WHERE m.nombre = v.nombre
);

-- Actualizar rutas existentes al formato de path libre
UPDATE menus SET ruta = '/portal/courts', actualizado_en = NOW() WHERE nombre = 'Canchas';
UPDATE menus SET ruta = '/portal/locations', actualizado_en = NOW() WHERE nombre = 'Sedes';
UPDATE menus SET ruta = '/portal/tusne-catalog', actualizado_en = NOW() WHERE nombre = 'Tusne';
UPDATE menus SET ruta = '/portal/users', actualizado_en = NOW() WHERE nombre = 'Usuarios';
UPDATE menus SET ruta = '/portal/roles-menus', actualizado_en = NOW() WHERE nombre = 'Roles y Menús';
UPDATE menus SET ruta = '/portal/menus', actualizado_en = NOW() WHERE nombre = 'Estructura de Menús';

INSERT INTO menus_roles (rol_id, menu_id)
SELECT r.id, m.id
FROM roles r
CROSS JOIN menus m
WHERE r.nombre = 'admin'
  AND NOT EXISTS (
    SELECT 1 FROM menus_roles mr
    WHERE mr.rol_id = r.id AND mr.menu_id = m.id
  );

SELECT 'menus' AS tabla, count(*) FROM menus
UNION ALL
SELECT 'menus_roles', count(*) FROM menus_roles;
