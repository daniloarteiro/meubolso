@extends('layouts.app')
@section('titulo', 'Orçamentos & Metas')

@section('conteudo')
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
        <h1 class="h4 mb-0">Orçamentos do mês</h1>
        <div class="d-flex gap-2 align-items-end">
            <form method="GET" action="{{ route('orcamentos.index') }}" class="d-flex gap-2 align-items-end">
                <div>
                    <label class="form-label small text-muted mb-0" for="mes">Mês</label>
                    <input type="month" class="form-control form-control-sm" id="mes" name="mes" value="{{ $mes }}">
                </div>
                <button type="submit" class="btn btn-outline-secondary btn-sm">Ver</button>
            </form>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMeta">+ Novo orçamento</button>
        </div>
    </div>

    <div class="row g-3">
        @foreach ($categorias as $c)
            <div class="col-md-6">
                <div class="card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>{{ $c['nome'] }}</strong>
                        @if ($c['limite'] && $c['limite'] > 0)
                            <span>R$ {{ number_format($c['gasto'], 2, ',', '.') }} de R$ {{ number_format($c['limite'], 2, ',', '.') }}</span>
                        @else
                            <span class="text-muted">Gasto: R$ {{ number_format($c['gasto'], 2, ',', '.') }} (sem meta)</span>
                        @endif
                    </div>

                    @if ($c['limite'] && $c['limite'] > 0)
                        @php
                            $pct = round(($c['gasto'] / $c['limite']) * 100);
                            $cor = $pct >= 100 ? 'danger' : ($pct >= 80 ? 'warning' : 'success');
                        @endphp
                        <div class="progress my-2" style="height: 10px;" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-{{ $cor }}" style="width: {{ min($pct, 100) }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            @if ($pct >= 100)
                                <small class="text-danger">Meta ultrapassada em R$ {{ number_format($c['gasto'] - $c['limite'], 2, ',', '.') }}.</small>
                            @elseif ($pct >= 80)
                                <small class="text-warning-emphasis">Atenção: você já usou {{ $pct }}% da meta.</small>
                            @else
                                <small class="text-muted">{{ $pct }}% da meta usada.</small>
                            @endif

                            @if ($c['orcamento_id'])
                                <form method="POST" action="{{ route('orcamentos.destroy', $c['orcamento_id']) }}" onsubmit="return confirm('Remover meta desta categoria?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm text-danger p-0 text-decoration-none">Remover</button>
                                </form>
                            @endif
                        </div>
                    @else
                        <div class="progress my-2" style="height: 10px;">
                            <div class="progress-bar bg-secondary" style="width: 0%"></div>
                        </div>
                        <small class="text-muted">Nenhuma meta definida para este mês.</small>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Janela para definir meta --}}
    <div class="modal fade" id="modalMeta" tabindex="-1" aria-labelledby="tituloMeta" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('orcamentos.store') }}" class="modal-content">
                @csrf
                <input type="hidden" name="mes_referencia" value="{{ $mes }}">
                <div class="modal-header">
                    <h2 class="modal-title h5" id="tituloMeta">Definir meta mensal</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="meta_categoria">Categoria</label>
                        <select class="form-select" id="meta_categoria" name="categoria_id" required>
                            @foreach ($categorias as $c)
                                <option value="{{ $c['id'] }}">{{ $c['nome'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="meta_limite">Limite do mês (R$)</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="meta_limite" name="limite" required>
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
