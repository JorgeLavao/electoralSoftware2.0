<?php

namespace App\Livewire;

use App\Models\News;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class NewsManager extends Component
{
    use AuthorizesRequests;

    public $title, $description, $published_at;
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
        ]);

        $payload = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'published_at' => $validated['published_at'] ?: now()->toDateString(),
        ];

        if ($this->isEdit) {
            $news->update($payload);

            session()->flash('success', 'Noticia actualizada');
        } else {
            News::create($payload + [
                'user_id' => auth()->id(),
            ]);

            session()->flash('success', 'Noticia creada');
        }

        $this->resetFields();

        return redirect()->route('news.manager');
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
        $news->delete();

        session()->flash('success', 'Noticia eliminada');
    }

    public function resetFields()
    {
        $this->title = '';
        $this->description = '';
        $this->published_at = '';
        $this->news_id = null;
        $this->isEdit = false;
    }

    public function render()
    {
        return view('livewire.news-manager', [
            'news' => News::with('user')->latest()->get()
        ]);
    }

    protected function fillFromNews(News $news): void
    {
        $this->news_id = $news->id;
        $this->title = $news->title;
        $this->description = $news->description;
        $this->published_at = optional($news->published_at)->format('Y-m-d');
        $this->isEdit = true;
    }
}
