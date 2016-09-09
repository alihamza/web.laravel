<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Auth\Persistence\Mapper;

use Dms\Common\Structure\Web\Persistence\EmailAddressMapper;
use Dms\Core\Persistence\Db\Mapping\Definition\MapperDefinition;
use Dms\Core\Persistence\Db\Mapping\EntityMapper;
use Dms\Web\Laravel\Auth\Role;
use Dms\Web\Laravel\Auth\Admin;

/**
 * The user entity mapper.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AdminMapper extends EntityMapper
{
    const AUTH_IDENTIFIER_COLUMN = 'username';
    const AUTH_PASSWORD_COLUMN = 'password_hash';
    const AUTH_REMEMBER_TOKEN_COLUMN = 'remember_token';

    /**
     * Defines the entity mapper
     *
     * @param MapperDefinition $map
     *
     * @return void
     */
    protected function define(MapperDefinition $map)
    {
        $map->type(Admin::class);
        $map->toTable('users');

        $map->idToPrimaryKey('id');

        $map->property(Admin::FULL_NAME)
            ->to('full_name')
            ->asVarchar(255);

        $map->embedded(Admin::EMAIL_ADDRESS)
            ->unique()
            ->using(new EmailAddressMapper('email'));

        $map->property(Admin::USERNAME)
            ->to(self::AUTH_IDENTIFIER_COLUMN)
            ->unique()
            ->asVarchar(255);

        $map->embedded(Admin::PASSWORD)
            ->withColumnsPrefixedBy('password_')
            ->using(new HashedPasswordMapper());

        $map->property(Admin::IS_SUPER_USER)
            ->to('is_super_user')
            ->asBool();

        $map->property(Admin::IS_BANNED)
            ->to('is_banned')
            ->asBool();

        $map->property(Admin::REMEMBER_TOKEN)
            ->to(self::AUTH_REMEMBER_TOKEN_COLUMN)
            ->nullable()
            ->asVarchar(255);

        $map->relation(Admin::ROLE_IDS)
            ->to(Role::class)
            ->toManyIds()
            ->withBidirectionalRelation(Role::USER_IDS)
            ->throughJoinTable('user_roles')
            ->withParentIdAs('user_id')
            ->withRelatedIdAs('role_id');
    }
}