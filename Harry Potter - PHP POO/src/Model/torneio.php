<?php


class Aluno {
    public $nome;
    public $casa;
    public $inscrito;
    public $pontuacao;

    public function __construct($nome, $casa) {
        $this->nome = $nome;
        $this->casa = $casa;
        $this->inscrito = false;
        $this->pontuacao = 0;
    }

    public function inscrever() {
        $this->inscrito = true;
    }

    public function adicionarPontuacao($pontos) {
        $this->pontuacao = $this->pontuacao + $pontos;
    }
}

class Casa {
    public $nome;
    public $pontuacaoTotal;

    public function __construct($nome) {
        $this->nome = $nome;
        $this->pontuacaoTotal = 0;
    }

    public function adicionarPontos($pontos) {
        $this->pontuacaoTotal = $this->pontuacaoTotal + $pontos;
    }
}


class Torneio {
    public $nome;
    public $desafios;
    public $alunos;

    public function __construct($nome) {
        $this->nome = $nome;
        $this->desafios = array();
        $this->alunos = array();
    }

    public function adicionarDesafio($nomeDesafio, $resultados) {
        $this->desafios[] = array(
            'nome' => $nomeDesafio,
            'resultados' => $resultados
        );
    }

    public function inscreverAluno($aluno) {
        $aluno->inscrever();
        $this->alunos[] = $aluno;
    }

    public function registrarResultados() {
        foreach ($this->desafios as $desafio) {
            foreach ($desafio['resultados'] as $nomeAluno => $pontos) {
                foreach ($this->alunos as $aluno) {
                    if ($aluno->nome == $nomeAluno) {
                        $aluno->adicionarPontuacao($pontos);
                    }
                }
            }
        }
    }
}


function mostrarRankingAlunos($alunos) {
    echo "<h3>Ranking por Aluno</h3>";


    for ($i = 0; $i < count($alunos) - 1; $i++) {
        for ($j = $i + 1; $j < count($alunos); $j++) {
            if ($alunos[$j]->pontuacao > $alunos[$i]->pontuacao) {
                $temp = $alunos[$i];
                $alunos[$i] = $alunos[$j];
                $alunos[$j] = $temp;
            }
        }
    }

    foreach ($alunos as $aluno) {
        echo $aluno->nome . " - " . $aluno->pontuacao . " pontos<br>";
    }
}

function mostrarRankingCasas($alunos) {
    $pontosCasas = array();

    foreach ($alunos as $aluno) {
        if (!isset($pontosCasas[$aluno->casa])) {
            $pontosCasas[$aluno->casa] = 0;
        }
        $pontosCasas[$aluno->casa] = $pontosCasas[$aluno->casa] + $aluno->pontuacao;
    }

    echo "<h3>Ranking por Casa</h3>";
    foreach ($pontosCasas as $casa => $pontos) {
        echo $casa . " - " . $pontos . " pontos<br>";
    }
}


$casa1 = new Casa("Grifinória");
$casa2 = new Casa("Sonserina");

$aluno1 = new Aluno("Harry Potter", "Grifinória");
$aluno2 = new Aluno("Draco Malfoy", "Sonserina");
$aluno3 = new Aluno("Hermione Granger", "Grifinória");

$torneio = new Torneio("Torneio Tribruxo");
$torneio->inscreverAluno($aluno1);
$torneio->inscreverAluno($aluno2);
$torneio->inscreverAluno($aluno3);

$torneio->adicionarDesafio("Desafio 1", array(
    "Harry Potter" => 10,
    "Draco Malfoy" => 7,
    "Hermione Granger" => 12
));

$torneio->registrarResultados();

$casa1->adicionarPontos($aluno1->pontuacao);
$casa1->adicionarPontos($aluno3->pontuacao);
$casa2->adicionarPontos($aluno2->pontuacao);

mostrarRankingAlunos(array($aluno1, $aluno2, $aluno3));
mostrarRankingCasas(array($aluno1, $aluno2, $aluno3));

?>
