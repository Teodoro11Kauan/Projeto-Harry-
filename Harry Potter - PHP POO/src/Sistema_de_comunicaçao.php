<?php

// Classes básicas
abstract class Usuario {
    public function __construct(protected int $id, protected string $nome, protected string $email, protected string $telefone, protected string $papel) {}
    public function getId(): int { return $this->id; }
    public function getNome(): string { return $this->nome; }
    public function getEmail(): string { return $this->email; }
}

class Aluno extends Usuario {
    public function __construct(int $id, string $nome, string $email, string $telefone, string $casa, int $anoLetivo, private array $disciplinasMatriculadasIds = []) { parent::__construct($id, $nome, $email, $telefone, 'Aluno'); }
    public function adicionarDisciplinaMatriculada(int $disciplinaId): void { $this->disciplinasMatriculadasIds[] = $disciplinaId; }
    public function getDisciplinasMatriculadasIds(): array { return $this->disciplinasMatriculadasIds; }
}

class Professor extends Usuario {
    public function __construct(int $id, string $nome, string $email, string $telefone, private array $disciplinasLecionadasIds = []) { parent::__construct($id, $nome, $email, $telefone, 'Professor'); }
    public function adicionarDisciplinaLecionada(int $disciplinaId): void { $this->disciplinasLecionadasIds[] = $disciplinaId; }
}

class Administrador extends Usuario {
    public function __construct(int $id, string $nome, string $email, string $telefone) { parent::__construct($id, $nome, $email, $telefone, 'Administrador'); }
}

// Mensagem e Canais
class Mensagem {
    public function __construct(public int $id, public int $remetenteId, public string $titulo, public string $conteudo, public string $tipo, public array $destinatariosIds, public array $canaisPreferenciais = [], public ?DateTime $dataHoraAgendamento = null) {}
}

interface CanalComunicacao {
    public function enviar(Mensagem $mensagem, Usuario $destinatario): bool;
    public function getNomeCanal(): string;
}

class NotificacaoAluno {
    public function __construct(public int $id, public int $alunoId, public int $mensagemOriginalId, public string $titulo, public string $conteudo, public string $tipo, public DateTime $dataHoraRecebimento, public bool $lida) {}
    public function marcarComoLida(): void { $this->lida = true; }
}

class NotificacaoRepository {
    private array $notificacoes = []; private int $nextId = 1;
    public function salvar(NotificacaoAluno $notif): void { if ($notif->id === 0) { $notif->id = $this->nextId++; } $this->notificacoes[$notif->id] = $notif; }
    public function findByAlunoId(int $alunoId): array { return array_values(array_filter($this->notificacoes, fn($n) => $n->alunoId === $alunoId)); }
    public function findById(int $id): ?NotificacaoAluno { return $this->notificacoes[$id] ?? null; }
}

class NotificacaoInterna implements CanalComunicacao {
    public function __construct(private NotificacaoRepository $notificacaoRepo) {}
    public function enviar(Mensagem $mensagem, Usuario $destinatario): bool {
        if (!$destinatario instanceof Aluno) return false;
        $this->notificacaoRepo->salvar(new NotificacaoAluno(0, $destinatario->getId(), $mensagem->id, $mensagem->titulo, $mensagem->conteudo, $mensagem->tipo, new DateTime(), false));
        return true;
    }
    public function getNomeCanal(): string { return "NotificacaoInterna"; }
}

class Email implements CanalComunicacao {
    public function enviar(Mensagem $mensagem, Usuario $destinatario): bool { return !empty($destinatario->getEmail()); }
    public function getNomeCanal(): string { return "Email"; }
}

class PreferenciasComunicacao {
    public function __construct(public int $usuarioId, private array $preferencias = []) {
        $this->preferencias = array_merge(['Urgente' => ['NotificacaoInterna', 'Email'], 'Informativo' => ['NotificacaoInterna', 'Email']], $preferencias);
    }
    public function getCanaisParaTipo(string $tipo): array { return $this->preferencias[$tipo] ?? []; }
}

// Sistema de Comunicação
class SistemaComunicacao {
    private array $canais = []; private array $usuarios = []; private array $preferencias = []; private array $agendadas = [];
    public function __construct(private NotificacaoRepository $notificacaoRepo) {}

    public function adicionarCanal(CanalComunicacao $canal): void { $this->canais[$canal->getNomeCanal()] = $canal; }
    public function registrarUsuario(Usuario $usuario): void { $this->usuarios[$usuario->getId()] = $usuario; }
    public function registrarPreferencias(PreferenciasComunicacao $prefs): void { $this->preferencias[$prefs->usuarioId] = $prefs; }
    public function getTodosUsuariosIds(): array { return array_keys($this->usuarios); }
    public function getUsuario(int $id): ?Usuario { return $this->usuarios[$id] ?? null; }

    public function enviarMensagem(Mensagem $msg): void {
        if ($msg->dataHoraAgendamento !== null) { $this->agendadas[] = $msg; echo "AGENDADO: '{$msg->titulo}' para {$msg->dataHoraAgendamento->format('d/m/Y H:i:s')}\n"; return; }
        echo "ENVIANDO IMEDIATAMENTE: '{$msg->titulo}'\n"; $this->processarEnvioReal($msg);
    }

    public function processarMensagensAgendadas(): void {
        $agora = new DateTime(); echo "PROCESSANDO AGENDAMENTOS...\n";
        $this->agendadas = array_values(array_filter($this->agendadas, function(Mensagem $msg) use ($agora) {
            if ($msg->dataHoraAgendamento <= $agora) { echo "  Enviando agendada: '{$msg->titulo}'\n"; $this->processarEnvioReal($msg); return false; } return true;
        }));
    }

