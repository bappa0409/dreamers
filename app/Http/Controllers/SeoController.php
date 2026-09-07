<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SeoController extends Controller
{
    /**
     * Public, indexable landing pages only. Keep this in sync with the
     * public route group in routes/web.php — never list admin, member,
     * auth, or dashboard routes here.
     */
    protected function publicUrls(): array
    {
        return [
            ['route' => 'home', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['route' => 'about', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['route' => 'activities', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['route' => 'transparency', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['route' => 'faq', 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['route' => 'contact', 'priority' => '0.7', 'changefreq' => 'monthly'],
        ];
    }

    public function sitemap(): Response
    {
        $urls = collect($this->publicUrls())->map(fn ($entry) => [
            'loc' => route($entry['route']),
            'priority' => $entry['priority'],
            'changefreq' => $entry['changefreq'],
        ]);

        $xml = view('seo.sitemap', compact('urls'))->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /member',
            'Disallow: /dashboard',
            'Disallow: /login',
            'Disallow: /setup-password',
            'Disallow: /forgot-password',
            'Disallow: /reset-password',
            'Disallow: /change-password',
            '',
            'Sitemap: ' . route('sitemap'),
        ];

        return response(implode("\n", $lines), 200)
            ->header('Content-Type', 'text/plain');
    }
}
