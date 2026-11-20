@extends('layouts.app')
@section('titulo', 'Orçamentos & Metas')

@section('conteudo')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">Orçamentos do mês</h1>
            <p class="text-muted small mb-0 mt-1">Defina limites por categoria de despesa e acompanhe os alertas.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <form method="GET" action="{{ route('orcamentos.index') }}" class="filtro-mes-grupo">
                <label class="filtro-mes-rotulo" for="mes">Mês</label>
                <input type="month" class="filtro-mes-input" id="mes" name="mes" value="{{ $mes }}" aria-label="Mês de referência">
                <button type="submit" class="filtro-mes-btn">Ver</button>
            </form>
            <button class="btn btn-primary btn-acao-topo" data-bs-toggle="modal" data-bs-target="#modalMeta" id="btnNovoOrcamento">
                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Novo orçamento
            </button>
        </div>
    </div>

    @php
        $totalGasto = collect($categorias)->sum('gasto');
        $totalLimite = collect($categorias)->filter(fn ($c) => $c['limite'] && $c['limite'] > 0)->sum('limite');
        $qtdComMeta = collect($categorias)->filter(fn ($c) => $c['limite'] && $c['limite'] > 0)->count();
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card p-3">
                <span class="text-muted small fw-medium">Categorias com meta</span>
                <div class="valor-grande mt-1">{{ $qtdComMeta }} <span class="fs-6 fw-normal text-muted">de {{ count($categorias) }}</span></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <span class="text-muted small fw-medium">Total planejado</span>
                <div class="valor-grande mt-1">R$ {{ number_format($totalLimite, 2, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <span class="text-muted small fw-medium">Total gasto no mês</span>
                <div class="valor-grande despesa mt-1">R$ {{ number_format($totalGasto, 2, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        @foreach ($categorias as $c)
            <div class="col-md-6">
                <div class="card p-3 p-lg-4 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                            <span class="cat-pill">
                                <span class="cat-bolinha" style="--cat-cor: {{ $c['cor'] ?? '#15803d' }}"></span>
                                <strong>{{ $c['nome'] }}</strong>
                            </span>

                            @if ($c['limite'] && $c['limite'] > 0)
                                @php
                                    $pct = round(($c['gasto'] / $c['limite']) * 100);
                                    $cor = $pct >= 100 ? 'danger' : ($pct >= 80 ? 'warning' : 'success');
                                    $badgeTexto = $pct >= 100 ? 'Ultrapassada' : ($pct >= 80 ? "Atenção ({$pct}%)" : "{$pct}% usado");
                                @endphp
                                <span class="badge rounded-pill bg-{{ $cor }}-subtle text-{{ $cor === 'warning' ? 'warning-emphasis' : $cor }} border border-{{ $cor }}-subtle">
                                    {{ $badgeTexto }}
                                </span>
                            @else
                                <span class="badge rounded-pill bg-secondary-subtle text-secondary">Sem meta</span>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between align-items-baseline mt-2">
                            <span class="valor-num fw-bold fs-5">R$ {{ number_format($c['gasto'], 2, ',', '.') }}</span>
                            @if ($c['limite'] && $c['limite'] > 0)
                                <span class="text-muted small valor-num">limite de R$ {{ number_format($c['limite'], 2, ',', '.') }}</span>
                            @else
                                <span class="text-muted small">sem limite definido</span>
                            @endif
                        </div>

                        @if ($c['limite'] && $c['limite'] > 0)
                            <div class="progress my-2" style="height: 10px; border-radius: 999px;" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar bg-{{ $cor }}" style="width: {{ min($pct, 100) }}%; border-radius: 999px;"></div>
                            </div>
                        @else
                            <div class="progress my-2" style="height: 10px; border-radius: 999px;">
                                <div class="progress-bar bg-secondary" style="width: 0%"></div>
                            </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center gap-2 pt-2 mt-1 border-top border-light-subtle">
                        @if ($c['limite'] && $c['limite'] > 0)
                            @if ($pct >= 100)
                                <small class="text-danger fw-medium">Excedida em R$ {{ number_format($c['gasto'] - $c['limite'], 2, ',', '.') }}.</small>
                            @elseif ($pct >= 80)
                                <small class="text-warning-emphasis fw-medium">Restam R$ {{ number_format($c['limite'] - $c['gasto'], 2, ',', '.') }}.</small>
                            @else
                                <small class="text-muted">Restam R$ {{ number_format($c['limite'] - $c['gasto'], 2, ',', '.') }}.</small>
                            @endif
                        @else
                            <small class="text-muted">Nenhuma meta definida para este mês.</small>
                        @endif

                        <div class="d-flex align-items-center gap-1">
                            @if ($c['limite'])
                                <button
                                    type="button"
                                    class="btn-icone-acao btn-definir-meta"
                                    title="Editar meta"
                                    aria-label="Editar meta"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalMeta"
                                    data-categoria="{{ $c['id'] }}"
                                    data-limite="{{ $c['limite'] }}"
                                >
                                    <img src="{{ asset('images/botao-editar.svg') }}" alt="" width="32" height="32" aria-hidden="true">
                                </button>
                            @else
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary btn-sm py-1 px-2 btn-definir-meta"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalMeta"
                                    data-categoria="{{ $c['id'] }}"
                                    data-limite=""
                                >
                                    Definir meta
                                </button>
                            @endif

                            @if ($c['orcamento_id'])
                                <form method="POST" action="{{ route('orcamentos.destroy', $c['orcamento_id']) }}" class="d-inline-flex m-0" onsubmit="return confirm('Remover meta desta categoria?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icone-acao" title="Remover meta" aria-label="Remover meta">
                                        <img src="{{ asset('images/botao-excluir.svg') }}" alt="" width="32" height="32" aria-hidden="true">
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Janela para definir meta --}}
    <div class="modal fade" id="modalMeta" tabindex="-1" aria-labelledby="tituloMeta" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('orcamentos.store') }}" class="modal-content">
                @csrf
                <input type="hidden" name="mes_referencia" value="{{ $mes }}">
                <div class="modal-header">
                    <h2 class="modal-title h5 fw-bold" id="tituloMeta">Definir meta mensal</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small" for="meta_categoria">Categoria</label>
                        <select class="form-select" id="meta_categoria" name="categoria_id" required>
                            @foreach ($categorias as $c)
                                <option value="{{ $c['id'] }}">{{ $c['nome'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-semibold small" for="meta_limite">Limite do mês (R$)</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="meta_limite" name="limite" placeholder="Ex.: 600,00" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar meta</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const metaCategoria = document.getElementById('meta_categoria');
    const metaLimite = document.getElementById('meta_limite');

    document.querySelectorAll('.btn-definir-meta').forEach(btn => {
        btn.addEventListener('click', () => {
            if (metaCategoria) metaCategoria.value = btn.dataset.categoria;
            if (metaLimite) metaLimite.value = btn.dataset.limite || '';
        });
    });

    const btnNovo = document.getElementById('btnNovoOrcamento');
    if (btnNovo) {
        btnNovo.addEventListener('click', () => {
            if (metaLimite) metaLimite.value = '';
        });
    }
</script>
@endpush