    private function processarEnvioReal(Mensagem $msg): void {
        foreach ($msg->destinatariosIds as $destId) {
            $dest = $this->usuarios[$destId] ?? null; if (!$dest) continue;
            $prefs = $this->preferencias[$destId] ?? null;
            $canaisDisponiveis = $prefs ? $prefs->getCanaisParaTipo($msg->tipo) : array_keys($this->canais);
            $canaisFinais = empty($msg->canaisPreferenciais) ? $canaisDisponiveis : array_intersect($msg->canaisPreferenciais, $canaisDisponiveis);
            foreach ($canaisFinais as $canalNome) { if (isset($this->canais[$canalNome])) { $this->canais[$canalNome]->enviar($msg, $dest); } }
        }
    }

    public function getNotificacoesParaAluno(int $alunoId): array { return $this->notificacaoRepo->findByAlunoId($alunoId); }
    public function marcarNotificacaoComoLida(int $notifId): void {
        $notif = $this->notificacaoRepo->findById($notifId); if ($notif) { $notif->marcarComoLida(); $this->notificacaoRepo->salvar($notif); }
    }
}

// --- Cenário de Uso ---

$repo = new NotificacaoRepository();
$sistema = new SistemaComunicacao($repo);
$sistema->adicionarCanal(new NotificacaoInterna($repo));
$sistema->adicionarCanal(new Email());

// Personagens (ID, Nome, Email, Telefone, Casa/Ano, Disciplinas)
$dumbledore = new Administrador(1, "Alvo Dumbledore", "dumby@hogwarts.com", "");
$mcgonagall = new Professor(2, "Minerva McGonagall", "mcgonagall@hogwarts.com", ""); $mcgonagall->adicionarDisciplinaLecionada(101);
$harry = new Aluno(3, "Harry Potter", "harry@hogwarts.com", "", "Grifinória", 1); $harry->adicionarDisciplinaMatriculada(101);
$hermione = new Aluno(4, "Hermione Granger", "hermione@hogwarts.com", "", "Grifinória", 1); $hermione->adicionarDisciplinaMatriculada(101);
$ron = new Aluno(5, "Rony Weasley", "ron@hogwarts.com", "", "Grifinória", 1); $ron->adicionarDisciplinaMatriculada(101);
$draco = new Aluno(6, "Draco Malfoy", "draco@hogwarts.com", "", "Sonserina", 1); $draco->adicionarDisciplinaMatriculada(101);

$sistema->registrarUsuario($dumbledore); $sistema->registrarUsuario($mcgonagall);
$sistema->registrarUsuario($harry); $sistema->registrarUsuario($hermione);
$sistema->registrarUsuario($ron); $sistema->registrarUsuario($draco);

// Preferências
$sistema->registrarPreferencias(new PreferenciasComunicacao($harry->getId()));
$sistema->registrarPreferencias(new PreferenciasComunicacao($hermione->getId()));
$sistema->registrarPreferencias(new PreferenciasComunicacao($ron->getId()));
$sistema->registrarPreferencias(new PreferenciasComunicacao($draco->getId()));
$sistema->registrarPreferencias(new PreferenciasComunicacao($mcgonagall->getId()));

$todosUsuariosIds = $sistema->getTodosUsuariosIds();
$alunosIds = [$harry->getId(), $hermione->getId(), $ron->getId(), $draco->getId()];

echo "--- COMUNICADOS DE HOGWARTS ---\n\n";

// Mensagem imediata
$sistema->enviarMensagem(new Mensagem(101, $dumbledore->getId(), "Alerta de Aula Extra", "Aula extra de Transfiguração na sala 3B hoje.", "Informativo", $alunosIds));

// Agendar aviso de férias (futuro distante)
$dataFerias = new DateTime('2025-07-01 09:00:00');
$sistema->enviarMensagem(new Mensagem(301, $dumbledore->getId(), "Férias de Verão", "Boas férias a todos!", "Informativo", $todosUsuariosIds, [], $dataFerias));

// Agendar aviso de segurança (futuro próximo: 3 segundos à frente)
$dataSeguranca = (new DateTime())->modify('+3 seconds');
$sistema->enviarMensagem(new Mensagem(302, $dumbledore->getId(), "Aviso de Segurança Urgente", "Ronda noturna, fiquem em seus dormitórios.", "Urgente", $alunosIds, [], $dataSeguranca));

echo "\nSimulando tempo passando...\n";
sleep(4); // Aguarda para que o aviso de segurança seja "ativado"

$sistema->processarMensagensAgendadas(); // Executa o "cron job"

echo "\n--- Notificações dos Alunos ---\n";
foreach ([$harry, $hermione, $ron, $draco] as $aluno) {
    echo "  {$aluno->getNome()}:\n";
    $notifs = $sistema->getNotificacoesParaAluno($aluno->getId());
    if (empty($notifs)) { echo "    Nenhuma notificação.\n"; }
    foreach ($notifs as $notif) {
        $status = $notif->lida ? "Lida" : "Não Lida";
        echo "    - '{$notif->titulo}' ({$notif->tipo}) - {$status} em {$notif->dataHoraRecebimento->format('H:i:s')}\n";
        // Exemplo: Marcar a primeira notificação não lida como lida
        if (!$notif->lida) { $sistema->marcarNotificacaoComoLida($notif->id); }
    }
}
echo "\n";
echo "Verificando novamente as notificações do Harry após marcar uma como lida:\n";
$notifsHarryAtualizadas = $sistema->getNotificacoesParaAluno($harry->getId());
foreach ($notifsHarryAtualizadas as $notif) {
    $status = $notif->lida ? "Lida" : "Não Lida";
    echo "    - '{$notif->titulo}' ({$notif->tipo}) - {$status} em {$notif->dataHoraRecebimento->format('H:i:s')}\n";
}

?>
