<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\File\Persistence;

use Dms\Core\Model\ICriteria;
use Dms\Core\Model\ISpecification;
use Dms\Core\Persistence\IRepository;
use Dms\Web\Laravel\File\TemporaryFile;

/**
 * The temporary file repository
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface ITemporaryFileRepository extends IRepository
{
    /**
     * {@inheritDoc}
     *
     * @return TemporaryFile[]
     */
    public function getAll() : array;

    /**
     * {@inheritDoc}
     *
     * @return TemporaryFile
     */
    public function get(int $id);

    /**
     * {@inheritDoc}
     *
     * @return TemporaryFile[]
     */
    public function getAllById(array $ids) : array;

    /**
     * {@inheritDoc}
     *
     * @return TemporaryFile|null
     */
    public function tryGet(int $id);

    /**
     * {@inheritDoc}
     *
     * @return TemporaryFile[]
     */
    public function tryGetAll(array $ids) : array;

    /**
     * {@inheritDoc}
     *
     * @return TemporaryFile[]
     */
    public function matching(ICriteria $criteria) : array;

    /**
     * {@inheritDoc}
     *
     * @return TemporaryFile[]
     */
    public function satisfying(ISpecification $specification) : array;
}