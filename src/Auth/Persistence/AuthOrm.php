<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Auth\Persistence;

use Dms\Core\Auth\Permission;
use Dms\Core\Persistence\Db\Mapping\Definition\Orm\OrmDefinition;
use Dms\Core\Persistence\Db\Mapping\Orm;
use Dms\Web\Laravel\Auth\Password\HashedPassword;
use Dms\Web\Laravel\Auth\Password\PasswordResetToken;
use Dms\Web\Laravel\Auth\Persistence\Mapper\HashedPasswordMapper;
use Dms\Web\Laravel\Auth\Persistence\Mapper\PasswordResetTokenMapper;
use Dms\Web\Laravel\Auth\Persistence\Mapper\PermissionMapper;
use Dms\Web\Laravel\Auth\Persistence\Mapper\RoleMapper;
use Dms\Web\Laravel\Auth\Persistence\Mapper\AdminMapper;
use Dms\Web\Laravel\Auth\Role;
use Dms\Web\Laravel\Auth\Admin;

/**
 * The auth orm module
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AuthOrm extends Orm
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
        $orm->valueObjects([
            HashedPassword::class => HashedPasswordMapper::class,
            Permission::class     => PermissionMapper::class,
        ]);

        $orm->entities([
            Role::class               => RoleMapper::class,
            Admin::class              => AdminMapper::class,
            PasswordResetToken::class => PasswordResetTokenMapper::class,
        ]);
    }
}