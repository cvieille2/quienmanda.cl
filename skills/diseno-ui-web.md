# Diseno UI Web

## Descripcion
Capacidad para disenar interfaces web atractivas y funcionales: bloques de planes, paginas de servicio, componentes responsivos, HTML/CSS inline compatible con WordPress.

## Entregables tipicos
- Bloques HTML/CSS responsivos
- Paginas de servicio completas
- Componentes visuales (tarjetas, tablas, CTAs)
- Maquetacion mobile-first

## Ficha de Motel (infomoteles.cl)
Al disenar una ficha de motel, seguir el orden visual del modelo en `input/como-debe-ser-ficha-motel.txt`:
1. Hero con foto principal (fondo completo)
2. Descripcion
3. Datos de ubicacion y contacto
4. Precios y tarifas
5. Servicios y amenidades (tarjetas o iconos)
6. Opiniones reales y mi opinion
7. Preguntas frecuentes (acordeon, siempre al final)

Si hace falta agregar secciones nuevas por investigacion, hacerlo sin alterar ese orden fijo.

Layout: una sola columna de contenido principal, con flujo vertical.
Estilo: el modelo premium ya implementado en single.php del child theme.
El ancho del contenedor debe controlar toda la ficha, no solo un card blanco interior.

## Dependencias
Recibe copy de Copywriter; entrega a Tecnico para implementacion.
