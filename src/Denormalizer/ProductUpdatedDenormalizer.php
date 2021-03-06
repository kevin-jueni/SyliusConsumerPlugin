<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Denormalizer;

use Sylake\SyliusConsumerPlugin\Event\ProductUpdated;
use SyliusLabs\RabbitMqSimpleBusBundle\Denormalizer\DenormalizationFailedException;

final class ProductUpdatedDenormalizer extends AkeneoDenormalizer
{
    /**
     * {@inheritdoc}
     */
    protected function denormalizePayload(array $payload): ProductUpdated
    {
        if ($this->logger) {
            $this->logger->debug(sprintf('Denormalizing product "%s" with the following payload: "%s"',
                $payload['identifier'], json_encode($payload)));
        }

        if (!array_key_exists('identifier', $payload) || !is_string($payload['identifier'])) {
            throw new DenormalizationFailedException('Identifier should be a string.');
        }

        $payload['enabled'] = $payload['enabled'] ?? false;
        if (!is_bool($payload['enabled'])) {
            throw new DenormalizationFailedException('Enabled should be a boolean.');
        }

        $payload['categories'] = $payload['categories'] ?? [];
        if (!is_array($payload['categories'])) {
            throw new DenormalizationFailedException('Categories should be an array.');
        }

        $payload['values'] = $payload['values'] ?? [];
        if (!is_array($payload['values'])) {
            throw new DenormalizationFailedException('Values should be an array.');
        }

        $payload['associations'] = $payload['associations'] ?? [];
        if (!is_array($payload['associations'])) {
            throw new DenormalizationFailedException('Associations should be an array.');
        }

        $payload['created'] = $payload['created'] ?? null;
        if (null !== $payload['created'] && !is_string($payload['created'])) {
            throw new DenormalizationFailedException('Created should be a string or null.');
        }

        $payload['parent'] = $payload['parent'] ?? null;

        if (null !== $payload['parent'] && !is_string($payload['parent'])) {
            throw new DenormalizationFailedException('Parent should be a string or null.');
        }

        return new ProductUpdated(
            $payload['identifier'],
            $payload['enabled'],
            $payload['categories'],
            $payload['values'],
            $this->getAssociations($payload),
            $this->getAssets($payload),
            $payload['family'] ?? '',
            array_values($payload['groups'] ?? []),
            \DateTime::createFromFormat(\DateTime::ATOM, $payload['created']) ?: null,
            $payload['parent']
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedMessageType(): string
    {
        return 'akeneo_product_updated';
    }

    /**
     * @param array $payload
     * @return array
     */
    private function getAssociations(array $payload): array
    {
        $associations = [];
        foreach ($payload['associations'] as $associationTypeCode => $value) {
            $associations[$associationTypeCode] = $value['products'];
        }

        return $associations;
    }

    /**
     * @param array $payload
     * @return array
     */
    private function getAssets(array $payload): array
    {
        $assets = [];

        if (array_key_exists('standard_images',
                $payload['values']) === true && !is_array($payload['values']['standard_images'])) {
            throw new DenormalizationFailedException('Images should be an array.');
        }

        if (array_key_exists('standard_images', $payload['values'])) {
            foreach ($payload['values']['standard_images'] as $asset) {
                $assets = array_merge(
                    $assets,
                    $asset['data']
                );
            }
        }

        return $assets;
    }
}
