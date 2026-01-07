<?php

declare(strict_types=1);

namespace PERSPEQTIVE\SuluPermissionAwareTemplatesBundle\Tests\Mocks\Sulu;

use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadataLoaderInterface;
use Sulu\Bundle\AdminBundle\Metadata\MetadataInterface;

class MockFormMetadataLoader implements FormMetadataLoaderInterface
{
    public function __construct(public ?MetadataInterface $metadata = null)
    {
    }

    public function getMetadata(string $key, string $locale, array $metadataOptions): ?MetadataInterface
    {
        return $this->metadata;
    }
}
