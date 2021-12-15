<?php
namespace App\mWork;
use Illuminate\Support\Facades\DB;

class PkGenerator
{
    public static function generaePK(CommonVO $commonVO)
    {
        if($commonVO->getTable()!=null)
        {
            try{
                    $seq_name='seq_'.$commonVO->getTable();
                    $query="select nextval('$seq_name')";
                    $result=DB::select($query);
                    $pk=get_object_vars($result[0]);
                    return $pk['nextval'];
                }catch (\Exception $e)
                {
                    throw new \Exception("Sequence Not Found in Database");     
                }
        }else
        {
            throw new \Exception("Table not Entered to Generate Sequence");
        }
     }
}