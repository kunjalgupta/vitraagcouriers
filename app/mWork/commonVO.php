<?php
namespace App\mWork;
class CommonVO
{
    private $table;

    public function getTable()
    {
        return $this->table;
    }
    public function setTable($value)
    {
        $this->table=$value;
    }
}