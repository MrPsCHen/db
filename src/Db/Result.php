<?php

namespace EasyDb;

class Result
{
    protected array $result;
    protected array $gather = [];

    public function __construct(mixed $result)
    {

       is_array($result) && $this->result = $result;

    }

    public function toArray(): ?array
    {
        return $this->result ?? null;
    }

    public function addResult(mixed $result){
        if(is_array($result)){
            $this->gather[] = $result;
            $this->result = $result;
        }
    }

    public function getAllResult(): array
    {
        return $this->gather;
    }

    public function first(){
        if(empty($this->result))return [];
        return reset($this->result);
    }


}