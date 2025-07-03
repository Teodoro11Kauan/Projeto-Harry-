<?php
declare(strict_types=1);

namespace App\Model;

class Professor {
    public string $nome;
    public string $email;
    public array $disciplinas = [];
    public array $cronograma = [];
    public array $atividades = [];

    public function __construct(string $nome, string $email) {
        $this->nome = $nome;
        $this->email = $email;
    }
}

class Disciplina {
    public static function adicionarDisciplina(Professor $professor, string $disciplina, string $turma): void {
        $professor->disciplinas[] = ['disciplina' => $disciplina, 'turma' => $turma];
    }
}

class Cronograma {
    public static function adicionarAula(Professor $professor, string $dia, string $horario, string $disciplina): void {
        $professor->cronograma[] = ['dia' => $dia, 'horario' => $horario, 'disciplina' => $disciplina];
    }
}

class Atividade {
    public static function adicionar(Professor $professor, string $atividade): void {
        $professor->atividades[] = $atividade;
    }
}

class Exibicao {
    public static function exibir(Professor $professor): void {
        echo "<hr>";
        echo "<h2>Professor: {$professor->nome}</h2>";
        echo "Email: {$professor->email}<br><br>";

        echo "<b>Disciplinas:</b><br>";
        foreach ($professor->disciplinas as $d) {
            echo "- {$d['disciplina']} (Turma: {$d['turma']})<br>";
        }

        echo "<br><b>Cronograma:</b><br>";
        foreach ($professor->cronograma as $aula) {
            echo "- {$aula['dia']} às {$aula['horario']}: {$aula['disciplina']}<br>";
        }

        echo "<br><b>Atividades:</b><br>";
        foreach ($professor->atividades as $atividade) {
            echo "- {$atividade}<br>";
        }

        echo "<br>";
    }
}




$professor1 = new Professor("Minerva McGonagall", "minerva@hogwarts.edu");
$professor2 = new Professor("Remus Lupin", "remus@hogwarts.edu");

Disciplina::adicionarDisciplina($professor1, "Transfiguração", "1º ano A");
Disciplina::adicionarDisciplina($professor2, "Defesa Contra as Artes das Trevas", "3º ano B");


Cronograma::adicionarAula($professor1, "Segunda-feira", "08:00", "Transfiguração");
Cronograma::adicionarAula($professor1, "Quarta-feira", "10:00", "Transfiguração");

Cronograma::adicionarAula($professor2, "Terça-feira", "09:00", "Defesa Contra as Artes das Trevas");
Cronograma::adicionarAula($professor2, "Quinta-feira", "11:00", "Defesa Contra as Artes das Trevas");


Atividade::adicionar($professor1, "Coordenação do Clube de Transformações");
Atividade::adicionar($professor2, "Organização do Clube de Duelos");


Exibicao::exibir($professor1);
Exibicao::exibir($professor2);

?>