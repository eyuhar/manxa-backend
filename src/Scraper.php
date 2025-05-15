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

    public static function getManxa($title): array {
        $client = new Client([
            'base_uri' => 'https://www.mangakakalot.gg',
            'timeout'  => 10.0,
        ]);
        
        try {
            $response = $client->request('GET', '/manga'.'/'.$title);
            if ($response->getStatusCode() !== 200) {
                return ["error" => "Failed to fetch data from the server."];
            }
            $html = $response->getBody()->getContents();

            $crawler = new Crawler($html);

            // Filter the HTML to get data of required manxa
            $manxaElement = $crawler->filter('.container > .main-wrapper > .leftCol');
            $manxaInfoElement = $manxaElement->filter('.manga-info-top > .manga-info-content > .manga-info-text');
            $title = $manxaInfoElement->filter('li')->eq(0)->text();
            $authors = $manxaInfoElement->filter('li')->eq(1)->filter('a')->text();
            
            $status = (function() use ($manxaInfoElement) {
                $string = $manxaInfoElement->filter('li')->eq(2)->text();
                $pieces = explode(" ", $string);
                
                return array_pop($pieces);
            })();
            
            $lastUpdate = (function() use ($manxaInfoElement) {
                $string = $manxaInfoElement->filter('li')->eq(3)->text();
                $pieces = explode(" ", $string);

                return implode(" " ,array_slice($pieces, 3));
            })();

            $views = (function() use ($manxaInfoElement) {
                $string = $manxaInfoElement->filter('li')->eq(5)->text();
                $pieces = explode(" ", $string);

                return array_pop($pieces);
            })();
            
            $genres = [];
            $manxaInfoElement->filter('.genres')->filter('a')->each(function (Crawler $node) use (&$genres) {
                array_push($genres, $node->text());
            });
            
            $rating = (function() use ($manxaInfoElement) {
                $string = $manxaInfoElement->filter('#rate_row_cmd')->text();
                $pieces = explode(" ", $string);

                return implode("", array_slice($pieces, 3, 3));
            })();
            
            $img = $manxaElement->filter('.manga-info-top > .manga-info-pic > img')->attr('src');

            $chapters = [];
            $manxaElement->filter(".chapter-list > .row")->each(function (Crawler $node) use (&$chapters) {
                $chapter = $node->filter('a')->text();
                $chapterUrl = $node->filter('a')->attr('href');
                $chapterViews = $node->filter('span')->eq(1)->text();
                $chapterUploadTime = $node->filter('span')->eq(2)->attr('title');

                $chapters[] = [
                    'chapter' => $chapter,
                    'chapterUrl' => $chapterUrl,
                    'chapterViews' => $chapterViews,
                    'chapterUploadTime' => $chapterUploadTime,
                ];
            });

            $manxa = [
                'img' => $img,
                'title' => $title,
                'authors' => $authors,
                'status' => $status,
                'lastUpdate' => $lastUpdate,
                'views' => $views,
                'genres' => $genres,
                'rating' => $rating,
                'chapters' => $chapters,
            ];

            return $manxa;

        } catch (\Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public static function getImage($url): string {
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => 
                    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n" .
                    "Accept: image/avif,image/webp,image/apng,image/*,*/*;q=0.8\r\n" .
                    "Referer: https://www.mangakakalot.gg/\r\n"
            ]
        ];
        
        $context = stream_context_create($opts);
        $imageData = @file_get_contents($url, false, $context);
        
        if ($imageData === false) {
            http_response_code(500);
            echo "Failed to load image.";
            exit;
        }
        
        return $imageData;
    }

    public static function getChapter($url): array {
        $client = new Client([
            'timeout'  => 10.0,
        ]);
        
        try {
            $response = $client->request('GET', $url);
            if ($response->getStatusCode() !== 200) {
                return ["error" => "Failed to fetch data from the server."];
            }
            $html = $response->getBody()->getContents();

            $crawler = new Crawler($html);

            // Filter the HTML to get the image URLs of required manxa chapter
            $chapterImageElements = $crawler->filter('.container-chapter-reader > img');
            
            $imageUrls = [];
            $chapterImageElements->each(function (Crawler $node) use (&$imageUrls) {
                array_push($imageUrls, $node->attr('src'));
            });

            return $imageUrls;

        } catch (\Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }
}