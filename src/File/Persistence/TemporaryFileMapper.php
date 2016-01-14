<?php

namespace Dms\Web\Laravel\File\Persistence;

use Dms\Common\Structure\DateTime\Persistence\DateTimeMapper;
use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;
use Dms\Web\Laravel\File\TemporaryFile;

/**
 * The temporary file mapper.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class TemporaryFileMapper extends EntityMapper
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
        $map->type(TemporaryFile::class);

        $map->toTable('temp_files');
        $map->idToPrimaryKey('id');

        $map->property(TemporaryFile::TOKEN)->to('token')->unique()->asVarchar(40);

        $map->embedded(TemporaryFile::FILE)
                ->using(new FileAndSubclassesMapper());

        $map->embedded(TemporaryFile::EXPIRY)
                ->using(new DateTimeMapper('expiry_time'));
    }
}