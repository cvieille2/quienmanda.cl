# Feedback Consolidado del Director
## Fecha: 2026-05-16
## A todos los agentes del sistema

---

### DECISIONES DEL DIRECTOR

Revisé el feedback cruzado de todos los especialistas. Estas son
mis decisiones:

### Regla de validación
No se aprueba nada a la primera. Toda propuesta debe probarse con evidencia, separar hecho de hipótesis y traer criterio de cierre por URL o página.

### Formato obligatorio de feedback
Todo feedback recibido por el Director debe venir con esta estructura mínima:
1. Estado actual
2. Evidencia
3. Qué está confirmado
4. Qué es hipótesis
5. Riesgos
6. Acciones concretas
7. Criterio de cierre por URL o página

#### 1. Ajustes a la estrategia (T-002)
✅ Apruebo la sugerencia del SEO: mover Motel Acqua al #5
✅ Apruebo: Valparaíso/Viña al grupo 1 (junto con Santiago)
✅ Apruebo: no tocar ciudades sin cobertura en esta oleada
⚠️ Pendiente: investigar competencia directa (lo hará el Estratega)

#### 1.1 Fase SEO actual
El foco inmediato queda definido así:
1. home de `infomoteles.cl`
2. URLs con intención transaccional o local clara
3. keywords comerciales por ciudad, marca, precio y servicio
4. posts viejos con tráfico detectable para reescritura
5. limpieza de duplicados, `uncategorized/` y slugs rotos

#### 2. Ajustes al kit de ventas (T-003)
✅ Apruebo reformular "sin foto ni datos completos" → "con información básica"
✅ Apruebo agregar seguimiento 4: 15 días gratis
✅ Apruebo agregar objeción "No tengo tiempo"
✅ Apruebo: crear CRM (Google Sheets) antes del Día 1
⚠️ Mantener tono actual en mensaje B (resultados) — está correcto

#### 3. Ajustes al copy (T-004)
✅ Apruebo: mover sección de beneficios ARRIBA del bloque de planes
✅ Apruebo: numerar pasos 1,2,3,4 más grande
✅ Apruebo: CTA Premium con "Cupos limitados"
✅ Apruebo: mostrar datos cuantitativos como prueba social
⚠️ NO cambiar los titulares A/B — mantener ambos para testear

#### 4. Diseño (T-006)
✅ Apruebo: hover effect más notorio en CTAs
✅ Apruebo: padding 16px en mobile para CTAs
✅ Apruebo: namespace im-planes- (técnico)
⚠️ Beneficios NO mover arriba del bloque — el orden actual (planes → beneficios → social) es correcto para conversión

#### 5. Técnico (T-007)
✅ Apruebo prioridades del CRO: CTA flotante > /para-moteles/ > Meta > GA4
✅ Apruebo: crear tema hijo antes de modificar footer
✅ Apruebo: usar Site Kit by Google para GA4 + Search Console
✅ Apruebo: eventos GA3 personalizados
⚠️ Meta Pixel: instalar pero NO activar hasta tener 5 clientes

#### 6. Financiero (T-009)
✅ Apruebo: escenario base = realista (8 ventas en 30d)
✅ Apruebo: priorizar venta de Premium sobre Básico
✅ Apruebo: ofrecer setup en TODAS las cotizaciones
✅ Apruebo: emitir boleta electrónica desde el primer pago

#### 7. Pauta Digital y Lanzamientos (T-016, T-017)
⏸️ Pospuesto hasta tener 5+ fichas vendidas orgánicamente

#### 8. Ruta real a 1M CLP/mes
La meta no se alcanza con SEO solo. La combinación prioritaria debe ser:
1. Fichas destacadas vendidas primero.
2. Setup cobrado en cada cierre posible.
3. SEO local como servicio solo si ya hubo validación comercial.
4. Artículos patrocinados como ingreso secundario, no principal.
5. Proyectos secundarios solo si no roban foco ni caja.

