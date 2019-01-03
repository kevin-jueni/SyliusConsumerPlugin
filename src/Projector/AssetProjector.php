<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Projector;

use App\Cloudtec\Bundle\SyliusBundle\Entity\Asset;
use Gaufrette\FilesystemInterface;
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
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param RepositoryInterface $repository
     * @param FilesystemInterface $filesystem
     * @param LoggerInterface $logger
     */
    public function __construct(
        RepositoryInterface $repository,
        FilesystemInterface $filesystem,
        LoggerInterface $logger
    ) {
        $this->repository = $repository;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
    }

    /**
     * @param AssetUpdated $event
     * @throws \GuzzleHttp\Exception\GuzzleException
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

        try {

            $path = $this->persistAsset($event);
            $asset->setPath($path);

            $asset->setType('akeneo');

            $this->repository->add($asset);
        } catch (\Exception $e) {
            $this->logger->debug(sprintf('Unable to persist asset "%s".', $event->getPath()));
        }
    }

    /**
     * @param AssetUpdated $event
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function persistAsset(AssetUpdated $event): string
    {
        $path = 'http://192.168.115.31/media/show/' . urlencode(urlencode($event->getPath())) . '/preview';

        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $path);

        $success = $this->filesystem->write(
            $event->getPath(),
            $response->getBody(),
            true
        );

        if ($success === false) {
            throw new \Exception(sprintf(
                'Unable to persist asset: %s',
                $path
            ));
        }

        return $event->getPath();
    }
}
