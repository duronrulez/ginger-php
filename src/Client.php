<?php

namespace GingerPayments\Payment;

use GingerPayments\Payment\Client\ClientException;
use GingerPayments\Payment\Client\OrderNotFoundException;
use GingerPayments\Payment\Common\ArrayFunctions;
use GingerPayments\Payment\Ideal\Issuers;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\RequestException;

final class Client
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Get possible iDEAL issuers.
     *
     * @return Issuers
     */
    public function getIdealIssuers()
    {
        try {
            return Issuers::fromArray(
                $this->httpClient->get('ideal/issuers/')->json()
            );
        } catch (RequestException $exception) {
            throw new ClientException(
                'An error occurred while processing the request: ' . $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * Create a new iDEAL order.
     *
     * @param integer $amount Amount in cents.
     * @param string $currency A valid currency code.
     * @param string $issuerId The SWIFT/BIC code of the iDEAL issuer.
     * @param string $description A description of the order.
     * @param string $merchantOrderId A merchant-defined order identifier.
     * @param string $returnUrl The return URL.
     * @param string $expirationPeriod The expiration period as an ISO 8601 duration
     * @return Order The newly created order.
     */
    public function createIdealOrder(
        $amount,
        $currency,
        $issuerId,
        $description = null,
        $merchantOrderId = null,
        $returnUrl = null,
        $expirationPeriod = null
    ) {
        return $this->postOrder(
            Order::createWithIdeal(
                $amount,
                $currency,
                $issuerId,
                $description,
                $merchantOrderId,
                $returnUrl,
                $expirationPeriod
            )
        );
    }

    /**
     * Create a new credit card order.
     *
     * @param integer $amount Amount in cents.
     * @param string $currency A valid currency code.
     * @param string $description A description of the order.
     * @param string $merchantOrderId A merchant-defined order identifier.
     * @param string $returnUrl The return URL.
     * @param string $expirationPeriod The expiration period as an ISO 8601 duration
     * @return Order The newly created order.
     */
    public function createCreditCardOrder(
        $amount,
        $currency,
        $description = null,
        $merchantOrderId = null,
        $returnUrl = null,
        $expirationPeriod = null
    ) {
        return $this->postOrder(
            Order::createWithCreditCard(
                $amount,
                $currency,
                $description,
                $merchantOrderId,
                $returnUrl,
                $expirationPeriod
            )
        );
    }

    /**
     * Create a new order.
     *
     * @param integer $amount Amount in cents.
     * @param string $currency A valid currency code.
     * @param string $paymentMethod The payment method to use.
     * @param array $paymentMethodDetails An array of extra payment method details.
     * @param string $description A description of the order.
     * @param string $merchantOrderId A merchant-defined order identifier.
     * @param string $returnUrl The return URL.
     * @param string $expirationPeriod The expiration period as an ISO 8601 duration
     * @return Order The newly created order.
     */
    public function createOrder(
        $amount,
        $currency,
        $paymentMethod,
        array $paymentMethodDetails = [],
        $description = null,
        $merchantOrderId = null,
        $returnUrl = null,
        $expirationPeriod = null
    ) {
        return $this->postOrder(
            Order::create(
                $amount,
                $currency,
                $paymentMethod,
                $paymentMethodDetails,
                $description,
                $merchantOrderId,
                $returnUrl,
                $expirationPeriod
            )
        );
    }

    /**
     * Get a single order.
     *
     * @param string $id The order ID.
     * @return Order
     */
    public function getOrder($id)
    {
        try {
            return Order::fromArray(
                $this->httpClient->get("orders/$id")->json()
            );
        } catch (RequestException $exception) {
            if ($exception->getCode() == 404) {
                throw new OrderNotFoundException('No order with that ID was found.', 404, $exception);
            }

            throw new ClientException(
                'An error occurred while getting the order: ' . $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * Post a new order.
     *
     * @param Order $order
     * @return Order
     */
    private function postOrder(Order $order)
    {
        try {
            $response = $this->httpClient->post(
                'orders/',
                [
                    'timeout' => 3,
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => json_encode(
                        ArrayFunctions::withoutNullValues($order->toArray())
                    )
                ]
            );
        } catch (RequestException $exception) {
            throw new ClientException(
                'An error occurred while posting the order: ' . $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        return Order::fromArray($response->json());
    }
}
