<?php

namespace App\Console\Commands;

use App\Services\OfferSyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncOffers extends Command
{
    protected $signature = 'offers:sync';

    protected $description = 'Atualiza as ofertas públicas das lojas de Nova Odessa';

    public function handle(OfferSyncService $service): int
    {
        try {
            foreach ($service->sync() as $market => $count) {
                $this->info("{$market}: {$count} ofertas atualizadas.");
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
