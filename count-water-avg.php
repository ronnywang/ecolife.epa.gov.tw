<?php

class CountAVG
{
    public function main($year)
    {
        $year = intval($year);
        $output = fopen('php://output', 'w');
        if (!$year) {
            throw new Exception("Usage: php count-avg.php <year>");
        }

        // 先抓縣市升格資料，因為原始用水資料的代碼都是用最舊代碼 (台北縣、高雄縣時代...)
        $url = 'https://sheethub.com/area.reference.tw/中華民國行政區/?sql=SELECT+*+FROM+this+WHERE+type%3D%27town%27+AND+note+%21%3D+%27%27&format=array_json';
        $json = json_decode(file_get_contents($url));
        if (!is_array($json)) {
            throw new Exception("找不到 {$year} 年人口");
        }
        $upgrade_map = array();
        foreach ($json as $record) {
            if (preg_match('/從 #([0-9]*) 升格成 #([0-9]*)/', $record->note, $matches)) {
                $upgrade_map[$matches[1]] = $matches[2];
            }
        }

        // 從 sheethub 抓人口
        $url = 'https://sheethub.com/ronnywang/全國人口統計_鄉鎮市區_' . ($year - 1911) . '年12月/?sql=SELECT+"鄉鎮市區代碼".town_id+%2C+戶數%2C+人口數+FROM+this&format=array_json';
        $json = json_decode(file_get_contents($url));
        if (!is_array($json)) {
            throw new Exception("找不到 {$year} 年人口");
        }

        $populations = array();
        foreach ($json as $record) {
            $populations[$record->town_id] = $record;
        }

        $files = glob(__DIR__ . '/water/town/' . $year . '-*.csv');
        $town_stats = array();
        foreach ($files as $file) {
            if (!preg_match('#^([0-9]*)-([0-9]*)\.csv$#', basename($file), $matches)) {
                continue;
            }
            list(, $year, $month) = $matches;
            $id = ceil($month / 2);

            $fp = fopen($file, 'r');
            while ($rows = fgetcsv($fp)) {
                list($county_id, $county_name, $town_id, $town_name, , $count) = $rows;
                if (!$town_id) continue;
                if (!array_key_exists($town_id, $town_stats)) {
                    $town_stats[$town_id] = array(
                        $county_name,
                        $town_name,
                        array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0),
                    );
                }
                $town_stats[$town_id][2][$id] += $count;
            }
            fclose($fp);
        }

        fputcsv($output, array(
            'town_id',
            '縣市',
            '鄉鎮',
            '人口數',
            '家戶數',
            '1-2月總用水',
            '1-2月家戶用水',
            '3-4月總用水',
            '3-4月家戶用水',
            '5-6月總用水',
            '5-6月家戶用水',
            '7-8月總用水',
            '7-8月家戶用水',
            '9-10月總用水',
            '9-10月家戶用水',
            '11-12月總用水',
            '11-12月家戶用水',
        ));

        foreach ($town_stats as $town_id => $stat) {
            if (array_key_exists($town_id, $populations)) {
                $population_data = $populations[$town_id];
            } elseif (array_key_exists($town_id, $upgrade_map) and array_key_exists($upgrade_map[$town_id], $populations)) {
                $population_data = $populations[$upgrade_map[$town_id]];
            } else {
                error_log($town_id . ' ' . $stat[0] . ' ' . $stat[1]);
                continue;
            }
            fputcsv($output, array(
                $population_data->town_id,
                $stat[0],
                $stat[1],
                $population_data->{'人口數'},
                $population_data->{'戶數'},
                $stat[2][1],
                $stat[2][1] / $population_data->{'戶數'},
                $stat[2][2],
                $stat[2][2] / $population_data->{'戶數'},
                $stat[2][3],
                $stat[2][3] / $population_data->{'戶數'},
                $stat[2][4],
                $stat[2][4] / $population_data->{'戶數'},
                $stat[2][5],
                $stat[2][5] / $population_data->{'戶數'},
                $stat[2][6],
                $stat[2][6] / $population_data->{'戶數'},
            ));
        }

        
    }
}

$c = new CountAVG;
$c->main($_SERVER['argv'][1]);
