<?php declare(strict_types=1);

namespace Dms\Web\Laravel\Renderer\Module;

use Dms\Core\Exception\InvalidArgumentException;
use Dms\Core\Module\IModule;
use Dms\Web\Laravel\Http\ModuleContext;

/**
 * The module dashboard renderer interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IModuleRenderer
{
    /**
     * Returns whether this renderer can render the supplied module.
     *
     * @param ModuleContext $moduleContext
     *
     * @return bool
     */
    public function accepts(ModuleContext $moduleContext) : bool;

    /**
     * Renders the supplied module dashboard as a html string.
     *
     * @param ModuleContext $moduleContext
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function render(ModuleContext $moduleContext) : string;
}