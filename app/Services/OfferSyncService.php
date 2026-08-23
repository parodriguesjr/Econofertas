<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\Supermarket;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class OfferSyncService
{
    public const CITY = 'Nova Odessa';

    public function sync(): array
    {
        return [
            'São Vicente' => $this->syncSaoVicente(),
            'Pague Menos' => $this->syncPagueMenos(),
        ];
    }

    public function syncSaoVicente(): int
    {
        $market = $this->market('São Vicente', 'sao-vicente', '#D9272E');
        $url = 'https://www.svicente.com.br/ofertas';
        $xpath = $this->xpath($this->download($url));
        $nodes = $xpath->query("//div[@id='NovaOdessa']//div[contains(concat(' ', normalize-space(@class), ' '), ' img_desktop ')]//img[@src]");
        $seen = [];

        foreach ($nodes as $node) {
            $image = $this->absoluteUrl($node->getAttribute('src'), 'https://www.svicente.com.br');
            if ($image === '' || isset($seen[$image])) {
                continue;
            }
            $seen[$image] = true;
            $this->store($market, [
                'external_id' => hash('sha256', $image),
                'type' => 'flyer_image',
                'name' => 'Encarte de ofertas - Nova Odessa',
                'image_url' => $image,
                'source_url' => $url,
            ]);
        }

        $this->deactivateMissing($market, array_keys($seen), true);

        return count($seen);
    }

    public function syncPagueMenos(): int
    {
        $market = $this->market('Pague Menos', 'pague-menos', '#8D2878');
        $url = 'https://www.superpaguemenos.com.br/folhetos-de-oferta';
        $xpath = $this->xpath($this->download($url));
        $forms = $xpath->query("//form[contains(concat(' ', normalize-space(@class), ' '), ' product-form ')][@data-json]");
        $seen = [];

        foreach ($forms as $form) {
            $data = json_decode(html_entity_decode($form->getAttribute('data-json'), ENT_QUOTES | ENT_HTML5), true);
            if (! is_array($data) || empty($data['item_id']) || empty($data['item_name'])) {
                continue;
            }
            $externalId = (string) $data['item_id'];
            $container = $this->closestProduct($form);
            $imageNode = $container ? $xpath->query('.//img[@data-src]', $container)->item(0) : null;
            $priceNode = $xpath->query(".//input[@name='price']", $form)->item(0);
            $path = $form->getAttribute('action');
            $brand = isset($data['item_brand']) ? (string) $data['item_brand'] : null;
            $name = (string) $data['item_name'];
            $salePrice = $priceNode ? (float) $priceNode->getAttribute('value') : (float) ($data['price'] ?? 0);
            $original = (float) ($data['price'] ?? 0);
            $seen[] = $externalId;

            $this->store($market, [
                'external_id' => $externalId,
                'type' => 'product',
                'name' => $name,
                'brand' => $brand,
                'match_key' => OfferName::matchKey($name, $brand),
                'price' => $salePrice ?: null,
                'original_price' => $original > $salePrice ? $original : null,
                'image_url' => $imageNode ? $this->absoluteUrl($imageNode->getAttribute('data-src'), 'https:') : null,
                'source_url' => $this->absoluteUrl($path, 'https://www.superpaguemenos.com.br'),
            ]);
        }

        $this->deactivateMissing($market, $seen);

        return count($seen);
    }

    private function download(string $url): string
    {
        $response = Http::timeout(30)->retry(2, 1000)->withHeaders([
            'User-Agent' => 'Econofertas/1.0 (+ofertas locais; contato do administrador)',
            'Accept-Language' => 'pt-BR,pt;q=0.9',
        ])->get($url);

        if (! $response->successful()) {
            throw new RuntimeException("Falha ao consultar {$url}: HTTP {$response->status()}");
        }

        return $response->body();
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument;
        @$document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);

        return new DOMXPath($document);
    }

    private function market(string $name, string $slug, string $color): Supermarket
    {
        return Supermarket::firstOrCreate(['slug' => $slug], [
            'name' => $name,
            'description' => 'Loja de Nova Odessa/SP',
            'color' => $color,
            'is_active' => true,
        ]);
    }

    private function store(Supermarket $market, array $data): void
    {
        Offer::updateOrCreate(
            ['supermarket_id' => $market->id, 'external_id' => $data['external_id']],
            $data + ['city' => self::CITY, 'is_active' => true, 'captured_at' => now()],
        );
    }

    private function deactivateMissing(Supermarket $market, array $externalIds, bool $hashedUrls = false): void
    {
        $ids = $hashedUrls ? array_map(fn ($url) => hash('sha256', $url), $externalIds) : $externalIds;
        $query = $market->offers();
        if ($ids !== []) {
            $query->whereNotIn('external_id', $ids);
        }
        $query->update(['is_active' => false]);
    }

    private function closestProduct(DOMElement $element): ?DOMElement
    {
        for ($node = $element->parentNode; $node instanceof DOMElement; $node = $node->parentNode) {
            if (preg_match('/(^|\s)li(\s|$)/', $node->getAttribute('class'))) {
                return $node;
            }
        }

        return null;
    }

    private function absoluteUrl(string $url, string $base): string
    {
        if (Str::startsWith($url, '//')) {
            return 'https:'.$url;
        }
        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return rtrim($base, '/').'/'.ltrim($url, '/');
    }
}
