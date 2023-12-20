<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class Content extends Component
{
    use WithPagination;
    public $categoryLink = null; // 接收連結值
    public $data = null;

    #[On('updateComponent')]
    public function updated($link = 'daily-log', $data = null)
    {
        $this->categoryLink = $link;

        $this->data = $data;
    }
    
    public function render()
    {
        return view('livewire.components.content', ['link' => $this->categoryLink , 'data' => $this->data]);
    }
}
