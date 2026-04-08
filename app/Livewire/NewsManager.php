<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\News;

class NewsManager extends Component
{
    public $title, $description, $published_at;
    public $news_id;
    public $isEdit = false;

    public function save()
    {
        $this->validate([
            'title' => 'required',
            'description' => 'required',
            'published_at' => 'required|date',
        ]);

        if ($this->isEdit) {
            News::find($this->news_id)->update([
                'title' => $this->title,
                'description' => $this->description,
                'published_at' => $this->published_at,
            ]);

            session()->flash('success', 'Noticia actualizada');
        } else {
            News::create([
                'title' => $this->title,
                'description' => $this->description,
                'published_at' => $this->published_at,
            ]);

            session()->flash('success', 'Noticia creada');
        }

        $this->resetFields();
    }

    public function edit($id)
    {
        $news = News::find($id);

        $this->news_id = $id;
        $this->title = $news->title;
        $this->description = $news->description;
        $this->published_at = $news->published_at;
        $this->isEdit = true;
    }

    public function delete($id)
    {
        News::find($id)->delete();

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
            'news' => News::latest()->get()
        ]);
    }
}