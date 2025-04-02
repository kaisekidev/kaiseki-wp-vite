<?php

declare(strict_types=1);

namespace Kaiseki\WordPress\Vite\AssetFilter;

use Inpsyde\Assets\Asset;
use Inpsyde\Assets\Script;
use Kaiseki\WordPress\Vite\ChunkInterface;

class ScriptFilter extends AbstractAssetFilter implements AssetFilterInterface
{
    /** @var array<string, array<mixed>|callable|int|string> */
    protected array $localize = [];

    /** @var array{after:string[], before:string[]} */
    protected array $inlineScripts = [
        'after' => [],
        'before' => [],
    ];

    protected string $translationDomain = '';

    protected ?string $translationPath = null;

    public static function create(): self
    {
        return new self();
    }

    public function __invoke(Asset $asset, ChunkInterface $chunk): ?Asset
    {
        if (!$asset instanceof Script) {
            return $asset;
        }

        $asset = $this->prepareAsset($asset);

        foreach ($this->localize as $objectName => $data) {
            $asset = $asset->withLocalize($objectName, $data);
        }

        foreach ($this->inlineScripts['before'] as $inlineScript) {
            $asset->prependInlineScript($inlineScript);
        }

        foreach ($this->inlineScripts['after'] as $inlineScript) {
            $asset->appendInlineScript($inlineScript);
        }

        if ($this->translationDomain !== '') {
            $asset->withTranslation(
                $this->translationDomain,
                $this->translationPath
            );
        }

        return $asset;
    }

    /**
     * @param string                           $objectName
     * @param array<mixed>|callable|int|string $data
     *
     * @return self
     */
    public function withLocalize(string $objectName, string|int|array|callable $data): self
    {
        $this->localize[$objectName] = $data;

        return $this;
    }

    public function prependInlineScript(string $jsCode): self
    {
        $this->inlineScripts['before'][] = $jsCode;

        return $this;
    }

    public function appendInlineScript(string $jsCode): self
    {
        $this->inlineScripts['after'][] = $jsCode;

        return $this;
    }

    public function withTranslation(string $domain = 'default', ?string $path = null): self
    {
        $this->translationDomain = $domain;
        $this->translationPath = $path;

        return $this;
    }

    public function useAsyncFilter(): self
    {
        $this->withAttributes(['async' => true]);

        return $this;
    }

    public function useDeferFilter(): self
    {
        $this->withAttributes(['defer' => true]);

        return $this;
    }
}
