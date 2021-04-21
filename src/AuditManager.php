<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SimpleThings\EntityAudit;

use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use SimpleThings\EntityAudit\EventListener\CreateSchemaListener;
use SimpleThings\EntityAudit\EventListener\LogRevisionsListener;

/**
 * Audit Manager grants access to metadata and configuration
 * and has a factory method for audit queries.
 */
class AuditManager
{
    private $config;

    private $metadataFactory;

    public function __construct(AuditConfiguration $config)
    {
        $this->config = $config;
        $this->metadataFactory = $config->createMetadataFactory();
    }

    public function getMetadataFactory()
    {
        return $this->metadataFactory;
    }

    public function getConfiguration()
    {
        return $this->config;
    }

    public function createAuditReader(EntityManager $em)
    {
        return new AuditReader($em, $this->config, $this->metadataFactory);
    }

    public function registerEvents(EventManager $evm, EntityManagerInterface $em): void
    {
        $evm->addEventSubscriber(new CreateSchemaListener($this, $em));
        $evm->addEventSubscriber(new LogRevisionsListener($this));
    }
}
