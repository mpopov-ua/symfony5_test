<?php

namespace App\Api;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Comment;
use Doctrine\ORM\QueryBuilder;

class FilterPublishedCommentQueryExtension implements QueryNameGeneratorInterface, QueryItemExtensionInterface
{
    public function applyToCollection (QueryBuilder $db, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        if (Comment::class === $resourceClass) {
            $db->andWhere(sprintf("%s.state = 'published'", $db->getRootAliases()[0]));
        }
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = [])
    {
        if (Comment::class === $resourceClass) {
            $queryBuilder->andWhere(sprintf("%s.state = 'published'", $queryBuilder->getRootAliases()[0]));
        }
    }

    public function generateJoinAlias(string $association): string
    {
        // TODO: Implement generateJoinAlias() method.
    }

    public function generateParameterName(string $name): string
    {
        // TODO: Implement generateParameterName() method.
    }
}