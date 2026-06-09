<?php

namespace App\Filament\Resources\ElectronicInvoices\Pages;

use App\Filament\Resources\ElectronicInvoices\ElectronicInvoiceResource;
use Filament\Resources\Pages\ListRecords;

class ListElectronicInvoices extends ListRecords
{
    protected static string $resource = ElectronicInvoiceResource::class;
}
