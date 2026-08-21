<?php

use App\Exports\LeadsImportTemplateExport;
use App\Imports\LeadsImport;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

new class extends Component
{
    use WithFileUploads;

    public $file = null;

    public bool $showResults = false;

    public int $importedCount = 0;

    /**
     * @var array<int, string>
     */
    public array $failureMessages = [];

    #[On('open-lead-import')]
    public function open(): void
    {
        $this->reset(['file', 'showResults', 'importedCount', 'failureMessages']);
        $this->resetErrorBag();

        $this->dispatch('show-lead-import-modal');
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new LeadsImportTemplateExport, 'leads-import-template.xlsx');
    }

    public function import(): void
    {
        $this->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $importer = new LeadsImport;

        Excel::import($importer, $this->file->getRealPath());

        $this->importedCount = $importer->importedCount;
        $this->failureMessages = $importer->failures()
            ->map(fn ($failure) => "Row {$failure->row()}: ".implode(' ', $failure->errors()))
            ->all();
        $this->showResults = true;

        if ($this->importedCount > 0) {
            $this->dispatch('lead-saved');
        }
    }
}; ?>

{{--
    Alpine-driven popup instead of a Bootstrap JS modal — see the leads index
    and form-modal components for why: Bootstrap's async backdrop lifecycle
    can be left stuck, invisibly blocking every click on the page.
--}}
<div x-data="{ open: false }" x-on:show-lead-import-modal.window="open = true" x-on:keydown.escape.window="open = false" x-effect="document.body.classList.toggle('modal-open', open)">
    <div id="lead-import-modal" class="modal fade show" x-show="open" x-cloak tabindex="-1" role="dialog" aria-modal="true" aria-labelledby="lead-import-modal-title">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lead-import-modal-title">{{ __('Import leads') }}</h5>
                    <button type="button" class="btn-close" x-on:click="open = false" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    @if ($showResults)
                        <p>
                            {{ trans_choice('{0} No leads were imported.|{1} :count lead imported.|[2,*] :count leads imported.', $importedCount, ['count' => $importedCount]) }}
                        </p>

                        @if (count($failureMessages))
                            <p class="text-danger small mb-1">{{ __('Skipped rows:') }}</p>
                            <ul class="small text-danger" style="max-height:220px; overflow-y:auto;">
                                @foreach ($failureMessages as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        @endif
                    @else
                        <p class="text-muted small">
                            {{ __('Upload an Excel or CSV file with columns: Name, Company, Email, Phone, Source, Status, Notes. Name is required; everything else is optional.') }}
                        </p>

                        <button type="button" class="btn btn-link px-0" wire:click="downloadTemplate">
                            {{ __('Download template') }}
                        </button>

                        <div class="form-group">
                            <label for="lead-import-file">{{ __('File') }}</label>
                            <input wire:model="file" type="file" id="lead-import-file" class="form-control" accept=".xlsx,.xls,.csv">
                            @error('file') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            <div wire:loading wire:target="file" class="small text-muted mt-1">{{ __('Uploading…') }}</div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    @if ($showResults)
                        <button type="button" class="btn btn-secondary" x-on:click="open = false">{{ __('Close') }}</button>
                    @else
                        <button type="button" class="btn btn-secondary" x-on:click="open = false">{{ __('Cancel') }}</button>
                        <button type="button" class="btn btn-primary" wire:click="import" wire:loading.attr="disabled" wire:target="import">
                            {{ __('Import') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show" x-show="open" x-cloak x-on:click="open = false"></div>
</div>
