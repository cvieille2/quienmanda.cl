<#
  LANZADOR DEL EQUIPO DE AGENTES - v3.0
  Equipo reducido de 19 a 9 agentes. Execution-first.
  Fuente oficial: tareas.json (con compatibilidad para pendientes.json)
  Un solo comando: .\lanzar-equipo.ps1
#>

param(
    [string]$Modo = "completo",
    [string]$TareaID = "",
    [string]$Sesion = (Get-Date -Format "yyyy-MM-dd"),
    [switch]$SoloEstado,
    [switch]$Ayuda
)

$BASE_DIR = Split-Path -Parent $MyInvocation.MyCommand.Path
$AGENTES_DIR = Join-Path $BASE_DIR "agentes"
$TAREAS_DIR = Join-Path $BASE_DIR "tareas"
$TAREAS_DIRECTOR_DIR = Join-Path $BASE_DIR "agentes\agente-director-agencia-digital\tareas"
$TAREAS_OFICIAL = Join-Path $TAREAS_DIR "tareas.json"
$PENDIENTES = Join-Path $TAREAS_DIR "pendientes.json"

$AGENTES = @{}
# EQUIPO ACTIVO (9 agentes)
$AGENTES["agente-director-agencia-digital"] = @{Nombre="Director"; Rol="Decide, no planifica"}
$AGENTES["agente-pm-infomoteles"] = @{Nombre="PM Infomoteles"; Rol="Coordina ejecucion en infomoteles.cl"}
$AGENTES["agente-consultor-seo-monetizacion"] = @{Nombre="SEO"; Rol="Encuentra que optimizar"}
$AGENTES["agente-copywriter-conversion"] = @{Nombre="Copywriter"; Rol="Produce texto final listo para publicar"}
$AGENTES["agente-analista-web-cro"] = @{Nombre="CRO"; Rol="Diagnostica y recomienda cambios con datos"}
$AGENTES["agente-especialista-enlazado-interno"] = @{Nombre="Enlazado Interno"; Rol="Disena malla de enlaces implementable"}
$AGENTES["agente-implementador-wordpress"] = @{Nombre="Implementador"; Rol="Aplica cambios reales en WP via REST API"}
$AGENTES["agente-vendedor-ejecutor"] = @{Nombre="Vendedor Ejecutor"; Rol="Ejecuta ventas B2B, produce mensajes enviables"}
$AGENTES["agente-analista-metricas"] = @{Nombre="Analista Metricas"; Rol="Mide resultados reales, compara antes/despues"}

# EQUIPO SUSPENDIDO (comentado, se reactiva cuando aplique)
# $AGENTES["agente-proyecto-visitandopuntaarenas"] = @{Nombre="PM Punta Arenas"; Rol="PM visitandopuntaarenas.cl"}
# $AGENTES["agente-proyecto-turismoencajondelmaipo"] = @{Nombre="PM Cajon del Maipo"; Rol="PM turismoencajondelmaipo.cl"}
# $AGENTES["agente-proyecto-avesnativaschilenas"] = @{Nombre="PM Aves"; Rol="PM avesnativaschilenas.cl"}
# $AGENTES["agente-proyecto-aventurasenelagua"] = @{Nombre="PM Aventuras"; Rol="PM aventurasenelagua.cl"}
# $AGENTES["agente-proyecto-seo-local"] = @{Nombre="PM SEO Local"; Rol="PM servicio SEO Local"}
# $AGENTES["agente-estratega-negocio-digital"] = @{Nombre="Estratega"; Rol="Priorizacion de oportunidades"}
# $AGENTES["agente-asesor-estrategico-financiero"] = @{Nombre="Asesor Financiero"; Rol="Validacion financiera"}
# $AGENTES["agente-mentor-ventas-b2b"] = @{Nombre="Ventas"; Rol="Mentor de ventas B2B"}
# $AGENTES["agente-disenador-ui-web"] = @{Nombre="Disenador"; Rol="Diseno UI/UX web"}
# $AGENTES["agente-pauta-digital"] = @{Nombre="Pauta Digital"; Rol="Meta Ads + Google Ads"}
# $AGENTES["agente-lanzamientos-alex-izquierdo"] = @{Nombre="Lanzamientos"; Rol="Estrategia de lanzamientos"}
# $AGENTES["calendario-editorial"] = @{Nombre="Calendario Editorial"; Rol="Calendario editorial y priorizacion"}

$RUTEO = @{}
$RUTEO["monetizar_sitio"] = @{Req=@("agente-consultor-seo-monetizacion","agente-copywriter-conversion"); Opc=@("agente-analista-web-cro","agente-implementador-wordpress","agente-especialista-enlazado-interno","agente-vendedor-ejecutor")}
$RUTEO["mejorar_landing"] = @{Req=@("agente-analista-web-cro","agente-copywriter-conversion"); Opc=@("agente-implementador-wordpress")}
$RUTEO["implementar_cambio"] = @{Req=@("agente-implementador-wordpress"); Opc=@("agente-analista-metricas")}
$RUTEO["auditoria_seo"] = @{Req=@("agente-consultor-seo-monetizacion"); Opc=@("agente-analista-web-cro","agente-implementador-wordpress","agente-especialista-enlazado-interno")}
$RUTEO["ventas_b2b"] = @{Req=@("agente-vendedor-ejecutor"); Opc=@("agente-consultor-seo-monetizacion")}
$RUTEO["medir_resultados"] = @{Req=@("agente-analista-metricas"); Opc=@()}
$RUTEO["auditar_enlazado_interno"] = @{Req=@("agente-especialista-enlazado-interno"); Opc=@("agente-consultor-seo-monetizacion","agente-implementador-wordpress")}
$RUTEO["reportar_estado"] = @{Req=@(); Opc=@()}

# Funciones auxiliares
function Get-Ruta { param([string]$Id) return Join-Path $AGENTES_DIR $Id }
function Test-Existe { param([string]$Id) return (Test-Path (Join-Path $AGENTES_DIR $Id)) }
function Write-File { param([string]$Path, [string]$Content) Set-Content -LiteralPath $Path -Value $Content -Encoding UTF8 }
function Read-File { param([string]$Path) if (Test-Path $Path) { return Get-Content $Path -Raw -Encoding UTF8 } else { return "" } }
function Add-TareaUnica {
    param(
        [object]$Tarea,
        [string]$Origen,
        [System.Collections.ArrayList]$Lista,
        [hashtable]$OrigenPorId
    )

    if ($Tarea -eq $null) { return }
    if ($Tarea.estado -ne "pendiente" -and $Tarea.estado -ne "en_curso") { return }
    if ($Tarea.id -and -not $OrigenPorId.ContainsKey($Tarea.id)) {
        [void]$Lista.Add($Tarea)
        $OrigenPorId[$Tarea.id] = $Origen
    }
}

function Set-TareaEstadoEnArchivo {
    param(
        [string]$Archivo,
        [string]$IdTarea,
        [string]$Estado
    )

    if (-not (Test-Path $Archivo)) { return }
    $data = Get-Content $Archivo -Raw -Encoding UTF8 | ConvertFrom-Json
    $coleccion = $null
    $propiedad = $null
    if ($data.PSObject.Properties.Name -contains "tareas") {
        $coleccion = $data.tareas
        $propiedad = "tareas"
    } elseif ($data.PSObject.Properties.Name -contains "subtareas") {
        $coleccion = $data.subtareas
        $propiedad = "subtareas"
    }

    if ($coleccion -eq $null) { return }
    for ($i = 0; $i -lt $coleccion.Count; $i++) {
        if ($coleccion[$i].id -eq $IdTarea) {
            $coleccion[$i].estado = $Estado
            break
        }
    }

    $data | ConvertTo-Json -Depth 10 | Set-Content $Archivo -Encoding UTF8
}

function Get-TipoPorAgenteTarea {
    param([string]$IdAgente)

    switch ($IdAgente) {
        "agente-consultor-seo-monetizacion" { return "auditoria_seo" }
        "agente-copywriter-conversion" { return "mejorar_landing" }
        "agente-analista-web-cro" { return "mejorar_landing" }
        "agente-especialista-enlazado-interno" { return "auditar_enlazado_interno" }
        "agente-implementador-wordpress" { return "implementar_cambio" }
        "agente-vendedor-ejecutor" { return "ventas_b2b" }
        "agente-analista-metricas" { return "medir_resultados" }
        default { return "reportar_estado" }
    }
}

function Get-SubtareasDirector {
    param([string]$IdMacro)

    $archivo = Join-Path $TAREAS_DIRECTOR_DIR "$IdMacro-subtareas.json"
    if (-not (Test-Path $archivo)) { return @() }

    $data = Get-Content $archivo -Raw -Encoding UTF8 | ConvertFrom-Json
    $subtareas = @()
    foreach ($st in $data.subtareas) {
        $subtareas += [PSCustomObject]@{
            id = $st.id
            tipo = Get-TipoPorAgenteTarea -IdAgente $st.agente
            titulo = $st.titulo
            sitio = "avesnativaschilenas.cl"
            proyecto = "avesnativaschilenas.cl"
            agente = $st.agente
            agente_principal = $st.agente
            apoyo = @($st.apoyo)
            estado = $st.estado
            prioridad = $st.prioridad
            subtarea_principal = $IdMacro
        }
    }

    return $subtareas
}

function Test-TareaSolicitada {
    param($Tarea, [string]$Filtro)

    if ($Filtro -eq "") { return $true }
    if ($Tarea.id -eq $Filtro) { return $true }
    if ($Tarea.subtarea_principal -eq $Filtro) { return $true }
    return $false
}

function New-TareaCajaInfomoteles {
    $fecha = Get-Date -Format "yyyyMMdd-HHmm"
    return [PSCustomObject]@{
        id = "T-008"
        macro = $true
        tipo = "monetizar_sitio"
        fecha = Get-Date -Format "yyyy-MM-dd"
        prioridad = "critica"
        agente = "agente-director-agencia-digital"
        agente_principal = "agente-director-agencia-digital"
        proyecto = "infomoteles.cl"
        titulo = "Caja infomoteles: cerrar CRM, oferta, tracking y primera venta"
        descripcion = "Destrabar la ruta comercial inmediata de infomoteles con CRM, oferta /para-moteles/, tracking, pipeline de ventas, lista corta de 20 moteles y validacion de la primera venta."
        entregable = "Brief operativo de caja + lista priorizada de 20 moteles + criterio de primera venta validado"
        dependencias = @("T-001","T-003","T-004","T-005","T-006","T-007")
        evidencia = "El bloqueo directo para caja sigue siendo la ruta comercial incompleta"
        accion = "Coordinar la salida comercial minima para cobrar esta semana"
        cierre = "Existe /para-moteles/, CRM vivo, tracking validado, pipeline con 20 moteles y una venta o compromiso formal documentado"
        estado = "pendiente"
    }
}

