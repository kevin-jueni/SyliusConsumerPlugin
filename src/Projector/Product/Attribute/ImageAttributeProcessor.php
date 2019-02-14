<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector\Product\Attribute;

use Gaufrette\FilesystemInterface;
use Psr\Log\LoggerInterface;
use Sylake\SyliusConsumerPlugin\Model\Attribute;
use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ImageAttributeProcessor implements AttributeProcessorInterface
{
    /** @var FactoryInterface */
    private $productImageFactory;

    /** @var RepositoryInterface */
    private $productImageRepository;

    /** @var string */
    private $imageAttribute;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var string
     */
    private $basePath;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ImageAttributeProcessor constructor.
     * @param FactoryInterface $productImageFactory
     * @param RepositoryInterface $productImageRepository
     * @param string $imageAttribute
     * @param FilesystemInterface $filesystem
     * @param string $basePath
     * @param LoggerInterface $logger
     */
    public function __construct(
        FactoryInterface $productImageFactory,
        RepositoryInterface $productImageRepository,
        string $imageAttribute,
        FilesystemInterface $filesystem,
        string $basePath,
        LoggerInterface $logger
    ) {
        $this->productImageFactory = $productImageFactory;
        $this->productImageRepository = $productImageRepository;
        $this->imageAttribute = $imageAttribute;
        $this->filesystem = $filesystem;
        $this->basePath = $basePath;
        $this->logger = $logger;
    }

    /** {@inheritdoc} */
    public function process(ProductInterface $product, Attribute $attribute): array
    {
        if (!$this->supports($attribute)) {
            return [];
        }

        $currentImages = $product->getImagesByType('akeneo')->toArray();
        $processedImages = $this->processImages($product, $attribute);

        $compareImages = function (ProductImageInterface $a, ProductImageInterface $b): int {
            return $a->getId() <=> $b->getId();
        };

        $productImageToAdd = array_udiff(
            $processedImages,
            $currentImages,
            $compareImages
        );

        foreach ($productImageToAdd as $productImage) {
            $product->addImage($productImage);
        }

        $productImageToRemove = array_udiff(
            $currentImages,
            $processedImages,
            $compareImages
        );
        foreach ($productImageToRemove as $productImage) {
            $product->removeImage($productImage);
        }

        return [];
    }

    private function supports(Attribute $attribute): bool
    {
        return strpos($attribute->attribute(),
                $this->imageAttribute) === 0 && (null === $attribute->data() || is_string($attribute->data()));
    }

    /**
     * @param ProductInterface $product
     * @param Attribute $attribute
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function processImages(ProductInterface $product, Attribute $attribute): array
    {
        if (null === $attribute->data()) {
            return [];
        }

        /** @var ProductImageInterface|null $image */
        $productImage = $this->productImageRepository->findOneBy([
            'owner' => $product,
            'type' => 'akeneo',
            'path' => $attribute->data(),
        ]);

        if (null === $productImage) {
            /** @var ProductImageInterface $productImage */
            $productImage = $this->productImageFactory->createNew();
        }

        try {
            $path = $this->persistAsset($attribute->data());
            $productImage->setType('akeneo');
            $productImage->setPath($path);

            return [$productImage];
        } catch (\Exception $e) {
            $this->logger->debug(sprintf('Unable to persist asset "%s".', $attribute->data()));
            return [];
        }
    }

    /**
     * @param string $imagePath
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function persistAsset(string $imagePath): string
    {
        $path = $this->basePath . urlencode(urlencode($imagePath)) . '/preview';

        $this->logger->debug(sprintf('Persisting asset with path "%s".', $path));

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $path);

        $success = $this->filesystem->write(
            $imagePath,
            $response->getBody(),
            true
        );

        if ($success === false) {
            throw new \Exception(sprintf(
                'Unable to persist asset: %s',
                $path
            ));
        }

        return $imagePath;
    }
}
