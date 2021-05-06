<?php
class museoweb_modules_ecommerce_gateway_Simulate extends museoweb_modules_ecommerce_gateway_AbstractGateway implements museoweb_modules_ecommerce_gateway_IGateway
{
    private $orderId;

    public function pay($user, $orderId, $orderCode, $total, $items)
    {
        $arOrder = pinax_ObjectFactory::createModel('museoweb.modules.ecommerce.models.Order');
        $arOrder->load($orderId);
        $arOrder->order_state = 'completed';
        $arOrder->save();

        // simula la conferma s2s ed invia  l'email
        $this->orderId = $orderId;
        $this->s2s();

        pinax_helpers_Navigation::gotoUrl(__Routing::makeUrl('museoweb_ecommConfirm'));
    }

    public function s2s()
    {
        $this->sendEmail($this->orderId);
    }

    public function confirm()
    {
        museoweb_modules_ecommerce_Helper::resetCart();
    }
}
