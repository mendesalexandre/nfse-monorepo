<?php

namespace App\Filament\Resources\NfseEmissaos;

use App\Filament\Resources\NfseEmissaos\Pages\ListNfseEmissaos;
use App\Filament\Resources\NfseEmissaos\Pages\ViewNfseEmissao;
use App\Filament\Resources\NfseEmissaos\Schemas\NfseEmissaoInfolist;
use App\Filament\Resources\NfseEmissaos\Tables\NfseEmissaosTable;
use App\Models\NfseEmissao;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class NfseEmissaoResource extends Resource
{
    protected static ?string $model = NfseEmissao::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $modelLabel = 'NFS-e';

    protected static ?string $pluralModelLabel = 'NFS-e';

    protected static ?string $navigationLabel = 'NFS-e emitidas';

    protected static ?string $recordTitleAttribute = 'chave_acesso';

    protected static ?int $navigationSort = 2;

    public static function infolist(Schema $schema): Schema
    {
        return NfseEmissaoInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NfseEmissaosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNfseEmissaos::route('/'),
            'view' => ViewNfseEmissao::route('/{record}'),
        ];
    }

    // ===== Permissões — read-only com cancelamento =====

    public static function canViewAny(): bool
    {
        return auth()->user()?->temPermissao('nfse.consultar') ?? false;
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->temPermissao('nfse.consultar') ?? false;
    }

    public static function canCreate(): bool
    {
        return false; // Emissão é via API REST /api/v1/nfse, não pelo painel
    }

    public static function canEdit(Model $record): bool
    {
        return false; // Imutável após emissão
    }

    public static function canDelete(Model $record): bool
    {
        return false; // Apenas cancelamento via SEFIN (action própria), nunca delete físico
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
