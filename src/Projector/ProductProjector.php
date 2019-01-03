<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector;

use App\Cloudtec\Bundle\SyliusBundle\Entity\ProductInterface;
use Psr\Log\LoggerInterface;
use Sylake\SyliusConsumerPlugin\Event\ProductUpdated;
use Sylake\SyliusConsumerPlugin\Projector\Product\ProductAssetProjector;
use Sylake\SyliusConsumerPlugin\Projector\Product\ProductAssociationProjector;
use Sylake\SyliusConsumerPlugin\Projector\Product\ProductAttributeProjector;
use Sylake\SyliusConsumerPlugin\Projector\Product\ProductPostprocessorInterface;
use Sylake\SyliusConsumerPlugin\Projector\Product\ProductSlugGeneratorInterface;
use Sylake\SyliusConsumerPlugin\Projector\Product\ProductTaxonProjector;
use Sylius\Component\Core\Model\ProductTranslationInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Resource\Model\TranslationInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class ProductProjector
{
    /**
     * @var ProductFactoryInterface
     */
    private $productFactory;

    /**
     * @var ProductSlugGeneratorInterface
     */
    private $productSlugGenerator;

    /**
     * @var RepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductTaxonProjector
     */
    private $productTaxonProjector;

    /**
     * @var ProductAttributeProjector
     */
    private $productAttributeProjector;

    /**
     * @var ProductAssociationProjector
     */
    private $productAssociationProjector;

    /**
     * @var ProductAssetProjector
     */
    private $productAssetProjector;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array|ProductPostprocessorInterface[]
     */
    private $postprocessors = [];

    public function __construct(
        ProductFactoryInterface $productFactory,
        ProductSlugGeneratorInterface $productSlugGenerator,
        RepositoryInterface $productRepository,
        ProductTaxonProjector $productTaxonProjector,
        ProductAttributeProjector $productAttributeProjector,
        ProductAssociationProjector $productAssociationProjector,
        ProductAssetProjector $productAssetProjector,
        LoggerInterface $logger
    ) {
        $this->productFactory = $productFactory;
        $this->productSlugGenerator = $productSlugGenerator;
        $this->productRepository = $productRepository;
        $this->productTaxonProjector = $productTaxonProjector;
        $this->productAttributeProjector = $productAttributeProjector;
        $this->productAssociationProjector = $productAssociationProjector;
        $this->productAssetProjector = $productAssetProjector;
        $this->logger = $logger;
    }

    public function addPostprocessor(ProductPostprocessorInterface $postprocessor): void
    {
        $this->postprocessors[] = $postprocessor;
    }

    public function __invoke(ProductUpdated $event): void
    {
        $this->logger->debug(sprintf('Projecting product with code "%s".', $event->code()));

        $product = $this->provideProduct($event->code());

        ($this->productTaxonProjector)($event, $product);
        ($this->productAttributeProjector)($event, $product);
        ($this->productAssociationProjector)($event, $product);
        ($this->productAssetProjector)($event, $product);
        $this->handleEnabled($event->enabled(), $product);
        $this->handleCreatedAt($event->createdAt(), $product);
        $this->handleSlug($product);
        $this->handleParentCode($event->getParentCode(), $product, $event->code());

        $this->handlePostprocessors($event, $product);

        $this->productRepository->add($product);
    }

    private function handleParentCode(?string $parentCode, ProductInterface $product, string $code): void
    {
        if ($parentCode) {
            $product->setParentCode($parentCode);
        } else {
            $product->setParentCode($code);
        }
    }

    private function handleEnabled(bool $enabled, ProductInterface $product): void
    {
        $product->setEnabled($enabled);
    }

    private function handleCreatedAt(\DateTime $createdAt, ProductInterface $product): void
    {
        // Doctrine saves dates stripping the timezone and retrieves them with the default one,
        // so we have to convert it to the default timezone to make it right
        $createdAt->setTimezone(new \DateTimeZone(date_default_timezone_get()));

        $product->setCreatedAt($createdAt);
        foreach ($product->getVariants() as $productVariant) {
            $productVariant->setCreatedAt($createdAt);
        }
    }

    private function handleSlug(ProductInterface $product): void
    {
        foreach ($product->getTranslations() as $productTranslation) {
            /** @var ProductTranslationInterface|TranslationInterface $productTranslation */
            $productTranslation->setSlug($this->productSlugGenerator->generate($product,
                $productTranslation->getLocale()));
        }
    }

    private function provideProduct(string $code): ProductInterface
    {
        /** @var ProductInterface|null $product */
        $product = $this->productRepository->findOneBy(['code' => $code]);

        if (null === $product) {
            $product = $this->productFactory->createWithVariant();
            $product->setCode($code);

            $productVariant = current($product->getVariants()->slice(0, 1));
            $productVariant->setCode($code);
        }

        return $product;
    }

    private function handlePostprocessors(ProductUpdated $event, ProductInterface $product): void
    {
        foreach ($this->postprocessors as $postprocessor) {
            $product = $postprocessor($event, $product);
        }
    }
}
