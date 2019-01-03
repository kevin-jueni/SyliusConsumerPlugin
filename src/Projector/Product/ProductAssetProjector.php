<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector\Product;

use App\Cloudtec\Bundle\SyliusBundle\Entity\Asset;
use Sylake\SyliusConsumerPlugin\Event\ProductUpdated;
use App\Cloudtec\Bundle\SyliusBundle\Entity\ProductInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ProductAssetProjector
{
    /** @var RepositoryInterface */
    private $assetRepository;

    /**
     * @param RepositoryInterface $assetRepository
     */
    public function __construct(
        RepositoryInterface $assetRepository
    ) {
        $this->assetRepository = $assetRepository;
    }

    /**
     * @param ProductUpdated $event
     * @param ProductInterface $product
     */
    public function __invoke(ProductUpdated $event, ProductInterface $product): void
    {
        $this->handleProductAssets($event->assets(), $product);
    }

    /**
     * @param array $assetCodes
     * @param ProductInterface $product
     */
    private function handleProductAssets(array $assetCodes, ProductInterface $product): void
    {
        $currentAssets = $product->getAssets()->toArray();
        $processedAssets = $this->processAssets($assetCodes);

        $assetsToAdd = ResourceUtil::diff($processedAssets, $currentAssets);
        foreach ($assetsToAdd as $asset) {
            $product->addAsset($asset);
        }

        $assetsToRemove = ResourceUtil::diff($currentAssets, $processedAssets);
        foreach ($assetsToRemove as $asset) {
            $product->removeAsset($asset);
        }
    }

    /**
     * @param string[] $assetCodes
     * @return Asset[]
     */
    private function processAssets(array $assetCodes): array
    {
        /** @var Asset[] $assets */
        $assets = [];

        foreach ($assetCodes as $assetCode) {
            /** @var Asset $asset */
            $asset = $this->assetRepository->findOneBy(['code' => $assetCode]);

            if ($asset === null) {
                continue;
            }

            $assets[] = $asset;
        }

        return $assets;
    }
}
