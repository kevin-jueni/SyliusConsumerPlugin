<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Denormalizer;

use Sylake\SyliusConsumerPlugin\Event\TaxonUpdated;
use Sylake\SyliusConsumerPlugin\Model\Translations;

final class TaxonUpdatedDenormalizer extends AkeneoDenormalizer
{
    /**
     * {@inheritdoc}
     */
    protected function denormalizePayload(array $payload)
    {
        if ($this->logger) {
            $this->logger->debug(sprintf('Denormalizing taxon "%s" with the following payload: "%s"', $payload['code'],
                json_encode($payload)));
        }

        return new TaxonUpdated($payload['code'], $payload['parent'], new Translations($payload['labels']));
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedMessageType(): string
    {
        return 'akeneo_category_updated';
    }
}
