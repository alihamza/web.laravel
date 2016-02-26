<?php

namespace Dms\Web\Laravel\Renderer\Action;

use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\Action\Table\IReorderAction;
use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Model\IEntity;
use Dms\Web\Laravel\Util\ActionSafetyChecker;
use Dms\Web\Laravel\Util\StringHumanizer;

/**
 * The object action button builder class.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ObjectActionButtonBuilder
{
    /**
     * @var ActionSafetyChecker
     */
    protected $actionSafetyChecker;

    /**
     * ObjectActionButtonBuilder constructor.
     *
     * @param ActionSafetyChecker $actionSafetyChecker
     */
    public function __construct(ActionSafetyChecker $actionSafetyChecker)
    {
        $this->actionSafetyChecker = $actionSafetyChecker;
    }

    /**
     * @param IReadModule $module
     * @param IEntity     $object
     * @param string      $excludeActionName
     *
     * @return array|ActionButton[]
     */
    public function buildActionButtons(IReadModule $module, IEntity $object = null, string $excludeActionName = null) : array
    {
        $rowActions = [];

        foreach ($module->getObjectActions() as $action) {
            if ($object && !$action->isSupported($object)) {
                continue;
            }

            if ($action->getName() === $excludeActionName) {
                continue;
            }

            if ($action instanceof IReorderAction) {
                // The reorder actions are handled via the table renderer
                continue;
            }

            $canShowActionResult         = $action->getStagedForm()->getAmountOfStages() === 1 && $this->actionSafetyChecker->isSafeToShowActionResultViaGetRequest($action);
            $requiresExtraFormSubmission = $action->getStagedForm()->getAmountOfStages() > 1;

            if ($canShowActionResult) {
                $submitForm = false;
                $formUrl    = route(
                    'dms::package.module.action.show',
                    [$module->getPackageName(), $module->getName(), $action->getName(), '__object__']
                );
            } elseif ($requiresExtraFormSubmission) {
                $submitForm = false;
                $formUrl    = route(
                    'dms::package.module.action.form',
                    [$module->getPackageName(), $module->getName(), $action->getName(), '__object__']
                );
            } else {
                $submitForm = true;
                $formUrl    = route('dms::package.module.action.run', [
                    $module->getPackageName(),
                    $module->getName(),
                    $action->getName(),
                    IObjectAction::OBJECT_FIELD_NAME => '__object__',
                ]);
            }

            $rowActions[$action->getName()] = new ActionButton(
                $submitForm,
                $action->getName(),
                StringHumanizer::title($action->getName()),
                function (int $objectId) use ($formUrl) {
                    return str_replace('__object__', $objectId, $formUrl);
                }
            );
        }

        return $rowActions;
    }
}