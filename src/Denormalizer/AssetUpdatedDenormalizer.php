<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Denormalizer;

use Sylake\SyliusConsumerPlugin\Event\AssetUpdated;
use SyliusLabs\RabbitMqSimpleBusBundle\Denormalizer\DenormalizationFailedException;

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

        $path = null;

        if (array_key_exists('variations', $payload) && count($payload['variations'])) {
            $path = $payload['variations'][0];
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
