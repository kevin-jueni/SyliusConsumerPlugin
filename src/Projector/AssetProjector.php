<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector;

use App\Cloudtec\Bundle\SyliusBundle\Entity\Asset;
use Psr\Log\LoggerInterface;
use Sylake\SyliusConsumerPlugin\Event\AssetUpdated;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class AssetProjector
{
    /**
     * @var RepositoryInterface
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param RepositoryInterface $repository
     * @param LoggerInterface $logger
     */
    public function __construct(
        RepositoryInterface $repository,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->logger = $logger;
    }

    /**
     * @param AssetUpdated $event
     */
    public function __invoke(AssetUpdated $event)
    {
        $this->logger->debug(sprintf('Projecting asset with code "%s".', $event->getCode()));

        /** @var Asset|null $asset */
        $asset = $this->repository->findOneBy(['code' => $event->getCode()]);

        if ($asset === null) {
            $asset = new Asset();
            $asset->setCode($event->getCode());
        }

        $asset->setPath($event->getPath());
        $asset->setType('akeneo');

        $this->repository->add($asset);
    }
}
