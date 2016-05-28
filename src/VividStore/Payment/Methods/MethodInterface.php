<?php
namespace Concrete\Package\VividStore\Src\VividStore\Payment;

interface MethodInterface 
{
    public function dashboardForm();
    public function save($data);
    public function validate($args, $e);
    public function checkoutForm();
    public function submitPayment();     
}