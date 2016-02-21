<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Widget;

use Dms\Core\Module\IModule;
use Dms\Core\Module\IParameterizedAction;
use Dms\Core\Widget\ActionWidget;
use Dms\Core\Widget\IWidget;
use Dms\Web\Laravel\Renderer\Form\ActionFormRenderer;
use Dms\Web\Laravel\Util\KeywordTypeIdentifier;

/**
 * The widget renderer for parameterized actions.
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ParameterizedActionWidgetRenderer extends WidgetRenderer
{
    /**
     * @var KeywordTypeIdentifier
     */
    protected $keywordTypeIdentifier;

    /**
     * @var ActionFormRenderer
     */
    protected $actionFormRenderer;

    /**
     * ParameterizedActionWidgetRenderer constructor.
     *
     * @param KeywordTypeIdentifier $keywordTypeIdentifier
     * @param ActionFormRenderer    $actionFormRenderer
     */
    public function __construct(KeywordTypeIdentifier $keywordTypeIdentifier, ActionFormRenderer $actionFormRenderer)
    {
        $this->keywordTypeIdentifier = $keywordTypeIdentifier;
        $this->actionFormRenderer    = $actionFormRenderer;
    }

    /**
     * Returns whether this renderer can render the supplied widget.
     *
     * @param IModule $module
     * @param IWidget $widget
     *
     * @return bool
     */
    public function accepts(IModule $module, IWidget $widget) : bool
    {
        return $widget instanceof ActionWidget
        && $widget->getAction() instanceof IParameterizedAction;
    }

    /**
     * Gets an array of links for the supplied widget.
     *
     * @param IModule $module
     * @param IWidget $widget
     *
     * @return string
     */
    protected function getWidgetLinks(IModule $module, IWidget $widget) : array
    {
        return [];
    }

    /**
     * Renders the supplied widget input as a html string.
     *
     * @param IModule $module
     * @param IWidget $widget
     *
     * @return string
     */
    protected function renderWidget(IModule $module, IWidget $widget) : string
    {
        /** @var ActionWidget $widget */
        $action = $widget->getAction();

        return view('dms::components.widget.parameterized-action')
            ->with([
                'action'            => $action,
                'actionFormContent' => $this->actionFormRenderer->renderActionForm($action),
            ])
            ->render();
    }
}