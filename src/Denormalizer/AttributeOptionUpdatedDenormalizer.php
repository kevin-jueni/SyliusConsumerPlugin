<?php

declare(strict_types=1);

namespace Sylake\SyliusConsumerPlugin\Denormalizer;

use Sylake\SyliusConsumerPlugin\Event\AttributeOptionUpdated;
use Sylake\SyliusConsumerPlugin\Model\Translations;

final class AttributeOptionUpdatedDenormalizer extends AkeneoDenormalizer
{
    /**
     * {@inheritdoc}
     */
    protected function denormalizePayload(array $payload)
    {
        if ($this->logger) {
            $this->logger->debug(sprintf('Denormalizing attribute option "%s" with the following payload: "%s"',
                $payload['code'], json_encode($payload)));
        }

        return new AttributeOptionUpdated($payload['code'], $payload['attribute'],
            new Translations($payload['labels']));
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedMessageType(): string
    {
        return 'akeneo_attribute_option_updated';
    }
}
