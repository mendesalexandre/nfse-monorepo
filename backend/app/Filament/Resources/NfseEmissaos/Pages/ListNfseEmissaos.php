<?php

namespace App\Filament\Resources\NfseEmissaos\Pages;

use App\Filament\Resources\NfseEmissaos\NfseEmissaoResource;
use Filament\Resources\Pages\ListRecords;

class ListNfseEmissaos extends ListRecords
{
    protected static string $resource = NfseEmissaoResource::class;

    protected function getHeaderActions(): array
    {
        return []; // emissão é via API REST, não via painel
    }
}
