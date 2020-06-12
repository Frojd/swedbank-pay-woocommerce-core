<?php

use SwedbankPay\Core\PaymentAdapter;
use SwedbankPay\Core\PaymentAdapterInterface;
use SwedbankPay\Core\ConfigurationInterface;
use SwedbankPay\Core\Order\PlatformUrlsInterface;
use SwedbankPay\Core\OrderInterface;
use SwedbankPay\Core\OrderItemInterface;
use SwedbankPay\Core\Order\RiskIndicatorInterface;
use SwedbankPay\Core\Order\PayeeInfoInterface;

class Adapter extends PaymentAdapter implements PaymentAdapterInterface
{
    /**
     * @var Gateway
     */
    private $gateway;

    /**
     * Adapter constructor.
     *
     * @param Gateway $gateway
     */
    public function __construct(Gateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * Log a message.
     *
     * @param $level
     * @param $message
     * @param array $context
     */
    public function log($level, $message, array $context = [])
    {
	    file_put_contents(
	    	get_temp_dir() . '/swedbankpay.log',
		    sprintf('[%s] %s [%s]', $level, $message, var_export($context, true)) . "\n",
		    FILE_APPEND
	    );
    }

    /**
     * Get Adapter Configuration.
     *
     * @return array
     */
    public function getConfiguration()
    {
        return [
            ConfigurationInterface::DEBUG => $this->gateway->debug,
            ConfigurationInterface::MERCHANT_TOKEN => $this->gateway->merchant_token,
            ConfigurationInterface::PAYEE_ID => $this->gateway->payee_id,
            ConfigurationInterface::PAYEE_NAME => $this->gateway->payee_name,
            ConfigurationInterface::MODE => $this->gateway->testmode,
            ConfigurationInterface::AUTO_CAPTURE => $this->gateway->auto_capture,
            ConfigurationInterface::SUBSITE => $this->gateway->subsite,
            ConfigurationInterface::LANGUAGE => $this->gateway->language,
            ConfigurationInterface::SAVE_CC => $this->gateway->save_cc,
            ConfigurationInterface::TERMS_URL => $this->gateway->terms_url,
            ConfigurationInterface::REJECT_CREDIT_CARDS => $this->gateway->reject_credit_cards,
            ConfigurationInterface::REJECT_DEBIT_CARDS => $this->gateway->reject_debit_cards,
            ConfigurationInterface::REJECT_CONSUMER_CARDS => $this->gateway->reject_consumer_cards,
            ConfigurationInterface::REJECT_CORPORATE_CARDS => $this->gateway->reject_corporate_cards,
        ];
    }

    /**
     * Get Platform Urls of Actions of Order (complete, cancel, callback urls).
     *
     * @param mixed $orderId
     *
     * @return array
     */
    public function getPlatformUrls($orderId)
    {
        return [
            PlatformUrlsInterface::COMPLETE_URL => 'https://example.com/complete',
            PlatformUrlsInterface::CANCEL_URL => 'https://example.com/cancel',
            PlatformUrlsInterface::CALLBACK_URL => 'https://example.com/callback',
            PlatformUrlsInterface::TERMS_URL => 'https://example.com/terms'
        ];
    }

    /**
     * Get Order Data.
     *
     * @param mixed $orderId
     *
     * @return array
     */
    public function getOrderData($orderId)
    {
        $items = [];
        $items[] = [
            // The field Reference must match the regular expression '[\\w-]*'
            OrderItemInterface::FIELD_REFERENCE   => 'TEST',
            OrderItemInterface::FIELD_NAME        => 'Test',
            OrderItemInterface::FIELD_TYPE        => OrderItemInterface::TYPE_PRODUCT,
            OrderItemInterface::FIELD_CLASS       => 'product',
            OrderItemInterface::FIELD_ITEM_URL    => 'https://example.com/product1',
            OrderItemInterface::FIELD_IMAGE_URL   => 'https://example.com/product1.jpg',
            OrderItemInterface::FIELD_DESCRIPTION => 'Test product',
            OrderItemInterface::FIELD_QTY         => 1,
            OrderItemInterface::FIELD_QTY_UNIT    => 'pcs',
            OrderItemInterface::FIELD_UNITPRICE   => round( 125 * 100 ),
            OrderItemInterface::FIELD_VAT_PERCENT => round( 25 * 100 ),
            OrderItemInterface::FIELD_AMOUNT      => round( 125 * 100 ),
            OrderItemInterface::FIELD_VAT_AMOUNT  => round( 25 * 100 ),
        ];

        return [
            OrderInterface::ORDER_ID => $orderId,
            OrderInterface::AMOUNT => 125,
            OrderInterface::VAT_AMOUNT => 25,
            OrderInterface::VAT_RATE => 25,
            OrderInterface::SHIPPING_AMOUNT => 0,
            OrderInterface::SHIPPING_VAT_AMOUNT => 0,
            OrderInterface::DESCRIPTION => 'Test order',
            OrderInterface::CURRENCY => $this->gateway->currency,
            OrderInterface::STATUS => OrderInterface::STATUS_AUTHORIZED,
            OrderInterface::CREATED_AT => gmdate( 'Y-m-d H:i:s' ),
            OrderInterface::PAYMENT_ID => null,
            OrderInterface::PAYMENT_ORDER_ID => null,
            OrderInterface::NEEDS_SAVE_TOKEN_FLAG => false,
            OrderInterface::HTTP_ACCEPT => null,
            OrderInterface::HTTP_USER_AGENT => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0',
            OrderInterface::BILLING_COUNTRY => 'Sweden',
            OrderInterface::BILLING_COUNTRY_CODE => 'SE',
            OrderInterface::BILLING_ADDRESS1 => 'Hökvägen 5',
            OrderInterface::BILLING_ADDRESS2 => '',
            OrderInterface::BILLING_ADDRESS3 => '',
            OrderInterface::BILLING_CITY => 'Järfälla',
            OrderInterface::BILLING_STATE => null,
            OrderInterface::BILLING_POSTCODE => '17674',
            OrderInterface::BILLING_PHONE => '+46739000001',
            OrderInterface::BILLING_EMAIL => 'leia.ahlstrom@payex.com',
            OrderInterface::BILLING_FIRST_NAME => 'Leia',
            OrderInterface::BILLING_LAST_NAME => 'Ahlström',
            OrderInterface::SHIPPING_COUNTRY => 'Sweden',
            OrderInterface::SHIPPING_COUNTRY_CODE => 'SE',
            OrderInterface::SHIPPING_ADDRESS1 => 'Hökvägen 5',
            OrderInterface::SHIPPING_ADDRESS2 => '',
            OrderInterface::SHIPPING_ADDRESS3 => '',
            OrderInterface::SHIPPING_CITY => 'Järfälla',
            OrderInterface::SHIPPING_STATE => null,
            OrderInterface::SHIPPING_POSTCODE => '17674',
            OrderInterface::SHIPPING_PHONE => '+46739000001',
            OrderInterface::SHIPPING_EMAIL => 'leia.ahlstrom@payex.com',
            OrderInterface::SHIPPING_FIRST_NAME => 'Leia',
            OrderInterface::SHIPPING_LAST_NAME => 'Ahlström',
            OrderInterface::CUSTOMER_ID => 1,
            OrderInterface::CUSTOMER_IP => '127.0.0.1',
            OrderInterface::PAYER_REFERENCE => uniqid('ref'),
            OrderInterface::ITEMS => $items,
            OrderInterface::LANGUAGE => 'en-US',
        ];
    }

    /**
     * Get Risk Indicator of Order.
     *
     * @param mixed $orderId
     *
     * @return array
     */
    public function getRiskIndicator($orderId)
    {
        return [
            // Two-day or more shipping
            'deliveryTimeFrameIndicator' => '04'
        ];
    }

    /**
     * Get Payee Info of Order.
     *
     * @param mixed $orderId
     *
     * @return array
     */
    public function getPayeeInfo($orderId)
    {
        return array(
            PayeeInfoInterface::ORDER_REFERENCE => $orderId,
        );
    }

    /**
     * Update Order Status.
     *
     * @param mixed $orderId
     * @param string $status
     * @param string|null $message
     * @param mixed|null $transactionId
     */
    public function updateOrderStatus($orderId, $status, $message = null, $transactionId = null)
    {
        // @todo
    }

    /**
     * Save Transaction data.
     *
     * @param mixed $orderId
     * @param array $transactionData
     */
    public function saveTransaction($orderId, array $transactionData = [])
    {
        // @todo
    }

    /**
     * Find for Transaction.
     *
     * @param $field
     * @param $value
     *
     * @return array
     */
    public function findTransaction($field, $value)
    {
        // @todo
    }

    /**
     * Save Payment Token.
     *
     * @param mixed $customerId
     * @param string $paymentToken
     * @param string $recurrenceToken
     * @param string $cardBrand
     * @param string $maskedPan
     * @param string $expiryDate
     * @param mixed|null $orderId
     */
    public function savePaymentToken(
        $customerId,
        $paymentToken,
        $recurrenceToken,
        $cardBrand,
        $maskedPan,
        $expiryDate,
        $orderId = null
    ) {
        // @todo
    }
}
