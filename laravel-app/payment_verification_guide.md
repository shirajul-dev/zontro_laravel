# Payment Verification & Webhook Architecture Guide

This document explains the technical logic behind PipraPay's secure IPN (Inbound) and Merchant Webhook (Outbound) systems, and provides instructions for testing them.

---

## 🛡️ 1. IPN: Secure Inbound Verification
When a gateway (SSLCommerz, bKash, etc.) sends a notification back to PipraPay, we no longer "trust" the status parameters sent in the URL. Instead, we use an **API-to-API Handshake**.

### SSLCommerz Logic:
1.  **Callback**: SSLCommerz sends a POST request to `/ipn/sslcommerz`.
2.  **Handshake**: Our server takes the `tran_id` and calls the SSLCommerz **Validation API**.
3.  **Verification**: If the SSLCommerz API confirms the payment is `VALID`, only then do we mark the transaction as `completed` in our database.

### bKash Logic:
1.  **Callback**: bKash redirects the user back to `/ipn/bkash-api-tokenized` with a `paymentID`.
2.  **Handshake**: Our server calls the bKash **Execute Payment API** using that `paymentID`.
3.  **Verification**: If the bKash API returns `statusMessage: Successful`, we finalize the transaction.

---

## 🚀 2. Webhooks: Secure Outbound Notifications
Once a payment is verified, PipraPay notifies the merchant's server. To ensure the merchant knows the request is legitimately from PipraPay, we use **HMAC-SHA256 Signing**.

### How it Works:
1.  **Payload**: We build a JSON object containing `ref`, `amount`, `status`, and `trx_id`.
2.  **Signing**: We use the merchant's `api_key` as a secret key to generate a signature of the JSON payload.
3.  **Delivery**: We send the JSON to the merchant's `webhook_url` with a special header: `X-PipraPay-Signature`.

### Merchant-Side Validation:
The merchant should re-generate the signature using their API key and compare it to the header. If they match, the notification is authentic.

---

## 🧪 3. How to Test & Verify

### Step A: Test the Webhook Delivery
1.  Go to [Webhook.site](https://webhook.site) and copy your unique URL.
2.  In the PipraPay database (or via a test payment link), set the `webhook_url` of a transaction to that Webhook.site URL.
3.  Simulate a successful payment (or manually trigger the IPN route).
4.  **Observe**: You will see a POST request appear on Webhook.site with the transaction data and the `X-PipraPay-Signature` header.

### Step B: Check Audit Logs
If a payment isn't showing up correctly, you can check the database for detailed logs:
*   **IPN Logs**: Check `storage/logs/gateway_sslcommerz.log` or `gateway_bkash-api-tokenized.log`.
*   **Webhook Logs**: Query the `pp_webhook_log` table. It stores:
    *   The URL we sent to.
    *   The full JSON payload.
    *   The HTTP status code from the merchant's server (e.g., `200` for success).
    *   The error message if it failed.

### Step C: Verify Database Sync
Check the `pp_transaction` table. Ensure that:
*   `status` is `completed`.
*   `trx_id` is populated with the real ID from the bank/gateway.
*   `updated_date` reflects the exact time of verification.
