<?php

namespace App;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class Scraper {
    public static function getManxaList($page = 1): array {
        $client = new Client([
            'base_uri' => 'https://www.mangakakalot.gg',
            'timeout'  => 10.0,
        ]);
        
        try {
            $response = $client->request('GET', '/manga-list/hot-manga?page='.$page);
            if ($response->getStatusCode() !== 200) {
                return ["error" => "Failed to fetch data from the server."];
            }
            $html = $response->getBody()->getContents();

            $crawler = new Crawler($html);

            $manxas = [];

            // Filter the HTML to get the list of manxa
            $crawler->filter('.container > .main-wrapper > .listCol > .truyen-list > .list-truyen-item-wrap')->each(function (Crawler $node) use (&$manxas) {
                $title = $node->filter('h3 > a')->attr("title");
                $url = $node->filter('h3 > a')->attr('href');
                $img = $node->filter('a > img')->attr('src');
                $newestChapter = $node->filter('.list-story-item-wrap-chapter')->text();
                $summary = $node->filter('p')->text();

                $manxas[] = [
                    'title' => $title,
                    'url'   => $url,
                    'img'   => $img,
                    'newestChapter' => $newestChapter,
                    'summary' => $summary,
                ];
            });

            return [
                "count" => count($manxas),
                "results" => $manxas
            ];

        } catch (\Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }
}