# Billing & Subscriptions

This document describes how to configure and manage billing and subscriptions in Ekklesia CMS using Laravel Cashier (Stripe).

## Configuration

### Environment Variables

Ensure the following variables are set in your `.env` file:

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
CASHIER_CURRENCY=usd
CASHIER_MODEL=App\Models\Tenant
```

### Plan Limits

Plans are defined in the `plan_limits` table. Each plan must have a corresponding `stripe_price_id` to allow users to subscribe via the UI.

## Webhooks

The system handles Stripe webhooks automatically to maintain subscription state.

### Endpoint
The webhook endpoint is: `https://your-domain.com/api/v1/stripe/webhook`

### Handling Multi-Tenancy
Since Ekklesia CMS is a multi-tenant application, the `StripeWebhookController` automatically identifies the tenant based on the Stripe `customer` ID and initializes the tenancy context before processing the event.

## User Interface

Church administrators can manage their subscriptions from the **Billing** page in the Admin Panel.

### Available Actions
- **Upgrade/Subscribe**: Initiates a Stripe Checkout session.
- **Manage Billing**: Redirects to the Stripe Customer Portal for payment method management and invoice history.
- **Cancel**: Cancels the subscription (effective at the end of the period).
- **Resume**: Resumes a cancelled subscription that is still in its grace period.

## Development & Testing

### Mocking Stripe
For local development, you can use the Stripe CLI to forward webhooks:
```bash
stripe listen --forward-to localhost/api/v1/stripe/webhook
```

### Billing Portal
To test the Billing Portal locally, ensure you have configured it in your Stripe Dashboard and that you have at least one customer with a `stripe_id`.
