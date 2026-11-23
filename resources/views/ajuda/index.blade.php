@extends(auth()->check() ? 'layouts.app' : 'layouts.publico')
@section('titulo', 'Ajuda & Perguntas Frequentes')

@section('conteudo')
    @php
        $anterior = url()->previous();
        $hostAtual = request()->getHost();
        $urlAnteriorValida = is_string($anterior)
            && parse_url($anterior, PHP_URL_HOST) === $hostAtual
            && parse_url($anterior, PHP_URL_PATH) !== '/ajuda';

        $destinoVoltar = $urlAnteriorValida
            ? $anterior
            : (auth()->check() ? route('painel.index') : route('apresentacao'));
    @endphp

    <div class="ajuda-wrapper mx-auto">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-0 fw-bold">Ajuda e Perguntas Frequentes</h1>
                <p class="text-muted small mb-0 mt-1">Respostas rápidas sobre como usar cada recurso do MeuBolso.</p>
            </div>
            <a href="{{ $destinoVoltar }}" class="btn btn-outline-secondary btn-acao-topo">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Voltar
            </a>
        </div>

        <div class="card p-3 p-lg-4 mb-4">
            <label for="buscaAjuda" class="form-label fw-semibold small text-muted mb-2">Buscar dúvida</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted" style="border-color: #dcd6c7; border-radius: 10px 0 0 10px;">
                    <i class="bi bi-search" aria-hidden="true"></i>
                </span>
                <input
                    type="search"
                    id="buscaAjuda"
                    class="form-control border-start-0"
                    placeholder="Ex.: orçamento, PDF, categoria, senha..."
                    autocomplete="off"
                >
            </div>

            <div class="d-flex flex-wrap gap-2 mt-3">
                @foreach ($secoes as $secao)
                    <a href="#secao-{{ $secao['id'] }}" class="ajuda-atalho">
                        <i class="bi {{ $secao['icone'] }}" aria-hidden="true"></i>
                        <span>{{ $secao['titulo'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <div id="ajudaSemResultados" class="card p-4 text-center text-muted d-none mb-4">
            <i class="bi bi-question-circle fs-3 mb-2 d-block" aria-hidden="true"></i>
            <div class="fw-semibold text-dark">Nenhuma pergunta encontrada para essa busca.</div>
            <div class="small mt-1">Tente usar palavras mais simples, como "meta", "transação", "PDF" ou "e-mail".</div>
        </div>

        @foreach ($secoes as $secao)
            <section id="secao-{{ $secao['id'] }}" class="ajuda-secao mb-4" data-ajuda-secao>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="ajuda-secao-icone">
                        <i class="bi {{ $secao['icone'] }}" aria-hidden="true"></i>
                    </span>
                    <h2 class="h5 mb-0 fw-bold">{{ $secao['titulo'] }}</h2>
                </div>

                <div class="accordion ajuda-accordion" id="accordion-{{ $secao['id'] }}">
                    @foreach ($secao['itens'] as $indice => $item)
                        @php
                            $collapseId = "faq-{$secao['id']}-{$indice}";
                            $abertoInicial = $loop->parent->first && $loop->first;
                        @endphp
                        <div class="accordion-item ajuda-item" data-ajuda-item>
                            <h3 class="accordion-header">
                                <button
                                    class="accordion-button {{ $abertoInicial ? '' : 'collapsed' }}"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#{{ $collapseId }}"
                                    aria-expanded="{{ $abertoInicial ? 'true' : 'false' }}"
                                    aria-controls="{{ $collapseId }}"
                                >
                                    {{ $item['pergunta'] }}
                                </button>
                            </h3>
                            <div
                                id="{{ $collapseId }}"
                                class="accordion-collapse collapse {{ $abertoInicial ? 'show' : '' }}"
                                data-bs-parent="#accordion-{{ $secao['id'] }}"
                            >
                                <div class="accordion-body text-muted">
                                    {{ $item['resposta'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('buscaAjuda');
        const avisoVazio = document.getElementById('ajudaSemResultados');
        if (!input) return;

        const normalizar = (texto) => texto
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');

        input.addEventListener('input', () => {
            const termo = normalizar(input.value.trim());
            const secoes = document.querySelectorAll('[data-ajuda-secao]');
            let totalVisiveis = 0;

            secoes.forEach((secao) => {
                const itens = secao.querySelectorAll('[data-ajuda-item]');
                let visiveisNaSecao = 0;

                itens.forEach((item) => {
                    const conteudo = normalizar(item.textContent || '');
                    const corresponde = termo === '' || conteudo.includes(termo);
                    item.classList.toggle('d-none', !corresponde);
                    if (corresponde) {
                        visiveisNaSecao++;
                        totalVisiveis++;
                    }
                });

                secao.classList.toggle('d-none', visiveisNaSecao === 0);
            });

            if (avisoVazio) {
                avisoVazio.classList.toggle('d-none', totalVisiveis > 0);
            }
        });
    })();
</script>
@endpush