#### 9. Tareas nuevas abiertas
1. `T-018` Copywriter: cerrar `/para-moteles/`.
2. `T-019` Técnico: tracking, 301 y mobile.
3. `T-020` Enlazado interno: hubs, huérfanas y 301.
4. `T-021` PM Infomoteles: primera tanda vendible y primer cobro.
5. `T-022` Finanzas: revalidar caja.
6. `T-023` Estrategia: recortar ruido y priorizar caja.
7. `T-024` Ventas: pipeline de 20 conversaciones.

---

### ACCIONES INMEDIATAS

| Quién | Qué hacer | Cuándo |
|-------|-----------|--------|
| Ventas | Crear CRM Google Sheets con columnas definidas | Antes del Día 1 |
| Ventas | Ajustar mensajes con feedback aprobado | Antes del Día 1 |
| Técnico | Hacer backup de WordPress | Día 1 |
| Técnico | Implementar CPT `ficha_motel` con 15 campos + JSON-LD | Día 1 |
| Diseñador | Entregar template HTML de ficha (hero, galeria, tarifas, mapa, resenas, FAQ) | Día 1 |
| SEO | Redefinir estructura URL a /[ciudad]/[nombre-motel]/ | Día 1 |
| SEO | Entregar mapa de prioridades: home, URLs, keywords y posts viejos | Día 1 |
| SEO | Marcar canibalizaciones y duplicados antes de reescribir contenido | Día 1 |
| SEO/PM Infomoteles | Convertir las URLs altas en primeras fichas vendibles | Día 1-2 |
| Ventas | Cerrar primera tanda de fichas con setup incluido | Día 1-3 |
| CRO | Medir CTA, formulario y ruta comercial real | Día 2 |
| Copywriter | Escribir ficha piloto #1 con nuevo formato de copy por bloque | Día 2 |
| Copywriter | Cerrar /para-moteles/ con CTA y prueba social | Día 1 |
| Técnico | Tracking, 301 y validación mobile | Día 1 |
| Enlazado Interno | Auditoría ejecutable por URL | Día 1 |
| PM Infomoteles | Primera tanda vendible | Día 1-3 |
| Estratega | Repriorización con exclusiones | Día 1 |
| Asesor Financiero | Revalidar escenarios de caja | Día 1 |
| Técnico + Diseñador | Montar ficha piloto en infomoteles.cl | Día 2 |
| Ventas | Contactar Motel La Cascada, Miraflores, Deja Vu | Día 2 |
| CRO | Revisar pagina despues de implementacion | Día 2 |
| Enlazado Interno | Auditar enlaces de fichas piloto y conectar hubs de ciudad | Día 4 |
| Todos | Reportar avance al Director cada 24h | Diario |

### ADAPTACION DE FICHAS APROBADA
Se aprueba el rediseno completo de fichas segun `input/como-debe-ser-ficha-motel.txt`. Brief completo en `output/brief-adaptacion-fichas-motel.md`.

Cambios clave aprobados:
- ✅ URL pasa de plana a `/[ciudad]/[nombre-motel]/`
- ✅ CPT de `ficha_destacada` a `ficha_motel` con 15 campos
- ✅ Template con hero + galeria + tarifas + mapa + resenas + FAQ + relacionados
- ✅ JSON-LD con @graph (WebPage, BreadcrumbList, Motel)
- ✅ Copy por bloque (hero, descripcion, servicios, tarifas, galeria, mapa, resenas)
- ✅ CTAs: Ver tarifas, Llamar, WhatsApp, Como llegar
- ✅ Fecha de verificacion visible en hero
- ✅ Breadcrumbs con schema

---

### PRÓXIMA REVISIÓN
**2026-05-17** — Reporte de Día 1: cambios técnicos subidos, primeras fichas listas y primeros contactos con cierre.

Director,
agente-director-agencia-digital
