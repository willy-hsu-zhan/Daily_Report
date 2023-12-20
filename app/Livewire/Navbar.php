<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redirect;
use App\Models\User;

class Navbar extends Component
{
    public $currentIndex = 0;
    public $categoryLink = 'daily-log';
    protected $listeners = ['categoryLinkUpdated' => 'handleCategoryLinkUpdated'];


    public function setCurrentIndex($index)
    {
        $this->currentIndex = $index;
    }

    public function updateCategoryLink($newCategoryLink)
    {
        $this->categoryLink = $newCategoryLink;

        if(!$this->isUrl($this->categoryLink))
        {
            $this->dispatch('updateComponent', link: $newCategoryLink);
        }
        else
        {
            return Redirect::to($this->categoryLink);
        }
    }

    public function isUrl($newCategoryLink)
    {
        return Str::startsWith($newCategoryLink, ['http://', 'https://']);
    }

    public function render()
    {
        $data = User::isAdmin(Auth::user()) ? config('route.admin') : config('route.default');

        return view('livewire.components.navbar', compact('data'));
    }
}