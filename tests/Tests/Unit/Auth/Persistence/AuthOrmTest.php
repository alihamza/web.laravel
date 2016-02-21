<?php

namespace Dms\Web\Laravel\Tests\Unit\Auth\Persistence;

use Dms\Common\Structure\DateTime\DateTime;
use Dms\Common\Structure\Web\EmailAddress;
use Dms\Core\Auth\Permission;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Dms\Web\Laravel\Auth\Password\HashedPassword;
use Dms\Web\Laravel\Auth\Password\PasswordResetToken;
use Dms\Web\Laravel\Auth\Persistence\AuthOrm;
use Dms\Web\Laravel\Auth\Persistence\PasswordResetTokenRepository;
use Dms\Web\Laravel\Auth\Persistence\RoleRepository;
use Dms\Web\Laravel\Auth\Persistence\UserRepository;
use Dms\Web\Laravel\Auth\Role;
use Dms\Web\Laravel\Auth\User;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AuthOrmTest extends DbIntegrationTest
{
    /**
     * @var UserRepository
     */
    protected $userRepo;

    /**
     * @var RoleRepository
     */
    protected $roleRepo;

    /**
     * @var PasswordResetTokenRepository
     */
    protected $passwordResetTokenRepo;

    /**
     * @return IOrm
     */
    protected function loadOrm()
    {
        return new AuthOrm();
    }

    /**
     * @inheritDoc
     */
    protected function mapperAndRepoType()
    {
        return User::class;
    }

    public function setUp()
    {
        parent::setUp();

        $this->userRepo               = new UserRepository($this->connection, $this->orm);
        $this->roleRepo               = new RoleRepository($this->connection, $this->orm);
        $this->passwordResetTokenRepo = new PasswordResetTokenRepository($this->connection, $this->orm);
    }

    public function testSaveUser()
    {
        $this->userRepo->save(new User(
            new EmailAddress('admin@admin.com'),
            'admin',
            new HashedPassword('hash', 'bcrypt', 10)
        ));

        $this->assertDatabaseDataSameAs([
            'password_resets' => [],
            'permissions'     => [],
            'roles'           => [],
            'user_roles'      => [],
            'users'           => [
                [
                    'id'                   => 1,
                    'email'                => 'admin@admin.com',
                    'username'             => 'admin',
                    'password_hash'        => 'hash',
                    'password_algorithm'   => 'bcrypt',
                    'password_cost_factor' => 10,
                    'is_super_user'        => false,
                    'is_banned'            => false,
                    'remember_token'       => null,
                ],
            ],
        ]);
    }

    public function testLoadUser()
    {
        $this->setDataInDb([
            'password_resets' => [],
            'permissions'     => [],
            'roles'           => [],
            'user_roles'      => [],
            'users'           => [
                [
                    'id'                   => 1,
                    'email'                => 'admin@admin.com',
                    'username'             => 'admin',
                    'password_hash'        => 'hash',
                    'password_algorithm'   => 'bcrypt',
                    'password_cost_factor' => 10,
                    'is_super_user'        => false,
                    'is_banned'            => false,
                    'remember_token'       => null,
                ],
            ],
        ]);

        $expected = new User(
            new EmailAddress('admin@admin.com'),
            'admin',
            new HashedPassword('hash', 'bcrypt', 10)
        );
        $expected->setId(1);

        $this->assertEquals($expected, $this->userRepo->get(1));
    }

    public function testPasswordResetTokenSave()
    {
        $this->passwordResetTokenRepo->save(new PasswordResetToken('test@email.com', 'token', DateTime::fromString('2000-01-01 12:00:00')));

        $this->assertDatabaseDataSameAs([
            'password_resets' => [
                ['id' => 1, 'email' => 'test@email.com', 'token' => 'token', 'created_at' => '2000-01-01 12:00:00'],
            ],
            'permissions'     => [],
            'roles'           => [],
            'user_roles'      => [],
            'users'           => [],
        ]);
    }

    public function testLoadPasswordResetToken()
    {
        $this->setDataInDb([
            'password_resets' => [
                ['id' => 1, 'email' => 'test@email.com', 'token' => 'token', 'created_at' => '2000-01-01 12:00:00'],
            ],
        ]);

        $expected = new PasswordResetToken(
            'test@email.com',
            'token',
            DateTime::fromString('2000-01-01 12:00:00')
        );
        $expected->setId(1);

        $this->assertEquals($expected, $this->passwordResetTokenRepo->get(1));
    }

    public function testSaveRole()
    {
        $this->roleRepo->save(new Role(
            'admin',
            Permission::collectionFromNames(['a', 'b', 'c'])
        ));

        $this->assertDatabaseDataSameAs([
            'password_resets' => [],
            'roles'           => [
                ['id' => 1, 'name' => 'admin'],
            ],
            'permissions'     => [
                ['id' => 1, 'role_id' => 1, 'name' => 'a'],
                ['id' => 2, 'role_id' => 1, 'name' => 'b'],
                ['id' => 3, 'role_id' => 1, 'name' => 'c'],
            ],
            'user_roles'      => [],
            'users'           => [],
        ]);
    }

    public function testLoadRole()
    {
        $this->setDataInDb([
            'roles'       => [
                ['id' => 1, 'name' => 'admin'],
            ],
            'permissions' => [
                ['id' => 1, 'role_id' => 1, 'name' => 'a'],
                ['id' => 2, 'role_id' => 1, 'name' => 'b'],
                ['id' => 3, 'role_id' => 1, 'name' => 'c'],
            ],
        ]);

        $expected = new Role(
            'admin',
            Permission::collectionFromNames(['a', 'b', 'c'])
        );
        $expected->setId(1);

        $this->assertEquals($expected, $this->roleRepo->get(1));
    }

    public function testAssociateUserToRole()
    {
        $user = new User(new EmailAddress('admin@admin.com'), 'admin', new HashedPassword('hash', 'bcrypt', 10));
        $role = new Role('admin', Permission::collection());

        $this->userRepo->save($user);
        $this->roleRepo->save($role);

        $user->giveRole($role);

        $this->userRepo->save($user);

        $this->assertDatabaseDataSameAs([
            'password_resets' => [],
            'permissions'     => [],
            'users'           => [
                [
                    'id'                   => 1,
                    'email'                => 'admin@admin.com',
                    'username'             => 'admin',
                    'password_hash'        => 'hash',
                    'password_algorithm'   => 'bcrypt',
                    'password_cost_factor' => 10,
                    'is_super_user'        => false,
                    'is_banned'            => false,
                    'remember_token'       => null,
                ],
            ],
            'roles'           => [
                ['id' => 1, 'name' => 'admin'],
            ],
            'user_roles'      => [
                ['id' => 1, 'role_id' => 1, 'user_id' => 1],
            ],
        ]);
    }

    public function testLoadRoleIds()
    {
        $this->setDataInDb([
            'password_resets' => [],
            'permissions'     => [],
            'users'           => [
                [
                    'id'                   => 1,
                    'email'                => 'admin@admin.com',
                    'username'             => 'admin',
                    'password_hash'        => 'hash',
                    'password_algorithm'   => 'bcrypt',
                    'password_cost_factor' => 10,
                    'is_super_user'        => false,
                    'is_banned'            => false,
                    'remember_token'       => null,
                ],
            ],
            'roles'           => [
                ['id' => 10, 'name' => 'admin'],
            ],
            'user_roles'      => [
                ['id' => 1, 'role_id' => 10, 'user_id' => 1],
            ],
        ]);

        $expected                 = new User(
            new EmailAddress('admin@admin.com'),
            'admin',
            new HashedPassword('hash', 'bcrypt', 10)
        );
        $expected->getRoleIds()[] = 10;
        $expected->setId(1);

        $this->assertEquals($expected, $this->userRepo->get(1));
    }
}