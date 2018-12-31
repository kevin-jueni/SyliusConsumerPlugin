<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector\Product;

use App\Cloudtec\Bundle\SyliusBundle\Entity\ProductInterface;
use Sylake\SyliusConsumerPlugin\Event\ProductUpdated;

interface ProductPostprocessorInterface
{
    public function __invoke(ProductUpdated $event, ProductInterface $product): ProductInterface;
}
