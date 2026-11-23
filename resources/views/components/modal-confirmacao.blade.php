<div class="modal fade" id="modalConfirmacao" tabindex="-1" aria-labelledby="modalConfirmacaoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-confirmacao-dialog">
        <div class="modal-content">
            <div class="modal-body p-4">
                <div class="d-flex align-items-start gap-3">
                    <span class="modal-confirmacao-icone" aria-hidden="true">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </span>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <h2 class="h5 fw-bold mb-1" id="modalConfirmacaoTitulo">Confirmar ação</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <p class="text-muted small mb-0" id="modalConfirmacaoMensagem">Tem certeza que deseja continuar?</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger px-3" id="modalConfirmacaoBtn">Confirmar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalEl = document.getElementById('modalConfirmacao');
        if (!modalEl || typeof bootstrap === 'undefined') return;

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        const tituloEl = document.getElementById('modalConfirmacaoTitulo');
        const mensagemEl = document.getElementById('modalConfirmacaoMensagem');
        const btnConfirmar = document.getElementById('modalConfirmacaoBtn');
        const textoPadraoBotao = btnConfirmar.textContent;
        let formPendente = null;

        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || !form.dataset.confirmMensagem) {
                return;
            }

            event.preventDefault();
            formPendente = form;

            tituloEl.textContent = form.dataset.confirmTitulo || 'Confirmar ação';
            mensagemEl.textContent = form.dataset.confirmMensagem;
            btnConfirmar.textContent = form.dataset.confirmBotao || textoPadraoBotao;
            btnConfirmar.disabled = false;

            modal.show();
        });

        btnConfirmar.addEventListener('click', () => {
            if (!formPendente) return;

            const form = formPendente;
            formPendente = null;
            btnConfirmar.disabled = true;
            modal.hide();
            form.submit();
        });

        modalEl.addEventListener('hidden.bs.modal', () => {
            formPendente = null;
            btnConfirmar.disabled = false;
        });
    });
</script>
@endpush
