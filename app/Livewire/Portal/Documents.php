<?php

namespace App\Livewire\Portal;

use App\Enums\DocumentFolder;
use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Documents extends Component
{
    use WithPagination;

    #[Url(as: 'carpeta', except: null)]
    public ?string $activeFolder = null;

    public function selectFolder(string $folder): void
    {
        $this->activeFolder = $folder;
        $this->resetPage();
    }

    public function clearFolder(): void
    {
        $this->activeFolder = null;
        $this->resetPage();
    }

    #[Computed]
    public function folderCounts(): Collection
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return collect();
        }

        return Document::query()
            ->where('user_id', $user->id)
            ->where('tenant_id', $user->tenant_id)
            ->where('is_visible_to_employee', true)
            ->selectRaw('folder, count(*) as total')
            ->groupBy('folder')
            ->pluck('total', 'folder');
    }

    public function render(): View
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $documents = null;

        if ($this->activeFolder !== null) {
            $documents = Document::where('user_id', $user->id)
                ->where('tenant_id', $user->tenant_id)
                ->where('is_visible_to_employee', true)
                ->where('folder', $this->activeFolder)
                ->orderByDesc('uploaded_at')
                ->orderByDesc('created_at')
                ->paginate(15);
        }

        return view('livewire.portal.documents', [
            'documents' => $documents,
            'activeFolderEnum' => $this->activeFolder !== null
                ? DocumentFolder::tryFrom($this->activeFolder)
                : null,
            'tenantKey' => (string) $user->tenant_id,
        ]);
    }
}
