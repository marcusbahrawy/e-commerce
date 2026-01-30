<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Repositories\CategoryRepository;
use App\Repositories\PageRepository;
use App\Repositories\ProductRepository;

class SitemapController
{
    public function __construct(
        private CategoryRepository $categoryRepo,
        private ProductRepository $productRepo,
        private PageRepository $pageRepo
    ) {
    }

    public function index(Request $request, array $params): Response
    {
        $baseUrl = rtrim(\App\Support\Env::string('APP_URL', 'http://localhost'), '/');
        $urls = [];
        $urls[] = ['loc' => $baseUrl . '/', 'changefreq' => 'daily', 'priority' => '1.0'];
        foreach ($this->categoryRepo->getRootCategories() as $c) {
            $urls[] = ['loc' => $baseUrl . '/kategori/' . ($c['slug'] ?? ''), 'changefreq' => 'weekly', 'priority' => '0.8'];
        }
        foreach ($this->productRepo->listSlugsForSitemap() as $row) {
            $urls[] = ['loc' => $baseUrl . '/produkt/' . ($row['slug'] ?? ''), 'changefreq' => 'weekly', 'priority' => '0.7'];
        }
        foreach ($this->pageRepo->listAllForAdmin() as $p) {
            $urls[] = ['loc' => $baseUrl . '/side/' . ($p['slug'] ?? ''), 'changefreq' => 'monthly', 'priority' => '0.5'];
        }
        $xml = $this->buildXml($baseUrl, $urls);
        return Response::html($xml, 200)->withHeader('Content-Type', 'application/xml; charset=UTF-8');
    }

    private function buildXml(string $baseUrl, array $urls): string
    {
        $out = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($urls as $u) {
            $out .= '<url><loc>' . htmlspecialchars($u['loc'], ENT_XML1, 'UTF-8') . '</loc>';
            $out .= '<changefreq>' . ($u['changefreq'] ?? 'weekly') . '</changefreq>';
            $out .= '<priority>' . ($u['priority'] ?? '0.5') . '</priority></url>';
        }
        $out .= '</urlset>';
        return $out;
    }
}
