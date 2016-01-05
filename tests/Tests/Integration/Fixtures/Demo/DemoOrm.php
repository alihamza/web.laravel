<?php

namespace Dms\Web\Laravel\Tests\Integration\Fixtures\Demo;

use Dms\Core\Persistence\Db\Mapping\Definition\Orm\OrmDefinition;
use Dms\Core\Persistence\Db\Mapping\Orm;
use Dms\Web\Laravel\Auth\Persistence\AuthOrm;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class DemoOrm extends Orm
{
    /**
     * Defines the object mappers registered in the orm.
     *
     * @param OrmDefinition $orm
     *
     * @return void
     */
    protected function define(OrmDefinition $orm)
    {
        $orm->encompass((new AuthOrm())->inNamespace('dms_'));
    }
}