<?php

namespace SwedbankPay\Core\Library\Methods;

use SwedbankPay\Core\Api\Response;
use SwedbankPay\Core\Exception;
use SwedbankPay\Core\Log\LogLevel;
use SwedbankPay\Core\Order;

use SwedbankPay\Api\Service\Payment\Resource\Collection\PricesCollection;
use SwedbankPay\Api\Service\Payment\Resource\Collection\Item\PriceItem;
use SwedbankPay\Api\Service\MobilePay\Request\Purchase;
use SwedbankPay\Api\Service\MobilePay\Resource\Request\PaymentPayeeInfo;
use SwedbankPay\Api\Service\MobilePay\Resource\Request\PaymentPrefillInfo;
use SwedbankPay\Api\Service\MobilePay\Resource\Request\PaymentUrl;
use SwedbankPay\Api\Service\MobilePay\Resource\Request\Payment;
use SwedbankPay\Api\Service\MobilePay\Resource\Request\PaymentObject;
use SwedbankPay\Api\Service\Data\ResponseInterface as ResponseServiceInterface;

/**
 * Trait Mobilepay
 * @package SwedbankPay\Core\Library\Methods
 */
trait Mobilepay
{
    /**
     * Initiate Mobilepay Payment
     *
     * @param mixed $orderId
     * @param string $phone Pre-fill phone, optional
     *
     * @return Response
     * @throws Exception
     */
    public function initiateMobilepayPayment($orderId, $phone = '')
    {
        /** @var Order $order */
        $order = $this->getOrder($orderId);

        /** @var Order\PlatformUrls $urls */
        $urls = $this->getPlatformUrls($orderId);

        $url = new PaymentUrl();
        $url->setCompleteUrl($urls->getCompleteUrl())
            ->setCancelUrl($urls->getCancelUrl())
            ->setCallbackUrl($urls->getCallbackUrl())
            ->setHostUrls($urls->getHostUrls());

        $payeeInfo = new PaymentPayeeInfo($this->getPayeeInfo($orderId)->toArray());

        $prefillInfo = new PaymentPrefillInfo();
        if (!empty($phone)) {
            $prefillInfo->setMsisdn($phone);
        }

        $price = new PriceItem();
        $price->setType(MobilepayInterface::PRICE_TYPE_MOBILEPAY)
            ->setAmount($order->getAmountInCents())
            ->setVatAmount($order->getVatAmountInCents());

        $prices = new PricesCollection();
        $prices->addItem($price);

        $payment = new Payment();
        $payment->setOperation(self::OPERATION_PURCHASE)
            ->setIntent(self::INTENT_AUTHORIZATION)
            ->setCurrency($order->getCurrency())
            ->setDescription($order->getDescription())
            ->setUserAgent($order->getHttpUserAgent())
            ->setLanguage($order->getLanguage())
            ->setUrls($url)
            ->setPayeeInfo($payeeInfo)
            ->setPrefillInfo($prefillInfo)
            ->setPrices($prices)
            ->setPayerReference($order->getPayerReference());

        $paymentObject = new PaymentObject();
        $paymentObject->setPayment($payment)
            ->setShoplogoUrl($urls->getLogoUrl());

        $purchaseRequest = new Purchase($paymentObject);
        $purchaseRequest->setClient($this->client);

        try {
            /** @var ResponseServiceInterface $responseService */
            $responseService = $purchaseRequest->send();

            return new Response($responseService->getResponseData());
        } catch (\Exception $e) {
            $this->log(LogLevel::DEBUG, sprintf('%s::%s: API Exception: %s', __CLASS__, __METHOD__, $e->getMessage()));

            throw new Exception($e->getMessage());
        }
    }
}