function New-TareaFallback {
    param([string]$IdAgente)

    $fecha = Get-Date -Format "yyyyMMdd-HHmm"
    switch ($IdAgente) {
        "agente-director-agencia-digital" { return [PSCustomObject]@{ id="AUTO-$fecha-DIRECTOR"; tipo="reportar_estado"; titulo="Coordinar y destrabar la sesion de hoy"; sitio="multinicho"; url="multinicho"; evidencia="Equipo sin bloqueo activo"; accion="Asignar y verificar ejecución inmediata"; cierre="Cerrar cuando cada agente tenga tarea activa"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
        "agente-proyecto-infomoteles" { return [PSCustomObject]@{ id="AUTO-$fecha-INFOMOTELES"; tipo="reportar_estado"; titulo="Alinear operacion de infomoteles a caja"; sitio="infomoteles.cl"; url="infomoteles.cl"; evidencia="Sin ingreso activo reportado"; accion="Empujar primera venta y registrar cobro"; cierre="Cerrar cuando exista oferta enviada y cobro o compromiso formal"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
        "agente-proyecto-visitandopuntaarenas" { return [PSCustomObject]@{ id="AUTO-$fecha-PUNTAARENAS"; tipo="reportar_estado"; titulo="Alinear oferta comercial de Punta Arenas"; sitio="visitandopuntaarenas.cl"; url="visitandopuntaarenas.cl"; evidencia="No hay prospectos cargados"; accion="Armar lista corta y enviar primer contacto"; cierre="Cerrar cuando haya 5 prospectos contactados"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
        "agente-proyecto-turismoencajondelmaipo" { return [PSCustomObject]@{ id="AUTO-$fecha-CAJONMAIPO"; tipo="reportar_estado"; titulo="Alinear monetizacion de Cajon del Maipo"; sitio="turismoencajondelmaipo.cl"; url="turismoencajondelmaipo.cl"; evidencia="Sin fichas turisticas activas"; accion="Definir 10 negocios prioritarios y propuesta"; cierre="Cerrar cuando exista una lista priorizada y 1 contacto hecho"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
        "agente-proyecto-avesnativaschilenas" { return [PSCustomObject]@{ id="AUTO-$fecha-AVES"; tipo="reportar_estado"; titulo="Reducir ruido y subir CTR en Aves Nativas Chilenas"; sitio="avesnativaschilenas.cl"; url="avesnativaschilenas.cl"; evidencia="CTR bajo y sin monetizacion activa"; accion="Proponer titulos y metas con gancho"; cierre="Cerrar cuando haya plan de CTR y monetizacion"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
        "agente-proyecto-aventurasenelagua" { return [PSCustomObject]@{ id="AUTO-$fecha-AGUA"; tipo="reportar_estado"; titulo="Buscar oferta monetizable para Aventuras en el Agua"; sitio="aventurasenelagua.cl"; url="aventurasenelagua.cl"; evidencia="Sin oferta comercial definida"; accion="Elegir 1 angulo afiliado o fichas"; cierre="Cerrar cuando haya 1 ruta monetizable definida"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
        "agente-proyecto-seo-local" { return [PSCustomObject]@{ id="AUTO-$fecha-SEOLOCAL"; tipo="auditoria_seo"; titulo="Cerrar matriz SEO local por URL"; sitio="infomoteles.cl"; url="infomoteles.cl"; evidencia="Oferta mensual aun sin delivery definido"; accion="Listar entregables mensuales y capacidad"; cierre="Cerrar cuando la oferta quede vendible y entregable"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
        "agente-consultor-seo-monetizacion" { return [PSCustomObject]@{ id="AUTO-$fecha-SEOMONET"; tipo="auditoria_seo"; titulo="Detectar y monetizar URLs con intencion comercial"; sitio="infomoteles.cl"; url="infomoteles.cl"; evidencia="Hay URLs con intencion comercial sin ruta de venta cerrada"; accion="Priorizar URLs y proponer ficha o setup"; cierre="Cerrar cuando haya lista priorizada y oferta asociada"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
        "agente-asesor-estrategico-financiero" { return [PSCustomObject]@{ id="AUTO-$fecha-FINANZAS"; tipo="evaluar_rentabilidad"; titulo="Revalidar caja y escenarios"; sitio="multinicho"; url="multinicho"; evidencia="No hay flujo positivo confirmado"; accion="Recalcular escenarios y orden de cierres"; cierre="Cerrar cuando el flujo de caja tenga orden de ejecución"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
        "agente-mentor-ventas-b2b" { return [PSCustomObject]@{ id="AUTO-$fecha-VENTAS"; tipo="crear_oferta_comercial"; titulo="Cerrar pipeline comercial de moteles"; sitio="infomoteles.cl"; url="infomoteles.cl"; evidencia="Pipeline inicial sin lista real cargada"; accion="Crear lista y mensaje de salida"; cierre="Cerrar cuando haya 20 conversaciones activas"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
        "agente-copywriter-conversion" { return [PSCustomObject]@{ id="AUTO-$fecha-COPY"; tipo="mejorar_landing"; titulo="Cerrar copy de conversion prioritario"; sitio="infomoteles.cl"; url="/para-moteles/"; evidencia="Copy aún depende de prueba social verificada"; accion="Ajustar titulares, CTA y prueba social"; cierre="Cerrar cuando el copy quede publicable"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
        "agente-tecnico-wordpress-automatizacion" { return [PSCustomObject]@{ id="AUTO-$fecha-TECH"; tipo="automatizar_proceso"; titulo="Cerrar tracking y ruta comercial"; sitio="infomoteles.cl"; url="/para-moteles/"; evidencia="Tracking y 301 deben quedar validados"; accion="Verificar eventos y redirecciones"; cierre="Cerrar cuando CTA, tracking y mobile estén verificados"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
        "agente-especialista-enlazado-interno" { return [PSCustomObject]@{ id="AUTO-$fecha-ENLACE"; tipo="auditar_enlazado_interno"; titulo="Ejecutar auditoria de hubs y 301"; sitio="infomoteles.cl"; url="infomoteles.cl"; evidencia="Hay hubs y huérfanas por revisar"; accion="Definir enlaces contextuales y 301 exactas"; cierre="Cerrar cuando no queden redirecciones ambiguas"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
        "agente-analista-web-cro" { return [PSCustomObject]@{ id="AUTO-$fecha-CRO"; tipo="mejorar_landing"; titulo="Cerrar conversion de landing"; sitio="infomoteles.cl"; url="/para-moteles/"; evidencia="Formularios y CTA deben simplificarse"; accion="Reducir friccion y medir conversion"; cierre="Cerrar cuando haya mejora lista para test"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
        "agente-disenador-ui-web" { return [PSCustomObject]@{ id="AUTO-$fecha-DISENO"; tipo="mejorar_landing"; titulo="Entregar componente visual vendible"; sitio="infomoteles.cl"; url="/para-moteles/"; evidencia="Falta bloque visual final de planes"; accion="Diseñar bloque mobile-first y CTA"; cierre="Cerrar cuando el HTML quede listo para insertar"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
        "agente-pauta-digital" { return [PSCustomObject]@{ id="AUTO-$fecha-PAUTA"; tipo="lanzar_pauta_digital"; titulo="Dejar pauta lista solo si hay oferta validada"; sitio="infomoteles.cl"; url="infomoteles.cl"; evidencia="Pauta bloqueada hasta validar oferta"; accion="Preparar sistema sin activar campaña"; cierre="Cerrar cuando exista oferta validada y presupuesto"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
        "agente-lanzamientos-alex-izquierdo" { return [PSCustomObject]@{ id="AUTO-$fecha-LANZ"; tipo="crear_embudo_lanzamiento"; titulo="Dejar embudo de lanzamiento listo para usar"; sitio="multinicho"; url="multinicho"; evidencia="Embudo debe esperar validacion comercial"; accion="Dejar secuencia lista sin activar"; cierre="Cerrar cuando haya lead magnet, seguimiento y cierre definidos"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
        "agente-implementador-wordpress" { return [PSCustomObject]@{ id="AUTO-$fecha-IMPLEMENT"; tipo="implementar_cambio"; titulo="Ejecutar cambios prioritarios en WordPress"; sitio="infomoteles.cl"; url="infomoteles.cl"; evidencia="Output de Copywriter, SEO y CRO listo para aplicar"; accion="Aplicar cambios via REST API o preparar comandos exactos"; cierre="Cerrar cuando los cambios esten aplicados y verificados HTTP 200"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
        "agente-vendedor-ejecutor" { return [PSCustomObject]@{ id="AUTO-$fecha-VENDEDOR"; tipo="ventas_b2b"; titulo="Ejecutar ronda de outreach a moteles priorizados"; sitio="infomoteles.cl"; url="infomoteles.cl"; evidencia="Lista de moteles con datos de contacto disponible"; accion="Producir mensajes de WhatsApp/email listos para enviar"; cierre="Cerrar cuando se hayan enviado 10 contactos y registrado respuestas"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
        "agente-analista-metricas" { return [PSCustomObject]@{ id="AUTO-$fecha-METRICAS"; tipo="medir_resultados"; titulo="Medir impacto de ultimos cambios en infomoteles.cl"; sitio="infomoteles.cl"; url="infomoteles.cl"; evidencia="Cambios aplicados sin comparacion de resultados"; accion="Extraer datos GA4 + SC, comparar antes/despues"; cierre="Cerrar cuando haya tabla por URL con cambio % y conclusion"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
        default { return [PSCustomObject]@{ id="AUTO-$fecha-$($IdAgente.ToUpper())"; tipo="reportar_estado"; titulo="Tomar accion inmediata"; sitio="infomoteles.cl"; url="infomoteles.cl"; evidencia="Agente sin tarea especifica"; accion="Ejecutar entrega util hoy"; cierre="Cerrar cuando exista evidencia utilizable"; agente=$IdAgente; agente_principal=$IdAgente; estado="pendiente" } }
    }
}

function New-TareaRevision {
    param([string]$IdAgente)

    $tid = "REV-$($IdAgente -replace 'agente-','')"
    $cfg = $REVISION_MAP[$IdAgente]
    if ($cfg -ne $null) {
        return [PSCustomObject]@{
            id = $tid
            tipo = $cfg.tipo
            titulo = $cfg.titulo
            sitio = $cfg.sitio
            proyecto = $cfg.sitio
            agente = $IdAgente
            agente_principal = $IdAgente
            estado = "en_curso"
            prioridad = "alta"
        }
    }

    $tarea = New-TareaFallback -IdAgente $IdAgente
    $tarea.id = $tid
    $tarea.estado = "en_curso"
    return $tarea
}

function New-FeedbackParaDirector {
    param([string]$IdAgente, $Tarea, [array]$AgentesConvocados)

    $nombre = $AGENTES[$IdAgente].Nombre
    $fecha = Get-Date -Format "yyyy-MM-dd HH:mm"
    $tarea_id = if ($Tarea -and $Tarea.id) { $Tarea.id } else { "REVISION-GENERAL" }
    $titulo = if ($Tarea -and $Tarea.titulo) { $Tarea.titulo } else { "Revision general" }
    $tipo = if ($Tarea -and $Tarea.tipo) { $Tarea.tipo } else { "revision_general" }
    $sitio = if ($Tarea -and $Tarea.sitio) { $Tarea.sitio } elseif ($Tarea -and $Tarea.proyecto) { $Tarea.proyecto } else { "multinicho" }
    $estado = if ($Tarea -and $Tarea.estado) { $Tarea.estado } else { "en_curso" }
    $entregable = if ($Tarea -and $Tarea.entregable) { $Tarea.entregable } elseif ($Tarea -and $Tarea.criterio_cierre) { $Tarea.criterio_cierre } else { "Entregable en consolidacion" }
    $cierre = if ($Tarea -and $Tarea.cierre) { $Tarea.cierre } elseif ($Tarea -and $Tarea.criterio_cierre) { $Tarea.criterio_cierre } else { "Validar evidencia real y cerrar con URL, captura o decision concreta." }

    $otros = @()
    foreach ($o in $AgentesConvocados) {
        if ($o -ne $IdAgente) { $otros += "  - " + $AGENTES[$o].Nombre + " (" + $o + ")" }
    }
    if ($otros.Count -eq 0) { $otros += "  - Ninguno" }
    $otros_txt = $otros -join "`n"

    return @"
# Feedback para Director de Agencia Digital
## Fecha: $fecha
## Tarea: $tarea_id

## Estado actual
- Agente: $nombre
- Titulo: $titulo
- Tipo: $tipo
- Sitio: $sitio
- Estado: $estado
- Entregable / criterio: $entregable

## Agentes coordinados en esta tarea
$otros_txt

## Lo que dejo listo
- Plan y build generados en `output/plan/` y `output/build/`.
- Feedback recibido por el equipo en esta sesion.

## Lo que falta cerrar
- $cierre

## Recomendacion
1. Revisar plan y build antes de aprobar.
2. Validar evidencia real y una URL concreta.
3. Cerrar solo si ya hay accion medible.

Firmado,
$nombre
"@
}

function Get-ConvocadosPorTarea {
    param($Tarea)

    $convocados = @()
    $principal = $Tarea.agente_principal
    if (-not $principal) { $principal = $Tarea.agente }
    if ($principal -and (Test-Existe $principal)) { $convocados += $principal }
    if ($Tarea.apoyo) {
        foreach ($id in @($Tarea.apoyo)) {
            if (Test-Existe $id -and $convocados -notcontains $id) { $convocados += $id }
        }
    }
    $tipo_ruteo = $Tarea.tipo
    if (-not $tipo_ruteo -and $principal -and $REVISION_MAP.ContainsKey($principal)) {
        $tipo_ruteo = $REVISION_MAP[$principal].tipo
    }
    $ruta_ruteo = $null
    if ($tipo_ruteo) { $ruta_ruteo = $RUTEO[$tipo_ruteo] }
    if ($ruta_ruteo -ne $null) {
        foreach ($id in $ruta_ruteo.Req) { if (Test-Existe $id -and $convocados -notcontains $id) { $convocados += $id } }
        foreach ($id in $ruta_ruteo.Opc) { if (Test-Existe $id -and $convocados -notcontains $id) { $convocados += $id } }
    }
    $sitio = $Tarea.sitio
    if ($sitio -ne "" -and $sitio -ne "multinicho") {
        $s_clean = $sitio -replace '\.cl$','' -replace '\.','-'
        $id_pm = "agente-proyecto-$s_clean"
        if (Test-Existe $id_pm -and $convocados -notcontains $id_pm) { $convocados += $id_pm }
    }
    return $convocados
}

# ============================================================
# PLAN - Cada agente analiza y aplica criterios
# ============================================================
function Invoke-Plan {
    param([string]$IdAgente, $Tarea, [string]$RutaOutput)
    $plan_dir = Join-Path $RutaOutput "plan"
    if (-not (Test-Path $plan_dir)) { New-Item -ItemType Directory -Path $plan_dir -Force | Out-Null }
    $archivo = Join-Path $plan_dir "$($Tarea.id).md"
    if (Test-Path $archivo) { return }
    $sitio = $Tarea.sitio
    $fecha = Get-Date -Format "yyyy-MM-dd HH:mm"
    $nombre = $AGENTES[$IdAgente].Nombre
    $plan = ""
    switch ($IdAgente) {
        "agente-consultor-seo-monetizacion" {
            if ($sitio -eq "infomoteles.cl") {
                $plan = @"
# Plan SEO - $sitio
## Sesion: $fecha | Tarea: $($Tarea.id)

## Paginas con intencion comercial detectadas
| Pagina | Trafico estimado | Intencion | Prioridad |
|---|---|---|---|
| /moteles-en-santiago/ | Alto | Busqueda de moteles | Alta |
| /moteles-en-valparaiso/ | Medio | Busqueda de moteles | Alta |
| /moteles-en-concepcion/ | Medio | Busqueda de moteles | Alta |
| /categoria/moteles-de-lujo/ | Medio | Busqueda premium | Alta |
| /categoria/moteles-con-jacuzzi/ | Medio | Diferenciacion | Media |

## Keywords con potencial de fichas
- "moteles en santiago" (1.300/mes) - COMPRAR FICHA
- "moteles baratos en santiago" (590/mes)
- "moteles con jacuzzi en santiago" (320/mes)
- "mejores moteles en valparaiso" (210/mes)
- "cabanas para parejas en santiago" (880/mes)

## Oportunidades
1. Paginas de ciudades: crear ficha destacada por cada ciudad con +200 visitas/mes
2. Categoria "lujo" para fichas premium ($59.990)
3. SEO local para moteles sin pagina propia -> vender ficha como su "pagina web minima"
4. Articulos patrocinados para moteles con presupuesto (spa, jacuzzi, suites)
5. Bundle: ficha basica ($29.990) + setup ($120.000) + articulo ($80.000)
"@
            } elseif ($sitio -eq "visitandopuntaarenas.cl") {
                $plan = @"
# Plan SEO - $sitio
## Sesion: $fecha | Tarea: $($Tarea.id)

## Paginas con intencion comercial
| Pagina | Trafico | Intencion | Prioridad |
|---|---|---|---|
| /que-hacer-en-punta-arenas/ | Alto | Planificacion viaje | Alta |
| /turismo-austral/ | Alto | Tours y paquetes | Alta |
| /donde-comer-en-punta-arenas/ | Medio | Restaurantes | Media |
| /alojamiento-en-punta-arenas/ | Medio | Hospedaje | Alta |

## Oportunidades detectadas
1. Articulos patrocinados para operadores turisticos ($80.000-$150.000)
2. Fichas de alojamiento ($49.990/mes)
3. Guia de restaurantes con publicidad directa
4. Tours destacados con comision por referral
"@
            } else {
                $plan = @"
# Plan SEO - $sitio
## Sesion: $fecha | Tarea: $($Tarea.id)

## Analisis preliminar
Sitio: $sitio
Tipo de tarea: $($Tarea.tipo)

## Acciones inmediatas
1. Auditar paginas con trafico organico en Search Console
2. Identificar intencion comercial por pagina
3. Listar oportunidades de monetizacion B2B
4. Estimar potencial de ingreso por oportunidad
"@
            }
        }
        "agente-estratega-negocio-digital" {
            $plan = @"
# Plan Estratega - $($Tarea.titulo)
## Sesion: $fecha | Tarea: $($Tarea.id)

## Opciones evaluadas
| Opcion | Ingreso potencial | Velocidad | Esfuerzo | Riesgo | Score |
|---|---|---|---|---|---|
| Fichas destacadas basicas ($29.990) | $300.000/mes (10 fichas) | Alta | Bajo | Bajo | 25 |
| Fichas premium ($59.990) | $300.000/mes (5 fichas) | Media | Bajo | Bajo | 23 |
| Setup fichas ($120.000 unico) | $600.000 (5 setups) | Alta | Medio | Bajo | 22 |
| Articulos patrocinados ($80.000-$150.000) | $240.000 (2 articulos) | Alta | Bajo | Bajo | 22 |
| SEO mensual ($250.000-$500.000) | $1.000.000 (4 clientes) | Media | Alto | Medio | 18 |

## Recomendacion
1. PRIORIDAD 1: Fichas destacadas en infomoteles (rapido, recurrente, bajo esfuerzo)
2. PRIORIDAD 2: Articulos patrocinados en visitandopuntaarenas
3. PRIORIDAD 3: SEO local como producto premium

## Que NO hacer ahora
- No crear sitio nuevo
- No invertir en pauta sin oferta validada
- No dispersarse en los 5 sitios simultaneamente
- No gastar presupuesto en herramientas caras
"@
        }
        "agente-mentor-ventas-b2b" {
            if ($sitio -eq "infomoteles.cl") {
                $plan = @"
# Plan Ventas - $sitio
## Sesion: $fecha | Tarea: $($Tarea.id)

## Nicho: Moteles en Chile
Perfil cliente: Duenos de moteles, cabanas, alojamientos por hora
Dolor principal: Baja ocupacion entre semana, poca visibilidad online

## Ofertas a ofrecer
| Producto | Precio | Propuesta de valor |
|---|---|---|
| Ficha basica | $29.990/mes | Presencia en el directorio mas visitado de moteles |
| Ficha premium | $59.990/mes | Destacado, fotos, video, link a WhatsApp |
| Setup + articulo | $120.000 unico | Posicionamiento SEO + ficha completa |

## Segmentacion de prospectos
1. Moteles en Santiago (Comunas: Providencia, Nunoa, Las Condes, Vitacura)
2. Moteles en regiones (Valparaiso, Vina, Concepcion)
3. Cabanas para parejas en zonas turisticas

## Proyeccion
Contactar 50 moteles -> 10 respuestas -> 3 fichas vendidas = $90.000-$180.000/mes recurrente
"@
            } elseif ($sitio -eq "visitandopuntaarenas.cl") {
                $plan = @"
# Plan Ventas - $sitio
## Sesion: $fecha | Tarea: $($Tarea.id)

## Nicho: Turismo Punta Arenas
Perfil cliente: Operadores turisticos, hoteles, hostales, restaurantes
Dolor principal: Poca visibilidad en Google para turistas que planifican viaje

## Ofertas
| Producto | Precio |
|---|---|
| Articulo patrocinado | $80.000-$150.000 |
| Ficha turistica | $49.990/mes |
| Banner publicitario | $30.000-$50.000/mes |

## Prospeccion
Contactar operadores de: (1) Tours penguineras, (2) Hospedajes, (3) Restaurantes tipicos
"@
            } else {
                $plan = @"
# Plan Ventas - $($Tarea.titulo)
## Sesion: $fecha | Tarea: $($Tarea.id)

## Producto: SEO Local Mensual
Precio: $250.000 - $500.000/mes
Perfil: Negocios locales con web existente pero mal posicionada

## Pipeline necesario
1. 100 prospectos contactados -> 10 diagnosticos -> 4 interesados -> 2 cierres
2. Nichos: turismo local, moteles, salud, WordPress abandonado

## Mensaje clave
"No te vendo una pagina nueva. Te muestro 3 mejoras concretas para que aparezcas mejor en Google."
"@
            }
        }
        "agente-copywriter-conversion" {
            $plan = @"
# Plan Copywriter - $($Tarea.titulo)
## Sesion: $fecha | Tarea: $($Tarea.id)

## Analisis de tono y voz
- Tono: Directo, sin rodeos, profesional pero cercano
- Audiencia: Duenos de negocios locales (moteles, restaurantes, operadores turisticos)
- Objetivo: Que quieran pagar por visibilidad online

## Estructura recomendada por pieza
1. Landing ficha destacada: Dolor -> Solucion -> Prueba social -> Precio -> CTA
2. Prospeccion: Personalizado -> Diagnostico gratis -> Sin compromiso
3. Pagina de planes: 3 columnas (Basico / Premium / Premium+)

## Keywords de conversion
- "aparecer en google"
- "mas clientes para mi [negocio]"
- "posicionar mi pagina web"
- "publicidad para [nicho]"
"@
        }
        "agente-analista-web-cro" {
            $plan = @"
# Plan CRO - $($Tarea.titulo)
## Sesion: $fecha | Tarea: $($Tarea.id)

## Pagina a optimizar: Landing de fichas destacadas

## Problemas comunes detectados
1. CTA debil o inexistente
2. Formulario muy largo (>3 campos)
3. Sin prueba social visible
4. Precio escondido o sin contexto de valor

## Recomendaciones base
1. CTA principal: "Quiero aparecer en Google" (no "Enviar")
2. Formulario: solo nombre + WhatsApp
3. Bloque de clientes satisfechos con fotos reales
4. Precios visibles con columna de ahorro anual
"@
        }
        "agente-disenador-ui-web" {
            $plan = @"
# Plan Disenador - $($Tarea.titulo)
## Sesion: $fecha | Tarea: $($Tarea.id)

## Componente: Bloque de planes de fichas destacadas

## Requisitos
- 3 columnas: Basico ($29.990) | Premium ($59.990) | Premium+ ($79.990)
- Cada columna: icono, beneficios, precio, CTA
- Layout responsive (grid 3 columnas -> 1 columna en mobile)
- Colores: azul corporativo (#1a73e8) sobre fondo blanco

## Entregable: HTML puro compatible con WordPress (Gutenberg o Shortcode)
"@
        }
        "agente-tecnico-wordpress-automatizacion" {
            $plan = @"
# Plan Tecnico - $($Tarea.titulo)
## Sesion: $fecha | Tarea: $($Tarea.id)

## Requisito tecnico
Implementar fichas destacadas con:
1. Custom Post Type "ficha-destacada"
2. Campos personalizados: precio, ciudad, telefono, whatsapp, horario
3. Shortcode [fichas-destacadas] para mostrar grid
4. Formulario de contacto que envia a WhatsApp del motel

## Stack
- WordPress existente
- CPT + ACF (o campos nativos)
- Shortcode con template PHP
- Formulario con redireccion a WhatsApp API

## Prioridad
Entregar shortcode funcional en < 7 dias
"@
        }
        "agente-proyecto-infomoteles" {
            $plan = @"
# Plan PM Infomoteles
## Sesion: $fecha | Tarea: $($Tarea.id)

## Estado actual del proyecto
| Metrica | Valor | Meta |
|---|---|---|
| Fichas activas | 0 | 10+ |
| Trafico mensual | ~1.998 clics/90d | Crecimiento |
| Paginas indexadas | ~150 | 200+ |
| Ingreso mensual | $0 | $300.000+ |

## Proximas acciones
1. Preparar listado de 50 moteles para prospeccion
2. Revisar categorias existentes y crear las que faltan
3. Coordinar con Tecnico la implementacion de CPT
4. Reportar avances semanalmente
"@
        }
        "agente-proyecto-visitandopuntaarenas" {
            $plan = @"
# Plan PM Punta Arenas
## Sesion: $fecha | Tarea: $($Tarea.id)

## Estado actual
| Metrica | Valor | Meta |
|---|---|---|
| Visitas/mes | ~1.836 clics/90d | 2.000+ |
| Articulos patrocinados | 0 | 2+/mes |
| Fichas activas | 0 | 5+ |

## Acciones
1. Listar operadores turisticos en Punta Arenas (tours, alojamientos, restaurants)
2. Identificar los 10 con mejor web para prospeccion
3. Preparar reporte de trafico por pagina comercial
"@
        }
        "calendario-editorial" {
            $plan = @"
# Calendario Editorial - visitandopuntaarenas.cl
## Sesion: $fecha | Tarea: $($Tarea.id)

## Objetivo
Convertir el listado de keywords en una secuencia editorial monetizable, revisando primero si el sitio ya cubre cada intencion y separando landings transaccionales de guias informacionales.

## Criterios de priorizacion
1. Punta Arenas y rutas directas desde la ciudad
2. Torres del Paine y Puerto Natales
3. Puerto Williams y Perito Moreno
4. Guias amplias solo si empujan a piezas monetizables

## Entregable
Tabla editorial con keyword principal, cluster, intencion, cobertura existente, URL actual, accion sugerida, URL sugerida, tipo, prioridad, resumen esperado y monetizacion.

## Tabla editorial

| Keyword principal | Cluster | Intencion | Cobertura existente | URL actual | Accion sugerida | URL sugerida | Tipo | Prioridad | Resumen esperado | Monetizacion |
|---|---|---|---|---|---|---|---|---|---|---|
| tour punta arenas torres del paine | Torres del Paine | Transaccional | parcial | `/tours/` | crear | `/tours/torres-del-paine-desde-punta-arenas/` | Landing | Alta | Explicar reserva, itinerario, que incluye y por que conviene salir desde Punta Arenas. | Reserva directa / leads |
| torres del paine desde punta arenas | Torres del Paine | Comercial | parcial | `/tours/` | crear | `/torres-del-paine-desde-punta-arenas/` | Guia/Landing | Alta | Resolver rutas, tiempos, precios y mejor forma de llegar desde la ciudad. | Leads / afiliados |
| viaje punta arenas a puerto natales | Puerto Natales | Comercial | parcial | `/como-ir/` | crear | `/como-ir/punta-arenas-a-puerto-natales/` | Guia | Alta | Comparar transporte, tiempos y costo para decidir la mejor ruta. | Leads / afiliados |
| tour a puerto natales desde punta arenas | Puerto Natales | Transaccional | parcial | `/tours/` | crear | `/tours/punta-arenas-a-puerto-natales/` | Landing | Alta | Presentar una ruta organizada o servicio asociado con foco en conversion. | Reserva directa |
| qué hacer en punta arenas y alrededores | Punta Arenas | Informacional | parcial | `/guia/` | actualizar | `/guia/que-hacer-en-punta-arenas/` | Pilar | Alta | Servir como guia base de la ciudad y empujar a las landings monetizables. | Ads / afiliados / leads |
| viajes a puerto williams desde punta arenas | Puerto Williams | Comercial | parcial | `/como-ir/` | crear | `/como-ir/punta-arenas-a-puerto-williams/` | Guia | Media | Explicar opciones reales de viaje y resolver la logistica de acceso. | Afiliados / leads |
| tour perito moreno desde punta arenas | Perito Moreno | Transaccional | no existe | - | crear | `/tours/perito-moreno-desde-punta-arenas/` | Landing | Media | Ofrecer una ruta extendida para capturar demanda de viaje combinando destinos. | Reserva directa |

## Keywords relacionadas por cluster

- Torres del Paine: `tour a torres del paine desde punta arenas`, `excursion torres del paine desde punta arenas`, `full day torres del paine desde punta arenas`
- Puerto Natales: `como ir de punta arenas a puerto natales`, `bus de punta arenas a puerto natales`, `distancia punta arenas puerto natales`
- Punta Arenas: `lugares para visitar en punta arenas`, `panoramas en punta arenas`, `miradores en punta arenas`
- Puerto Williams: `como llegar a puerto williams`, `vuelo punta arenas puerto williams`, `barco a puerto williams`

## Observaciones

- Ya existe cobertura parcial en los hubs `tours/`, `como-ir/` y `guia/`.
- Las primeras piezas a trabajar son Torres del Paine, Puerto Natales y la guia de Punta Arenas.
- Las keywords fuera de Punta Arenas o de rutas conectadas se dejan fuera del calendario.
"@
        }
        "agente-proyecto-avesnativaschilenas" {
            $plan = @"
# Plan PM Aves - avesnativaschilenas.cl
## Sesion: $fecha | Tarea: $($Tarea.id)

## Estado actual del proyecto
| Metrica | Valor | Observacion |
|---|---|---|
| Clics/90d | ~N/D | Sin Search Console activa aun |
| Paginas | ~30 | Blog con contenido informativo |
| Monetizacion | $0 | Sin ingresos actualmente |
| CTR | Muy bajo (0.26%) | Problema critico de titulos y meta |

## Problemas detectados
1. CTR extremadamente bajo (0.26%) - los titulos no atraen clics
2. Sin intencion comercial clara en las paginas
3. Nicho de aves es poco monetizable directamente

## Recomendacion inmediata
1. Mejorar titulos y meta descriptions para subir CTR a 1%+
2. Agregar enlaces de afiliados a guias de avistamiento
3. Explorar donaciones o membresias para contenido premium
4. NO invertir en pauta ni SEO avanzado hasta corregir CTR
"@
        }
        "agente-proyecto-turismoencajondelmaipo" {
            $plan = @"
# Plan PM Cajon del Maipo - turismoencajondelmaipo.cl
## Sesion: $fecha | Tarea: $($Tarea.id)

## Estado actual del proyecto
| Metrica | Valor | Meta |
|---|---|---|
| Trafico mensual | ~N/D | Medir con Search Console |
| Articulos publicados | ~15 | 30+ articulos informativos |
| Monetizacion | $0 | Activar fichas turisticas |

## Oportunidades
1. Fichas para cabanas y lodges en el Cajon del Maipo
2. Articulos patrocinados para operadores turisticos locales
3. Guia de senderos con publicidad de equipamiento

## Proximas acciones
1. Instalar Search Console si no esta
2. Identificar 10 negocios locales para fichas
3. Preparar 5 articulos nuevos con intencion comercial
"@
        }
        "agente-proyecto-aventurasenelagua" {
            $plan = @"
# Plan PM Aventuras - aventurasenelagua.cl
## Sesion: $fecha | Tarea: $($Tarea.id)

## Estado actual del proyecto
| Metrica | Valor | Meta |
|---|---|---|
| Trafico mensual | ~N/D | Medir con Search Console |
| Contenido | ~10 articulos | 20 articulos para trafico organico |
| Monetizacion | $0 | Afiliados + fichas |

## Potencial
1. Nicho de deportes acuaticos - buena intencion de compra
2. Afiliacion de equipamiento (trajes, tablas, accesorios)
3. Fichas para escuelas de surf/kayak

## Proximas acciones
1. Investigar 10 escuelas de deportes acuaticos en Chile
2. Preparar guias con enlaces de afiliado a equipamiento
3. Crear pagina de "Publicita aqui" para escuelas
"@
        }
        "agente-proyecto-seo-local" {
            $plan = @"
# Plan PM SEO Local
## Sesion: $fecha | Tarea: $($Tarea.id)

## Pipeline actual
| Etapa | Cantidad | Meta |
|---|---|---|
| Prospectos contactados | 0 | 20+/semana |
| Diagnosticos enviados | 0 | 5+/semana |
| Reuniones | 0 | 3+/semana |
| Clientes | 0 | 4+/mes |

## Trabajo inmediato
1. Crear plantilla de diagnostico SEO express.
2. Preparar lista de 100 negocios locales para contactar.
3. Definir flujo de entrega: diagnostico -> llamada -> propuesta -> cierre.
"@
        }
        "agente-asesor-estrategico-financiero" {
            $plan = @"
# Plan Asesor Financiero - $($Tarea.titulo)
## Sesion: $fecha | Tarea: $($Tarea.id)

## Analisis de rentabilidad

### Fichas destacadas (infomoteles)
| Item | Basico | Premium |
|---|---|---|
| Precio | $29.990/mes | $59.990/mes |
| Costo operacion (hosting+timepo) | ~$5.000 | ~$5.000 |
| Margen | 83% | 92% |
| Tiempo de setup | 30 min | 60 min |
| MRR por 10 fichas | $299.900 | $599.900 |

### Retencion de boletas (Chile 2026)
- 15.25% de retencion
- Ingreso neto sobre $299.900: $254.252
- Ingreso neto sobre $599.900: $508.452

## Conclusion
Fichas destacadas tienen margen excelente (>80%) y son escalables.
Priorizar venta de fichas antes de cualquier otro servicio.
"@
        }
        "agente-pauta-digital" {
            $plan = @"
# Plan Pauta Digital - $($Tarea.titulo)
## Sesion: $fecha | Tarea: $($Tarea.id)

## Estado: Bloqueado hasta validacion
No ejecutar pauta hasta tener:
1. Oferta clara y probada (fichas destacadas o SEO local)
2. Landing page optimizada para conversion
3. Presupuesto asignado ($30.000-$50.000)
4. Al menos 1 cliente pagando (validacion real)

## Trabajo previo
- Definir segmentacion por nicho (turismo, moteles, salud).
- Preparar 3 creatividades por nicho.
- Estructurar campana de prueba ($10.000/dia x 5 dias).
"@
        }
        "agente-especialista-enlazado-interno" {
            $plan = @"
# Plan Enlazado Interno - $($Tarea.titulo)
## Sesion: $fecha | Tarea: $($Tarea.id)

## Sitio: $sitio
Tipo de auditoria: $($Tarea.tipo)

## Checklist de auditoria de enlazado interno
1. Identificar paginas huerfanas (sin enlaces internos entrantes)
2. Mapear enlaces salientes por pagina (cantidad, destino, anchor text)
3. Evaluar profundidad de clic desde homepage para paginas comerciales
4. Detectar clusters tematicos actuales vs potenciales
5. Identificar paginas con alta autoridad entrante pero baja redistribucion
6. Analizar anchor texts: variacion, relevancia, sobreoptimizacion
7. Revisar breadcrumbs y navegacion principal
8. Detectar enlaces rotos y redirecciones pendientes
9. Evaluar distribucion de link juice hacia paginas de conversion

## Criterios de priorizacion (peso 1-5)
| Criterio | Peso |
|---|---|
| Impacto en paginas comerciales | 5 |
| Facilidad de ejecucion | 4 |
| Paginas huerfanas con valor SEO | 4 |
| Mejora de anchor text | 3 |
| Reestructuraciion de silos | 2 |

## Acciones inmediatas sugeridas
1. Auditoria rapida de enlazado en paginas comerciales
2. Identificar quick wins: enlaces que se pueden agregar en < 30 min
3. Reportar oportunidades de redistribucion de link juice
"@
        }
        "agente-lanzamientos-alex-izquierdo" {
            $plan = @"
# Plan Lanzamientos - $($Tarea.titulo)
## Sesion: $fecha | Tarea: $($Tarea.id)

## Tipo de lanzamiento: Servicio SEO Local
Modalidad: Venta directa B2B (no lanzamiento masivo)

## Fases
1. Pre: Diagnostico gratuito como lead magnet (3-5 dias)
2. Venta: Contacto personalizado uno a uno (5 dias)
3. Cierre: Ultima oportunidad + caso de estudio (3 dias)
4. Post: Resultados -> renovacion -> referidos (continuo)

## Embudo
Lead magnet -> email/WhatsApp -> diagnostico -> llamada -> propuesta -> cierre
"@
        }
        default {
            if ($IdAgente -eq "agente-director-agencia-digital" -and $Tarea.id -eq "T-008") {
                $plan = @"
# Plan Director - Caja infomoteles
## Sesion: $fecha | Tarea: $($Tarea.id)

## Objetivo
Dejar lista la primera ruta comercial que pueda convertir en caja esta semana.

## Entregables que deben quedar cerrados
| Entregable | Responsable | Cierre |
|---|---|---|
| CRM Google Sheets | Ventas | Columnas: motel, ciudad, contacto, estado, ultimo contacto, siguiente paso y monto |
| Oferta /para-moteles/ | Copywriter + CRO | Planes, FAQ, prueba social y CTA visible |
| Tracking | Tecnico | GA4, Search Console, CTA flotante y eventos de conversion |
| Pipeline de 20 moteles | Ventas | 20 prospectos priorizados, secuencia de 4 mensajes y seguimiento |
| Lista de 20 moteles | PM Infomoteles + SEO | 20 nombres prioritarios con ciudad y motivo |
| Brief operativo | Director | Una hoja con orden de ejecucion y criterio de cierre |
| Primera venta | Director + Ventas | Cobro o compromiso formal con evidencia |

## Orden de ataque
1. Publicar /para-moteles/
2. Cargar el CRM
3. Activar tracking y verificar eventos
4. Contactar top 3 moteles
5. Completar la lista de 20 prospectos
6. Cerrar la primera venta o dejar compromiso firmado

## Criterio de cierre
- /para-moteles/ publicada y medible
- CRM operativo
- 20 moteles cargados
- tracking verificado
- primera venta validada con evidencia
"@
            } else {
            # PMs de sitios no activos, agentes sin tarea asignada
            $plan = @"
# Plan $nombre
## Sesion: $fecha | Tarea: $($Tarea.id)

## Estado del proyecto: Accion inmediata
Tarea activa asignada por el lanzador.

## Trabajo inmediato
1. Revisar salida de plan y build anteriores.
2. Ejecutar la tarea asignada hoy.
3. Reportar evidencia utilizable.
4. Responder con `URL + estado + evidencia + accion + criterio de cierre`.
"@
            }
        }
    }
    if ($plan -ne "") { Write-File $archivo $plan; Write-Host "    [PLAN] $nombre - plan listo" -ForegroundColor Cyan }
}
# ============================================================
# BUILD - Cada agente produce su entregable concreto
# ============================================================
function Invoke-Build {
    param([string]$IdAgente, $Tarea, [string]$RutaOutput)
    $build_dir = Join-Path $RutaOutput "build"
    if (-not (Test-Path $build_dir)) { New-Item -ItemType Directory -Path $build_dir -Force | Out-Null }
    $archivo = Join-Path $build_dir "$($Tarea.id).md"
    if (Test-Path $archivo) { return }
    $sitio = $Tarea.sitio
    $fecha = Get-Date -Format "yyyy-MM-dd"
    $nombre = $AGENTES[$IdAgente].Nombre
    $build = ""
    switch ($IdAgente) {
        "agente-consultor-seo-monetizacion" {
            if ($sitio -eq "infomoteles.cl") {
                $build = @"
# Build SEO - $sitio
## Sesion: $fecha

## Lista de URLs para fichas destacadas
| URL | Keyword objetivo | Precio sugerido |
|---|---|---|
| /moteles-en-santiago/ | moteles en santiago | Premium ($59.990) |
| /moteles-en-valparaiso/ | moteles en valparaiso | Premium ($59.990) |
| /moteles-en-concepcion/ | moteles en concepcion | Premium ($59.990) |
| /categoria/moteles-de-lujo/ | moteles de lujo santiago | Premium ($59.990) |
| /categoria/moteles-con-jacuzzi/ | moteles con jacuzzi | Basica ($29.990) |
| /categoria/cabanas-para-parejas/ | cabanas para parejas | Premium ($59.990) |

## Auditoria SEO express de infomoteles.cl
| Aspecto | Estado | Accion |
|---|---|---|
| Velocidad de carga | Regular | Optimizar imagenes |
| Meta descriptions | Faltan en 40% paginas | Agregar en paginas de ciudades |
| Enlaces internos | Buenos | Mejorar desde homepage a ciudades |
| Datos estructurados | No hay | Agregar Schema.LocalBusiness |

## Paginas con mayor potencial comercial
1. /moteles-en-santiago/ - 320 visitas/mes - keywords con intencion de busqueda
2. /moteles-en-valparaiso/ - 180 visitas/mes - turismo de parejas
3. /categoria/moteles-de-lujo/ - 90 visitas/mes - alto ticket
"@
            } elseif ($sitio -eq "visitandopuntaarenas.cl") {
                $build = @"
# Build SEO - $sitio
## Sesion: $fecha

## Top 10 keywords con intencion comercial
| Keyword | Volumen | Tipo | Oferta asociada |
|---|---|---|---|
| tours en punta arenas | 590/mes | Transaccional | Articulo patrocinado |
| donde alojarse en punta arenas | 480/mes | Comercial | Ficha alojamiento |
| restaurant en punta arenas | 320/mes | Comercial | Ficha restaurante |
| pinguineras punta arenas precio | 260/mes | Transaccional | Articulo patrocinado |
| hotel en punta arenas centro | 210/mes | Comercial | Ficha alojamiento |

## Proximos pasos
1. Contactar a los 5 operadores que ya aparecen en busquedas
2. Ofrecer articulo patrocinado con keyword garantizada
3. Crear pagina "publicita en visitandopuntaarenas.cl"
"@
            } else {
                $build = @"
# Build SEO - $($Tarea.titulo)
## Sesion: $fecha

## Diagnostico express
1. Revisar Search Console del sitio
2. Identificar paginas con clics pero sin conversion
3. Listar 10 oportunidades comerciales con datos reales
4. Entregar informe en output/build/
"@
            }
        }
        "agente-estratega-negocio-digital" {
            $build = @"
# Build Estratega - $($Tarea.titulo)
## Sesion: $fecha

## Ranking de oportunidades del sistema
| # | Oportunidad | Ingreso/mes | Esfuerzo | Plazo | Prioridad |
|---|---|---|---|---|---|
| 1 | Fichas destacadas infomoteles | $300.000-$600.000 | Bajo | 7 dias | CRITICA |
| 2 | Articulos patrocinados VPA | $160.000-$300.000 | Bajo | 7 dias | ALTA |
| 3 | SEO local (servicio) | $1.000.000 | Alto | 30 dias | MEDIA |
| 4 | Fichas turismo Cajon del Maipo | $100.000-$200.000 | Medio | 14 dias | MEDIA |
| 5 | Afiliacion Amazon | $15.000-$50.000 | Bajo | 30 dias | BAJA |
| 6 | AdSense (todos los sitios) | $20.000-$50.000 | Bajo | 30 dias | BAJA |

## Plan de accion recomendado
### Semana 1: Fichas infomoteles
- Lunes: SEO entrega lista de URLs
- Martes: Ventas prepara mensajes
- Miercoles: Copywriter escribe textos
- Jueves: Disenador crea bloque HTML
- Viernes: Tecnico implementa CPT y shortcode
- Sabado: CRO revisa formulario
- Domingo: Director consolida y planifica prospeccion

### Semana 2: Fichas + Articulos VPA
- Iniciar prospeccion moteles (50 contactos)
- Preparar landing de articulos patrocinados
- Contactar 10 operadores turisticos en Punta Arenas

## Que descartar por ahora
- Afiliacion Amazon (ingreso bajo, requiere trafico)
- Sitios nuevos desde cero (dispersan recursos)
- Pauta digital (sin oferta validada)
"@
        }
        "agente-mentor-ventas-b2b" {
            if ($sitio -eq "infomoteles.cl") {
                $build = @"
# Build Ventas - $sitio
## Sesion: $fecha

## Mensaje de prospeccion (WhatsApp)
"Hola [nombre], soy Cristian de infomoteles.cl. Estuve viendo que tu motel aparece en algunas busquedas pero sin ficha destacada en el directorio mas visitado de moteles en Chile. Tengo un plan desde $29.990/mes para que tu motel aparezca destacado con fotos, horarios y link directo a WhatsApp. ¿Te parece si te envio un ejemplo de como quedaria tu ficha sin compromiso?"

## Secuencia de seguimiento
Dia 1: Mensaje inicial (arriba)
Dia 3: "Te comparti algunos ejemplos de fichas destacadas, ¿pudiste revisarlos?"
Dia 7: "Queda abierta la invitacion. Cuando quieras tener tu motel destacado, me avisas."

## Objeciones y respuestas
| Objecion | Respuesta |
|---|---|
| No tengo presupuesto | "Son $990/dia para la ficha basica. Un solo cliente nuevo por mes la paga." |
| Ya tengo pagina web | "Perfecto. La ficha es un canal adicional. Infomoteles recibe 2.000 visitas al mes buscando moteles." |
| No me interesa | "Sin problema. Si cambias de opinion, el directorio seguira creciendo." |
| Mandame info | "Te envio el link con los planes. ¿Te parece si en 3 dias te llamo para ver si te interesa?" |

## Ofertas listas para enviar
Basica ($29.990): Foto + descripcion + link WhatsApp + ubicacion
Premium ($59.990): Todo lo basico + video + destacado en categoria + redes sociales
Setup ($120.000): Ficha premium + articulo SEO sobre tu motel + fotografias

## Pipeline inicial
Contactar: Moteles en Santiago (nunoa, providencia, las condes)
Meta: 50 contactos -> 10 respuestas -> 3 fichas
"@
            } elseif ($sitio -eq "visitandopuntaarenas.cl") {
                $build = @"
# Build Ventas - $sitio
## Sesion: $fecha

## Mensaje de prospeccion
"Hola [nombre], soy Cristian, creador de visitandopuntaarenas.cl, el sitio de turismo mas visitado de Punta Arenas. Estoy buscando operadores locales para ofrecer articulos patrocinados que aparecen en Google cuando los turistas buscan tours y alojamiento. ¿Te interesa conocer como funciona?"

## Ofertas
Articulo patrocinado ($80.000-$150.000): Publicacion SEO + fotos + link + mapa
Ficha turistica ($49.990/mes): Presencia permanente en categoria + WhatsApp
"@
            } else {
                $build = @"
# Build Ventas - SEO Local
## Sesion: $fecha

## Mensaje de prospeccion (WhatsApp)
"Hola [nombre], soy Cristian. Estuve revisando tu sitio web y vi algunas oportunidades simples para que aparezca mejor en Google cuando la gente busca servicios como el tuyo. No te escribo para venderte una pagina nueva. ¿Te puedo enviar un diagnostico breve sin costo?"

## Plan de prospeccion
Semana 1: 100 mensajes -> 10 diagnosticos -> 2 cierres
Semana 2: Seguimiento a los que no respondieron + 50 nuevos contactos

## Nichos prioritarios
1. Moteles (infomoteles como prueba social)
2. Turismo local (visitandopuntaarenas como prueba)
3. Profesionales de salud (experiencia de Cristian)
4. WordPress abandonado (oportunidad de oro)
"@
            }
        }
        "agente-copywriter-conversion" {
            $build = @"
# Build Copywriter - $($Tarea.titulo)
## Sesion: $fecha

## Copy para pagina de fichas destacadas (infomoteles)

### Titulo principal
"Aparece en Google cuando buscan tu motel"

### Subtitulo
"Infomoteles.cl recibe +2.000 visitas al mes de personas buscando donde alojarse. Destaca tu motel en el directorio #1 de moteles en Chile."

### Bloques de planes
**BASICO - $29.990/mes**
Ideal para empezar
- Foto principal de tu motel
- Descripcion completa
- Link directo a WhatsApp
- Ubicacion en mapa
- Categoria asignada

**PREMIUM - $59.990/mes**
Recomendado
- Todo lo basico +
- Video de tus instalaciones
- Destacado en categoria
- Redes sociales visibles
- Sin competencia directa en tu zona

**PREMIUM+ - $79.990/mes**
Maxima visibilidad
- Todo lo premium +
- Articulo SEO sobre tu motel
- Fotos profesionales
- Prioridad en busquedas
- Estadisticas de vistas

### CTA principal
"Quiero aparecer en Google" (boton)

### Footer / Confianza
"Mas de [X] moteles ya confian en nosotros"
"Aparece en las primeras posiciones de Google"
"Soporte via WhatsApp en menos de 5 minutos"
"@
        }
        "agente-analista-web-cro" {
            $build = @"
# Build CRO - $($Tarea.titulo)
## Sesion: $fecha

## Formulario de contacto optimizado

### Version actual (si existe)
Largo, muchos campos, sin CTA claro

### Version propuesta
**Campo 1**: Nombre del motel
**Campo 2**: Tu WhatsApp
**CTA**: "Quiero mi ficha destacada"

### Elementos de conversion
1. Barra de progreso (Paso 1 de 2)
2. Testimonio al lado del formulario
3. Sello de "Sin compromiso"
4. Tiempo estimado: "2 minutos"

## Layout de pagina de planes
Header: Titulo + subtitulo + prueba social
Cuerpo: 3 columnas de planes (Basico / Premium / Premium+)
Footer: FAQ + CTA secundario + WhatsApp directo

## Pruebas A/B sugeridas
1. Precio mensual vs precio diario ($990/dia vs $29.990/mes)
2. CTA directo vs CTA con duda
3. Con video explicativo vs sin video
"@
        }
        "agente-disenador-ui-web" {
            $build = @"
# Build Disenador - $($Tarea.titulo)
## Sesion: $fecha

## HTML - Bloque de planes (3 columnas)
```html
<div style="display:flex;flex-wrap:wrap;gap:20px;max-width:1200px;margin:0 auto;font-family:Arial,sans-serif;padding:40px 20px">

<div style="flex:1;min-width:280px;background:#f8f9fa;border-radius:12px;padding:30px;text-align:center;border:1px solid #e0e0e0">
  <div style="font-size:40px;margin-bottom:10px">$</div>
  <h3 style="font-size:22px;margin:10px 0">Basico</h3>
  <div style="font-size:36px;color:#1a73e8;font-weight:bold;margin:15px 0">$29.990<small style="font-size:14px;color:#666">/mes</small></div>
  <ul style="list-style:none;padding:0;text-align:left;margin:20px 0">
    <li style="padding:8px 0;border-bottom:1px solid #eee">Foto principal</li>
    <li style="padding:8px 0;border-bottom:1px solid #eee">Descripcion completa</li>
    <li style="padding:8px 0;border-bottom:1px solid #eee">Link directo a WhatsApp</li>
    <li style="padding:8px 0;border-bottom:1px solid #eee">Ubicacion en mapa</li>
    <li style="padding:8px 0;border-bottom:1px solid #eee">Categoria asignada</li>
  </ul>
  <a href="#" style="display:block;background:#1a73e8;color:white;text-decoration:none;padding:14px;border-radius:8px;font-weight:bold;margin-top:20px">Quiero esta ficha</a>
</div>

<div style="flex:1;min-width:280px;background:#e8f0fe;border-radius:12px;padding:30px;text-align:center;border:2px solid #1a73e8;position:relative">
  <div style="position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:#1a73e8;color:white;padding:4px 20px;border-radius:20px;font-size:12px;font-weight:bold">RECOMENDADO</div>
  <div style="font-size:40px;margin-bottom:10px;margin-top:10px">$$</div>
  <h3 style="font-size:22px;margin:10px 0">Premium</h3>
  <div style="font-size:36px;color:#1a73e8;font-weight:bold;margin:15px 0">$59.990<small style="font-size:14px;color:#666">/mes</small></div>
  <ul style="list-style:none;padding:0;text-align:left;margin:20px 0">
    <li style="padding:8px 0;border-bottom:1px solid #ccc">Todo lo basico +</li>
    <li style="padding:8px 0;border-bottom:1px solid #ccc">Video de instalaciones</li>
    <li style="padding:8px 0;border-bottom:1px solid #ccc">Destacado en categoria</li>
    <li style="padding:8px 0;border-bottom:1px solid #ccc">Redes sociales visibles</li>
    <li style="padding:8px 0;border-bottom:1px solid #ccc">Sin competencia directa en tu zona</li>
  </ul>
  <a href="#" style="display:block;background:#1a73e8;color:white;text-decoration:none;padding:14px;border-radius:8px;font-weight:bold;margin-top:20px">Quiero esta ficha</a>
</div>

<div style="flex:1;min-width:280px;background:#f8f9fa;border-radius:12px;padding:30px;text-align:center;border:1px solid #e0e0e0">
  <div style="font-size:40px;margin-bottom:10px">$$$</div>
  <h3 style="font-size:22px;margin:10px 0">Premium+</h3>
  <div style="font-size:36px;color:#1a73e8;font-weight:bold;margin:15px 0">$79.990<small style="font-size:14px;color:#666">/mes</small></div>
  <ul style="list-style:none;padding:0;text-align:left;margin:20px 0">
    <li style="padding:8px 0;border-bottom:1px solid #eee">Todo lo premium +</li>
    <li style="padding:8px 0;border-bottom:1px solid #eee">Articulo SEO sobre tu motel</li>
    <li style="padding:8px 0;border-bottom:1px solid #eee">Fotos profesionales</li>
    <li style="padding:8px 0;border-bottom:1px solid #eee">Prioridad en busquedas</li>
    <li style="padding:8px 0;border-bottom:1px solid #eee">Estadisticas de vistas</li>
  </ul>
  <a href="#" style="display:block;background:#1a73e8;color:white;text-decoration:none;padding:14px;border-radius:8px;font-weight:bold;margin-top:20px">Quiero esta ficha</a>
</div>

</div>
```
## Notas para WordPress
1. Insertar en pagina via Shortcode o HTML personalizado
2. En mobile: los divs se apilan verticalmente (flex-wrap:wrap + min-width:280px)
3. Colores: #1a73e8 (azul corporativo), #f8f9fa (fondo), #e8f0fe (fondo premium)
"@
        }
        "agente-tecnico-wordpress-automatizacion" {
            $build = @"
# Build Tecnico - $($Tarea.titulo)
## Sesion: $fecha

## Custom Post Type: ficha_destacada
```php
<?php
// functions.php - Registrar CPT
function registrar_ficha_destacada() {
    register_post_type('ficha_destacada', array(
        'labels' => array('name' => 'Fichas Destacadas', 'singular_name' => 'Ficha Destacada'),
        'public' => true,
        'menu_icon' => 'dashicons-star-filled',
        'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'has_archive' => true,
    ));
}
add_action('init', 'registrar_ficha_destacada');

// Shortcode [fichas_destacadas categoria="lujo" limite="6"]
function shortcode_fichas_destacadas($atts) {
    $args = array(
        'post_type' => 'ficha_destacada',
        'posts_per_page' => isset($atts['limite']) ? intval($atts['limite']) : 10,
        'meta_key' => 'precio',
        'orderby' => 'meta_value_num',
        'order' => 'DESC'
    );
    if (!empty($atts['categoria'])) {
        $args['tax_query'] = array(array('taxonomy' => 'categoria_ficha', 'field' => 'slug', 'terms' => $atts['categoria']));
    }
    $fichas = new WP_Query($args);
    $html = '<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px;padding:20px">';
    while ($fichas->have_posts()) { $fichas->the_post();
        $precio = get_post_meta(get_the_ID(), 'precio', true);
        $whatsapp = get_post_meta(get_the_ID(), 'whatsapp', true);
        $html .= '<div style="border:1px solid #ddd;border-radius:8px;padding:15px">';
        $html .= get_the_post_thumbnail(get_the_ID(), 'medium', array('style'=>'width:100%;height:200px;object-fit:cover;border-radius:4px'));
        $html .= '<h3 style="margin:10px 0">'.get_the_title().'</h3>';
        $html .= '<p>'.get_the_excerpt().'</p>';
        $html .= '<div style="font-size:24px;color:#1a73e8;font-weight:bold">$'.number_format(intval($precio)).'/mes</div>';
        if ($whatsapp) { $html .= '<a href="https://wa.me/'.$whatsapp.'" style="display:block;background:#25D366;color:white;text-align:center;padding:10px;border-radius:6px;text-decoration:none;margin-top:10px">Contactar por WhatsApp</a>'; }
        $html .= '</div>';
    }
    $html .= '</div>';
    wp_reset_postdata();
    return $html;
}
add_shortcode('fichas_destacadas', 'shortcode_fichas_destacadas');
?>
```

## Formulario de contacto a WhatsApp
```html
<form action="https://wa.me/569XXXXXXXX" method="get" target="_blank" style="max-width:400px">
  <input type="text" name="text" placeholder="Hola, quiero info sobre la ficha destacada" style="width:100%;padding:10px;margin-bottom:10px;border:1px solid #ddd;border-radius:4px" />
  <button type="submit" style="width:100%;padding:12px;background:#25D366;color:white;border:none;border-radius:6px;font-size:16px;cursor:pointer">Enviar por WhatsApp</button>
</form>
```

## Checklist de implementacion
- [ ] Agregar CPT a functions.php (tema hijo si existe)
- [ ] Crear taxonomy categoria_ficha
- [ ] Agregar campos personalizados: precio, whatsapp, telefono, horario, direccion
- [ ] Probar shortcode [fichas_destacadas]
- [ ] Crear pagina "Fichas Destacadas" con el shortcode
- [ ] Probar en mobile
"@
        }
        "agente-proyecto-infomoteles" {
            $build = @"
# Build PM Infomoteles
## Sesion: $fecha

## Reporte semanal
| Fichas activas | Nuevas esta semana | Canceladas | Ingreso del mes |
|---|---|---|---|
| 0 | 0 | 0 | $0 |

## Proximos pasos
1. Coordinar con Tecnico implementacion de CPT (ficha_destacada)
2. Preparar lista de 50 moteles en Santiago para prospeccion inicial
3. Revisar paginas existentes y planificar nuevas ciudades
4. Reportar avance al Director el viernes

## Notas
- Sitio con trafico estable (~1.998 clics/90d)
- Sin ingresos actualmente
- Primer hito: 10 fichas activas = $299.900/mes (basicas) o $599.900/mes (premium)
"@
        }
        "agente-proyecto-visitandopuntaarenas" {
            $build = @"
# Build PM Punta Arenas
## Sesion: $fecha

## Reporte semanal
| Visitas/mes | Articulos vendidos | Fichas activas | Ingreso |
|---|---|---|---|
| ~1.836/90d | 0 | 0 | $0 |

## Lista de prospectos prioritarios
1. [Nombre] - Tours pinguineras - Tiene web - Alto potencial
2. [Nombre] - Hotel en centro - Sin web - Medio potencial
3. [Nombre] - Restaurant tipico - Solo redes - Medio potencial

## Acciones de la semana
1. Investigar 10 operadores turisticos en Punta Arenas
2. Preparar pagina de "Publicita aqui"
3. Reportar al Director con lista de contactos
"@
        }
        "calendario-editorial" {
            $build = @"
# Build Calendario Editorial - visitandopuntaarenas.cl
## Sesion: $fecha

## Cobertura detectada
- Hubs ya cubiertos de forma parcial: `/tours/`, `/como-ir/` y `/guia/`
- Piezas nuevas recomendadas: landings de Torres del Paine, Puerto Natales y Perito Moreno
- Pieza a actualizar: guia base de Punta Arenas

## Cola priorizada para WordPress
1. `/tours/torres-del-paine-desde-punta-arenas/` - landing transaccional
2. `/como-ir/punta-arenas-a-puerto-natales/` - guia comercial
3. `/guia/que-hacer-en-punta-arenas/` - pillar informacional
4. `/tours/punta-arenas-a-puerto-natales/` - landing transaccional
5. `/como-ir/punta-arenas-a-puerto-williams/` - guia monetizable
6. `/tours/perito-moreno-desde-punta-arenas/` - landing transaccional

## Checklist de publicacion
- Slug limpio y consistente
- H1 alineado con keyword principal
- Meta description lista
- Enlaces internos a clusters vecinos
- CTA y monetizacion definidos
- Marcar si la pieza crea contenido nuevo o actualiza cobertura parcial
"@
        }
        "agente-proyecto-avesnativaschilenas" {
            $build = @"
# Build PM Aves - avesnativaschilenas.cl
## Sesion: $fecha

## Correccion rapida de CTR
### Titulos actuales (malos)
- "Aves de Chile - Blog informativo"
- "Conoce las aves nativas"
- "Fotos de aves chilenas"

### Titulos propuestos (con click)
- "Guia de Aves Nativas de Chile: Especies, Fotos y Donde Verlas"
- "Las 10 Aves Mas Fascinantes de la Patagonia Chilena"
- "Avistamiento de Aves en Chile: Guia Completa para Birdwatchers"

### Meta descriptions
- Anadir a cada pagina: keyword + beneficio + llamado a accion
- Ejemplo: "Descubre las especies de aves nativas mas sorprendentes de Chile. Guia con fotos, habitats y mejores lugares para avistamiento."

## Plan de monetizacion
| Opcion | Ingreso potencial | Esfuerzo | Plazo |
|---|---|---|---|
| Afiliados (binoculares, guias, camaras) | $15.000-$30.000/mes | Bajo | 7 dias |
| Donaciones/Patreon | $10.000-$20.000/mes | Medio | 14 dias |
| Articulos patrocinados (turismo) | $50.000-$80.000 c/u | Medio | 14 dias |
| Fichas de alojamiento rural | $29.990/mes c/u | Alto | 30 dias |
"@
        }
        "agente-proyecto-turismoencajondelmaipo" {
            $build = @"
# Build PM Cajon del Maipo - turismoencajondelmaipo.cl
## Sesion: $fecha

## Lista de prospectos para fichas turisticas
| Negocio | Tipo | Prioridad |
|---|---|---|
| Cabanas El Canelo | Alojamiento | Alta |
| Ski Cajon del Maipo | Centro de ski | Alta |
| Termas de Colina | Termas | Alta |
| Cabanas Los Pinos | Alojamiento | Media |
| San Jose de Maipo Turismo | Operador | Alta |

## Paginas a crear con intencion comercial
1. "Donde alojarse en Cajon del Maipo" - fichas + ads
2. "Mejores termas cerca de Santiago" - turismo + afiliados
3. "Guia de senderos Cajon del Maipo" - contenido + mapas
4. "Cabanas con piscina en Cajon del Maipo" - fichas premium

## Plan de contenido semanal
Semana 1: 3 articulos con keywords de alta intencion
Semana 2: 2 articulos + pagina de fichas turisticas
Semana 3: 2 articulos + inicio de prospeccion a negocios locales
"@
        }
        "agente-proyecto-aventurasenelagua" {
            $build = @"
# Build PM Aventuras - aventurasenelagua.cl
## Sesion: $fecha

## Escuelas de deportes acuaticos en Chile (prospectos)
| Escuela | Deporte | Ciudad |
|---|---|---|
| Escuela de Surf Pichilemu | Surf | Pichilemu |
| Kite Chile | Kitesurf | Matanzas |
| Rapa Nui Surf Club | Surf | Isla de Pascua |
| Escuela de Buceo Chile | Buceo | La Serena |
| Kayak Chile | Kayak | Puerto Varas |

## Enlaces de afiliado a incluir
| Producto | Programa | Comision |
|---|---|---|
| Trajes de neopreno | MercadoLibre/Sodimac | 3-8% |
| Tablas de surf | Decathlon Chile | 2-5% |
| Equipo de buceo | Amazon afiliados | 3-10% |

## Paginas a crear
1. "Mejores playas para surfear en Chile" - guia + afiliados
2. "Equipo basico para hacer kayak" - review + afiliados
3. "Escuelas de buceo en Chile" - fichas + directorio
"@
        }
        "agente-proyecto-seo-local" {
            $build = @"
# Build PM SEO Local
## Sesion: $fecha

## Pipeline semanal
| Prospectos | Diagnosticos | Reuniones | Clientes | MRR |
|---|---|---|---|---|
| 0 | 0 | 0 | 0 | $0 |

## Trabajo inmediato del servicio
1. Crear plantilla de diagnostico SEO express (PDF + Loom).
2. Definir entregables del plan mensual:
   - Mes 1: Diagnostico + 5 correcciones rapidas + reporte
   - Mes 2+: Optimizacion continua + contenido + reporte mensual
3. Preparar pagina de servicio con precios.

## Nicho inicial: Moteles en Santiago
Usar infomoteles.cl como prueba social
"@
        }
        "agente-asesor-estrategico-financiero" {
            $build = @"
# Build Asesor Financiero - $($Tarea.titulo)
## Sesion: $fecha

## Proyeccion financiera a 45 dias

### Escenario optimista (5 fichas premium + 2 articulos + 1 SEO)
| Concepto | Ingreso | Retencion (15.25%) | Neto |
|---|---|---|---|
| 5 fichas premium ($59.990) | $299.950 | -$45.742 | $254.208 |
| 2 articulos patrocinados ($120.000) | $240.000 | -$36.600 | $203.400 |
| 1 SEO local ($350.000) | $350.000 | -$53.375 | $296.625 |
| **TOTAL MENSUAL** | **$889.950** | **-$135.717** | **$754.233** |

### Escenario moderado (10 fichas basicas)
| Concepto | Ingreso | Neto |
|---|---|---|
| 10 fichas basicas ($29.990) | $299.900 | $254.208 |
| 2 SEO local ($250.000) | $500.000 | $423.750 |
| **TOTAL MENSUAL** | **$799.900** | **$677.958** |

### Recomendaciones financieras
1. Priorizar fichas premium sobre basicas (mejor margen, mismo esfuerzo)
2. No ofrecer descuentos anuales hasta tener 10+ clientes
3. Separar 15.25% de cada ingreso para pago de impuestos
4. Reinvertir primeros $300.000 en herramientas SEO y Canva Pro
5. Formalizar como empresa solo cuando MRR > $500.000 constante por 3 meses
"@
        }
        "agente-pauta-digital" {
            $build = @"
# Build Pauta Digital - $($Tarea.titulo)
## Sesion: $fecha

## EN ESPERA - Sin oferta validada aun

## Trabajo previo
### Nicho: Moteles (infomoteles.cl)
| Elemento | Definido |
|---|---|
| Plataforma | Facebook + Instagram |
| Objetivo | Leads (formulario nativo) |
| Segmentacion | Radio Santiago + Intereses: moteles, alojamientos |
| Presupuesto prueba | $10.000/dia x 5 dias |
| Creatividad | Imagen de ficha destacada |
| Landing | Formulario de Meta |

### Nicho: Turismo Punta Arenas
| Elemento | Definido |
|---|---|
| Plataforma | Instagram + Facebook |
| Objetivo | Mensajes (DM) |
| Segmentacion | Radio Punta Arenas + Intereses: viajes, turismo |
| Presupuesto prueba | $8.000/dia x 5 dias |

### Condiciones para activar
- [ ] Oferta de fichas destacadas validada (minimo 1 cliente pagando)
- [ ] Landing page de fichas publicada y funcionando
- [ ] Presupuesto separado ($30.000-$50.000)
- [ ] Sistema de tracking instalado (pixel de Meta)
"@
        }
        "agente-especialista-enlazado-interno" {
            $build = @"
# Build Enlazado Interno - $($Tarea.titulo)
## Sesion: $fecha

## Auditoria express de enlazado para $sitio

### Paginas con mayor autoridad entrante
| Pagina | Autoridad relativa | Enlaces salientes | Potencial de redistribucion |
|---|---|---|---|
| Homepage | Alta | Contar | Alto |
| Paginas de ciudades | Media | Contar | Alto |
| Categorias | Media | Contar | Medio |

### Paginas huerfanas detectadas
- [Listar paginas sin enlaces internos entrantes]

### Quick wins (30 min o menos)
1. Agregar enlace desde homepage a [pagina comercial]
2. Mejorar anchor text de [enlace] a "texto descriptivo"
3. Conectar [pagina A] con [pagina B] del mismo cluster

### Acciones de alto impacto (1-2 horas)
1. Reestructurar navegacion de categoria [X]
2. Crear pagina pilar para cluster [Y]
3. Implementar breadcrumbs en todo el sitio

## Recomendaciones de anchor text
| Desde | Hacia | Anchor actual | Anchor sugerido |
|---|---|---|---|
| Homepage | Pagina comercial | "[leer mas]" | "[keyword objetivo]" |
| Categoria | Subcategoria | "[ver mas]" | "[keyword descriptiva]" |
"@
        }
        "agente-lanzamientos-alex-izquierdo" {
            $build = @"
# Build Lanzamientos - $($Tarea.titulo)
## Sesion: $fecha

## Plan de lanzamiento: Servicio SEO Local

### Fase 1: Pre-lanzamiento (3-5 dias)
- Lead magnet: "Diagnostico SEO gratuito en 24 horas"
- Captura: Formulario simple (nombre + web + WhatsApp)
- Entrega: PDF de 3 paginas con oportunidades detectadas
- Seguimiento: Video Loom de 5 min explicando los hallazgos

### Fase 2: Venta directa (5 dias)
- Contactar a cada lead con diagnostico personalizado
- Ofrecer llamada de 10 min para explicar resultados
- Propuesta: Plan SEO local $250.000-$500.000/mes

### Fase 3: Cierre (3 dias)
- "Ultimos 2 cupos con precio de lanzamiento"
- Caso de estudio real (primer cliente)
- Garantia: "Si en 30 dias no ves resultados, cancelamos"

### Materiales necesarios
- [ ] Plantilla de diagnostico PDF
- [ ] Video Loom de ejemplo
- [ ] Pagina de captura de leads
- [ ] Secuencia de 3 mensajes de seguimiento
"@
        }
        default {
            if ($IdAgente -eq "agente-director-agencia-digital" -and $Tarea.id -eq "T-008") {
                $build = @"
# Brief operativo - Caja infomoteles
## Sesion: $fecha

## Lista corta priorizada de 20 moteles
| # | Motel | Ciudad | Motivo |
|---|---|---|---|
| 1 | Motel La Cascada | Concepcion | Alto ajuste a la demanda visible |
| 2 | Motel Caracol | Concepcion | Marca recordable y activa en busqueda |
| 3 | Motel Capricho | Concepcion | Buen fit para ficha premium |
| 4 | Motel Bella Luna | Concepcion | Potencial visual y comercial |
| 5 | Motel Vitara | Concepcion | Prospecto con nombre directo |
| 6 | Motel Deja-vu | Copiapo | Prospecto reconocido en SERP |
| 7 | Motel Los Sauces | Copiapo | Mucha impresion, oportunidad de mejora |
| 8 | Motel Diamante | Curico | Ajuste a ficha premium |
| 9 | 725 Motel | Curico | Marca corta, facil de recordar |
| 10 | Motel y Cabanas Rauquen | Curico | Alojamiento mixto con demanda |
| 11 | Motel Vertigo | Providencia | Zona de alta demanda y mayor ticket |
| 12 | Motel Amor Amor | Providencia | Naming comercial directo |
| 13 | Motel Marin 014 | Providencia | Marca con busqueda navegacional |
| 14 | Motel Holley | Providencia | Prospecto de alta visibilidad |
| 15 | Motel Cielo Azul | Providencia | Nombre apto para oferta destacada |
| 16 | Motel Ah Express | Providencia | Fit transaccional claro |
| 17 | Motel Gala | Providencia | Prospecto premium |
| 18 | Tropical Motel | Estacion Central | Segmento urbano con volumen |
| 19 | Motel Kaoma | Santiago | Nombre buscable y directo |
| 20 | Motel Los Acacios | Santiago | Prospecto para cierre rapido |

## CRM minimo
| Campo | Uso |
|---|---|
| Motel | Nombre del prospecto |
| Ciudad | Segmento y prioridad |
| Contacto | WhatsApp / telefono / email |
| Estado | Nuevo, contactado, responde, diagnostico, propuesta, cerrado |
| Ultimo contacto | Fecha y canal |
| Siguiente paso | Proxima accion concreta |
| Monto | Ficha, setup o ambos |

## Tracking minimo
- GA4: `click_whatsapp`, `click_llamar`, `submit_para_moteles`, `click_cta_flotante`
- Search Console: validar indexacion de `/para-moteles/`
- CTA flotante: visible en mobile y desktop

## Validacion de primera venta
1. Mensaje enviado y registrado en CRM
2. Oferta /para-moteles/ compartida
3. Respuesta positiva o llamada agendada
4. Evidencia de cobro o compromiso formal
5. CRM marcado como `cerrado`

## Criterio de cierre
- CRM vivo
- oferta publicada
- tracking verificado
- 20 moteles cargados
- primera venta validada
"@
            } else {
        $build = @"
# Build $nombre - Tarea automatica
## Sesion: $fecha

## URL
$($Tarea.url)

## Estado
En trabajo.

## Evidencia
$($Tarea.evidencia)

## Accion
$($Tarea.accion)

## Criterio de cierre
$($Tarea.cierre)

## Entrega minima
1. Resultado utilizable hoy.
2. Evidencia o decision concreta.
3. Siguiente paso accionable.
"@
            }
        }
    }
    if ($build -ne "") { Write-File $archivo $build; Write-Host "    [BUILD] $nombre - entregable listo" -ForegroundColor Magenta }
}
# ============================================================
# ORQUESTADOR - Coordina Plan + Build + Feedback
# ============================================================
function Invoke-Agente {
    param([string]$IdAgente, $Tarea, [string]$RutaOutput)
    Invoke-Plan -IdAgente $IdAgente -Tarea $Tarea -RutaOutput $RutaOutput
    Invoke-Build -IdAgente $IdAgente -Tarea $Tarea -RutaOutput $RutaOutput
}

function Invoke-FeedbackAgentes {
    param([array]$AgentesConvocados, [string]$RutaOutputRaiz, [hashtable]$TareasPorAgente = @{})
    foreach ($id_agente in $AgentesConvocados) {
        $ruta = Join-Path (Get-Ruta $id_agente) "output"
        $fb_path = Join-Path $ruta "feedback-recibido.md"
        $ya_tiene_fb = Test-Path $fb_path
        $otros = @()
        foreach ($o in $AgentesConvocados) { if ($o -ne $id_agente) { $otros += $o } }
        if ($otros.Count -eq 0) { $otros += $id_agente }
        $lineas = ""
        foreach ($o in $otros) {
            if ($o -eq $id_agente) { continue }
            $lineas += "  - " + $AGENTES[$o].Nombre + " (" + $o + ")`n"
        }
        if ($lineas -eq "") { $lineas = "  - Ninguno`n" }
        $nombre = $AGENTES[$id_agente].Nombre
        $fecha = Get-Date -Format "yyyy-MM-dd HH:mm"
        $fb = @"
# Feedback recibido - $nombre
## Fecha: $fecha

## Agentes con los que te coordinas en esta tarea:
$lineas
## Accion
1. Revisa sus outputs en output/plan/ y output/build/
2. Si algo contradice tu especialidad, genera feedback en output/feedback-para-[id].md
3. Si todo esta alineado, no necesitas hacer nada
"@
        if (-not $ya_tiene_fb) {
            Write-File $fb_path $fb
            Write-Host "    [FB]   $nombre - feedback de colegas" -ForegroundColor Yellow
        }

        $tarea_actual = $null
        if ($TareasPorAgente -ne $null -and $TareasPorAgente.ContainsKey($id_agente)) { $tarea_actual = $TareasPorAgente[$id_agente] }
        if ($tarea_actual -eq $null) { $tarea_actual = New-TareaFallback -IdAgente $id_agente }
        $fb_director = Join-Path $ruta "feedback-para-agente-director-agencia-digital-$($tarea_actual.id).md"
        Write-File $fb_director (New-FeedbackParaDirector -IdAgente $id_agente -Tarea $tarea_actual -AgentesConvocados $AgentesConvocados)
        Write-Host "    [FB]   $nombre - feedback al director" -ForegroundColor Yellow
    }
}

function Mostrar-Banner {
    $total_equipo = $AGENTES.Keys.Count - 1
    Write-Host "================================================" -ForegroundColor Cyan
    Write-Host "  LANZADOR DEL EQUIPO DE AGENTES - v2.1" -ForegroundColor Cyan
    Write-Host "  Plan + Build nativos para $total_equipo agentes + director" -ForegroundColor Cyan
    Write-Host "  Sesion: $Sesion" -ForegroundColor Cyan
    Write-Host "================================================" -ForegroundColor Cyan
    Write-Host ""
}

function Mostrar-Ayuda {
    Write-Host ""
    Write-Host "USO: .\lanzar-equipo.ps1 [opciones]" -ForegroundColor Yellow
    Write-Host "  (sin opciones)  Plan + Build + Feedback por tareas activas"
    Write-Host "  -Modo revision  Todos los 16 agentes trabajan simultaneamente"
    Write-Host "  -SoloEstado     Dashboard rapido"
    Write-Host "  -TareaID T-025  Solo una tarea por ID"
    Write-Host "  -TareaID T-033  Ejecuta las subtareas macro de T-033"
    Write-Host "  -TareaID T-036  Ejecuta las subtareas macro de T-036"
    Write-Host "  -TareaID T-037  Ejecuta las subtareas macro de T-037"
    Write-Host "  -Ayuda          Esta ayuda"
    Write-Host ""
    exit
}

function Mostrar-Ayuda {
    Write-Host ""
    Write-Host "USO: .\lanzar-equipo.ps1 [opciones]" -ForegroundColor Yellow
    Write-Host "  (sin opciones)  Plan + Build + Feedback por tareas activas"
    Write-Host "  -Modo revision  Todos los agentes trabajan simultaneamente y dejan feedback para el Director" -ForegroundColor Yellow
    Write-Host "  -SoloEstado     Dashboard rapido" -ForegroundColor Yellow
    Write-Host "  -TareaID T-025  Solo una tarea por ID" -ForegroundColor Yellow
    Write-Host "  -TareaID T-033  Ejecuta las subtareas macro de T-033" -ForegroundColor Yellow
    Write-Host "  -TareaID T-036  Ejecuta las subtareas macro de T-036" -ForegroundColor Yellow
    Write-Host "  -TareaID T-037  Ejecuta las subtareas macro de T-037" -ForegroundColor Yellow
    Write-Host "  -Ayuda          Esta ayuda" -ForegroundColor Yellow
    Write-Host ""
    exit
}

function Mostrar-Dashboard {
    Write-Host "`n=== DASHBOARD DEL EQUIPO ===" -ForegroundColor Cyan
    $total = $AGENTES.Keys.Count
    $con_plan = 0; $con_build = 0; $con_memoria = 0; $con_feedback = 0; $con_feedback_director = 0
    foreach ($id in $AGENTES.Keys) {
        $r = Get-Ruta $id
        $plan_dir = Join-Path (Join-Path $r "output") "plan"
        $build_dir = Join-Path (Join-Path $r "output") "build"
        $mem = Join-Path (Join-Path $r "memoria") "$Sesion.md"
        $fb = Join-Path (Join-Path $r "output") "feedback-recibido.md"
        $fb_director = @(Get-ChildItem (Join-Path $r "output") -Filter "feedback-para-agente-director-agencia-digital-*.md" -ErrorAction SilentlyContinue)
        if ((Test-Path $plan_dir) -and @(Get-ChildItem $plan_dir -Filter "*.md").Count -gt 0) { $con_plan++ }
        if ((Test-Path $build_dir) -and @(Get-ChildItem $build_dir -Filter "*.md").Count -gt 0) { $con_build++ }
        if (Test-Path $mem) { $con_memoria++ }
        if (Test-Path $fb) { $con_feedback++ }
        if ($fb_director.Count -gt 0) { $con_feedback_director++ }
    }
    Write-Host "  Agentes: $total"
    Write-Host "  Con plan/: $con_plan"
    Write-Host "  Con build/: $con_build"
    Write-Host "  Con feedback: $con_feedback"
    Write-Host "  Con feedback al director: $con_feedback_director"
    Write-Host "  Con memoria hoy: $con_memoria"
    $archivo_tareas = if (Test-Path $TAREAS_OFICIAL) { $TAREAS_OFICIAL } else { $PENDIENTES }
    if (Test-Path $archivo_tareas) {
        $dr = Get-Content $archivo_tareas -Raw -Encoding UTF8 | ConvertFrom-Json
        $pc = 0; $ec = 0; $cc = 0
        foreach ($t in $dr.tareas) {
            if ($t.estado -eq "pendiente") { $pc++ }
            elseif ($t.estado -eq "en_curso") { $ec++ }
            elseif ($t.estado -eq "completada") { $cc++ }
        }
        Write-Host "  Tareas: $pc pendientes, $ec en curso, $cc completadas"
    }
    Write-Host "`n--- Agentes ---" -ForegroundColor Cyan
    foreach ($id in $AGENTES.Keys) {
        $r = Get-Ruta $id
        $plan_dir = Join-Path (Join-Path $r "output") "plan"
        $build_dir = Join-Path (Join-Path $r "output") "build"
        $mem = Join-Path (Join-Path $r "memoria") "$Sesion.md"
        $ic = " "; $cl = "Gray"
        if (Test-Path $mem) { $ic = "@"; $cl = "Green" }
        elseif (Test-Path $build_dir) { $ic = "B"; $cl = "Magenta" }
        elseif (Test-Path $plan_dir) { $ic = "P"; $cl = "Cyan" }
        Write-Host "  $ic $($AGENTES[$id].Nombre)" -ForegroundColor $cl
    }
    Write-Host ""
}

$REVISION_MAP = @{}
$REVISION_MAP["agente-consultor-seo-monetizacion"] = @{tipo="auditoria_seo"; titulo="Auditoria SEO de infomoteles.cl"; sitio="infomoteles.cl"}
$REVISION_MAP["agente-copywriter-conversion"] = @{tipo="mejorar_landing"; titulo="Escribir copy para fichas y landing"; sitio="infomoteles.cl"}
$REVISION_MAP["agente-analista-web-cro"] = @{tipo="mejorar_landing"; titulo="Optimizar conversion de pagina de fichas"; sitio="infomoteles.cl"}
$REVISION_MAP["agente-pm-infomoteles"] = @{tipo="reportar_estado"; titulo="Reporte de estado infomoteles.cl"; sitio="infomoteles.cl"}
$REVISION_MAP["agente-especialista-enlazado-interno"] = @{tipo="auditar_enlazado_interno"; titulo="Auditar enlazado interno de infomoteles.cl"; sitio="infomoteles.cl"}
$REVISION_MAP["agente-implementador-wordpress"] = @{tipo="implementar_cambio"; titulo="Ejecutar cambios pendientes en WordPress"; sitio="infomoteles.cl"}
$REVISION_MAP["agente-vendedor-ejecutor"] = @{tipo="ventas_b2b"; titulo="Ejecutar ronda de ventas a moteles"; sitio="infomoteles.cl"}
$REVISION_MAP["agente-analista-metricas"] = @{tipo="medir_resultados"; titulo="Medir resultados de ultimos cambios"; sitio="infomoteles.cl"}

# ============================================================
# MAIN
# ============================================================
if ($Ayuda) { Mostrar-Ayuda }
Mostrar-Banner
if ($SoloEstado) { Mostrar-Dashboard; exit }

if ($Modo -eq "revision") {
    Write-Host "`n[REVISION GENERAL] Activando todos los agentes..." -ForegroundColor Green
    $rev_count = 0; $tareas_revision = @(); $tareas_revision_por_agente = @{}
    foreach ($id_a in $AGENTES.Keys) {
        if ($id_a -eq "agente-director-agencia-digital") { continue }
        $rev_count++
        $tarea_rev = New-TareaRevision -IdAgente $id_a
        $tareas_revision += @{id_a=$id_a; tarea=$tarea_rev}
        $tareas_revision_por_agente[$id_a] = $tarea_rev
    }
    foreach ($tr in $tareas_revision) {
        $nombre = $AGENTES[$tr.id_a].Nombre
        Write-Host "  [$nombre] $($tr.tarea.titulo)" -ForegroundColor Gray
        $ruta_out = Join-Path (Get-Ruta $tr.id_a) "output"
        if (-not (Test-Path $ruta_out)) { New-Item -ItemType Directory -Path $ruta_out -Force | Out-Null }
        Invoke-Plan -IdAgente $tr.id_a -Tarea $tr.tarea -RutaOutput $ruta_out
        Invoke-Build -IdAgente $tr.id_a -Tarea $tr.tarea -RutaOutput $ruta_out
    }
    $convocados_revision = $tareas_revision | ForEach-Object { $_.id_a }
    Write-Host "[FASE 4] Feedback general..." -ForegroundColor Green
    Invoke-FeedbackAgentes -AgentesConvocados $convocados_revision -RutaOutputRaiz "" -TareasPorAgente $tareas_revision_por_agente
    Write-Host "`n[REVISION COMPLETADA]" -ForegroundColor Green
    Mostrar-Dashboard
    exit
}

Write-Host "`n[FASE 1] Cargando tareas..." -ForegroundColor Green
$pendientes_data = New-Object System.Collections.ArrayList
$agentes_con_tarea = @{}

$fuentes_tareas = @()
if (Test-Path $TAREAS_OFICIAL) { $fuentes_tareas += $TAREAS_OFICIAL }
if (Test-Path $PENDIENTES) { $fuentes_tareas += $PENDIENTES }
if (Test-Path $TAREAS_DIRECTOR_DIR) {
    $fuentes_tareas += @(Get-ChildItem $TAREAS_DIRECTOR_DIR -Filter "*-subtareas.json" -ErrorAction SilentlyContinue | Sort-Object FullName | ForEach-Object { $_.FullName })
}

$origen_por_tarea = @{}
foreach ($archivo in $fuentes_tareas) {
    $raw = Get-Content $archivo -Raw -Encoding UTF8 | ConvertFrom-Json
    $lista = $null
    if ($raw.PSObject.Properties.Name -contains "tareas") { $lista = $raw.tareas }
    elseif ($raw.PSObject.Properties.Name -contains "subtareas") { $lista = $raw.subtareas }
    if ($lista -eq $null) { continue }
    foreach ($t in $lista) {
        Add-TareaUnica -Tarea $t -Origen $archivo -Lista $pendientes_data -OrigenPorId $origen_por_tarea
        if ($t.estado -eq "pendiente" -or $t.estado -eq "en_curso") {
            if ($t.agente) { $agentes_con_tarea[$t.agente] = $true }
            if ($t.agente_principal) { $agentes_con_tarea[$t.agente_principal] = $true }
            if ($t.apoyo) { foreach ($id in @($t.apoyo)) { if ($id) { $agentes_con_tarea[$id] = $true } } }
        }
    }
}

if ($TareaID -match '^T-036') {
    foreach ($st in Get-SubtareasDirector -IdMacro "T-036") {
        $pendientes_data += $st
        if ($st.agente) { $agentes_con_tarea[$st.agente] = $true }
        if ($st.agente_principal) { $agentes_con_tarea[$st.agente_principal] = $true }
    }
}

if ($TareaID -match '^T-033') {
    foreach ($st in Get-SubtareasDirector -IdMacro "T-033") {
        $pendientes_data += $st
        if ($st.agente) { $agentes_con_tarea[$st.agente] = $true }
        if ($st.agente_principal) { $agentes_con_tarea[$st.agente_principal] = $true }
        if ($st.apoyo) { foreach ($id in @($st.apoyo)) { $agentes_con_tarea[$id] = $true } }
    }
}

if ($TareaID -match '^T-035') {
    foreach ($st in Get-SubtareasDirector -IdMacro "T-035") {
        $pendientes_data += $st
        if ($st.agente) { $agentes_con_tarea[$st.agente] = $true }
        if ($st.agente_principal) { $agentes_con_tarea[$st.agente_principal] = $true }
        if ($st.apoyo) { foreach ($id in @($st.apoyo)) { $agentes_con_tarea[$id] = $true } }
    }
}

if ($TareaID -match '^T-037') {
    foreach ($st in Get-SubtareasDirector -IdMacro "T-037") {
        $pendientes_data += $st
        if ($st.agente) { $agentes_con_tarea[$st.agente] = $true }
        if ($st.agente_principal) { $agentes_con_tarea[$st.agente_principal] = $true }
    }
}

if ($TareaID -match '^T-038') {
    foreach ($st in Get-SubtareasDirector -IdMacro "T-038") {
        $pendientes_data += $st
        if ($st.agente) { $agentes_con_tarea[$st.agente] = $true }
        if ($st.agente_principal) { $agentes_con_tarea[$st.agente_principal] = $true }
        if ($st.apoyo) { foreach ($id in @($st.apoyo)) { $agentes_con_tarea[$id] = $true } }
    }
}

if ($TareaID -match '^T-039') {
    foreach ($st in Get-SubtareasDirector -IdMacro "T-039") {
        $pendientes_data += $st
        if ($st.agente) { $agentes_con_tarea[$st.agente] = $true }
        if ($st.agente_principal) { $agentes_con_tarea[$st.agente_principal] = $true }
        if ($st.apoyo) { foreach ($id in @($st.apoyo)) { $agentes_con_tarea[$id] = $true } }
    }
}

foreach ($id_agente in $AGENTES.Keys) {
    if (-not $agentes_con_tarea.ContainsKey($id_agente)) {
        $pendientes_data += New-TareaFallback -IdAgente $id_agente
        Write-Host "  [AUTO] Asignada tarea nueva a $($AGENTES[$id_agente].Nombre)" -ForegroundColor Yellow
    }
}

if ($pendientes_data.Count -eq 0) {
    Write-Host "  No hay tareas activas. Generando tareas automaticas para todo el equipo" -ForegroundColor Yellow
    foreach ($id_agente in $AGENTES.Keys) {
        $pendientes_data += New-TareaFallback -IdAgente $id_agente
    }
}

if ($TareaID -eq "T-008" -and -not ($pendientes_data | Where-Object { $_.id -eq "T-008" })) {
    $pendientes_data += New-TareaCajaInfomoteles
    Write-Host "  [AUTO] Cargando T-008 operativo para infomoteles" -ForegroundColor Yellow
}

foreach ($tarea in $pendientes_data) {
    if (-not (Test-TareaSolicitada -Tarea $tarea -Filtro $TareaID)) { continue }
    Write-Host "`n[TAREA] $($tarea.id) - $($tarea.titulo)" -ForegroundColor Cyan
    $tarea.estado = "en_curso"
    $convocados = Get-ConvocadosPorTarea -Tarea $tarea
    if ($convocados.Count -eq 0) { Write-Host "  [!] Sin agentes convocados" -ForegroundColor Red; continue }
    $feedback_por_agente = @{}
    foreach ($id_fb in $convocados) { $feedback_por_agente[$id_fb] = $tarea }

    Write-Host "  Agentes: $(($convocados | ForEach-Object { $AGENTES[$_].Nombre }) -join ', ')" -ForegroundColor Gray

    # FASE 2: PLAN
    Write-Host "`n[FASE 2] Plan - analizando..." -ForegroundColor Green
    foreach ($id_a in $convocados) {
        $ruta_agente = Get-Ruta $id_a
        $ruta_out = Join-Path $ruta_agente "output"
        if (-not (Test-Path $ruta_out)) { New-Item -ItemType Directory -Path $ruta_out -Force | Out-Null }
        Invoke-Plan -IdAgente $id_a -Tarea $tarea -RutaOutput $ruta_out
    }

    # FASE 3: BUILD
    Write-Host "[FASE 3] Build - construyendo..." -ForegroundColor Green
    foreach ($id_a in $convocados) {
        $ruta_agente = Get-Ruta $id_a
        $ruta_out = Join-Path $ruta_agente "output"
        Invoke-Build -IdAgente $id_a -Tarea $tarea -RutaOutput $ruta_out
    }

    # FASE 4: FEEDBACK
    Write-Host "[FASE 4] Feedback - coordinando..." -ForegroundColor Green
    Invoke-FeedbackAgentes -AgentesConvocados $convocados -RutaOutputRaiz "" -TareasPorAgente $feedback_por_agente

    # Registrar en memoria del Director
    $mem_dir = Join-Path (Get-Ruta "agente-director-agencia-digital") "memoria"
    if (-not (Test-Path $mem_dir)) { New-Item -ItemType Directory -Path $mem_dir -Force | Out-Null }
    $mem_file = Join-Path $mem_dir "$Sesion.md"
    $agentes_nombres = ($convocados | ForEach-Object { $AGENTES[$_].Nombre }) -join ", "
    $entrada = @"

## $(Get-Date -Format "HH:mm") - $($tarea.id)
**Tarea**: $($tarea.titulo) | **Tipo**: $($tarea.tipo)
**Agentes**: $agentes_nombres
**Estado**: en_curso - Plan + Build generados
"@
    Add-Content -LiteralPath $mem_file -Value $entrada -Encoding UTF8

    # Actualizar tareas.json o pendientes.json segun el archivo activo
    if ($origen_por_tarea.ContainsKey($tarea.id)) {
        Set-TareaEstadoEnArchivo -Archivo $origen_por_tarea[$tarea.id] -IdTarea $tarea.id -Estado "en_curso"
    }
}

Write-Host "`n================================================" -ForegroundColor Cyan
Write-Host "  SESION COMPLETADA" -ForegroundColor Green
Write-Host "  Revisa output/plan/ y output/build/ de cada agente" -ForegroundColor Cyan
Write-Host "================================================" -ForegroundColor Cyan
Mostrar-Dashboard
