<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional\Ticket;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Tests\Models\DDC117\DDC117Article;
use Doctrine\Tests\Models\DDC117\DDC117ArticleDetails;
use Doctrine\Tests\OrmFunctionalTestCase;

/**
 * @group DDC-1685
 */
class DDC1685Test extends OrmFunctionalTestCase
{
    private $paginator;

    protected function setUp() : void
    {
        $this->useModelSet('ddc117');

        parent::setUp();

        $this->em->createQuery('DELETE FROM Doctrine\Tests\Models\DDC117\DDC117ArticleDetails ad')->execute();

        $article = new DDC117Article('Foo');
        $this->em->persist($article);
        $this->em->flush();

        $articleDetails = new DDC117ArticleDetails($article, 'Very long text');
        $this->em->persist($articleDetails);
        $this->em->flush();

        $dql   = 'SELECT ad FROM Doctrine\Tests\Models\DDC117\DDC117ArticleDetails ad';
        $query = $this->em->createQuery($dql);

        $this->paginator = new Paginator($query);
    }

    public function testPaginateCount() : void
    {
        self::assertCount(1, $this->paginator);
    }

    public function testPaginateIterate() : void
    {
        self::assertContainsOnlyInstancesOf(DDC117ArticleDetails::class, $this->paginator);
    }

    public function testPaginateCountNoOutputWalkers() : void
    {
        $this->paginator->setUseOutputWalkers(false);

        self::assertCount(1, $this->paginator);
    }

    public function testPaginateIterateNoOutputWalkers() : void
    {
        $this->paginator->setUseOutputWalkers(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Paginating an entity with foreign key as identifier only works when using the Output Walkers. Call Paginator#setUseOutputWalkers(true) before iterating the paginator.');

        self::assertContainsOnlyInstancesOf(DDC117ArticleDetails::class, $this->paginator);
    }
}
