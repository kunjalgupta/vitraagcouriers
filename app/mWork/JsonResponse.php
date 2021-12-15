<?php
namespace App\mWork;
class JsonResponse
{
    public static function sendErrorRes()
    {
        return response()->json(['message'=>'Internal Error','status'=>0,'data'=>[]],500);      
    }
}