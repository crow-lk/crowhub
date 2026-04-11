<?php

namespace App\Filament\Pages;

use App\Models\Lead;
use App\Services\Sms\SmsService;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use UnitEnum;

class PromotionalMessages extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-megaphone';

    protected static string | UnitEnum | null $navigationGroup = 'Messaging';

    protected static ?string $title = 'Promotional Messages';

    protected string $view = 'filament.pages.promotional-messages';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->data ?? []);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Recipients')
                    ->schema([
                        Forms\Components\Checkbox::make('select_all')
                            ->label('Select all leads')
                            ->live()
                            ->afterStateUpdated(function (bool $state, Get $get, Set $set) {
                                if ($state) {
                                    $set('lead_ids', $this->getAllLeadIds());
                                } else {
                                    $set('lead_ids', []);
                                }
                            }),
                        Forms\Components\MultiSelect::make('lead_ids')
                            ->label('Leads')
                            ->options(fn (): array => $this->getLeadOptions())
                            ->searchable()
                            ->native(false)
                            ->live()
                            ->helperText('Select individual leads or choose "Select all leads" above.'),
                        TextEntry::make('recipient_count')
                            ->label('Recipients selected')
                            ->state(fn (Get $get): string => count($get('lead_ids') ?? []).' leads selected'),
                    ]),
                Section::make('Message')
                    ->schema([
                        Forms\Components\Textarea::make('message')
                            ->label('SMS content')
                            ->required()
                            ->rows(6)
                            ->maxLength(320)
                            ->helperText('320 characters max. Include your brand name for better delivery rates.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function send(SmsService $smsService): void
    {
        if (! $smsService->isEnabled()) {
            Notification::make()
                ->danger()
                ->title('SMS disabled')
                ->body('Enable and configure credentials in SMS Settings before sending messages.')
                ->send();

            return;
        }

        $state = $this->form->getState();
        $leadIds = $state['lead_ids'] ?? [];

        if (empty($leadIds)) {
            Notification::make()
                ->danger()
                ->title('No recipients selected')
                ->body('Please select at least one lead to send the promotional message.')
                ->send();

            return;
        }

        $leads = Lead::query()
            ->whereIn('id', $leadIds)
            ->whereNotNull('phone')
            ->get();

        if ($leads->isEmpty()) {
            Notification::make()
                ->danger()
                ->title('No valid recipients')
                ->body('None of the selected leads have a phone number on record.')
                ->send();

            return;
        }

        $message = $state['message'] ?? '';
        $sentCount = 0;
        $failedCount = 0;

        foreach ($leads as $lead) {
            if ($smsService->send($message, $lead->phone, [
                'first_name' => $lead->name,
                'email' => $lead->email,
                'lead_id' => $lead->id,
            ])) {
                $sentCount++;
            } else {
                $failedCount++;
            }
        }

        if ($sentCount > 0) {
            $this->reset('data');
            $this->mount();

            Notification::make()
                ->success()
                ->title('Messages sent')
                ->body("Promotional SMS sent to {$sentCount} recipient(s).")
                ->send();
        }

        if ($failedCount > 0) {
            Notification::make()
                ->warning()
                ->title('Partial failure')
                ->body("{$failedCount} message(s) failed to send.")
                ->send();
        }
    }

    protected function getLeadOptions(): array
    {
        return Lead::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected function getAllLeadIds(): array
    {
        return Lead::query()
            ->pluck('id')
            ->all();
    }
}