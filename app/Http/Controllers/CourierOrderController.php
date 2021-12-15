<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourierOrderRequest;
use App\Model\CourierOrder;
use App\Services\CourierOrderService;

class CourierOrderController extends Controller
{
    protected $courierOrderService;
    public function __construct(CourierOrderService $courierOrderService)
    {
        $this->courierOrderService = $courierOrderService;
    }

    public function store(CourierOrderRequest $request)
    {
        return CourierOrder::create($request->all());
    }

    public function rejectOrder($id)
    {
        return $this->courierOrderService->updateStatus($id, '117');
    }

    public function proceedOrder($id)
    {
        return $this->courierOrderService->updateStatus($id, '116');
    }

    public function courierOrderList($status)
    {
        return $this->courierOrderService->getListFromStatus($status);
    }
}
