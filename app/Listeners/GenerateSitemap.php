<?php

declare(strict_types=1);

namespace KickflipDocs\Listeners;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Kickflip\KickflipHelper;
use samdark\sitemap\Sitemap;

use function collect;
use function rtrim;
use function time;

final class GenerateSitemap
{
    /**
     * @var array|string[]
     */
    protected array $exclude = [
        '/assets',
        '/assets/*',
        '*/favicon.ico',
        '*/404',
    ];

    public function handle(): void
    {
        $kickflipConfig = KickflipHelper::config();
        $baseUrl = $kickflipConfig->get('site.baseUrl');
        $outputBaseDir = $kickflipConfig->get('paths.build.destination');

        if (! $baseUrl) {
            echo "\nTo generate a sitemap.xml file, please specify a 'baseUrl' in config.php.\n\n";

            return;
        }

        $sitemap = new Sitemap($outputBaseDir . '/sitemap.xml');

        collect($this->getOutputPaths((string) $outputBaseDir))
            ->reject(fn ($path) => $this->isExcluded($path))
            ->push('/')->sort()
            ->each(function ($path) use ($baseUrl, $sitemap) {
                $sitemap->addItem(rtrim($baseUrl, '/') . $path, time(), Sitemap::DAILY);
            });

        $sitemap->write();
    }

    public function isExcluded(string $path): bool
    {
        return Str::is($this->exclude, $path);
    }

    /**
     * @return string[]
     */
    private function getOutputPaths(string $outputBaseDir): array
    {
        /**
         * @var Filesystem|FilesystemAdapter $localFilesystem
         */
        $localFilesystem = Storage::disk('local');
        $relativeDir = Str::of($outputBaseDir)->after($localFilesystem->path(''));

        return collect($localFilesystem->allDirectories($relativeDir))
                        ->map(static fn ($value) => Str::after($value, $relativeDir))
                        ->toArray();
    }
}
