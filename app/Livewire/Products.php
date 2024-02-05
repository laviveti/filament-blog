<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Products extends Component
{
    public array $allProducts = [];
    public array $products = [];
    public int $page = 1;
    public string $search = '';
    public array $dropdowns = [
        'actions' => false,
        'filter' => false,
    ];
    public int $clickCount;
    public string $sortField = '';
    public ?bool $sortAsc = true;

    public function toggleDropdown(string $dropdown)
    {
        $this->dropdowns[$dropdown] = !$this->dropdowns[$dropdown];
    }

    public function mount()
    {
        $response = Http::get('https://dummyjson.com/products')->json();
        $this->allProducts = $response['products'];
        $this->loadPage();
    }

    public function sortBy(string $field)
    {
        if ($this->sortField === $field) {
            $this->clickCount++;
            if ($this->clickCount % 3 == 0) {
                $this->sortAsc = null;
                $this->sortField = '';
                $this->loadPage();
            } else {
                $this->sortAsc = !$this->sortAsc;
                $this->loadPage();
            }
        } else {
            $this->sortAsc = true;
            $this->clickCount = 1;
            $this->sortField = $field;
            $this->loadPage();
        }
    }


    public function loadPage()
    {
        if ($this->sortField != '') {
            $this->allProducts = collect($this->allProducts)->sortBy(function ($product) {
                return floatval($product[$this->sortField]);
            }, SORT_REGULAR, $this->sortAsc)->toArray();
        } else {
            $response = Http::get('https://dummyjson.com/products')->json();
            $this->allProducts = $response['products'];
        }
        $this->products = array_slice($this->allProducts, ($this->page - 1) * 5, 5);
    }


    public function goToPage($page)
    {
        $this->page = $page;
        $this->loadPage();
    }

    public function updatingSearch()
    {
        $response = Http::get('https://dummyjson.com/products')->json();
        $this->allProducts = collect($response['products'])->filter(function ($product) {
            return str_contains(strtolower($product['id']), strtolower($this->search)) ||
                str_contains(strtolower($product['title']), strtolower($this->search)) ||
                str_contains(strtolower($product['description']), strtolower($this->search)) ||
                str_contains(strtolower($product['price']), strtolower($this->search)) ||
                str_contains(strtolower($product['category']), strtolower($this->search));
        })->toArray();
        $this->goToPage(1);
    }

    public function render()
    {
        return view('livewire.products');
    }
}
