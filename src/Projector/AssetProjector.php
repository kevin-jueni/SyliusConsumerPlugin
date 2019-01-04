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
     * @return array
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
            $path = $this->persistAsset($event, $asset);

            if ($path !== null) {
                $asset->setPath($path);
                $asset->setType('akeneo');
                $this->repository->add($asset);
            } elseif ($path === null && $asset->getId()) {
                $this->repository->remove($asset);
            }
        } catch (\Exception $e) {
            $this->logger->debug(sprintf('Unable to persist asset "%s".', $event->getPath()));
            return [];
        }
    }

    /**
     * @param AssetUpdated $event
     * @param Asset $asset
     * @return string|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function persistAsset(AssetUpdated $event, Asset $asset): ?string
    {
        $client = new \GuzzleHttp\Client();

        if ($event->getPath() === null && $asset->getId()) {
            $this->filesystem->delete(
                $asset->getPath()
            );

            return null;
        } else {
            $path = 'http://192.168.115.31/media/show/' . urlencode(urlencode($event->getPath())) . '/preview';
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
}
