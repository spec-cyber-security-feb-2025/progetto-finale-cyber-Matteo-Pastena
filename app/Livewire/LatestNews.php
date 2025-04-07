<?php

namespace App\Livewire;

use GuzzleHttp\Client;
use Livewire\Component;
use App\Services\HttpService;

class LatestNews extends Component
{
    public $selectedApi;
    public $news;
    protected $httpService;

    public function __construct()
    {
        $this->httpService = app(HttpService::class);
    }

    public function fetchNews()
    {
        $allowedSources = [
            'NewsAPI-IT' => 'https://newsapi.org/v2/top-headlines?country=it&apiKey=5fbe92849d5648eabcbe072a1cf91473' . env('NEWSAPI_API_KEY'),
            'NewsAPI-UK' => 'https://newsapi.org/v2/top-headlines?country=gb&apiKey=5fbe92849d5648eabcbe072a1cf91473',
            'NewsAPI-US' => 'https://newsapi.org/v2/top-headlines?country=us&apiKey=5fbe92849d5648eabcbe072a1cf91473' . env('NEWSAPI_API_KEY'),
        ];
    
        // Controllo che la chiave esista nella lista consentita
        if (!array_key_exists($this->selectedApi, $allowedSources)) {
            $this->news = 'Fonte non autorizzata.';
            return;
        }
    
        // Recupero l'URL sicuro dalla lista
        $safeUrl = $allowedSources[$this->selectedApi];
    
        // Chiamata all'API
        $this->news = json_decode($this->httpService->getRequest($safeUrl), true);
    }
}
