<?php

namespace App\Livewire\Campaign;

use App\Models\Campaign;
use App\Models\Group;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CreateGroup extends Component
{
    use AuthorizesRequests;

    public Campaign $campaign;
    public string $name = '';
    public string $mode = 'supporters';
    public string $type = 'volunteer';
    public string $description = '';
    public string $strategy_content = '';
    public string $responsible_name = '';
    public string $zone = '';
    public int|string $sort_order = 0;
    public bool $is_hidden = false;
    public bool $is_active = true;
    public string $supporterSearch = '';
    public array $selectedSupporters = [];

    public function mount(Campaign $campaign): void
    {
        $this->authorize('manageGroups', $campaign);

        $this->campaign = $campaign;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'mode' => ['required', Rule::in(array_keys(Group::modeOptions()))],
            'type' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'strategy_content' => ['nullable', 'string'],
            'responsible_name' => ['nullable', 'string', 'max:150'],
            'zone' => ['nullable', 'string', 'max:150'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_hidden' => ['boolean'],
            'is_active' => ['boolean'],
            'selectedSupporters' => ['array'],
            'selectedSupporters.*' => ['integer'],
        ];
    }

    public function save(): void
    {
        $this->authorize('manageGroups', $this->campaign);

        $data = $this->validate();

        if ($data['mode'] === 'strategies' && trim((string) $data['strategy_content']) === '') {
            $this->addError('strategy_content', 'Escribe el contenido de la estrategia.');
            return;
        }

        $group = $this->campaign->groups()->create([
            'type' => $data['type'],
            'mode' => $data['mode'],
            'name' => trim($data['name']),
            'description' => $this->normalizeText($data['description'] ?? null),
            'strategy_content' => $data['mode'] === 'strategies' ? $this->normalizeText($data['strategy_content'] ?? null) : null,
            'responsible_name' => $this->normalizeText($data['responsible_name'] ?? null),
            'zone' => $this->normalizeText($data['zone'] ?? null),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_hidden' => (bool) $data['is_hidden'],
            'is_active' => (bool) $data['is_active'],
        ]);

        if ($data['mode'] === 'supporters' && ! empty($data['selectedSupporters'])) {
            $validIds = $this->campaign->foreign_users()
                ->wherePivot('validate', 1)
                ->whereIn('users.id', $data['selectedSupporters'])
                ->pluck('users.id')
                ->all();

            $group->users()->sync($validIds);
        }

        session()->flash('success', 'Grupo creado correctamente.');
        $this->redirectRoute('campaign.groups.edit', [$this->campaign->code, $group->id], navigate: true);
    }

    public function render()
    {
        $this->authorize('manageGroups', $this->campaign);

        $supporters = $this->campaign->foreign_users()
            ->wherePivot('validate', 1)
            ->when($this->supporterSearch !== '', fn ($query) => $query->search($this->supporterSearch))
            ->orderBy('first_name')
            ->limit(30)
            ->get();

        return view('livewire.campaign.create-group', [
            'modeOptions' => Group::modeOptions(),
            'typeOptions' => Group::definitions(),
            'supporters' => $supporters,
        ]);
    }

    private function normalizeText(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
