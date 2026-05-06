<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\Theme\ThemeService;
use App\Services\Legacy\LegacyRuntimeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly ThemeService $themeService,
        private readonly LegacyRuntimeService $legacyRuntimeService
    ) {
    }

    /**
     * Handle public invoice page (rendered via active theme)
     */
    public function show(Request $request, string $ref)
    {
        return $this->themeService->renderInvoice($request, $ref);
    }

    /**
     * Handle invoice webhooks (still uses legacy runtime for webhook POST logic)
     */
    public function webhook(Request $request)
    {
        if (config('piprapay.migration.native_invoice_webhook_enabled', false)) {
            return $this->nativeWebhook($request);
        }

        return $this->legacyRuntimeService->dispatch($request);
    }

    private function nativeWebhook(Request $request): Response
    {
        $dbPrefix = env('DB_PREFIX', 'pp_');

        $raw = (string) $request->getContent();
        if ($raw === '') {
            return response('No payload received', 400);
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return response('Invalid JSON', 400);
        }

        $ppId = (string) ($data['pp_id'] ?? '');
        if ($ppId !== '') {
            $transaction = DB::table($dbPrefix . 'transaction')
                ->where('ref', $ppId)
                ->first();

            if ($transaction !== null) {
                $metadata = json_decode((string) ($transaction->metadata ?? '{}'), true);
                $metadata = is_array($metadata) ? $metadata : [];
                $invoiceId = (string) ($metadata['invoice_id'] ?? '');

                if ($invoiceId !== '') {
                    $invoice = DB::table($dbPrefix . 'invoice')->where('ref', $invoiceId)->first();

                    if ($invoice !== null) {
                        $status = (string) ($transaction->status ?? '');
                        if ($status === 'completed' || $status === 'refunded') {
                            DB::table($dbPrefix . 'invoice')
                                ->where('id', $invoice->id)
                                ->update([
                                    'gateway_id' => (string) ($transaction->gateway_id ?? ''),
                                    'status' => $status === 'completed' ? 'paid' : 'refunded',
                                    'updated_date' => now('UTC')->format('Y-m-d H:i:s'),
                                ]);
                        }
                    }
                }
            }
        }

        return response('OK', 200);
    }
}
