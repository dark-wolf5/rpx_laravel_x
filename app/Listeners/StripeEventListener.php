<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Events\WebhookReceived;

class StripeEventListener
{
    private WebhookReceived $event;

    private array $allowedPayloadTypes = [
        'customer.created', 'customer.updated', 'customer.deleted',
        'customer.subscription.updated', 'customer.subscription.created', 'customer.subscription.deleted',
    ];

    /**
     * Handle received Stripe webhooks.
     *
     * @param \Laravel\Cashier\Events\WebhookReceived $event
     *
     * @return void
     */
    public function handle(WebhookReceived $event)
    {
        /*
         *
         * We have two types of customers.
         * 1. First type of stripe customer is the Ads models which each Ad has a different subscription.
         * 2. Second type of stripe customer is the Users models which each Users has a different subscription.
         *
         */

        $this->event = $event;

        if (!in_array($event->payload['type'], $this->allowedPayloadTypes))
        {
            Log::info($event->payload['type'] . ' - Stripe Event ID: ' . $event->payload['id']);
            return;
        }

        $userId = $this->getUserId();

        switch($event->payload['type'])
        {
            case 'customer.created':
                Log::info('Customer Created - Rpx UID: ' . $userId);
                return;
            case 'customer.updated':
                Log::info('Customer Updated - Rpx UID: ' . $userId);
                return;
            case 'customer.deleted':
                Log::info('Customer Deleted - Rpx UID: ' . $userId);
                return;
            case 'customer.subscription.updated':
                Log::info('Customer Subscription Updated - Rpx UID: ' . $userId);
                return;
            case 'customer.subscription.created':
                Log::info('Customer Subscription Created - Rpx UID: ' . $userId);
                return;
            case 'customer.subscription.deleted':
                Log::info('Customer Subscription Deleted - Rpx UID: ' . $userId);
                return;
        }
    }

    public function getUserId()
    {
        $event = $this->event;

        if ($event->payload['type'] === 'customer.created' ||
            $event->payload['type'] === 'customer.deleted' ||
            $event->payload['type'] === 'customer.updated')
        {
            $user = Cashier::findBillable($this->event->payload['data']['object']['id']);
        }
        else
        {
            $user = Cashier::findBillable($this->event->payload['data']['object']['customer']);
        }

        if ($user)
        {
            $userId = $user->id;
        }
        else
        {
            $userId = 'demo-api';
        }

        return $userId;
    }
}
