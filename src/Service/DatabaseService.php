<?php
namespace Wheniwork\Feedback\Service;

use Aura\Sql\ExtendedPdo;
use Aura\SqlQuery\QueryFactory;
use Wheniwork\Feedback\Domain\FeedbackDomain;

class DatabaseService
{
    private $tableName;
    private $pdo;
    private $queryFactory;

    public function __construct($tableName, ExtendedPdo $pdo, QueryFactory $queryFactory)
    {
        $this->tableName = $tableName;
        $this->pdo = $pdo;
        $this->queryFactory = $queryFactory;
    }

    public function addFeedbackItem($body, $source, $tone = FeedbackDomain::NEUTRAL) {
        $insert = $this->queryFactory->newInsert();

        $insert
            ->into($this->tableName)
            ->cols([
                'body' => $body,
                'source' => $source,
                'tone' => $tone
            ]);

        $sth = $this->pdo->prepare($insert->getStatement());
        $sth->execute($insert->getBindValues());
    }
}
