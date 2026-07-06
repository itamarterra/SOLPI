<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

/**
 * Detecta automaticamente o tipo de cada coluna de um Excel/CSV.
 */
final class ColumnDetector
{
    private const MAP = [
        'empresa'    => ['empresa','company','entidade','entity','cliente','client','org'],
        'nome'       => ['nome','name','firstname','primeiro'],
        'sobrenome'  => ['sobrenome','surname','lastname','last name','realname'],
        'login'      => ['usuario','usuário','user','login','username'],
        'senha'      => ['senha','password','pass','pwd'],
        'telefone'   => ['telefone','phone','fone','tel'],
        'celular'    => ['cel','celular','mobile','whatsapp','whatsapp'],
        'email'      => ['email','e-mail','mail','correio'],
        'problema'   => ['problema','descri','description','chamado','ticket','assunto','subject','ocorrencia','titulo','título'],
        'prioridade' => ['prioridade','priority','urgencia','urgência','sla'],
        'department' => ['departamento','department','dept','setor','área','area'],
        'position'   => ['cargo','position','função','funcao','role'],
        'categoria'  => ['categoria','category','tipo','type'],
        'supervisor' => ['supervisor','chefe','gestor','manager'],
        'serie'      => ['serie','serial','patrimonio','tombamento','tag'],
        'local'      => ['local','location','unidade','filial','site'],
    ];

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

    public function matchField(string $columnName): ?string
    {
        $normalized = mb_strtolower(preg_replace('/[^a-zA-ZÀ-ú0-9\s]/', '', $columnName) ?? $columnName);
        foreach (self::MAP as $field => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($normalized, $keyword)) return $field;
            }
        }
        return null;
    }
}
