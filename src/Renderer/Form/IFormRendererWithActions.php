<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Form;

use Dms\Core\Form\IForm;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * The form renderer with actions interface.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
interface IFormRendererWithActions
{
    /**
     * @param FormRenderingContext $renderingContext
     * @param IForm                $form
     * @param Request              $request
     * @param string               $actionName
     * @param array                $data
     *
     * @return Response
     */
    public function handleAction(FormRenderingContext $renderingContext, IForm $form, Request $request, string $actionName = null, array $data);
}