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
        if($result instanceof self)
        {
            $this->gather[] =$result->toArray();
            $this->result = $result->toArray();
        }
    }

    public function getAllResult(): array
    {
        return $this->gather;
    }

    public function first(){
        if(empty($this->result))return [];
        return $this->_first($this->result);
    }


    private function _first(mixed $result)
    {
        return is_array($result)?$this->_first(reset($result)):$result;

    }


}