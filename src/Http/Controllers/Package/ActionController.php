<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Http\Controllers\Package;

use Dms\Core\Auth\UserForbiddenException;
use Dms\Core\Common\Crud\Action\Object\IObjectAction;
use Dms\Core\Common\Crud\IReadModule;
use Dms\Core\Form\InvalidFormSubmissionException;
use Dms\Core\ICms;
use Dms\Core\Language\ILanguageProvider;
use Dms\Core\Module\ActionNotFoundException;
use Dms\Core\Module\IAction;
use Dms\Core\Module\IModule;
use Dms\Core\Module\IParameterizedAction;
use Dms\Core\Module\IUnparameterizedAction;
use Dms\Core\Module\ModuleNotFoundException;
use Dms\Core\Package\PackageNotFoundException;
use Dms\Web\Laravel\Action\ActionExceptionHandlerCollection;
use Dms\Web\Laravel\Action\ActionInputTransformerCollection;
use Dms\Web\Laravel\Action\ActionResultHandlerCollection;
use Dms\Web\Laravel\Action\UnhandleableActionExceptionException;
use Dms\Web\Laravel\Action\UnhandleableActionResultException;
use Dms\Web\Laravel\Http\Controllers\DmsController;
use Dms\Web\Laravel\Renderer\Form\ActionFormRenderer;
use Dms\Web\Laravel\Util\StringHumanizer;
use Illuminate\Http\Exception\HttpResponseException;
use Illuminate\Http\Request;

