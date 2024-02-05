<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Http;
use Livewire\Attributes\On;
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
    public int $clickCount = 0;
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
        // Filtrar os produtos com base na busca
        $filteredProducts = collect($this->allProducts)->filter(function ($product) {
            return stripos($product['id'], $this->search) !== false ||
                stripos($product['title'], $this->search) !== false ||
                stripos($product['description'], $this->search) !== false ||
                stripos($product['price'], $this->search) !== false ||
                stripos($product['category'], $this->search) !== false;
        })->toArray();

        // Ordenar os produtos filtrados, se necessário
        if ($this->sortField != '') {
            $filteredProducts = collect($filteredProducts)->sortBy(function ($product) {
                return floatval($product[$this->sortField]);
            }, SORT_REGULAR, $this->sortAsc)->toArray();
        }

        // Atualizar a propriedade $products com uma fatia dos produtos filtrados
        $this->products = array_slice($filteredProducts, ($this->page - 1) * 5, 5);
    }


    public function goToPage($page)
    {
        $this->page = $page;
        $this->loadPage();
    }

    #[On('searchUpdated')]
    public function updatingSearch($search)
    {
        $this->search = $search;
        $this->loadPage();
    }

    public function render()
    {
        return view('livewire.products');
    }
}
