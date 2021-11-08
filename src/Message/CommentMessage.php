<?php

namespace App\Message;

class CommentMessage
{
    /*
     * Add whatever properties & methods you need to hold the
     * data for this message class.
     */
    private $id;
    private $context;

    public function __construct(int $id, array $context = [])
    {
        $this->id=$id;
        $this->context=$context;
    }
    public function getId (): int
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
