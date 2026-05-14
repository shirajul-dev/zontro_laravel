# Telegram Bot Notification Pro — PipraPay V3 Addon

A complete port of the original Telegram Bot Notification Pro V2 plugin, rebuilt as a native **PipraPay V3 Addon**.

## Installation

1. Copy the `telegram-notification-pro` folder into:
   ```
   /pp-content/pp-modules/pp-addons/telegram-notification-pro/
   ```
2. Go to **Admin → Addons → New Addon**, select **Telegram Bot Notification Pro**, and click **Create**.
3. Click **Edit** on the newly created addon to open the configuration page.

## Features (all V2 features preserved + new ones)

| Feature | V2 Plugin | V3 Addon |
|---|---|---|
| Multiple Chat IDs | ✅ | ✅ |
| Per-recipient enable/disable | ✅ | ✅ |
| Completed notifications | ✅ | ✅ |
| Pending notifications | ✅ | ✅ |
| Failed notifications | ✅ | ✅ |
| **Refunded notifications** | ❌ | ✅ NEW |
| **Cancelled notifications** | ❌ | ✅ NEW |
| Custom message templates per status | ✅ | ✅ |
| **Send Test Message** to any chat ID | ✅ | ✅ |
| "Confirm Transaction" inline button | ✅ | ✅ |
| Bot command registration | ✅ | ✅ |
| Standalone / Hub / Node modes | ✅ | ✅ |
| Signed HMAC inter-site auth | ✅ | ✅ |
| One-click update (GitHub) | ✅ | ✅ |
| One-click update (Custom server) | ✅ | ✅ |
| Backup before update | ✅ | ✅ |

## Bot Commands
- `/start` — Get your Chat ID
- `/sales_today` — Today's completed sales total
- `/sales_yesterday` — Yesterday's sales
- `/sales_this_month` — This month's sales
- `/last_transaction` — Most recent transaction details
- `/pending_transactions` — Count of pending transactions
- `/failed_transactions` — Count of failed transactions
- `/completed_transactions` — Count of completed transactions
- `/help` — Show all commands

## Webhook URL
When you connect the bot, the webhook is automatically set to:
```
https://yoursite.com/pp-content/pp-modules/pp-addons/telegram-notification-pro/webhook.php
```

## Message Template Placeholders
`{amount}` `{currency}` `{customer_name}` `{payment_method}` `{sender_number}`
`{date}` `{payment_id}` `{gateway_trx_id}` `{status}` `{email}` `{mobile}`
`{fee}` `{total}` `{local_currency}`

## Author
**Refat Rahman** — [GitHub](https://github.com/refatbd) · [Facebook](https://www.facebook.com/rjrefat)
