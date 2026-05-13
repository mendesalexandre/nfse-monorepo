<?php

namespace App\Filament\Resources\NfseEmissaos\Pages;

use App\Filament\Resources\NfseEmissaos\NfseEmissaoResource;
use App\Models\NfseEmissao;
use App\Services\NFSe\NfseEmissorService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Http\Response;

class ViewNfseEmissao extends ViewRecord
{
    protected static string $resource = NfseEmissaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('baixar_danfse')
                ->label('Baixar DANFSe')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->visible(function (): bool {
                    /** @var NfseEmissao $record */
                    $record = $this->getRecord();

                    return $record->status === 'emitida'
                        && (auth()->user()?->temPermissao('nfse.consultar') ?? false);
                })
                ->action(function () {
                    /** @var NfseEmissao $record */
                    $record = $this->getRecord();
                    try {
                        $service = new NfseEmissorService($record->cliente);
                        $pdf = $service->gerarDanfsePdf($record->chave_acesso);
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->danger()
                            ->title('Falha ao gerar DANFSe')
                            ->body($e->getMessage())
                            ->send();

                        return null;
                    }

                    return response($pdf, Response::HTTP_OK, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'attachment; filename=danfse-'.$record->chave_acesso.'.pdf',
                    ]);
                }),
        ];
    }
}
