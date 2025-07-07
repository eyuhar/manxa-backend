<?php

namespace App;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class Scraper
{
    public static function getManxaList($page = 1): array
    {
        $client = new Client([
            'base_uri' => 'https://www.mangakakalot.gg',
            'timeout'  => 10.0,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3',
                'Referer' => 'https://www.mangakakalot.gg/'
            ]
        ]);

        try {
            $response = $client->request('GET', '/manga-list/hot-manga?page=' . $page);
            if ($response->getStatusCode() !== 200) {
                return ["error" => "Failed to fetch data from the server."];
            }
            $html = $response->getBody()->getContents();

            $crawler = new Crawler($html);

            $manxas = [];

            $wrapper = $crawler->filter('.container > .main-wrapper > .listCol > .truyen-list');

            if ($wrapper->count() === 0) {
                return ["error" => "Could not find main list wrapper on the page."];
            }

            // Filter the HTML to get the list of manxa
            $wrapper->filter('.list-truyen-item-wrap')->each(function (Crawler $node) use (&$manxas) {
                try {
                    $title = $node->filter('h3 > a')->count() > 0
                        ? $node->filter('h3 > a')->attr("title")
                        : 'Unknown Title';

                    $url = $node->filter('h3 > a')->count() > 0
                        ? $node->filter('h3 > a')->attr('href')
                        : '';

                    $img = $node->filter('a > img')->count() > 0
                        ? $node->filter('a > img')->attr('src')
                        : '';

                    $newestChapter = $node->filter('.list-story-item-wrap-chapter')->count() > 0
                        ? $node->filter('.list-story-item-wrap-chapter')->text()
                        : '';

                    $summary = $node->filter('p')->count() > 0
                        ? $node->filter('p')->text()
                        : '';

                    $manxas[] = [
                        'title' => $title,
                        'url'   => $url,
                        'img'   => $img,
                        'newestChapter' => $newestChapter,
                        'summary' => $summary,
                    ];
                } catch (\Exception $e) {
                    error_log("Error parsing an element on the page: " . $e->getMessage());
                }
            });

            // Total results
            $totalResults = 0;
            try {
                $totalResultsString = $wrapper->filter('.panel_page_number > .group_qty > .page_blue')->text();
                $totalResults = (int)preg_replace('/[^\d]/', '', $totalResultsString);
            } catch (\Exception $e) {
                error_log("Could not parse totalResults: " . $e->getMessage());
            }

            // Total pages
            $totalPages = 1;
            try {
                $totalPagesString = $wrapper->filter('.panel_page_number > .group_page > .page_last')->text();
                preg_match('/\((\d+)\)/', $totalPagesString, $matches);
                $totalPages = isset($matches[1]) ? (int)$matches[1] : 1;
            } catch (\Exception $e) {
                error_log("Could not parse totalPages: " . $e->getMessage());
            }

            return [
                "totalResults" => $totalResults,
                "totalPages" => $totalPages,
                "results" => $manxas
            ];
        } catch (\Exception $e) {
            error_log("Exception in getManxaList: " . $e->getMessage());
            return ["error" => $e->getMessage()];
        }
    }

    public static function getManxa($manxaUrl): array
    {
        $client = new Client([
            'timeout'  => 10.0,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3',
                'Referer' => 'https://www.mangakakalot.gg/'
            ]
        ]);

        try {
            $response = $client->request('GET', $manxaUrl);

            if ($response->getStatusCode() !== 200) {
                return ["error" => "Failed to fetch data from the server."];
            }

            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);

            $manxaElement = $crawler->filter('.container > .main-wrapper > .leftCol');

            $manxaInfoElement = $manxaElement->filter('.manga-info-top > .manga-info-content > .manga-info-text');

            $title = $manxaInfoElement->filter('li')->eq(0)->count() > 0 ? $manxaInfoElement->filter('li')->eq(0)->text() : 'Unknown Title';
            $authors = $manxaInfoElement->filter('li')->eq(1)->filter('a')->count() > 0 ? $manxaInfoElement->filter('li')->eq(1)->filter('a')->text() : '';

            $status = (function () use ($manxaInfoElement) {
                if ($manxaInfoElement->filter('li')->eq(2)->count() === 0) return '';
                $string = $manxaInfoElement->filter('li')->eq(2)->text();
                $pieces = explode(" ", $string);
                return array_pop($pieces);
            })();

            $lastUpdate = (function () use ($manxaInfoElement) {
                if ($manxaInfoElement->filter('li')->eq(3)->count() === 0) return '';
                $string = $manxaInfoElement->filter('li')->eq(3)->text();
                $pieces = explode(" ", $string);
                return implode(" ", array_slice($pieces, 3));
            })();

            $views = (function () use ($manxaInfoElement) {
                if ($manxaInfoElement->filter('li')->eq(5)->count() === 0) return 0;
                $string = $manxaInfoElement->filter('li')->eq(5)->text();
                // Remove all non-digit characters
                $numberString = preg_replace('/[^\d]/', '', $string);

                return (int) $numberString;
            })();

            $genres = [];
            if ($manxaInfoElement->filter('.genres')->count() > 0) {
                $manxaInfoElement->filter('.genres')->filter('a')->each(function (Crawler $node) use (&$genres) {
                    $genres[] = $node->text();
                });
            }

            $rating = (function () use ($manxaInfoElement) {
                if ($manxaInfoElement->filter('#rate_row_cmd')->count() === 0) return '';
                $string = $manxaInfoElement->filter('#rate_row_cmd')->text();
                $pieces = explode(" ", $string);
                return implode("", array_slice($pieces, 3, 3));
            })();

            $img = $manxaElement->filter('.manga-info-top > .manga-info-pic > img')->count() > 0
                ? $manxaElement->filter('.manga-info-top > .manga-info-pic > img')->attr('src')
                : '';

            $summary = '';
            if ($manxaElement->filter('#contentBox')->count() > 0) {
                $contentBox = $manxaElement->filter('#contentBox');

                $contentBox->filter('h2')->each(function (Crawler $node) {
                    $domNode = $node->getNode(0);
                    if ($domNode && $domNode->parentNode) {
                        $domNode->parentNode->removeChild($domNode);
                    }
                });

                $summary = trim($contentBox->text());
            }

            $chapters = [];
            if ($manxaElement->filter('.chapter-list > .row')->count() > 0) {
                $manxaElement->filter(".chapter-list > .row")->each(function (Crawler $node) use (&$chapters) {
                    $chapter = $node->filter('a')->count() > 0 ? $node->filter('a')->text() : '';
                    $chapterUrl = $node->filter('a')->count() > 0 ? $node->filter('a')->attr('href') : '';
                    $chapterViews = $node->filter('span')->eq(1)->count() > 0 ? $node->filter('span')->eq(1)->text() : '0';
                    $chapterUploadTime = $node->filter('span')->eq(2)->count() > 0 ? $node->filter('span')->eq(2)->attr('title') : '';

                    $chapters[] = [
                        'chapter' => $chapter,
                        'chapterUrl' => $chapterUrl,
                        'chapterViews' => (int) $chapterViews,
                        'chapterUploadTime' => $chapterUploadTime,
                    ];
                });
            }

            return [
                'img' => $img,
                'title' => $title,
                'authors' => $authors,
                'status' => $status,
                'lastUpdate' => $lastUpdate,
                'views' => $views,
                'genres' => $genres,
                'rating' => $rating,
                'summary' => $summary,
                'chapters' => $chapters,
            ];
        } catch (\Exception $e) {
            error_log("Error loading Manxa $manxaUrl: " . $e->getMessage());
            return ["error" => "An error occurred while processing the request."];
        }
    }

    public static function getImage($url): string|false
    {
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
        $imageData = file_get_contents($url, false, $context);

        if ($imageData === false) {
            error_log("[getImage] Failed to fetch image from: $url");
            return false;
        }

        return $imageData;
    }

    public static function getChapter($url): array
    {
        $client = new Client([
            'timeout'  => 10.0,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3',
                'Referer' => 'https://www.mangakakalot.gg/'
            ]
        ]);

        try {
            $response = $client->request('GET', $url);

            // Check if the response is successful
            if ($response->getStatusCode() !== 200) {
                error_log("Failed to fetch chapter from URL: $url (Status code: {$response->getStatusCode()})");
                return ["error" => "Failed to fetch data from the server."];
            }

            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);

            // Filter the HTML to get the image URLs of required manxa chapter
            $chapterImageElements = $crawler->filter('.container-chapter-reader > img');

            $imageUrls = [];
            $chapterImageElements->each(function (Crawler $node) use (&$imageUrls) {
                $src = $node->attr('src');
                if (!empty($src)) {
                    $imageUrls[] = $src;
                }
            });

            // Check if any images were found
            if (empty($imageUrls)) {
                error_log("No images found for chapter URL: $url");
                return ["error" => "No images found for this chapter."];
            }

            // Return the list of image URLs
            return $imageUrls;
        } catch (\Exception $e) {
            error_log("Exception in getChapter: " . $e->getMessage());
            return ["error" => "An unexpected error occurred while fetching the chapter."];
        }
    }

    public static function cleanSearchQuery(string $input): string
    {
        // 1. Replace all desired characters with _ or remove them
        $replacements = [
            '/[\s]*-[\s]*/' => '_',   // " - ", " -", "- ", "-" → "_"
            '/\s+/'         => '_',   // Spaces → "_"
            '/,/'           => '',    // Remove comma
            '/[!?]/'        => '',    // Remove ! and ?
            '/:/'           => '_',   // : → _
            '/\./'          => '',    // Remove dot
            "/'/"           => '_',   // Apostrophe → _
        ];

        $cleaned = $input;

        foreach ($replacements as $pattern => $replacement) {
            $cleaned = preg_replace($pattern, $replacement, $cleaned);
        }

        // 2. Reduce multiple underscores to a single one
        $cleaned = preg_replace('/_+/', '_', $cleaned);

        // 3. Remove leading or trailing underscores
        $cleaned = trim($cleaned, '_');

        // 4. Convert to lowercase
        $cleaned = strtolower($cleaned);

        return $cleaned;
    }

    public static function getSearchResults($query, $page = 1): array
    {
        $client = new Client([
            'base_uri' => 'https://www.mangakakalot.gg',
            'timeout'  => 10.0,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3',
                'Referer' => 'https://www.mangakakalot.gg/'
            ]
        ]);

        try {
            $response = $client->request('GET', '/search/story/' . urlencode(self::cleanSearchQuery($query)) . '?page=' . $page);

            if ($response->getStatusCode() !== 200) {
                error_log("Search failed for query '{$query}' (status: {$response->getStatusCode()})");
                return ["error" => "Failed to fetch search results from the server."];
            }

            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);

            $results = [];

            // Filter the HTML to get the search results
            $leftCol = $crawler->filter('.container > .main-wrapper > .leftCol');
            $leftCol->filter('.daily-update > .panel_story_list > .story_item')->each(function (Crawler $node) use (&$results) {
                try {
                    $title = $node->filter('.story_item_right > .story_name > a')->count() > 0
                        ? $node->filter('.story_item_right > .story_name > a')->text()
                        : 'Unknown Title';

                    $url = $node->filter('a')->count() > 0
                        ? $node->filter('a')->attr('href')
                        : '';

                    $img = $node->filter('a > img')->count() > 0
                        ? $node->filter('a > img')->attr('src')
                        : '';

                    $newestChapter = $node->filter('.story_item_right > .story_chapter')->eq(0)->filter('a')->count() > 0
                        ? $node->filter('.story_item_right > .story_chapter')->eq(0)->filter('a')->text()
                        : '';

                    $results[] = [
                        'title' => $title,
                        'url'   => $url,
                        'img'   => $img,
                        'newestChapter' => $newestChapter
                    ];
                } catch (\Exception $e) {
                    error_log("Failed to parse one search result item: " . $e->getMessage());
                }
            });

            // Total results
            $totalResults = 0;
            try {
                $totalResultsString = $leftCol->filter('.panel_page_number > .group_qty > .page_blue')->text();
                $totalResults = (int)preg_replace('/[^\d]/', '', $totalResultsString);
            } catch (\Exception $e) {
                error_log("Could not parse totalResults: " . $e->getMessage());
            }

            // Total pages
            $totalPages = 1;
            try {
                $totalPagesString = $leftCol->filter('.panel_page_number > .group_page > .page_last')->text();
                preg_match('/\((\d+)\)/', $totalPagesString, $matches);
                $totalPages = isset($matches[1]) ? (int)$matches[1] : 1;
            } catch (\Exception $e) {
                error_log("Could not parse totalPages: " . $e->getMessage());
            }

            return [
                "totalResults" => $totalResults,
                "totalPages" => $totalPages,
                "results" => $results
            ];
        } catch (\Exception $e) {
            error_log("Exception in getSearchResults: " . $e->getMessage());
            return ["error" => "An unexpected error occurred during the search."];
        }
    }
}
