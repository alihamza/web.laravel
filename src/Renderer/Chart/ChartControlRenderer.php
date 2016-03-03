<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Chart;

use Dms\Common\Structure\DateTime\Date;
use Dms\Common\Structure\DateTime\DateTime;
use Dms\Common\Structure\DateTime\TimeOfDay;
use Dms\Core\Module\IChartDisplay;
use Dms\Core\Table\Chart\IChartDataTable;
use Dms\Core\Table\Chart\Structure\GraphChart;
use Dms\Web\Laravel\Http\ModuleContext;

/**
 * The chart control renderer class
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class ChartControlRenderer
{
    /**
     * @var ChartRendererCollection
     */
    protected $chartRenderers;

    /**
     * ChartControlRenderer constructor.
     *
     * @param ChartRendererCollection $chartRenderers
     */
    public function __construct(ChartRendererCollection $chartRenderers)
    {
        $this->chartRenderers = $chartRenderers;
    }

    /**
     * Renders the supplied chart control as a html string.
     *
     * @param IChartDataTable $chartDataTable
     *
     * @return string
     * @throws UnrenderableChartException
     */
    public function renderChart(IChartDataTable $chartDataTable) : string
    {
        return $this->chartRenderers->findRendererFor($chartDataTable)->render($chartDataTable);
    }

    /**
     * Renders the supplied chart control as a html string.
     *
     * @param ModuleContext $moduleContext
     * @param IChartDisplay $chart
     * @param string        $viewName
     *
     * @return string
     */
    public function renderChartControl(ModuleContext $moduleContext, IChartDisplay $chart, string $viewName) : string
    {
        return view('dms::components.chart.chart-control')
            ->with([
                    'structure'        => $chart->getDataSource()->getStructure(),
                    'axes'             => $chart->getDataSource()->getStructure()->getAxes(),
                    'table'            => $chart->hasView($viewName) ? $chart->getView($viewName) : $chart->getDefaultView(),
                    'loadChartDataUrl' => $moduleContext->getUrl('chart.view.load', [$chart->getName(), $viewName]),
                ] + $this->getDateSettings($chart))
            ->render();
    }

    private function getDateSettings(IChartDisplay $chart) : array
    {
        $chartStructure = $chart->getDataSource()->getStructure();
        if (!($chartStructure instanceof GraphChart)) {
            return [];
        }

        $horizontalAxis = $chartStructure->getHorizontalAxis();
        $dateTimeClass  = $horizontalAxis->getType()->getPhpType()->nonNullable()->asTypeString();

        return [
            'dateAxisName' => $horizontalAxis->getName(),
            'dateFormat'   => defined($dateTimeClass . '::DISPLAY_FORMAT')
                ? constant($dateTimeClass . '::DISPLAY_FORMAT')
                : DateTime::DISPLAY_FORMAT,
            'dateMode'     => [
                                  TimeOfDay::class => 'time',
                                  Date::class      => 'date',
                                  DateTime::class  => 'date-time',
                              ][$dateTimeClass] ?? 'date-time',
        ];
    }
}