<?php
/*
 * ============================================================
 *  SISTEMA WEB DE ANÁLISE ESTATÍSTICA DE TURMA ESCOLAR
 *  Atividade Avaliativa 3 – PHP
 *  Curso: Desenvolvimento de Sistemas – São José dos Campos
 * ============================================================
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Análise de Turma</title>

    <!-- ======================================================
         ESTILO INTERNO – CSS para toda a aplicação
         ====================================================== -->
    <style>
        /* Reset básico e fonte padrão */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f0f2f5;
            color: #333;
            padding: 30px 20px;
        }

        /* Contêiner central da página */
        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }

        h1, h2, h3 {
            color: #1a237e;
            margin-bottom: 14px;
        }

        h1 { font-size: 1.6rem; }
        h2 { font-size: 1.25rem; margin-top: 26px; }
        h3 { font-size: 1.05rem; margin-top: 18px; }

        p { margin-bottom: 10px; line-height: 1.6; }

        /* Campos de formulário */
        label {
            display: block;
            margin-bottom: 4px;
            font-weight: bold;
            font-size: 0.9rem;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #bbb;
            border-radius: 4px;
            font-size: 0.9rem;
            margin-bottom: 14px;
        }

        input[type="submit"],
        button {
            background-color: #1a237e;
            color: #fff;
            border: none;
            padding: 10px 24px;
            font-size: 0.95rem;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 6px;
        }

        input[type="submit"]:hover,
        button:hover {
            background-color: #283593;
        }

        /* Separador entre alunos no formulário */
        .aluno-bloco {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 18px;
            background-color: #fafafa;
        }

        /* Tabela do relatório */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            font-size: 0.88rem;
        }

        th {
            background-color: #1a237e;
            color: #fff;
            padding: 9px 10px;
            text-align: left;
        }

        td {
            padding: 8px 10px;
            border-bottom: 1px solid #e0e0e0;
        }

        tr:nth-child(even) { background-color: #f5f5f5; }

        /* Caixas coloridas de situação acadêmica */
        .aprovado    { color: #2e7d32; font-weight: bold; }
        .recuperacao { color: #e65100; font-weight: bold; }
        .reprovado   { color: #c62828; font-weight: bold; }

        /* Caixa de resumo estatístico */
        .resumo {
            background-color: #e8eaf6;
            border-left: 4px solid #1a237e;
            padding: 16px 20px;
            border-radius: 4px;
            margin-top: 20px;
        }

        .resumo p { margin-bottom: 6px; }

        /* Mensagem automática de desempenho */
        .mensagem {
            margin-top: 18px;
            padding: 14px 18px;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: bold;
        }

        .msg-otimo    { background-color: #c8e6c9; color: #1b5e20; }
        .msg-bom      { background-color: #fff9c4; color: #f57f17; }
        .msg-alerta   { background-color: #ffccbc; color: #bf360c; }

        /* Link para voltar */
        .btn-voltar {
            display: inline-block;
            margin-top: 24px;
            background-color: #546e7a;
            color: #fff;
            padding: 9px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn-voltar:hover { background-color: #455a64; }

        hr { border: none; border-top: 1px solid #e0e0e0; margin: 22px 0; }
    </style>
</head>
<body>
<div class="container">

<?php
/* ==============================================================
 *  ETAPA 1 – NENHUM FORMULÁRIO FOI ENVIADO AINDA
 *  Exibe o primeiro formulário: nome da turma e qtd de alunos
 * ============================================================== */
if (!isset($_POST['etapa'])) {
?>
    <h1>Sistema de Análise de Turma Escolar</h1>
    <p>Informe o nome da turma e a quantidade de alunos para iniciar o cadastro.</p>

    <!-- Formulário da Etapa 1: dados iniciais da turma -->
    <form method="POST" action="">

        <!-- Campo: nome da turma -->
        <label for="nome_turma">Nome da turma:</label>
        <input type="text" name="nome_turma" id="nome_turma"
               placeholder="Ex.: 3°A – Desenvolvimento de Sistemas" required>

        <!-- Campo: quantidade de alunos (mínimo 1, máximo 50) -->
        <label for="qtd_alunos">Quantidade de alunos:</label>
        <input type="number" name="qtd_alunos" id="qtd_alunos"
               min="1" max="50" placeholder="Ex.: 10" required>

        <!-- Campo oculto que controla em qual etapa estamos -->
        <input type="hidden" name="etapa" value="1">

        <input type="submit" value="Próximo →">
    </form>

<?php
/* ==============================================================
 *  ETAPA 2 – USUÁRIO INFORMOU TURMA E QUANTIDADE
 *  Gera dinamicamente os campos de cada aluno
 * ============================================================== */
} elseif ($_POST['etapa'] == '1') {

    /* Recupera e sanitiza os dados da etapa anterior */
    $nome_turma = htmlspecialchars(trim($_POST['nome_turma']));
    $qtd_alunos = (int) $_POST['qtd_alunos'];

    /* Validação básica: impede valores absurdos */
    if ($qtd_alunos < 1) {
        $qtd_alunos = 1;
    }

    if ($qtd_alunos > 50) {
        $qtd_alunos = 50;
    }
?>
    <h1>Cadastro de Notas – <?php echo $nome_turma; ?></h1>
    <p>Preencha os dados de cada aluno abaixo.</p>

    <!-- Formulário da Etapa 2: notas de cada aluno -->
    <form method="POST" action="">

        <!-- Campos ocultos que carregam os dados para a próxima etapa -->
        <input type="hidden" name="etapa"      value="2">
        <input type="hidden" name="nome_turma" value="<?php echo $nome_turma; ?>">
        <input type="hidden" name="qtd_alunos" value="<?php echo $qtd_alunos; ?>">

        <?php
        /* ----------------------------------------------------------
         *  Loop: gera um bloco de campos para cada aluno
         *  A variável $i começa em 1 e vai até a quantidade informada
         * ---------------------------------------------------------- */
        $i = 1;
        while ($i <= $qtd_alunos) {
        ?>
            <!-- Bloco visual de cada aluno -->
            <div class="aluno-bloco">
                <h3>Aluno <?php echo $i; ?></h3>

                <!-- Nome do aluno -->
                <label>Nome:</label>
                <input type="text"
                       name="nome_aluno[]"
                       placeholder="Nome completo do aluno" required>

                <!-- Nota da Prova 1 (0 a 10) -->
                <label>Nota – Prova 1:</label>
                <input type="number"
                       name="nota1[]"
                       min="0" max="10" step="0.1"
                       placeholder="0.0 – 10.0" required>

                <!-- Nota da Prova 2 (0 a 10) -->
                <label>Nota – Prova 2:</label>
                <input type="number"
                       name="nota2[]"
                       min="0" max="10" step="0.1"
                       placeholder="0.0 – 10.0" required>

                <!-- Nota do Trabalho (0 a 10) -->
                <label>Nota – Trabalho:</label>
                <input type="number"
                       name="nota_trab[]"
                       min="0" max="10" step="0.1"
                       placeholder="0.0 – 10.0" required>
            </div>
        <?php
            $i = $i + 1; /* incrementa o contador do loop */
        } /* fim do while */
        ?>

        <input type="submit" value="Gerar Relatório →">
    </form>

<?php
/* ==============================================================
 *  ETAPA 3 – PROCESSAMENTO E EXIBIÇÃO DO RELATÓRIO
 *  Todos os dados foram enviados; calculamos e exibimos tudo
 * ============================================================== */
} elseif ($_POST['etapa'] == '2') {

    /* ----------------------------------------------------------
     *  Recupera os dados enviados pelo formulário
     * ---------------------------------------------------------- */
    $nome_turma  = htmlspecialchars(trim($_POST['nome_turma']));
    $qtd_alunos  = (int) $_POST['qtd_alunos'];

    /* Arrays paralelos com os dados de cada aluno */
    $nomes      = $_POST['nome_aluno'];
    $notas1     = $_POST['nota1'];
    $notas2     = $_POST['nota2'];
    $notas_trab = $_POST['nota_trab'];

    /* ----------------------------------------------------------
     *  Variáveis acumuladoras – serão preenchidas no loop
     * ---------------------------------------------------------- */
    $soma_medias     = 0;     /* soma de todas as médias individuais   */
    $soma_todas_notas = 0;    /* soma bruta de todas as notas lançadas */
    $qtd_aprovados   = 0;
    $qtd_recuperacao = 0;
    $qtd_reprovados  = 0;

    /* Maior e menor média da turma – inicializadas com extremos */
    $maior_media = -1;
    $menor_media = 11;

    /* Array que guardará os resultados calculados de cada aluno */
    $resultados = array();

    /* ----------------------------------------------------------
     *  LOOP PRINCIPAL – processa aluno por aluno
     * ---------------------------------------------------------- */
    $i = 0;
    while ($i < $qtd_alunos) {

        /* Lê as notas do aluno atual e converte para float */
        $n1   = (float) $notas1[$i];
        $n2   = (float) $notas2[$i];
        $ntrab = (float) $notas_trab[$i];
        $nome = htmlspecialchars(trim($nomes[$i]));

        /* ---------------------------------------------------
         *  Cálculos individuais do aluno
         * --------------------------------------------------- */

        /* 1. Média aritmética das 3 avaliações */
        $media = ($n1 + $n2 + $ntrab) / 3;

        /* 2. Raiz quadrada da soma das três notas  (função nativa sqrt) */
        $raiz_soma = sqrt($n1 + $n2 + $ntrab);

        /* 3. Diferença absoluta entre a maior e a menor nota
              abs() retorna sempre valor positivo                      */
        $maior_nota_aluno = $n1;
        if ($n2 > $maior_nota_aluno) {
            $maior_nota_aluno = $n2;
        }
        if ($ntrab > $maior_nota_aluno) {
            $maior_nota_aluno = $ntrab;
        }

        $menor_nota_aluno = $n1;
        if ($n2 < $menor_nota_aluno) {
            $menor_nota_aluno = $n2;
        }
        if ($ntrab < $menor_nota_aluno) {
            $menor_nota_aluno = $ntrab;
        }

        $diferenca_absoluta = abs($maior_nota_aluno - $menor_nota_aluno);

        /* 4. Situação acadêmica baseada na média individual */
        if ($media >= 7.0) {
            $situacao       = "Aprovado";
            $classe_css     = "aprovado";
            $qtd_aprovados  = $qtd_aprovados + 1;
        } elseif ($media >= 5.0) {
            $situacao        = "Recuperação";
            $classe_css      = "recuperacao";
            $qtd_recuperacao = $qtd_recuperacao + 1;
        } else {
            $situacao       = "Reprovado";
            $classe_css     = "reprovado";
            $qtd_reprovados = $qtd_reprovados + 1;
        }

        /* ---------------------------------------------------
         *  Atualiza os acumuladores da turma
         * --------------------------------------------------- */
        $soma_medias      = $soma_medias + $media;
        $soma_todas_notas = $soma_todas_notas + $n1 + $n2 + $ntrab;

        /* Verifica se a média atual é a maior ou menor da turma */
        if ($media > $maior_media) {
            $maior_media = $media;
        }
        if ($media < $menor_media) {
            $menor_media = $media;
        }

        /* Salva todos os dados calculados no array de resultados */
        $resultados[$i] = array(
            'nome'        => $nome,
            'n1'          => $n1,
            'n2'          => $n2,
            'ntrab'       => $ntrab,
            'media'       => $media,
            'raiz_soma'   => $raiz_soma,
            'dif_abs'     => $diferenca_absoluta,
            'situacao'    => $situacao,
            'classe_css'  => $classe_css
        );

        $i = $i + 1; /* avança para o próximo aluno */
    } /* fim do while principal */

    /* ----------------------------------------------------------
     *  Cálculos gerais da turma (feitos após o loop)
     * ---------------------------------------------------------- */

    /* Média geral = soma de todas as médias individuais / total de alunos */
    $media_geral = $soma_medias / $qtd_alunos;

    /* Percentual de aprovação = (aprovados / total) × 100 */
    $percentual_aprovacao = ($qtd_aprovados / $qtd_alunos) * 100;

    /* ----------------------------------------------------------
     *  EXIBIÇÃO DO RELATÓRIO
     * ---------------------------------------------------------- */
?>
    <h1>Relatório da Turma – <?php echo $nome_turma; ?></h1>
    <p>Total de alunos: <strong><?php echo $qtd_alunos; ?></strong></p>

    <!-- ============ TABELA INDIVIDUAL ============ -->
    <h2>Desempenho Individual dos Alunos</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nome</th>
                <th>Prova 1</th>
                <th>Prova 2</th>
                <th>Trabalho</th>
                <th>Média</th>
                <th>√(soma notas)</th>
                <th>Dif. Absoluta</th>
                <th>Situação</th>
            </tr>
        </thead>
        <tbody>
        <?php
        /* Loop para imprimir cada linha da tabela */
        $j = 0;
        while ($j < $qtd_alunos) {
            $r = $resultados[$j]; /* atalho para o array do aluno $j */
        ?>
            <tr>
                <td><?php echo $j + 1; ?></td>
                <td><?php echo $r['nome']; ?></td>
                <td><?php echo number_format($r['n1'],    1, ',', '.'); ?></td>
                <td><?php echo number_format($r['n2'],    1, ',', '.'); ?></td>
                <td><?php echo number_format($r['ntrab'], 1, ',', '.'); ?></td>
                <td><?php echo number_format($r['media'], 2, ',', '.'); ?></td>
                <td><?php echo number_format($r['raiz_soma'], 4, ',', '.'); ?></td>
                <td><?php echo number_format($r['dif_abs'],   2, ',', '.'); ?></td>
                <td class="<?php echo $r['classe_css']; ?>">
                    <?php echo $r['situacao']; ?>
                </td>
            </tr>
        <?php
            $j = $j + 1;
        } /* fim do while da tabela */
        ?>
        </tbody>
    </table>

    <!-- ============ RESUMO ESTATÍSTICO DA TURMA ============ -->
    <div class="resumo">
        <h2>Resumo Estatístico da Turma</h2>

        <p><strong>Média geral da turma:</strong>
            <?php echo number_format($media_geral, 2, ',', '.'); ?></p>

        <p><strong>Maior média individual:</strong>
            <?php echo number_format($maior_media, 2, ',', '.'); ?></p>

        <p><strong>Menor média individual:</strong>
            <?php echo number_format($menor_media, 2, ',', '.'); ?></p>

        <p><strong>Soma total de todas as notas lançadas:</strong>
            <?php echo number_format($soma_todas_notas, 2, ',', '.'); ?></p>

        <hr>

        <p><strong>Alunos aprovados:</strong>    <?php echo $qtd_aprovados; ?></p>
        <p><strong>Alunos em recuperação:</strong> <?php echo $qtd_recuperacao; ?></p>
        <p><strong>Alunos reprovados:</strong>    <?php echo $qtd_reprovados; ?></p>

        <p><strong>Percentual de aprovação:</strong>
            <?php echo number_format($percentual_aprovacao, 1, ',', '.'); ?>%</p>
    </div>

    <!-- ============ MENSAGEM AUTOMÁTICA DE DESEMPENHO ============ -->
    <?php
    /* Classifica o desempenho geral da turma pelo percentual de aprovação */
    if ($percentual_aprovacao >= 80) {
    ?>
        <div class="mensagem msg-otimo">
            ✔ Desempenho excelente! Mais de 80% da turma está aprovada.
        </div>
    <?php
    } elseif ($percentual_aprovacao >= 50) {
    ?>
        <div class="mensagem msg-bom">
            ⚠ Desempenho regular. Entre 50% e 79% da turma aprovada.
            Atenção aos alunos em recuperação.
        </div>
    <?php
    } else {
    ?>
        <div class="mensagem msg-alerta">
            ✘ Situação crítica! Menos da metade da turma está aprovada.
            Recomenda-se revisão do conteúdo.
        </div>
    <?php
    }
    ?>

    <!-- Link para reiniciar o sistema -->
    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn-voltar">
        ← Novo cadastro
    </a>

<?php
} /* fim do elseif etapa 2 */
?>

</div><!-- fim .container -->
</body>
</html>
