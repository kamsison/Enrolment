<?php namespace um\models;

use Carbon\Carbon;
use Ghunti\HighchartsPHP\Highchart;
use Ghunti\HighchartsPHP\HighchartJsExpr;
use Ghunti\HighchartsPHP\HighchartOption;
use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    /**
     * Set value for id column to integer
     * @param $value
     * @return int
     */
    public function getIdAttribute($value)
    {
        return (int)$value;
    }

    /**
     * Set value for created_by column to integer
     * @param $value
     * @return int
     */
    public function getCreatedByAttribute($value)
    {
        return (int)$value;
    }

    /**
     * Set value for updated_by column to integer
     * @param $value
     * @return int
     */
    public function getUpdatedByAttribute($value)
    {
        return (int)$value;
    }

    public static function getSortedSurvey($data)
    {
        usort($data, function ($a, $b) {
            $x = strcmp($a['x'], $b['x']);

            if ($x === 0) {
                return $b['y'] - $a['y'];
            }

            return $x;
        });

        return $data;
    }

    public static function getDashboardChart()
    {
        $chart = Base::getChartSettings('container-dashboard-chart');

        $i = 0;
        $dateFrom = Carbon::now()->subDay(7)->startOfDay();
        $dateTo = Carbon::now()->endOfDay();

        $statuses = [
            1 => 'Duplicate',
            2 => 'Suspected',
            4 => 'Positive',
            6 => 'Deleted'
        ];

        foreach ($statuses as $key => $status) {
            $data = [];
            $files = File::getFileCountByStatusAndDateRange($key, $dateFrom, $dateTo);

            foreach ($files as $file) {
                $score = $file->file_count;
                $date = Carbon::parse($file->file_date)->startOfDay()->timestamp;
                $data[] = [
                    'x' => $date * 1000, // epoch 13-digits
                    'y' => floatval(number_format($score, 2)),
                ];
            }

            $chart->series[$i]->name = $status;
            $chart->series[$i++]->data = Base::getSortedSurvey($data);
        }

        $globalOptions = new HighchartOption();
        $globalOptions->global->useUTC = false;

        return $chart;
    }

    private static function getChartSettings($renderTo, $type = 'column', $title = 'Bar Chart - Status Comparison')
    {
        $chart = new Highchart();

        $chart->title->text = $title;
        $chart->title->style->fontSize = '16px';

        $chart->chart->renderTo = $renderTo;
        $chart->chart->type = $type;
        $chart->chart->width = 475;

        $chart->credits->enabled = false;

        $chart->xAxis->type = "datetime";
        $chart->xAxis->startOnTick = true;
        $chart->xAxis->labels->style->font = '8px Arial';

        $chart->yAxis->title->text = "Count";
        $chart->yAxis->min = 0;
        $chart->yAxis->tickInterval = 10;

        if ($type == 'column') {
            $chart->xAxis->labels->formatter = new HighchartJsExpr("
                function() {
                    return Highcharts.dateFormat('%e-%b', this.value);
                }
            ");

            $chart->plotOptions->column->dataLabels->enabled = true;
            $chart->plotOptions->column->dataLabels->formatter = new HighchartJsExpr("
                function() {
                    if (this.y > 0)
                        return this.y;
                }
            ");

            $chart->tooltip->formatter = new HighchartJsExpr("
                function() {
                    return '<b>' + this.series.name + '</b><br/>' +
                    Highcharts.dateFormat('%A, %e-%b', this.x) + ': ' + this.y + ' files';
                }
            ");
        }

        $globalOptions = new HighchartOption();
        $globalOptions->global->useUTC = false;

        return $chart;
    }
}
