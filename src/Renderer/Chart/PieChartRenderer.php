<?php declare(strict_types = 1);

namespace Dms\Web\Laravel\Renderer\Chart;

use Dms\Core\Model\Object\Enum;
use Dms\Core\Table\Chart\IChartDataTable;
use Dms\Core\Table\Chart\Structure\PieChart;
use Dms\Core\Util\Hashing\ValueHasher;

/**
 * The chart renderer for pie charts
 *
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class PieChartRenderer extends ChartRenderer
{
    /**
     * Returns whether this renderer can render the supplied chart.
     *
     * @param IChartDataTable $chartData
     *
     * @return bool
     */
    public function accepts(IChartDataTable $chartData) : bool
    {
        return $chartData->getStructure() instanceof PieChart;
    }

    /**
     * @param IChartDataTable $chartData
     *
     * @return string
     */
    protected function renderChart(IChartDataTable $chartData) : string
    {
        /** @var PieChart $chartStructure */
        $chartStructure = $chartData->getStructure();

        $chartDataArray = $this->transformChartDataToIndexedArrays(
            $chartData,
            $chartStructure->getTypeAxis()->getName(),
            $chartStructure->getTypeAxis()->getComponent()->getName(),
            $chartStructure->getValueAxis()->getName(),
            $chartStructure->getValueAxis()->getComponent()->getName()
        );

        return view('dms::components.chart.pie-chart')
            ->with([
                'data' => $chartDataArray,
            ])
            ->render();
    }

    protected function transformChartDataToIndexedArrays(
        IChartDataTable $data,
        $labelAxisName,
        $labelComponentName,
        $valueAxisName,
        $valueComponentName
    )
    {
        $results = [];

        foreach ($data->getRows() as $row) {
            $key = $row[$labelAxisName][$labelComponentName];

            if ($key instanceof Enum) {
                $key = $key->getValue();
            }

            if (isset($results[$key])) {
                $results[$key]['value'] += $row[$valueAxisName][$valueComponentName];
            } else {
                $results[$key] = [
                    'label' => $key,
                    'value' => $row[$valueAxisName][$valueComponentName],
                ];
            }
        }

        return array_values($results);
    }
}