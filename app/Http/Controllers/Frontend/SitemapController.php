<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * Pages statiques du site vitrine, hors contenu dynamique (modules).
     *
     * @var array<int, array{route: string, changefreq: string, priority: string}>
     */
    private const STATIC_PAGES = [
        ['route' => 'index', 'changefreq' => 'weekly', 'priority' => '1.0'],
        ['route' => 'projet', 'changefreq' => 'monthly', 'priority' => '0.8'],
        ['route' => 'association', 'changefreq' => 'monthly', 'priority' => '0.6'],
        ['route' => 'adhesion', 'changefreq' => 'monthly', 'priority' => '0.6'],
        ['route' => 'categories.all', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['route' => 'frontend.modules.index', 'changefreq' => 'weekly', 'priority' => '0.8'],
        ['route' => 'contact', 'changefreq' => 'yearly', 'priority' => '0.4'],
        ['route' => 'connexion', 'changefreq' => 'yearly', 'priority' => '0.3'],
        ['route' => 'charte-graphique', 'changefreq' => 'yearly', 'priority' => '0.2'],
        ['route' => 'mentions-legales', 'changefreq' => 'yearly', 'priority' => '0.2'],
        ['route' => 'conditions-utilisation', 'changefreq' => 'yearly', 'priority' => '0.2'],
        ['route' => 'confidentialite', 'changefreq' => 'yearly', 'priority' => '0.2'],
        ['route' => 'cookies', 'changefreq' => 'yearly', 'priority' => '0.2'],
    ];

    public function index(): Response
    {
        $urls = Cache::remember('sitemap.xml', now()->addHour(), fn () => $this->buildUrls());

        $xml = view('frontend.sitemap', compact('urls'))->render();

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * @return array<int, array{loc: string, lastmod: ?string, changefreq: string, priority: string}>
     */
    private function buildUrls(): array
    {
        $urls = [];

        foreach (self::STATIC_PAGES as $page) {
            $urls[] = [
                'loc' => route($page['route']),
                'lastmod' => null,
                'changefreq' => $page['changefreq'],
                'priority' => $page['priority'],
            ];
        }

        Module::query()
            ->publiclyListable()
            ->whereNotNull('category_id')
            ->select(['id', 'category_id', 'updated_at'])
            ->chunk(200, function ($modules) use (&$urls): void {
                foreach ($modules as $module) {
                    $urls[] = [
                        'loc' => route('frontend.modules.show', [
                            'category' => $module->category_id,
                            'module' => $module->id,
                        ]),
                        'lastmod' => $module->updated_at?->toAtomString(),
                        'changefreq' => 'monthly',
                        'priority' => '0.6',
                    ];
                }
            });

        return $urls;
    }
}