/**
 * The action controller
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ActionController extends DmsController
{
    /**
     * @var ILanguageProvider
     */
    protected $lang;

    /**
     * @var ActionInputTransformerCollection
     */
    protected $inputTransformers;

    /**
     * @var ActionResultHandlerCollection
     */
    protected $resultHandlers;

    /**
     * @var ActionExceptionHandlerCollection
     */
    protected $exceptionHandlers;

    /**
     * @var ActionFormRenderer
     */
    protected $actionFormRenderer;

    /**
     * ActionController constructor.
     *
     * @param ICms                             $cms
     * @param ActionInputTransformerCollection $inputTransformers
     * @param ActionResultHandlerCollection    $resultHandlers
     * @param ActionExceptionHandlerCollection $exceptionHandlers
     * @param ActionFormRenderer               $actionFormRenderer
     */
    public function __construct(
        ICms $cms,
        ActionInputTransformerCollection $inputTransformers,
        ActionResultHandlerCollection $resultHandlers,
        ActionExceptionHandlerCollection $exceptionHandlers,
        ActionFormRenderer $actionFormRenderer
    )
    {
        parent::__construct($cms);
        $this->lang               = $cms->getLang();
        $this->inputTransformers  = $inputTransformers;
        $this->resultHandlers     = $resultHandlers;
        $this->exceptionHandlers  = $exceptionHandlers;
        $this->actionFormRenderer = $actionFormRenderer;
    }

    public function showForm($packageName, $moduleName, $actionName, $objectId = null)
    {
        /** @var IModule $module */
        /** @var IAction $action */
        list($module, $action) = $this->loadAction($packageName, $moduleName, $actionName);
        $titleParts = [$packageName, $moduleName, $actionName];

        if (!($action instanceof IParameterizedAction)) {
            abort(404);
        }

        if (!$action->isAuthorized()) {
            abort(401);
        }

        $hiddenValues = [];

        if ($objectId && $action instanceof IObjectAction) {
            /** @var IReadModule $module */
            /** @var IObjectAction $action */
            try {
                $object = $action->getObjectForm()->getField(IObjectAction::OBJECT_FIELD_NAME)->process($objectId);
            } catch (InvalidFormSubmissionException $e) {
                abort(404);
            }

            $action = $action->withSubmittedFirstStage([
                IObjectAction::OBJECT_FIELD_NAME => $object,
            ]);

            $hiddenValues[IObjectAction::OBJECT_FIELD_NAME] = $objectId;
            $extraTitle = $module->getLabelFor($object);
        } else {
            $extraTitle = null;
        }

        return view('dms::package.module.action')
            ->with([
                'pageTitle'       => StringHumanizer::title(implode(' :: ', $titleParts)) . ($extraTitle ? ' :: ' . $extraTitle : ''),
                'breadcrumbs'     => [
                    route('dms::index')                                                 => 'Home',
                    route('dms::package.dashboard', [$packageName])                     => StringHumanizer::title($packageName),
                    route('dms::package.module.dashboard', [$packageName, $moduleName]) => StringHumanizer::title($moduleName),
                ],
                'finalBreadcrumb' => StringHumanizer::title($actionName),
                'action'          => $action,
                'formRenderer'    => $this->actionFormRenderer,
                'hiddenValues'    => $hiddenValues,
            ]);
    }

    public function getFormStage(Request $request, $packageName, $moduleName, $actionName, $stageNumber)
    {
        /** @var IModule $module */
        /** @var IParameterizedAction $action */
        list($module, $action) = $this->loadAction($packageName, $moduleName, $actionName);

        if (!($action instanceof IParameterizedAction)) {
            return response()->json([
                'message' => 'This action does not require an input form',
            ], 403);
        }

        $form        = $action->getStagedForm();
        $stageNumber = (int)$stageNumber;

        if ($stageNumber < 1 || $stageNumber > $form->getAmountOfStages()) {
            return response()->json([
                'message' => 'Invalid stage number',
            ], 404);
        }

        try {
            if (!$action->isAuthorized()) {
                throw new UserForbiddenException(
                    $this->auth->getAuthenticatedUser(),
                    $action->getRequiredPermissions()
                );
            }

            $input = $this->inputTransformers->transform($action, $request->all());
            $form  = $form->getFormForStage($stageNumber, $input);
        } catch (\Exception $e) {
            return $this->exceptionHandlers->handle($action, $e);
        }

        return response($this->actionFormRenderer->renderFormFields($form), 200);
    }

    public function runAction(Request $request, $packageName, $moduleName, $actionName)
    {
        /** @var IModule $module */
        /** @var IAction $action */
        list($module, $action) = $this->loadAction($packageName, $moduleName, $actionName);

        try {
            if ($action instanceof IParameterizedAction) {
                /** @var IParameterizedAction $action */
                $input  = $this->inputTransformers->transform($action, $request->all());
                $result = $action->run($input);
            } else {
                /** @var IUnparameterizedAction $action */
                $result = $action->run();
            }

        } catch (\Exception $e) {
            return $this->handleActionException($action, $e);
        }

        try {
            return $this->resultHandlers->handle($action, $result);
        } catch (UnhandleableActionResultException $e) {
            return $this->handleUnknownHandlerException($e);
        }
    }

    /**
     * @param IAction    $action
     * @param \Exception $e
     *
     * @return mixed
     */
    protected function handleActionException(IAction $action, \Exception $e)
    {
        try {
            return $this->exceptionHandlers->handle($action, $e);
        } catch (UnhandleableActionExceptionException $e) {
            return $this->handleUnknownHandlerException($e);
        }
    }

    /**
     * @param \Exception $e
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws
     */
    protected function handleUnknownHandlerException(\Exception $e)
    {
        if (app()->isLocal()) {
            throw $e;
        } else {
            return response()->json([
                'message' => 'An internal error occurred',
            ], 500);
        }
    }

    /**
     * @param string $packageName
     * @param string $moduleName
     * @param string $actionName
     *
     * @return array
     */
    protected function loadAction(string $packageName, string $moduleName, string $actionName) : array
    {
        try {
            $module = $this->cms->loadPackage($packageName)->loadModule($moduleName);
            $action = $module->getAction($actionName);

            return [$module, $action];
        } catch (PackageNotFoundException $e) {
            $response = response()->json([
                'message' => 'Invalid package name',
            ], 404);
        } catch (ModuleNotFoundException $e) {
            $response = response()->json([
                'message' => 'Invalid module name',
            ], 404);
        } catch (ActionNotFoundException $e) {
            $response = response()->json([
                'message' => 'Invalid action name',
            ], 404);
        }

        throw new HttpResponseException($response);
    }
}