# T-008: Plan de Acción 7 Días — Primera Oleada infomoteles.cl
## Director: agente-director-agencia-digital
## Fecha: 2026-05-16

### META REAL
No basta con mover SEO o publicar fichas. Esta semana debe dejar:
- primeras fichas vendibles
- al menos 1 setup cobrado si aparece la oportunidad
- CRM operando
- seguimiento comercial real

### VISIÓN GENERAL

Basado en los entregables de:
- T-001 (SEO): 20+ moteles prospectables identificados
- T-002 (Estratega): Modelo de ingresos, ranking, orden de ataque
- T-003 (Ventas): Kit completo de prospección
- T-004 (Copy): Copy comercial listo
- T-005 (CRO): Diagnóstico con 6 cambios priorizados
- T-006 (Diseño): Bloque de planes HTML listo
- T-007 (Técnico): Plan de implementación listo
- T-009 (Financiero): Modelo financiero validado, precios OK

### ADAPTACION DE FICHAS — NUEVO FORMATO

Segun analisis en `input/como-debe-ser-ficha-motel.txt`, las fichas se redisenan como **landings transaccionales locales**. Ver brief completo en `output/brief-adaptacion-fichas-motel.md`.

**Cambios clave**:
- URL pasa a `/[ciudad]/[nombre-motel]/`
- CPT `ficha_motel` con 15+ campos personalizados (direccion, geo, tarifas, servicios, etc.)
- Template HTML con hero, galeria, tabla tarifas, mapa, resenas, FAQ, relacionados
- JSON-LD con @graph (WebPage, BreadcrumbList, Motel)
- Datos verificados con fecha de verificacion visible

**Pipeline actualizado**:

### PLAN DÍA POR DÍA

#### DÍA 1 — Preparar materiales + adaptar fichas
- [ ] SEO: priorizar home, URLs transaccionales, keywords comerciales y posts viejos con tráfico
- [ ] SEO redefine estructura URL de fichas a `/[ciudad]/[nombre-motel]/`
- [ ] PM Infomoteles: separar fichas listas, fichas a verificar y fichas a descartar
- [ ] Disenador entrega template HTML de ficha (hero, galeria, tarifas, mapa, resenas, FAQ)
- [ ] Tecnico implementa CPT `ficha_motel` con campos: nombre, ciudad, direccion, geo, precio, servicios, tarifas, horario, fecha_verificacion
- [ ] Tecnico implementa JSON-LD con @graph en servidor (WebPage, BreadcrumbList, Motel)
- [ ] Tener CRM listo (Google Sheets: nombre, ciudad, telf, estado)
- [ ] Tener mensajes WhatsApp copiados en el teléfono
- [ ] Preparar respuestas a objeciones

#### DÍA 2 — Probar template + primeros contactos
- [ ] Copywriter escribe ficha piloto #1 con hero, descripcion, FAQ
- [ ] Tecnico monta ficha piloto en infomoteles.cl
- [ ] QA: validar schema, breadcrumbs, PSI, indexabilidad
- [ ] Contactar #1 Motel La Cascada (Santiago)
- [ ] Contactar #2 Motel Miraflores (Santiago)
- [ ] Contactar #3 Motel Deja Vu (Copiapó)
- [ ] Registrar si hay intención de compra o solo curiosidad
- [ ] Probar versión A vs B del mensaje
- [ ] Registrar resultados en CRM

#### DÍA 3 — Segunda tanda + ajustes template
- [ ] Corregir template segun QA del Dia 2
- [ ] Copywriter escribe fichas piloto #2 y #3
- [ ] Publicar 3 fichas piloto en Santiago y Punta Arenas
- [ ] Contactar #4 Motel El Encuentro (Viña del Mar)
- [ ] Contactar #5 Motel Serrano (Antofagasta)
- [ ] Contactar #6 Motel Las Melosas (Curicó)
- [ ] Seguimiento a Día 2 que no respondieron
- [ ] Registrar todo en CRM
- [ ] Marcar prospectos fríos como descartados si no responden

#### DÍA 4 — Tercera tanda + enlazado interno
- [ ] Enlazado Interno: conectar fichas desde hubs de ciudad + breadcrumbs
- [ ] Activar tracking (GA4 con eventos de CTA)
- [ ] Contactar #7 Motel Paraíso (Concepción)
- [ ] Contactar #8 Motel Acqua (La Florida)
- [ ] Contactar #9 Motel Providencia (Providencia)
- [ ] Seguimiento fuerte a interesados del Día 2-3
- [ ] Cerrar primeros pagos si hay interesados
- [ ] Si no hay cierres, revisar guion y oferta, no seguir sumando contactos sin control

#### DÍA 5 — Cuarta tanda + setup técnico
- [ ] Contactar #10 Motel Vitacura (Vitacura)
- [ ] Si hay ventas: activar Meta Pixel
- [ ] Si hay ventas: implementar cambios CRO
- [ ] Seguimiento a todos los que falta responder
- [ ] Ofrecer setup solo si hay fricción real y capacidad de pago

#### DÍA 6 — Seguimiento general
- [ ] Contactar batch extra (de la lista de prioridad 2)
- [ ] Cerrar pendientes de toda la semana
- [ ] Si hay 3+ ventas: activar Google Analytics y Pixel
- [ ] Si no hay al menos 1 venta, el problema es oferta o prioridad, no volumen

#### DÍA 7 — Medición y ajuste
- [ ] Contar: contactos enviados, respuestas, ventas
- [ ] Medir rendimiento de fichas piloto en Search Console
- [ ] Revisar el impacto de URLs priorizadas y posts reescritos
- [ ] Analizar qué mensaje funcionó mejor (A vs B)
- [ ] Ajustar pitch para siguiente semana
- [ ] Reporte semanal al Director
- [ ] Decidir qué se vende la semana siguiente: ficha, premium o setup

### MÉTRICA DE ÉXITO

| KPI | Mínimo | Realista | Optimista |
|-----|--------|----------|-----------|
| Contactos enviados | 15 | 25 | 40 |
| Respuestas positivas | 3 | 6 | 10 |
| Ventas cerradas | 1 | 2-3 | 4-5 |
| MRR nuevo | $29.990 | $89.970-$179.970 | $239.960+ |
| Setup cobrados | 0 | 1 ($120.000) | 2 ($240.000) |

### OBJETIVO DE CAJA
- Mínimo aceptable: 1 venta + 1 setup o equivalente.
- Realista: 2-3 ventas + 1 setup.
- Si no se mueve caja, la semana se considera insuficiente.

### VALIDACIÓN PARA ACTIVAR PAUTA (T-016)
Activar PAUTA solo cuando: **5+ fichas vendidas** orgánicamente.

### RIESGOS Y MITIGACIONES

| Riesgo | Probabilidad | Mitigación |
|--------|-------------|------------|
| Dueños de motel no responden WhatsApp | Alta | Usar llamada directa al 3er intento |
| Precio muy alto para moteles chicos | Media | Ofrecer prueba gratis 15 días |
| No tener página /para-moteles/ lista | Baja | Subir antes del Día 1 |
| Competencia copia el modelo | Baja | Ejecutar rápido, ganar mercado primero |
