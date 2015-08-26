<?php
namespace Concrete\Package\VividStore\Src\VividStore\Orders;

use \Symfony\Component\EventDispatcher\GenericEvent;

class OrderEvent extends GenericEvent {

    protected $event;

    public function __construct($currentOrder, $previousOrder = null) {
        $this->currentOrder = $currentOrder;
        $this->previousOrder = $previousOrder;
    }

    public function getCurrentOrder() {
        return $this->currentOrder;
    }

    public function getOrderBeforeChange() {
       return $this->previousOrder;
    }
}
