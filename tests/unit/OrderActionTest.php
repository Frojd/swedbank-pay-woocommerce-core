<?php

use SwedbankPay\Core\Core;
use SwedbankPay\Core\OrderInterface;
use SwedbankPay\Core\Exception;

class OrderActionTest extends TestCase
{
    public function test_update_status() {
        $result = $this->core->canUpdateOrderStatus(1, OrderInterface::STATUS_FAILED, 123);
        $this->assertEquals(true, $result);
    }

    public function test_capture() {
        $this->expectException(Exception::class);
        $this->core->capture(1, 125, 25);
    }

    public function test_cancel() {
        $this->expectException(Exception::class);
        $this->core->cancel(1, 125, 25);
    }

    public function test_refund() {
        $this->expectException(Exception::class);
        $this->core->refund(1, 125, 25);
    }

    public function test_abort() {
        $this->expectException(Exception::class);
        $this->core->abort(1);
    }
}
