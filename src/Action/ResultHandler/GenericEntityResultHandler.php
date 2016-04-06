<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Action\ResultHandler;

use Dms\Core\Common\Crud\Action\Crud\CreateAction;
use Dms\Core\Common\Crud\Action\Crud\EditAction;
use Dms\Core\Common\Crud\Action\Crud\ViewDetailsAction;
use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\ICrudModule;
use Dms\Core\Model\Object\Entity;
use Dms\Core\Module\IAction;
use Dms\Web\Laravel\Action\ActionResultHandler;
use Dms\Web\Laravel\Http\ModuleContext;
use Dms\Web\Laravel\Util\EntityModuleMap;
use Illuminate\Http\Response;

/**
 * The generic entity action result handler.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class GenericEntityResultHandler extends ActionResultHandler
{
    /**
     * @var EntityModuleMap|null
     */
    protected $entityModuleMap;

    protected function getEntityModuleMap() : EntityModuleMap
    {
        if (!$this->entityModuleMap) {
            $this->entityModuleMap = app(EntityModuleMap::class);
        }
        
        return $this->entityModuleMap;
    }

    /**
     * @return string|null
     */
    protected function supportedResultType()
    {
        return Entity::class;
    }

    /**
     * @param ModuleContext $moduleContext
     * @param IAction       $action
     * @param mixed         $result
     *
     * @return bool
     */
    protected function canHandleResult(ModuleContext $moduleContext, IAction $action, $result) : bool
    {
        /** @var Entity $result */
        $class = get_class($result);

        return $result->getId() && $this->getEntityModuleMap()->hasModuleFor($class)
        && !($action instanceof IObjectAction && $action->getName() === ICrudModule::REMOVE_ACTION)
        && !($action instanceof EditAction)
        && !($action instanceof ViewDetailsAction)
        && !($action instanceof CreateAction);
    }

    /**
     * @param ModuleContext $moduleContext
     * @param IAction       $action
     * @param mixed         $result
     *
     * @return Response|mixed
     */
    protected function handleResult(ModuleContext $moduleContext, IAction $action, $result)
    {
        $module = $this->getEntityModuleMap()->loadModuleFor(get_class($result));

        if (!$module->getDetailsAction()->isAuthorized()) {
            return (new NullResultHandler())->handle($moduleContext, $action, null);
        }

        /** @var Entity $result */
        $url = route('dms::package.module.action.show', [$module->getPackageName(), $module->getName(), $module->getDetailsAction()->getName(), $result->getId()]);

        return \response()->json([
            'message'  => trans('dms::action.generic-response'),
            'redirect' => $url,
        ]);
    }
}