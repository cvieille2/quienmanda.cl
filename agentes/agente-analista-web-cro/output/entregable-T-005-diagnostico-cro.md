# Entregable T-005: Diagnóstico CRO — infomoteles.cl
## Agente: Analista Web CRO
## Fecha: 2026-05-16
## Proyecto: infomoteles.cl

### DIAGNÓSTICO GENERAL

El sitio tiene contenido valioso (guías de ciudades, reseñas de moteles) pero
está optimizado para el USUARIO FINAL (parejas buscando motel), NO para el
CLIENTE (dueño de motel que quiere promocionarse). Ahí está el problema:

**El sitio vende una cosa y debería vender otra.**

Lo que ES: directorio informativo para parejas.
Lo que DEBE SER: plataforma de promoción para moteles.

### PROBLEMAS DETECTADOS

| # | Problema | Impacto | Evidencia |
|---|----------|---------|-----------|
| 1 | No hay mención a dueños de moteles en homepage | 🔴 Crítico | Homepage habla solo a parejas |
| 2 | Sin CTA para "¿Tienes un motel? Promociónalo aquí" | 🔴 Crítico | Solo hay inbox de búsqueda y enlaces |
| 3 | Sin tabla de precios visible | 🔴 Crítico | $0 pagos por publicidad actualmente |
| 4 | Formulario de contacto genérico (no comercial) | 🟡 Alto | /contacto/ es estándar |
| 5 | Sin prueba social (n° de moteles, reseñas) | 🟡 Alto | Cero credibilidad comercial |
| 6 | No hay contraste visual para promocionar fichas | 🟡 Alto | Todo se ve igual, nada "destaca" |
| 7 | Velocidad: lazy loading con placeholder base64 | 🟢 Medio | Las imágenes cargan lentas |
| 8 | Sin pixel de tracking ni retargeting | 🟢 Medio | No se puede medir ni remarketing |

### 3 CAMBIOS RÁPIDOS (< 1 HORA)

**1. Agregar CTA flotante "¿Dueño de motel?"**
- Esquina inferior derecha
- Texto: "¿Tienes un motel? Aparece aquí →"
- Link a /para-moteles/ (nueva página)
- Tiempo: 20 min

**2. Cambiar descripción homepage**
- Meta description actual: habla de "encontrar moteles"
- Nueva: "El directorio #1 de moteles en Chile. Más de 10,000 visitas/mes.
  ¿Tienes un motel? Promociona tu negocio con nosotros."
- Tiempo: 5 min

**3. WhatsApp directo en header**
- Botón "Contáctanos" → enlace WhatsApp del admin
- Ideal para que dueños de moteles pregunten por precios
- Tiempo: 10 min

### 3 CAMBIOS DE ALTO IMPACTO

**4. Página "Para Moteles" (nueva)**
- URL: /para-moteles/
- Contenido: "Llega a miles de parejas buscando motel en tu ciudad"
- Planes: Básico ($29.990/mes), Premium ($59.990/mes), Setup ($120.000)
- Testimonios (cuando los haya)
- Tiempo: 4-6 horas + copy

**5. Bloques de "Moteles Destacados" en cada página de ciudad**
- Arriba del listado general: 3 fichas destacadas con foto grande
- Visualmente diferenciadas (borde, badge "DESTACADO", rating)
- Eso es el producto que se vende
- Tiempo: 2-3 horas

**6. Lead gen: formulario de contacto para dueños**
- Formulario específico en /para-moteles/
- Campos: Nombre motel, Ciudad, Teléfono, Email
- On submit: notificación + redirect a WhatsApp
- Tiempo: 3-4 horas

### MÉTRICAS A MEDIR

| Métrica | Actual | Target 30d | Cómo medir |
|---------|--------|-----------|------------|
| Clics en "¿Dueño de motel?" | 0 | 50+ | GA event |
| Contactos de moteleros | 0 | 10+ | Form + WhatsApp |
| Sesiones en /para-moteles/ | 0 | 200+ | GA pageview |
| Tasa de conversión (contacto → venta) | N/A | 30%+ | CRM manual |
| Clics en fichas destacadas | N/A | 100+ | GA event |

### TRACKING A IMPLEMENTAR

- [ ] Google Analytics 4 (verificar si existe)
- [ ] Search Console (verificar)
- [ ] Meta Pixel (para retargeting futuro)
- [ ] Eventos GA: clic en CTA "Dueño de motel"
- [ ] Evento GA: envío formulario contacto moteleros
