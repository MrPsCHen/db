<?php


namespace EasyDb;


abstract class Controller
{
    protected ?Model $model;
    public function __construct(Model $model = null)
    {
        $this->model = $model;

    }

    function view(int $page = 1,$limit = 20)
    {
//        $this->model->limit()
    }

    function del()
    {

    }

    function find()
    {

    }

    function update()
    {

    }


}