# WordPress y Automatizacion

## Descripcion
Capacidad para implementar cambios tecnicos en WordPress: Custom Post Types, campos personalizados, shortcodes, formularios con redireccion WhatsApp, tracking (GA4, Meta Pixel), optimizacion de velocidad.

## Entregables tipicos
- Codigo PHP para CPT y shortcodes
- Formularios de contacto funcionales
- Checklist de implementacion
- Configuracion de tracking

## Ficha de Motel (infomoteles.cl)
Al implementar una ficha de motel en WordPress, usar el modelo en `input/como-debe-ser-ficha-motel.txt`:
- Template: single.php del child theme con layout premium de dos columnas
- Customizer: colores configurables en `Apariencia > Personalizar > Ficha Motel` (fondo hero, primario, acento, texto, superficie)
- WhatsApp: link debe incluir `?text=Te%20estoy%20contactando%20desde%20infomoteles.cl%20y%20quisiera%20mas%20informacion`
- Post type: usar categorias (non-blog = motel) o CPT segun definicion del proyecto
- Datos: no inventar campos faltantes; dejarlos pendientes

Templates ya creados en el child theme: single.php, single-2101.php, single-ficha_motel.php.

## Dependencias
Recibe disenos de Disenador y requisitos de CRO y SEO.

## Problemas comunes (y como responder)
- Cloudflare/hosting caido (ej: HTTP 521 "Web server is down"):
  - Senal: la API de WordPress devuelve HTML de Cloudflare en vez de JSON; falla cualquier `POST` a `/wp-json/wp/v2/...`.
  - Accion: pausar publicaciones/updates; reintentar con backoff (ej: 5 intentos en 2-5 min) y registrar evidencia (status code + primeras lineas del body).
  - Mitigacion de flujo: dejar el contenido listo (HTML) y anotar el post ID/slug objetivo para aplicar el update apenas el origen vuelva.
- Reescritura/duplicacion de slugs:
  - Senal: WordPress cambia `slug` a `-2` en creacion.
  - Accion: hacer update inmediato del `title` y `slug` con el endpoint del post.
