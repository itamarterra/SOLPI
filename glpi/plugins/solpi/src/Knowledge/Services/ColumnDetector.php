<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

/**
 * Detecta automaticamente o tipo de cada coluna de um Excel/CSV.
 *
 * Mapeia nomes de colunas (em PT ou EN) para campos SOLPI/GLPI:
 *   empresa, nome, telefone, email, problema, prioridade, categoria, etc.
 */
final class ColumnDetector
{
    /**
     * Mapa de palavras-chave → campo SOLPI.
     * Ordem importa: mais específico primeiro.
     */
    private const MAP = [
        'empresa'    => ['empresa','company','razao','razão','cliente','client','cnpj','org'],
        'nome'       => ['nome','name','solicitante','requester','contato','contact','usuario','usuário','user'],
        'telefone'   => ['telefone','phone','fone','cel','celular','whatsapp','tel','mobile'],
        'email'      => ['email','e-mail','mail','correio'],
        'problema'   => ['problema','descri','description','chamado','ticket','assunto','subject','ocorrencia','ocorrência','solicitacao','solicitação','title','titulo','título'],
        'prioridade' => ['prioridade','priority','urgencia','urgência','nivel','nível','sla'],
        'categoria'  => ['categoria','category','tipo','type','setor','departamento','dept'],
        'serie'      => ['serie','serial','patrimonio','patrimônio','tombamento','tag','numero','número','num'],
        'local'      => ['local','location','unidade','filial','site','endereco','endereço'],
        'data'       => ['data','date','abertura','criado','created','prazo','deadline'],
        'tecnico'    => ['tecnico','técnico','responsavel','responsável','assigned','atribuido','atribuído'],
        'solucao'    => ['solucao','solução','resolucao','resolução','solution','resposta'],
        'status'     => ['status','estado','situacao','situação','state'],
    ];

    /**
     * Recebe array de nomes de colunas e retorna mapeamento:
     * coluna_original => campo_solpi
     *
     * @param  array<string> $headers Nomes das colunas do Excel
     * @return array<string, string>  ['Nome do Cliente' => 'nome', 'Empresa' => 'empresa', ...]
     */
    public function detect(array $headers): array
    {
        $mapping = [];

        foreach ($headers as $header) {
            $field = $this->matchField($header);
            if ($field !== null) {
                $mapping[$header] = $field;
            }
        }

        return $mapping;
    }

    /**
     * Retorna o campo SOLPI que melhor corresponde ao nome da coluna,
     * ou null se não reconhecido.
     */
    public function matchField(string $columnName): ?string
    {
        $normalized = mb_strtolower(
            preg_replace('/[^a-zA-ZÀ-ú0-9\s]/', '', $columnName) ?? $columnName
        );

        foreach (self::MAP as $field => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($normalized, $keyword)) {
                    return $field;
                }
            }
        }

        return null;
    }
}
