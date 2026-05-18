# Feedback — Revisión de Ficha: Motel y Hotel Abismo de Pasión (Quinta Normal)

**De:** Vilma Núñez — Referencia en Pauta Digital y Conversión
**Para:** Encargado WordPress / Director
**Página:** https://infomoteles.cl/quinta-normal/motel-y-hotel-abismo-de-pasion-quinta-normal/
**Post ID:** 2818
**Estado:** Usa template viejo (snippet 9, clases `motel-*` con inline styles)

---

## Hallazgos críticos

### 1. H1 roto — problema de conversión y SEO
El H1 es la primera impresión del usuario y la señal más fuerte para Google. Acá empieza con **"y" minúscula**:

```
y Hotel Abismo de Pasion, QUINTA NORMAL en Quinta Normal | Teléfono y Precios
```

Falta "Motel " al inicio. El título del post parece estar mal guardado, porque:
- El breadcrumb también muestra `y Hotel Abismo...`
- El H2 de información también dice `Información de y Hotel Abismo...`

**Impacto:** Google puede penalizar la relevancia, la tasa de clics orgánica baja, y el usuario percibe desorden.

### 2. Sin teléfono ni WhatsApp real
- Sidebar: `Teléfono: No disponible`
- WhatsApp: `56912345678` (placeholder, no es real)
- No hay ningún enlace `tel:` en la página

**Impacto en conversión:** Si un usuario llega con intención de compra y quiere llamar, no hay camino. Cada visita sin CTA funcional es tráfico perdido. En un negocio local como moteles, el teléfono y WhatsApp son el canal de cierre principal.

### 3. Una sola imagen — sin galería
Solo tiene la imagen destacada en el hero. Sin fotos de habitaciones, baños, ni galería.

### 4. Sin tabla de precios visible
Hay un H2 "Precios Quinta Normal" pero no se renderiza ninguna tabla de tarifas. El usuario no puede comparar precios ni habitaciones.

### 5. Breadcrumb con /category/ en la URL
`/category/quinta-normal/` en vez de `/quinta-normal/`. En el post de prueba (2101) ya se corrigió.

### 6. Motel similar incorrecto
El sidebar renderiza "Motel Test CPT" como motel similar — es un post de prueba.

### 7. Datos de contacto vacíos
| Campo | Valor actual |
|---|---|
| Dirección | "Consultar" |
| Teléfono | "No disponible" |
| Horario | "Consultar" |

### 8. Template viejo vs nuevo
Esta página usa el template viejo (snippet 9, clases `motel-*`). El post de prueba 2101 ya tiene la versión nueva (hero oscuro con gradient, badges, CTAs, breadcrumbs corregidos). La diferencia es notable:

| Aspecto | Template nuevo (2101) | Template viejo (2818) |
|---|---|---|
| Hero | Oscuro con gradient, CTAs, badges | Solo imagen destacada |
| Breadcrumbs | Sin /category/ | Con /category/ |
| Estilo | CSS limpio sin inline | 25 inline styles |
| JSON-LD | Schema Motel propio | Solo Yoast |

---

## Recomendaciones para el encargado WordPress

### Prioridad alta (urgencia)

1. **Corregir H1 del post 2818:** El título del post tiene "Motel y Hotel Abismo..." pero en el render aparece sin "Motel ". Revisar el raw del post en la DB o corregir el snippet que escribe el H1. El post se titula "Motel y Hotel Abismo de Pasion, QUINTA NORMAL en Quinta Normal | Teléfono y Precios" pero el H1 omite "Motel ".

2. **Mapear la plantilla nueva a todos los posts de motel:** El post 2101 tiene single-2101.php con el diseño nuevo pero funciona mediante `template_include` filter. Los demás posts (2818 incluido) usan el template viejo. Decidir: ¿todos los posts de categoría motel usan el template nuevo? Si es así, crear `single-post.php` en el child theme o un `category-quinta-normal.php` según corresponda.

3. **Validar datos de contacto por post:** Si no hay teléfono, al menos mostrar WhatsApp. Si no hay datos reales, ocultar la sidebar de contacto o mostrar un mensaje "Llama al motel" con CTA a Google Maps.

### Prioridad media

4. **Corregir breadcrumbs en el template viejo:** Reemplazar `get_category_link()` por `home_url('/' . $ciudad_slug . '/')` en el snippet 9, igual que se hizo en single-2101.php.

5. **Remover el motel de prueba (Test CPT) de la lista de similares** en el sidebar del template viejo.

6. **Agregar galería de imágenes:** Si el CPT tiene campo `imagenes_galeria`, mostrarlo. Si no existe, al menos duplicar la imagen destacada no es solución.

### Prioridad baja

7. **Revisar otros moteles de Quinta Normal** (Complices, Tu Jardín Secreto, La Casa Blanca) para ver si tienen el mismo problema de H1.

---

## Pregunta al Director

¿Queremos migrar todos los moteles existentes al template nuevo (el del post 2101) progresivamente, o dejamos el template viejo como está y solo aplicamos el nuevo a los que van entrando?

Si es progresivo, sugiero priorizar los posts con mejor tráfico orgánico (mayor cantidad de clics en Search Console) para maximizar el impacto de conversión de la migración.
