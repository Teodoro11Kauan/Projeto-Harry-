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
    // Simulação de envio de email
    $resposta = "Email enviado com sucesso!";
    return true;
}
