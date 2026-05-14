<?php

namespace App\Livewire;

use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class NewsManager extends Component
{
    use AuthorizesRequests, WithFileUploads;

    public $title, $description, $published_at;
    public $image;
    public $current_image_path;
    public $news_id;
    public $isEdit = false;

    public function mount(?News $news = null)
    {
        if ($news && $news->exists) {
            $this->authorize('update', $news);
            $this->fillFromNews($news);
            return;
        }

        $this->authorize('create', News::class);
    }

    public function save()
    {
        if ($this->isEdit) {
            $news = News::find($this->news_id);

            if (! $news) {
                session()->flash('success', 'La noticia que intentas editar ya no existe.');

                return redirect()->route('news.manager');
            }

            $this->authorize('update', $news);
        } else {
            $this->authorize('create', News::class);
        }

        $validated = $this->validate([
            'title' => 'required',
            'description' => 'required',
            'published_at' => 'nullable|date',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ]);

        $payload = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'published_at' => $validated['published_at'] ?: now()->toDateString(),
        ];

        if ($this->image) {
            $payload['image_path'] = $this->image->store('news', 'public');
        }

        if ($this->isEdit) {
            $oldImagePath = $news->image_path;

            $news->update($payload);

            if ($this->image && $oldImagePath) {
                Storage::disk('public')->delete($oldImagePath);
            }

            session()->flash('success', 'Noticia actualizada');
        } else {
            /** @var User|null $user */
            $user = auth()->user();
            $campaign = $user?->is_super_admin ? null : session('current_campaign');

            News::create($payload + [
                'user_id' => auth()->id(),
                'campaign_id' => $campaign?->id,
            ]);

            session()->flash('success', 'Noticia creada');
        }

        $this->resetFields();

        return redirect()->route('news.manager');
    }

    public function updatedImage(): void
    {
        $this->validateOnly('image', [
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ]);
    }

    public function edit($id)
    {
        $news = News::find($id);

        if (! $news) {
            return;
        }

        $this->authorize('update', $news);
        $this->fillFromNews($news);
    }

    public function delete($id)
    {
        $news = News::find($id);

        if (! $news) {
            return;
        }

        $this->authorize('delete', $news);

        if ($news->image_path) {
            Storage::disk('public')->delete($news->image_path);
        }

        $news->delete();

        session()->flash('success', 'Noticia eliminada');
    }

    public function resetFields()
    {
        $this->title = '';
        $this->description = '';
        $this->published_at = '';
        $this->image = null;
        $this->current_image_path = null;
        $this->news_id = null;
        $this->isEdit = false;
    }

    public function render()
    {
        $currentCampaign = session('current_campaign');
        $user = auth()->user();

        return view('livewire.news-manager', [
            'news' => News::with(['user', 'campaign'])
                ->visibleForUserInCampaign($user, $currentCampaign)
                ->latest()
                ->get()
        ]);
    }

    protected function fillFromNews(News $news): void
    {
        $this->news_id = $news->id;
        $this->title = $news->title;
        $this->description = $news->description;
        $this->published_at = optional($news->published_at)->format('Y-m-d');
        $this->current_image_path = $news->image_path;
        $this->image = null;
        $this->isEdit = true;
    }
}
