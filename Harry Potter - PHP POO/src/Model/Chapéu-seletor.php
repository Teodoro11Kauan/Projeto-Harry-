<?php
declare(strict_types=1);

namespace App\Model;

class ChapeuSeletor {
    public array $casas = ['Grifinória', 'Sonserina', 'Corvinal', 'Lufa-Lufa'];

    public function selecionarCasa(string $aluno): void {
        $casa = $this->casas[array_rand($this->casas)];
        echo "Aluno(a) {$aluno} foi selecionado(a) para a casa: <b>{$casa}</b><br>";
    }
}

function enviarEmail(array $alunos, string $dumbledore, string &$resposta): bool {

    $resposta = "Email enviado com sucesso!";
    return true;
}



$alunosAceitos = ['Harry Potter', 'Hermione Granger', 'Ron Weasley'];
$dumbledore = 'Alvo Dumbledore';
$resposta = '';

if (enviarEmail($alunosAceitos, $dumbledore, $resposta)) {
    echo "<h2>A cerimônia de seleção vai começar...</h2><br>";

    $chapeu = new ChapeuSeletor();

    foreach ($alunosAceitos as $aluno) {
        $chapeu->selecionarCasa($aluno);
    }
} else {
    echo "Falha ao enviar email: {$resposta}";
}

?>
