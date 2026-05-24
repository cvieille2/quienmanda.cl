# Feedback: Diseñador → Técnico
## Fecha: 2026-05-16 | Tarea revisada: T-007

### 1. Implementación del diseño
El HTML del bloque de planes está listo para insertar en WordPress.
Importante: USAR UN TEMA HIJO antes de modificar el footer.

### 2. Especificaciones técnicas del diseño
- El CSS debe ir en la hoja de estilos del tema hijo
- No uses !important a menos que sea estrictamente necesario
- Los media queries están en 768px — verifica que el tema no tenga
  sus propios breakpoints que interfieran
- Los colores: #e74c3c (rojo), #2c3e50 (dark), #ecf0f1 (bg light)
  Verifica que no choquen con los estilos del tema actual

### 3. Después de implementar
- Avísame para revisar visualmente en desktop y mobile
- Si algo se ve mal, hago los ajustes de CSS necesarios

### 4. Una nota
El tema actual usa lazy loading con placeholder base64. Si el bloque
de planes tiene imágenes, verifica que no se vean borrosas al cargar.
