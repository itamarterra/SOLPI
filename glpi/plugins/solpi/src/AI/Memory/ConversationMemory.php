<?php

declare(strict_types=1);

namespace SOLPI\AI\Memory;

final class ConversationMemory
{
    private array $messages=[];

    public function add(

        string $role,

        string $content

    ):void{

        $this->messages[]=[

            'role'=>$role,

            'content'=>$content,

            'time'=>date('Y-m-d H:i:s')

        ];

    }

    public function history():array
    {

        return $this->messages;

    }

    public function clear():void
    {

        $this->messages=[];

    }

}
