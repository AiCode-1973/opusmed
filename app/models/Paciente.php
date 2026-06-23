<?php

require_once __DIR__ . '/../../config/database.php';

class Paciente {

    private $db;

    public static $sexos = ['M' => 'Masculino', 'F' => 'Feminino', 'I' => 'Intersexo'];

    public static $estadosCivis = [
        'solteiro'       => 'Solteiro(a)',
        'casado'         => 'Casado(a)',
        'divorciado'     => 'Divorciado(a)',
        'viuvo'          => 'Viúvo(a)',
        'uniao_estavel'  => 'União estável',
        'outros'         => 'Outros',
    ];

    public static $tiposSanguineos = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];

    public static $statusList = [
        'ativo'        => 'Ativo',
        'inativo'      => 'Inativo',
        'obito'        => 'Óbito',
        'transferido'  => 'Transferido',
    ];

    public static $origenscadastro = [
        'recepcao'      => 'Recepção',
        'internet'      => 'Internet',
        'transferencia' => 'Transferência',
        'outros'        => 'Outros',
    ];

    public static $preferenciasContato = [
        'telefone' => 'Telefone',
        'whatsapp' => 'WhatsApp',
        'email'    => 'E-mail',
        'sms'      => 'SMS',
    ];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ── Listagem ─────────────────────────────────────────────

    public function listarTodos(bool $somenteAtivos = true): array {
        $where = $somenteAtivos ? 'WHERE p.ativo = 1' : '';
        return $this->db->query("
            SELECT p.id, p.prontuario, p.nome, p.nome_social, p.cpf,
                   p.data_nascimento, p.telefone, p.status, p.ativo,
                   p.created_at, c.nome AS convenio_nome
            FROM pacientes p
            LEFT JOIN convenios c ON c.id = p.convenio_id
            $where
            ORDER BY p.nome
        ")->fetchAll();
    }

    public function listarComFiltro(string $busca = '', string $status = ''): array {
        $params = [];
        $where  = ['1=1'];

        if ($busca !== '') {
            $where[] = '(p.nome LIKE :busca OR p.cpf LIKE :busca2 OR p.prontuario LIKE :busca3 OR p.cns LIKE :busca4)';
            $like = '%' . $busca . '%';
            $params[':busca']  = $like;
            $params[':busca2'] = $like;
            $params[':busca3'] = $like;
            $params[':busca4'] = $like;
        }

        if ($status !== '') {
            $where[] = 'p.status = :status';
            $params[':status'] = $status;
        }

        $sql = '
            SELECT p.id, p.prontuario, p.nome, p.nome_social, p.cpf,
                   p.data_nascimento, p.telefone, p.status, p.ativo,
                   p.created_at, c.nome AS convenio_nome
            FROM pacientes p
            LEFT JOIN convenios c ON c.id = p.convenio_id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY p.nome
        ';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function buscarPorId(int $id) {
        $stmt = $this->db->prepare('
            SELECT p.*, c.nome AS convenio_nome,
                   u.nome AS cadastrado_por_nome
            FROM pacientes p
            LEFT JOIN convenios c ON c.id = p.convenio_id
            LEFT JOIN usuarios  u ON u.id = p.cadastrado_por
            WHERE p.id = ?
        ');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function proximoProntuario(): string {
        $max = (int) $this->db->query('SELECT COALESCE(MAX(id), 0) + 1 FROM pacientes')->fetchColumn();
        return 'PAC-' . str_pad($max, 6, '0', STR_PAD_LEFT);
    }

    // ── Persistência ─────────────────────────────────────────

    private function _params(array $d): array {
        return [
            // Tab 1
            ':prontuario'               => $d['prontuario']               ?? null,
            ':nome'                     => $d['nome'],
            ':nome_social'              => $d['nome_social']              ?? null,
            ':data_nascimento'          => $d['data_nascimento']          ?? null,
            ':sexo_biologico'           => $d['sexo_biologico']           ?? null,
            ':genero'                   => $d['genero']                   ?? null,
            ':cpf'                      => $d['cpf']                      ?? null,
            ':rg'                       => $d['rg']                       ?? null,
            ':rg_orgao'                 => $d['rg_orgao']                 ?? null,
            ':cns'                      => $d['cns']                      ?? null,
            ':nome_mae'                 => $d['nome_mae']                 ?? null,
            ':nome_pai'                 => $d['nome_pai']                 ?? null,
            ':estado_civil'             => $d['estado_civil']             ?? null,
            ':nacionalidade'            => $d['nacionalidade']            ?? null,
            ':naturalidade'             => $d['naturalidade']             ?? null,
            ':foto'                     => $d['foto']                     ?? null,
            // Tab 2
            ':telefone'                 => $d['telefone']                 ?? null,
            ':telefone2'                => $d['telefone2']                ?? null,
            ':whatsapp'                 => $d['whatsapp']                 ?? null,
            ':email'                    => $d['email']                    ?? null,
            ':preferencia_contato'      => $d['preferencia_contato']      ?? 'telefone',
            ':aceite_mensagens'         => (int) ($d['aceite_mensagens']  ?? 0),
            // Tab 3
            ':cep'                      => $d['cep']                      ?? null,
            ':logradouro'               => $d['logradouro']               ?? null,
            ':numero'                   => $d['numero']                   ?? null,
            ':complemento'              => $d['complemento']              ?? null,
            ':bairro'                   => $d['bairro']                   ?? null,
            ':cidade'                   => $d['cidade']                   ?? null,
            ':estado_uf'                => $d['estado_uf']                ?? null,
            ':referencia'               => $d['referencia']               ?? null,
            // Tab 4
            ':resp_nome'                => $d['resp_nome']                ?? null,
            ':resp_parentesco'          => $d['resp_parentesco']          ?? null,
            ':resp_cpf'                 => $d['resp_cpf']                 ?? null,
            ':resp_telefone'            => $d['resp_telefone']            ?? null,
            ':resp_email'               => $d['resp_email']               ?? null,
            ':resp_observacao'          => $d['resp_observacao']          ?? null,
            // Tab 5
            ':alergias'                 => $d['alergias']                 ?? null,
            ':doencas_preexistentes'    => $d['doencas_preexistentes']    ?? null,
            ':medicamentos_continuos'   => $d['medicamentos_continuos']   ?? null,
            ':tipo_sanguineo'           => $d['tipo_sanguineo']           ?? null,
            ':condicoes_especiais'      => $d['condicoes_especiais']      ?? null,
            ':deficiencia'              => $d['deficiencia']              ?? null,
            ':gestante'                 => (int) ($d['gestante']          ?? 0),
            ':restricao_alimentar'      => $d['restricao_alimentar']      ?? null,
            // Tab 6
            ':convenio_id'              => $d['convenio_id']              ?: null,
            ':convenio_carteirinha'     => $d['convenio_carteirinha']     ?? null,
            ':convenio_validade'        => $d['convenio_validade']        ?? null,
            ':convenio_titular'         => $d['convenio_titular']         ?? null,
            ':convenio_matricula'       => $d['convenio_matricula']       ?? null,
            ':convenio_plano'           => $d['convenio_plano']           ?? null,
            ':convenio_cod_beneficiario'=> $d['convenio_cod_beneficiario']?? null,
            // Tab 7
            ':status'                   => $d['status']                   ?? 'ativo',
            ':unidade'                  => $d['unidade']                  ?? null,
            ':origem_cadastro'          => $d['origem_cadastro']          ?? 'recepcao',
            ':observacoes'              => $d['observacoes']              ?? null,
            ':cadastrado_por'           => $d['cadastrado_por']           ?: null,
            // Tab 8
            ':lgpd_consentimento'       => (int) ($d['lgpd_consentimento']      ?? 0),
            ':lgpd_whatsapp'            => (int) ($d['lgpd_whatsapp']           ?? 0),
            ':lgpd_sms'                 => (int) ($d['lgpd_sms']                ?? 0),
            ':lgpd_email_consent'       => (int) ($d['lgpd_email_consent']      ?? 0),
            ':lgpd_data_aceite'         => $d['lgpd_data_aceite']         ?? null,
            ':lgpd_responsavel_aceite'  => $d['lgpd_responsavel_aceite']  ?? null,
            ':lgpd_finalidade'          => $d['lgpd_finalidade']          ?? null,
        ];
    }

    public function criar(array $dados): int {
        $stmt = $this->db->prepare('
            INSERT INTO pacientes (
                prontuario, nome, nome_social, data_nascimento, sexo_biologico, genero,
                cpf, rg, rg_orgao, cns, nome_mae, nome_pai, estado_civil,
                nacionalidade, naturalidade, foto,
                telefone, telefone2, whatsapp, email, preferencia_contato, aceite_mensagens,
                cep, logradouro, numero, complemento, bairro, cidade, estado_uf, referencia,
                resp_nome, resp_parentesco, resp_cpf, resp_telefone, resp_email, resp_observacao,
                alergias, doencas_preexistentes, medicamentos_continuos, tipo_sanguineo,
                condicoes_especiais, deficiencia, gestante, restricao_alimentar,
                convenio_id, convenio_carteirinha, convenio_validade, convenio_titular,
                convenio_matricula, convenio_plano, convenio_cod_beneficiario,
                `status`, unidade, origem_cadastro, observacoes, cadastrado_por,
                lgpd_consentimento, lgpd_whatsapp, lgpd_sms, lgpd_email_consent,
                lgpd_data_aceite, lgpd_responsavel_aceite, lgpd_finalidade
            ) VALUES (
                :prontuario, :nome, :nome_social, :data_nascimento, :sexo_biologico, :genero,
                :cpf, :rg, :rg_orgao, :cns, :nome_mae, :nome_pai, :estado_civil,
                :nacionalidade, :naturalidade, :foto,
                :telefone, :telefone2, :whatsapp, :email, :preferencia_contato, :aceite_mensagens,
                :cep, :logradouro, :numero, :complemento, :bairro, :cidade, :estado_uf, :referencia,
                :resp_nome, :resp_parentesco, :resp_cpf, :resp_telefone, :resp_email, :resp_observacao,
                :alergias, :doencas_preexistentes, :medicamentos_continuos, :tipo_sanguineo,
                :condicoes_especiais, :deficiencia, :gestante, :restricao_alimentar,
                :convenio_id, :convenio_carteirinha, :convenio_validade, :convenio_titular,
                :convenio_matricula, :convenio_plano, :convenio_cod_beneficiario,
                :status, :unidade, :origem_cadastro, :observacoes, :cadastrado_por,
                :lgpd_consentimento, :lgpd_whatsapp, :lgpd_sms, :lgpd_email_consent,
                :lgpd_data_aceite, :lgpd_responsavel_aceite, :lgpd_finalidade
            )
        ');
        $stmt->execute($this->_params($dados));
        $novoId = (int) $this->db->lastInsertId();

        // Auto-gera prontuário se não foi informado
        if (empty($dados['prontuario'])) {
            $pron = 'PAC-' . str_pad($novoId, 6, '0', STR_PAD_LEFT);
            $this->db->prepare('UPDATE pacientes SET prontuario = ? WHERE id = ?')->execute([$pron, $novoId]);
        }

        return $novoId;
    }

    public function atualizar(int $id, array $dados): bool {
        $params = $this->_params($dados);
        unset($params[':cadastrado_por']); // não atualiza quem criou
        $params[':id'] = $id;

        $stmt = $this->db->prepare('
            UPDATE pacientes SET
                prontuario = :prontuario, nome = :nome, nome_social = :nome_social,
                data_nascimento = :data_nascimento, sexo_biologico = :sexo_biologico, genero = :genero,
                cpf = :cpf, rg = :rg, rg_orgao = :rg_orgao, cns = :cns,
                nome_mae = :nome_mae, nome_pai = :nome_pai, estado_civil = :estado_civil,
                nacionalidade = :nacionalidade, naturalidade = :naturalidade, foto = :foto,
                telefone = :telefone, telefone2 = :telefone2, whatsapp = :whatsapp,
                email = :email, preferencia_contato = :preferencia_contato, aceite_mensagens = :aceite_mensagens,
                cep = :cep, logradouro = :logradouro, numero = :numero, complemento = :complemento,
                bairro = :bairro, cidade = :cidade, estado_uf = :estado_uf, referencia = :referencia,
                resp_nome = :resp_nome, resp_parentesco = :resp_parentesco, resp_cpf = :resp_cpf,
                resp_telefone = :resp_telefone, resp_email = :resp_email, resp_observacao = :resp_observacao,
                alergias = :alergias, doencas_preexistentes = :doencas_preexistentes,
                medicamentos_continuos = :medicamentos_continuos, tipo_sanguineo = :tipo_sanguineo,
                condicoes_especiais = :condicoes_especiais, deficiencia = :deficiencia,
                gestante = :gestante, restricao_alimentar = :restricao_alimentar,
                convenio_id = :convenio_id, convenio_carteirinha = :convenio_carteirinha,
                convenio_validade = :convenio_validade, convenio_titular = :convenio_titular,
                convenio_matricula = :convenio_matricula, convenio_plano = :convenio_plano,
                convenio_cod_beneficiario = :convenio_cod_beneficiario,
                `status` = :status, unidade = :unidade, origem_cadastro = :origem_cadastro,
                observacoes = :observacoes,
                lgpd_consentimento = :lgpd_consentimento, lgpd_whatsapp = :lgpd_whatsapp,
                lgpd_sms = :lgpd_sms, lgpd_email_consent = :lgpd_email_consent,
                lgpd_data_aceite = :lgpd_data_aceite, lgpd_responsavel_aceite = :lgpd_responsavel_aceite,
                lgpd_finalidade = :lgpd_finalidade
            WHERE id = :id
        ');
        return $stmt->execute($params);
    }

    public function toggleAtivo(int $id): bool {
        $stmt = $this->db->prepare('UPDATE pacientes SET ativo = IF(ativo=1, 0, 1) WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
