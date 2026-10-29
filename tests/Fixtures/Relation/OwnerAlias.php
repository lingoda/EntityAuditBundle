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

namespace Sonata\EntityAuditBundle\Tests\Fixtures\Relation;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * An entity identified by an entity (OwnerEntity) that is not Stringable.
 *
 * @psalm-suppress ClassMustBeFinal
 */
#[ORM\Entity]
class OwnerAlias
{
    public function __construct(
        #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: OwnerEntity::class)]
        #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'some_strange_key_name', nullable: false)]
        protected OwnerEntity $owner,
        #[ORM\Column(type: Types::STRING, length: 255)]
        protected string $alias,
    ) {
    }

    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }
}
