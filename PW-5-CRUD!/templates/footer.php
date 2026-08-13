</main>

<footer class="rodape">
    <p>🌍 <strong>CRUD Mundo</strong> — Programação Web · Desenvolvimento de Sistemas — São José dos Campos</p>
    <p class="mini">PHP + MySQL · HTML5 · CSS3 · JavaScript</p>
</footer>

<?php if (defined('MODO_DEMO') && MODO_DEMO): ?>
    <div class="faixa-demo">
        ⚡ Modo demonstração (SQLite local). Para usar com MySQL: importe
        <code>database/bd_mundo.sql</code> no phpMyAdmin.
    </div>
<?php endif; ?>

<!-- Modal elegante de confirmação de exclusão -->
<div class="modal" id="modal-confirmar" hidden>
    <div class="modal-cartao" role="dialog" aria-modal="true" aria-labelledby="modal-titulo">
        <div class="modal-icone">⚠️</div>
        <h3 id="modal-titulo">Confirmar exclusão</h3>
        <p class="modal-texto"></p>
        <div class="modal-acoes">
            <button type="button" class="btn btn-neutro modal-cancelar">Cancelar</button>
            <button type="button" class="btn btn-perigo modal-ok">Sim, excluir</button>
        </div>
    </div>
</div>

</body>
</html>
