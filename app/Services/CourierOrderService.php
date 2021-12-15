<?php

namespace App\Services;

use App\Model\CourierOrder;
use App\mWork\JsonResponse;

class CourierOrderService
{

    public function updateStatus($id, $status)
    {
        $order = CourierOrder::find($id);
        if (is_null($order)) {
            return JsonResponse::sendErrorRes();
        }
        $flag = $order->update(['status' => $status]);
        if ($flag) {
            return response()->json(['message' => 'Updated Successfully', 'status' => 1, 'data' => []], 200);
        } else {
            return JsonResponse::sendErrorRes();
        }
    }

    public function getListFromStatus($status)
    {
        try {
            return CourierOrder::where('status', $status)->get();
        } catch (\Exception $e) {
            return JsonResponse::sendErrorRes();
        }
    }
}
