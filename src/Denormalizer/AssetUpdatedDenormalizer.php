<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Denormalizer;

use Sylake\SyliusConsumerPlugin\Event\AssetUpdated;

final class AssetUpdatedDenormalizer extends AkeneoDenormalizer
{
    /**
     * {@inheritdoc}
     */
    protected function denormalizePayload(array $payload)
    {
        if ($this->logger) {
            $this->logger->debug(sprintf('Denormalizing asset "%s" with the following payload: "%s"', $payload['code'],
                json_encode($payload)));
        }

        if (count($payload['variations'])) {
            $path = $payload['variations'][0]['references'][0];
        } else {
            $path = $payload['references'][0];
        }

        return new AssetUpdated($payload['code'], $path);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedMessageType(): string
    {
        return 'akeneo_asset_updated';
    }
}
