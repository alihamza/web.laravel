<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\Mixed\Persistence\Infrastructure;

use Dms\Core\Persistence\Db\Connection\IConnection;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Persistence\DbRepository;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\Mixed\Persistence\Services\ITestEntityRepository;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\Mixed\Domain\TestEntity;

/**
 * The database repository implementation for the Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\Mixed\Domain\TestEntity entity.
 */
class DbTestEntityRepository extends DbRepository implements ITestEntityRepository
{
    public function __construct(IConnection $connection, IOrm $orm)
    {
        parent::__construct($connection, $orm->getEntityMapper(TestEntity::class));
    }
}