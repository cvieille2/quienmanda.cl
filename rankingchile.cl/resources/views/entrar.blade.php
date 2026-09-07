@extends('layouts.app')

@section('title', 'Entrar — onboarding self-service | Quién Manda')

@push('meta')
    <meta name="description" content="Onboarding self-service de Quién Manda: pega tu perfil, define el destino y publica gratis o sube al puesto elegido." />
@endpush

@section('body')
<div class="min-h-screen bg-[#FDFDFC]" x-data="entrarWizard(@js([
    'wizardSteps' => $wizardSteps,
    'positionPricing' => $positionPricing,
    'defaultDraft' => $defaultDraft,
    'legalMicrocopy' => $legalMicrocopy,
]))">

    @php $activePage = 'entrar'; @endphp
    @include('partials.site-header')

    <main class="mx-auto max-w-4xl px-4 pb-28 pt-6 sm:px-6">
        <section class="overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-[#1B1B18] via-[#23150f] to-[#1B1B18] text-white shadow-[0_20px_60px_rgba(27,27,24,0.18)]">
            <div class="grid gap-6 p-6 md:grid-cols-[1.3fr_0.9fr] md:p-8">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-[#F8B803]/30 bg-white/5 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-[#F8B803]">
                        <span class="text-sm">👑</span>
                        Entra en 60 segundos · sin cuenta
                    </div>
                    <h1 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">Pega tu perfil. El resto lo completamos contigo.</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/70 sm:text-base">
                        Pega una URL o handle, revisa tu tarjeta y decide si quieres <span class="font-semibold text-white">PUBLICAR GRATIS</span> o <span class="font-semibold text-white">SUBIR AL #</span> con una inversión visible dentro del ranking.
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2 text-xs">
                        <span class="rounded-full bg-white/10 px-3 py-1 font-medium text-white/80">Mobile-first</span>
                        <span class="rounded-full bg-white/10 px-3 py-1 font-medium text-white/80">Máximo 4 pasos</span>
                        <span class="rounded-full bg-white/10 px-3 py-1 font-medium text-white/80">Sin registro bloqueante</span>
                        <span class="rounded-full bg-white/10 px-3 py-1 font-medium text-white/80">Visibilidad competitiva</span>
                    </div>
                </div>

                <div class="rounded-3xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-white/50">Estado actual</p>
                    <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-2xl bg-black/20 p-3">
                            <p class="text-white/50 text-xs">Paso activo</p>
                            <p class="mt-1 text-2xl font-black text-[#F8B803]" x-text="stepDisplay"></p>
                        </div>
                        <div class="rounded-2xl bg-black/20 p-3">
                            <p class="text-white/50 text-xs">Modo</p>
                            <p class="mt-1 font-bold" x-text="freePublish ? 'PUBLICAR GRATIS' : ctaLabel"></p>
                        </div>
                    </div>
                    <div class="mt-3 rounded-2xl bg-black/20 p-3 text-sm text-white/75">
                        <p class="font-semibold" x-text="displayName"></p>
                        <p class="mt-1 break-all text-white/60" x-text="destinationUrl"></p>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-5 rounded-3xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6">
            @include('entrar.partials.stepper', ['steps' => $wizardSteps])

            <div class="mt-6 space-y-5">
                <section x-show="step === 1" x-cloak class="space-y-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-gray-400">Paso 1</p>
                        <h2 class="mt-1 text-2xl font-black tracking-tight">Paso 1/4 · Tu perfil y proyecto</h2>
                        <p class="mt-2 text-sm text-gray-600">Pega tu perfil y completa lo básico.</p>
                    </div>

                    <label class="block">
                        <span class="mb-2 block text-sm font-bold text-[#1B1B18]">Pega tu perfil</span>
                        <input
                            type="text"
                            x-model="source"
                            @input="syncFromSource()"
                            class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-base outline-none transition focus:border-[#F53003] focus:ring-4 focus:ring-[#F53003]/10"
                            placeholder="youtube.com/@tu · x · tiktok · tudominio.cl"
                        >
                    </label>

                    <div class="rounded-2xl bg-[#FFF8E1] px-4 py-3 text-sm text-[#5A4300]">
                        <span class="font-bold">Detectado:</span>
                        <span class="ml-1 font-semibold" x-text="sourceHint"></span>
                        <span class="mx-1 text-[#C58D00]">·</span>
                        <span class="font-semibold" x-text="publicHandle"></span>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button type="button" @click="go(2)" :disabled="!isSourceValid" class="inline-flex items-center justify-center rounded-2xl px-5 py-3 font-black text-white transition" :class="isSourceValid ? 'bg-[#F53003] hover:bg-[#c22a02]' : 'cursor-not-allowed bg-gray-300'" x-text="isSourceValid ? 'Siguiente paso' : 'Completa el perfil'"></button>
                        <p class="text-xs leading-5 text-gray-500 sm:max-w-md">Legal: usamos un origen visible y público. El contenido privado no entra al flujo.</p>
                    </div>
                </section>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="space-y-3">
                            <label class="block">
                                <span class="mb-2 block text-sm font-bold text-[#1B1B18]">Nombre del proyecto</span>
                                <input x-model="displayName" type="text" class="w-full rounded-2xl border border-gray-300 px-4 py-3 outline-none transition focus:border-[#F53003] focus:ring-4 focus:ring-[#F53003]/10" placeholder="Tu proyecto">
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-bold text-[#1B1B18]">Descripción</span>
                                <textarea x-model="summary" rows="4" class="w-full rounded-2xl border border-gray-300 px-4 py-3 outline-none transition focus:border-[#F53003] focus:ring-4 focus:ring-[#F53003]/10" placeholder="Cuenta en una frase qué hace tu proyecto"></textarea>
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-bold text-[#1B1B18]">Categoría (opcional)</span>
                                <select x-model="category" class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 outline-none transition focus:border-[#F53003] focus:ring-4 focus:ring-[#F53003]/10">
                                    <option value="">Sin categoría</option>
                                    @foreach ($projectCategories as $categoryOption)
                                        <option value="{{ $categoryOption->name }}">{{ $categoryOption->displayName() }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-bold text-[#1B1B18]">Región de Chile (opcional)</span>
                                <select x-model="regionId" class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 outline-none transition focus:border-[#F53003] focus:ring-4 focus:ring-[#F53003]/10">
                                    <option value="">Sin región</option>
                                    @foreach ($regions as $region)
                                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="block">
                                <span class="mb-2 block text-sm font-bold text-[#1B1B18]">Imagen/Avatar URL</span>
                                <input x-model="avatarUrl" type="url" class="w-full rounded-2xl border border-gray-300 px-4 py-3 outline-none transition focus:border-[#F53003] focus:ring-4 focus:ring-[#F53003]/10" placeholder="https://...">
                            </label>
                        </div>

                        <div class="rounded-3xl border border-gray-200 bg-[#1B1B18] p-4 text-white">
                            <div class="flex items-center justify-between text-xs uppercase tracking-[0.22em] text-white/50">
                                <span>Preview en vivo</span>
                                <span class="rounded-full bg-[#F8B803] px-2 py-1 font-black text-[#1B1B18]" x-text="profileLabel"></span>
                            </div>
                            <div class="mt-4 flex items-start gap-4">
                                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-white/10 text-2xl font-black text-[#F8B803]">
                                    <template x-if="avatarUrl">
                                        <img :src="avatarUrl" alt="Avatar" class="h-full w-full object-cover">
                                    </template>
                                    <template x-if="!avatarUrl">
                                        <span x-text="avatarFallback"></span>
                                    </template>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm text-white/60" x-text="publicHandle"></p>
                                    <h3 class="mt-1 text-2xl font-black tracking-tight" x-text="displayName"></h3>
                                    <p class="mt-2 text-sm text-white/75" x-text="summary"></p>
                                </div>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                <div class="rounded-2xl bg-white/5 p-3">
                                    <p class="text-white/50 text-xs">Visibilidad</p>
                                    <p class="mt-1 font-bold">Competitiva</p>
                                </div>
                                <div class="rounded-2xl bg-white/5 p-3">
                                    <p class="text-white/50 text-xs">Salida</p>
                                    <p class="mt-1 break-all font-bold" x-text="source || 'Pendiente'"></p>
                                </div>
                            </div>
                            <div class="mt-3 rounded-2xl bg-white/5 p-3 text-xs text-white/70">
                                <p class="font-bold text-white/90">Redes principales</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <template x-if="socialInstagram"><span class="rounded-full bg-white/10 px-3 py-1" x-text="'IG · ' + socialInstagram"></span></template>
                                    <template x-if="socialTikTok"><span class="rounded-full bg-white/10 px-3 py-1" x-text="'TT · ' + socialTikTok"></span></template>
                                    <template x-if="socialX"><span class="rounded-full bg-white/10 px-3 py-1" x-text="'X · ' + socialX"></span></template>
                                    <template x-if="socialWebsite"><span class="rounded-full bg-white/10 px-3 py-1" x-text="'WEB · ' + socialWebsite"></span></template>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section x-show="step === 2" x-cloak class="space-y-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-gray-400">Paso 2</p>
                        <h2 class="mt-1 text-2xl font-black tracking-tight">Paso 2/4 · Destino de tráfico</h2>
                        <p class="mt-2 text-sm text-gray-600">Este es el destino final del botón. Es opcional: si no lo cambias, los clics irán a tu perfil.</p>
                    </div>

                    <label class="block">
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <span class="block text-sm font-bold text-[#1B1B18]">URL de destino <span class="font-normal text-gray-500">(opcional)</span></span>
                            <label class="inline-flex items-center gap-2 text-xs font-bold text-gray-600">
                                <input type="checkbox" x-model="useProfileAsDestination" @change="toggleProfileDestination()" class="h-4 w-4 rounded border-gray-300 text-[#F53003] focus:ring-[#F53003]">
                                Usar perfil como destino
                            </label>
                        </div>
                        <input x-model="destinationUrl" @input.debounce.600ms="queueMetaPreview()" :disabled="useProfileAsDestination" placeholder="https://tu-landing.cl" class="w-full rounded-[14px] border border-line bg-white px-[18px] py-3.5 font-mono text-sm text-ink outline-none transition-colors focus:border-accent disabled:cursor-not-allowed disabled:bg-gray-100" name="destination" value="" type="url" :required="!useProfileAsDestination">
                    </label>

                    <div class="rounded-2xl border border-dashed border-[#F8B803] bg-[#FFFCEB] px-4 py-3 text-sm text-[#5A4300]">
                        <span class="font-bold">Chequeo:</span>
                        <span class="ml-1" x-text="useProfileAsDestination ? 'Ruta del perfil cargada.' : (isValidDestination ? 'URL válida para avanzar.' : 'Agrega una URL pública completa para continuar.')"></span>
                    </div>

                    <div x-show="!useProfileAsDestination && metaPreview.loading" x-cloak class="rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-500">
                        Revisando metadata...
                    </div>

                    <div x-show="!useProfileAsDestination && (metaPreview.title || metaPreview.description)" x-cloak class="rounded-3xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-gray-400">Vista previa SEO</p>
                            <template x-if="metaPreview.has_good_paint">
                                <span class="inline-flex items-center gap-2 rounded-full bg-[#FFF8E1] px-3 py-1 text-xs font-black text-[#7A5600]">
                                    <span aria-hidden="true">🎟️</span>
                                    <span x-text="metaPreview.badge_label || 'Esto tiene buena pinta'"></span>
                                </span>
                            </template>
                        </div>
                        <div class="mt-3 space-y-2">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-[0.18em] text-gray-400">Meta title</span>
                                <span class="ml-2 font-semibold text-[#1B1B18]" x-text="metaPreview.title || '—'"></span>
                            </div>
                            <div>
                                <span class="text-xs font-bold uppercase tracking-[0.18em] text-gray-400">Meta description</span>
                                <span class="ml-2 text-sm text-gray-600" x-text="metaPreview.description || '—'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button type="button" @click="go(1)" class="inline-flex items-center justify-center rounded-2xl border border-gray-300 px-5 py-3 font-bold text-[#1B1B18] transition hover:bg-gray-50">
                            Volver al perfil
                        </button>
                        <button type="button" :disabled="!canContinueFromDestination" @click="go(3)" class="inline-flex items-center justify-center rounded-2xl px-5 py-3 font-black text-white transition" :class="canContinueFromDestination ? 'bg-[#F53003] hover:bg-[#c22a02]' : 'cursor-not-allowed bg-gray-300'">
                            Seguir a posición y costo
                        </button>
                        <p class="text-xs leading-5 text-gray-500 sm:max-w-md">Legal: el destino final debe ser accesible y coherente con lo publicado.</p>
                    </div>
                </section>

                <section x-show="step === 3" x-cloak class="space-y-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-gray-400">Paso 3</p>
                        <h2 class="mt-1 text-2xl font-black tracking-tight">Paso 3/4 · Posición, costo y CTA</h2>
                        <p class="mt-2 text-sm text-gray-600">El CTA cambia según la posición seleccionada. También puedes activar <strong>PUBLICAR GRATIS</strong>.</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-[1fr_0.95fr]">
                        <div class="space-y-4">
                            <div class="flex flex-wrap gap-2">
                                <button type="button" @click="setPaidMode()" class="rounded-full px-4 py-2 text-sm font-bold transition" :class="!freePublish ? 'bg-[#1B1B18] text-white' : 'bg-gray-100 text-gray-600'">Modo pagado</button>
                                <button type="button" @click="setFreeMode()" class="rounded-full px-4 py-2 text-sm font-bold transition" :class="freePublish ? 'bg-[#F8B803] text-[#1B1B18]' : 'bg-gray-100 text-gray-600'">PUBLICAR GRATIS</button>
                            </div>

                            <div class="grid gap-2 sm:grid-cols-5">
                                @foreach ($positionPricing as $option)
                                    <button type="button" @click="setPosition({{ $option['position'] }})" class="rounded-2xl border px-3 py-3 text-left transition" :class="position === {{ $option['position'] }} && !freePublish ? 'border-[#F53003] bg-[#FFF4F1]' : 'border-gray-200 bg-white hover:border-gray-300'">
                                        <span class="block text-xs uppercase tracking-[0.18em] text-gray-500">#{{ $option['position'] }}</span>
                                        <span class="mt-1 block text-sm font-black" x-text="money({{ $option['amount'] }})"></span>
                                    </button>
                                @endforeach
                            </div>

                            <div class="rounded-3xl border border-gray-200 bg-[#1B1B18] p-4 text-white">
                                <p class="text-xs font-bold uppercase tracking-[0.22em] text-white/50">CTA final</p>
                                <p class="mt-3 text-3xl font-black tracking-tight" x-text="ctaLabel"></p>
                                <p class="mt-2 text-sm text-white/70" x-text="freePublish ? 'El perfil se publica sin cobro y con visibilidad básica.' : 'La posición se cobra como visibilidad competitiva dentro del periodo.'"></p>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-gray-200 bg-[#FFF8E1] p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#8A6100]">Resumen del paso</p>
                            <dl class="mt-4 space-y-3 text-sm text-[#5A4300]">
                                <div class="flex items-center justify-between gap-3 border-b border-[#F1E0A5] pb-2">
                                    <dt class="font-medium">Posición</dt>
                                    <dd class="font-black" x-text="freePublish ? 'Gratis' : '#' + position"></dd>
                                </div>
                                <div class="flex items-center justify-between gap-3 border-b border-[#F1E0A5] pb-2">
                                    <dt class="font-medium">Costo</dt>
                                    <dd class="font-black" x-text="freePublish ? '$0' : money(selectedAmount)"></dd>
                                </div>
                                <div class="flex items-center justify-between gap-3 border-b border-[#F1E0A5] pb-2">
                                    <dt class="font-medium">Visible como</dt>
                                    <dd class="font-black" x-text="freePublish ? 'PUBLICAR GRATIS' : 'SUBIR AL #' + position"></dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="font-medium">Destino</dt>
                                    <dd class="max-w-[10rem] truncate font-black" x-text="useProfileAsDestination ? destinationUrl : destinationUrl"></dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button type="button" @click="back()" class="inline-flex items-center justify-center rounded-2xl border border-gray-300 px-5 py-3 font-bold text-[#1B1B18] transition hover:bg-gray-50">Retroceder</button>
                        <button type="button" @click="go(4)" class="inline-flex items-center justify-center rounded-2xl bg-[#F53003] px-5 py-3 font-black text-white transition hover:bg-[#c22a02]" x-text="'Paso 4/4'"></button>
                        <button type="button" @click="go(3)" class="inline-flex items-center justify-center rounded-2xl border border-gray-300 px-5 py-3 font-bold text-[#1B1B18] transition hover:bg-gray-50">
                            Ajustar destino
                        </button>
                    </div>
                </section>

                <section x-show="step === 4" x-cloak class="space-y-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-gray-400">Paso 4</p>
                        <h2 class="mt-1 text-2xl font-black tracking-tight">Paso 4/4 · Preview y confirmación</h2>
                        <p class="mt-2 text-sm text-gray-600">Revisa el resultado y confirma el envío.</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <article class="rounded-3xl border border-gray-200 bg-white p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-[0.22em] text-gray-400">Antes</p>
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-[11px] font-bold text-gray-600">Pre-checkout</span>
                            </div>
                            <div class="mt-4 rounded-2xl bg-[#1B1B18] p-4 text-white">
                                <div class="flex items-center justify-between text-xs text-white/50">
                                    <span x-text="publicHandle"></span>
                                    <span x-text="freePublish ? 'PUBLICAR GRATIS' : ctaLabel"></span>
                                </div>
                                <h3 class="mt-3 text-2xl font-black" x-text="displayName"></h3>
                                <p class="mt-2 text-sm text-white/70" x-text="summary"></p>
                                <div class="mt-4 rounded-2xl bg-white/5 px-3 py-2 text-sm text-white/80">
                                    <p class="font-semibold">Destino</p>
                                    <p class="break-all text-white/60" x-text="useProfileAsDestination ? destinationUrl : destinationUrl"></p>
                                </div>
                            </div>
                        </article>

                        <article class="rounded-3xl border border-[#F8B803]/40 bg-[#FFF8E1] p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#8A6100]">Después</p>
                                <span class="rounded-full bg-[#F8B803] px-2 py-1 text-[11px] font-black text-[#1B1B18]">Publicación lista</span>
                            </div>
                            <div class="mt-4 rounded-2xl bg-white p-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#1B1B18] text-lg font-black text-[#F8B803]">
                                        👑
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="truncate text-xl font-black text-[#1B1B18]" x-text="displayName"></h3>
                                        <p class="text-sm text-gray-500" x-text="freePublish ? 'Publicado gratis' : 'Objetivo inicial #' + position + ' por ' + money(selectedAmount)"></p>
                                    </div>
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                    <div class="rounded-2xl bg-[#FFF8E1] p-3">
                                        <p class="text-xs text-[#8A6100]">Estado</p>
                                        <p class="mt-1 font-black" x-text="freePublish ? 'Publicado' : 'Checkout confirmado'"></p>
                                    </div>
                                    <div class="rounded-2xl bg-[#FFF8E1] p-3">
                                        <p class="text-xs text-[#8A6100]">Objetivo inicial</p>
                                        <p class="mt-1 font-black" x-text="freePublish ? 'Visible gratis' : '#' + position"></p>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div class="rounded-3xl border border-dashed border-[#F8B803] bg-[#FFFCEB] px-4 py-3 text-sm text-[#5A4300]">
                        <span class="font-bold">Legal:</span>
                        <span class="ml-1">el monto inicia una posición estimada; la posición final se calcula al aprobar el checkout. En modo gratis la publicación entra sin cobro.</span>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button type="button" @click="submitDraft()" :disabled="submitting || !canContinueFromDestination" class="inline-flex items-center justify-center rounded-2xl bg-[#F53003] px-5 py-3 font-black text-white transition hover:bg-[#c22a02] disabled:cursor-not-allowed disabled:bg-gray-300" x-text="submitting ? 'Enviando...' : (freePublish ? 'PUBLICAR GRATIS' : ctaLabel)"></button>
                        <button type="button" @click="copyPayload()" class="inline-flex items-center justify-center rounded-2xl border border-gray-300 px-5 py-3 font-bold text-[#1B1B18] transition hover:bg-gray-50">
                            Copiar resumen
                        </button>
                    </div>

                    <div x-show="submitting" x-cloak class="rounded-3xl bg-[#1B1B18] p-4 text-white">
                        <p class="mt-2 text-sm text-white/75">Enviando tu solicitud...</p>
                    </div>
                    <div x-show="errorMessage" x-cloak class="rounded-3xl bg-red-50 p-4 text-red-700">
                        <p class="text-sm font-semibold" x-text="errorMessage"></p>
                    </div>
                    <div x-show="submitted" x-cloak class="rounded-3xl bg-[#1B1B18] p-4 text-white">
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-[#F8B803]">Enviado</p>
                        <p class="mt-2 text-sm text-white/75">Tu solicitud fue registrada. Estás siendo redirigido...</p>
                    </div>
                </section>
            </div>
        </section>

        <section class="mt-5 grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
            <div class="rounded-3xl border border-gray-200 bg-white p-5">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-gray-400">Resumen vivo</p>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl bg-[#FFF8E1] p-4">
                        <p class="text-xs text-[#8A6100]">Origen</p>
                        <p class="mt-1 break-all font-bold text-[#1B1B18]" x-text="source"></p>
                    </div>
                    <div class="rounded-2xl bg-[#FFF8E1] p-4">
                        <p class="text-xs text-[#8A6100]">Destino</p>
                        <p class="mt-1 break-all font-bold text-[#1B1B18]" x-text="useProfileAsDestination ? destinationUrl : destinationUrl"></p>
                    </div>
                    <div class="rounded-2xl bg-[#FFF8E1] p-4">
                        <p class="text-xs text-[#8A6100]">CTA</p>
                        <p class="mt-1 font-black text-[#1B1B18]" x-text="ctaLabel"></p>
                    </div>
                    <div class="rounded-2xl bg-[#FFF8E1] p-4">
                        <p class="text-xs text-[#8A6100]">Modo</p>
                        <p class="mt-1 font-black text-[#1B1B18]" x-text="freePublish ? 'PUBLICAR GRATIS' : 'Pago competitivo'"></p>
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button type="button" @click="copyPayload()" class="rounded-2xl bg-[#1B1B18] px-4 py-3 text-sm font-bold text-white transition hover:bg-black">
                        Copiar payload
                    </button>
                    <a href="{{ route('home') }}" class="rounded-2xl border border-gray-300 px-4 py-3 text-sm font-bold text-[#1B1B18] transition hover:bg-gray-50">
                        Volver al ranking
                    </a>
                </div>
            </div>

            <aside class="rounded-3xl bg-[#1B1B18] p-5 text-white">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-white/50">Microcopy legal</p>
                <div class="mt-4">
                    @include('entrar.partials.legal-microcopy', ['items' => $legalMicrocopy])
                </div>
            </aside>
        </section>

        <section class="mt-5 rounded-3xl border border-gray-200 bg-white p-5 text-center">
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-gray-400">Siguiente paso</p>
            <p class="mt-2 text-sm text-gray-600">Al confirmar, tu perfil se publica gratis o inicia el checkout competitivo según el modo elegido.</p>
            <a href="{{ route('rules') }}" class="mt-4 inline-flex items-center justify-center rounded-2xl bg-[#F53003] px-5 py-3 font-black text-white transition hover:bg-[#c22a02]">
                Ver reglas completas
            </a>
        </section>

        @include('partials.site-footer')
    </main>
</div>

@push('scripts')
<script>
    function entrarWizard(config) {
        return {
            step: 1,
            maxStep: config.wizardSteps.length,
            source: config.defaultDraft.source,
            handle: config.defaultDraft.handle,
            displayName: config.defaultDraft.display_name,
            summary: config.defaultDraft.summary,
            destinationUrl: config.defaultDraft.destination_url,
            useProfileAsDestination: config.defaultDraft.use_profile_as_destination ?? false,
            avatarUrl: config.defaultDraft.avatar_url,
            socialInstagram: '',
            socialTikTok: '',
            socialX: '',
            socialWebsite: '',
            metaPreview: { loading: false, title: '', description: '', has_good_paint: false, badge_label: null },
            metaPreviewTimer: null,
            position: config.defaultDraft.position,
            freePublish: config.defaultDraft.free_publish,
            category: config.defaultDraft.category || '',
            regionId: config.defaultDraft.region_id || '',
            submitted: false,
            submitting: false,
            errorMessage: null,
            copied: false,
            positionPricing: config.positionPricing,
            legalMicrocopy: config.legalMicrocopy,
            get stepDisplay() {
                return `${this.step}/${this.maxStep}`;
            },            get pricingMap() {
                return this.positionPricing.reduce((carry, item) => {
                    carry[item.position] = item.amount;
                    return carry;
                }, {});
            },
            get selectedAmount() {
                return this.freePublish ? 0 : (this.pricingMap[this.position] ?? 0);
            },
            get ctaLabel() {
                return this.freePublish ? 'PUBLICAR GRATIS' : `SUBIR AL #${this.position} POR ${this.money(this.selectedAmount)}`;
            },
            get publicHandle() {
                return this.handle ? `@${this.handle}` : '@publico';
            },
            get sourceHint() {
                if (!this.source) {
                    return 'Pega un origen público';
                }
                return this.source.includes('@') ? 'Handle detectado' : 'URL pública detectada';
            },
            get isSourceValid() {
                return Boolean(this.source && this.source.trim().length > 0 && this.handle && this.handle.trim().length > 0);
            },
            get avatarFallback() {
                return this.displayName ? this.displayName.slice(0, 1).toUpperCase() : 'Q';
            },
            get profileLabel() {
                return this.freePublish ? 'GRATIS' : `#${this.position}`;
            },
            get isValidDestination() {
                if (this.useProfileAsDestination) {
                    return true;
                }
                try {
                    return Boolean(this.destinationUrl) && Boolean(new URL(this.destinationUrl));
                } catch (e) {
                    return false;
                }
            },
            get canContinueFromDestination() {
                return this.useProfileAsDestination || this.isValidDestination;
            },
            init() {
                this.syncFromSource();
                this.syncDestinationMode();
            },
            money(value) {
                return '$' + Number(value || 0).toLocaleString('es-CL');
            },
            syncFromSource() {
                const raw = (this.source || '').trim();
                if (!raw) {
                    this.handle = '';
                    if (this.useProfileAsDestination) {
                        this.destinationUrl = this.sourceToUrl(raw);
                    }
                    return;
                }
                const withoutProtocol = raw.replace(/^https?:\/\//i, '').replace(/^www\./i, '');
                const firstSegment = withoutProtocol.split(/[\/?#]/)[0];
                const candidate = (raw.startsWith('@') ? raw.slice(1) : firstSegment)
                    .replace(/^@/, '')
                    .replace(/[^a-z0-9._-]+/gi, '-')
                    .replace(/-+/g, '-')
                    .replace(/^[.-]+|[.-]+$/g, '')
                    .toLowerCase();
                this.handle = candidate;
                if (!this.displayName || this.displayName === this.defaultDisplayName()) {
                    this.displayName = this.humanizeHandle(candidate);
                }
                if (this.useProfileAsDestination) {
                    this.destinationUrl = this.sourceToUrl(this.source);
                }
            },
            defaultDisplayName() {
                return config.defaultDraft.display_name;
            },
            humanizeHandle(handle) {
                if (!handle) {
                    return 'Perfil público';
                }
                return handle
                    .replace(/[._-]+/g, ' ')
                    .replace(/\b\w/g, (m) => m.toUpperCase())
                    .trim();
            },
            fillSource(value) {
                this.source = value;
                this.syncFromSource();
            },
            setPosition(position) {
                this.freePublish = false;
                this.position = position;
            },
            setFreeMode() {
                this.freePublish = true;
            },
            profileDestinationUrl() {
                return this.sourceToUrl(this.source);
            },
            syncDestinationMode() {
                if (this.useProfileAsDestination) {
                    this.destinationUrl = this.profileDestinationUrl();
                } else if (!this.destinationUrl || this.destinationUrl.includes('/perfil/')) {
                    this.destinationUrl = '';
                }
            },
            toggleProfileDestination() {
                this.syncDestinationMode();
                this.queueMetaPreview();
            },
            setPaidMode() {
                this.freePublish = false;
                if (!this.position) {
                    this.position = config.defaultDraft.position;
                }
            },
            queueMetaPreview() {
                if (this.metaPreviewTimer) {
                    window.clearTimeout(this.metaPreviewTimer);
                }
                this.metaPreviewTimer = window.setTimeout(() => this.loadMetaPreview(), 600);
            },
            async loadMetaPreview() {
                if (this.useProfileAsDestination) {
                    this.metaPreview = { loading: false, title: '', description: '', has_good_paint: false, badge_label: null };
                    return;
                }
                if (!this.isValidDestination) {
                    this.metaPreview = { loading: false, title: '', description: '', has_good_paint: false, badge_label: null };
                    return;
                }

                this.metaPreview.loading = true;
                try {
                    const res = await fetch('/api/meta-preview', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({ url: this.destinationUrl }),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        throw new Error(data.message || 'No pudimos leer la metadata.');
                    }
                    this.metaPreview = {
                        loading: false,
                        title: data.title || '',
                        description: data.description || '',
                        has_good_paint: Boolean(data.has_good_paint),
                        badge_label: data.badge_label || null,
                    };
                } catch (error) {
                    this.metaPreview = { loading: false, title: '', description: '', has_good_paint: false, badge_label: null };
                }
            },
            go(step) {
                if (step > this.step + 1 || step < this.step - 1) {
                    return;
                }
                if (step > 1 && !this.isSourceValid) {
                    return;
                }
                if (step === 3 && !this.canContinueFromDestination) {
                    return;
                }
                this.step = Math.min(Math.max(step, 1), this.maxStep);
            },
            next() {
                this.go(this.step + 1);
            },
            back() {
                this.go(this.step - 1);
            },
            payload() {
                const links = [
                    { label: 'Instagram', url: this.socialInstagram },
                    { label: 'TikTok', url: this.socialTikTok },
                    { label: 'X', url: this.socialX },
                    { label: 'Sitio web', url: this.socialWebsite },
                ].filter((link) => link.url && link.url.trim());

                return {
                    source: this.source,
                    handle: this.handle,
                    display_name: this.displayName,
                    summary: this.summary,
                    category: this.category || null,
                    region_id: this.regionId || null,
                    destination_url: this.destinationUrl,
                    use_profile_as_destination: this.useProfileAsDestination,
                    avatar_url: this.avatarUrl,
                    links,
                    position: this.freePublish ? null : this.position,
                    amount_clp: this.selectedAmount,
                    free_publish: this.freePublish,
                };
            },
            async copyPayload() {
                try {
                    await navigator.clipboard.writeText(JSON.stringify(this.payload(), null, 2));
                    this.copied = true;
                    window.setTimeout(() => { this.copied = false; }, 1800);
                } catch (error) {
                    this.copied = false;
                }
            },
            sourceToUrl(raw) {
                const value = (raw || '').trim();
                if (!value) return '';
                if (/^https?:\/\//i.test(value)) return value;
                if (value.startsWith('@')) {
                    // Un @handle se interpreta como plataforma por defecto (Instagram).
                    return 'https://instagram.com/' + value.slice(1);
                }
                if (value.startsWith('www.')) return 'https://' + value;
                if (value.includes('.')) return 'https://' + value;
                return value;
            },
            async submitDraft() {
                this.submitting = true;
                try {
                    const detect = await fetch('/entrar/detectar', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' },
                        body: JSON.stringify({
                            display_name: this.displayName,
                            category: this.category || null,
                            region_id: this.regionId || null,
                            source_url: this.sourceToUrl(this.source),
                            destination_url: this.destinationUrl,
                            use_profile_as_destination: this.useProfileAsDestination,
                        }),
                    });
                    const detected = await detect.json();
                    if (!detect.ok) {
                        this.errorMessage = detected.message || detected.error || 'No pudimos validar el origen.';
                        this.submitted = false;
                        return;
                    }

                    if (detected.status === 'rejected' || detected.error === 'duplicate_profile') {
                        this.errorMessage = 'Este perfil ya está en el ranking. Ábrelo desde el ranking para ver su posición y apoyarlo.';
                        this.submitted = false;
                        return;
                    }

                    if (this.freePublish) {
                        const confirm = await fetch('/entrar/' + detected.submission_id + '/confirmar', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' },
                        });
                        const confirmed = await confirm.json();
                        if (confirm.ok && confirmed.redirect_url) {
                            window.location.href = confirmed.redirect_url;
                            return;
                        }
                        this.errorMessage = confirmed.message || 'No pudimos publicar gratis.';
                        return;
                    }

                    const checkout = await fetch('/entrar/' + detected.submission_id + '/checkout', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' },
                        body: JSON.stringify({
                            amount_clp: this.selectedAmount,
                            age_declared_18: 1,
                            is_anonymous: true,
                        }),
                    });
                    const result = await checkout.json();
                    if (checkout.ok && result.checkout_url) {
                        window.location.href = result.checkout_url;
                        return;
                    }
                    this.errorMessage = result.message || result.error || 'No pudimos iniciar el checkout.';
                } catch (error) {
                    this.errorMessage = 'Error de conexión, intenta nuevamente.';
                } finally {
                    this.submitting = false;
                }
            },
        };
    }
</script>
@endpush
@endsection
