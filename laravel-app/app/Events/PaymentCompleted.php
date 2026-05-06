<?php

namespace App\Events;

use App\Models\PpTransaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaction;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\PpTransaction  $transaction
     * @return void
     */
    public function __construct(PpTransaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
