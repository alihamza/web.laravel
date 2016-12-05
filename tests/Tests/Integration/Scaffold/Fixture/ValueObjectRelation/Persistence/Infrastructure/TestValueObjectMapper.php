<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObjectRelation\Persistence\Infrastructure;

use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\IndependentValueObjectMapper;
use Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObjectRelation\Domain\TestValueObject;


/**
 * The Dms\Web\Laravel\Tests\Integration\Scaffold\Fixture\ValueObjectRelation\Domain\TestValueObject value object mapper.
 */
class TestValueObjectMapper extends IndependentValueObjectMapper
{
    /**
     * Defines the entity mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    protected function define(MapperDefinition $map)
    {
        $map->type(TestValueObject::class);

        $map->property(TestValueObject::STRING)->to('string')->asVarchar(255);


    }
}