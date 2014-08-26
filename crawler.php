<?php

class NoTownDataException extends Exception { }

class Crawler
{
    public function getTownsFromCountry($id, $year)
    {
        if ($year <= 2011) {
            $url = 'http://ecolife.epa.gov.tw/Cooler/_ws/wsBase.asmx/GetDistrictNoZip_Old';
        } else {
            $url = 'http://ecolife.epa.gov.tw/Cooler/_ws/wsBase.asmx/GetDistrictNoZip';
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.66 Safari/537.36');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=UTF-8'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array('category' => 'District', 'knownCategoryValues' => 'City:' . $id . ';')));
        $content = curl_exec($curl);
        $ret = array();
        foreach (json_decode($content)->d as $d) {
            $ret[$d->value] = $d->name;
        }
        return $ret;
    }

    public function getCountryList($year)
    {
        if ($year <= 2011) {
            return array(
                63000 => '臺北市',
                10001 => '臺北縣',
                10019 => '臺中市',
                10021 => '臺南市',
                64000 => '高雄市',
                10002 => '宜蘭縣',
                10003 => '桃園縣',
                10004 => '新竹縣',
                10005 => '苗栗縣',
                10007 => '彰化縣',
                10008 => '南投縣',
                10009 => '雲林縣',
                10010 => '嘉義縣',
                10013 => '屏東縣',
                10014 => '臺東縣',
                10015 => '花蓮縣',
                10016 => '澎湖縣',
                10017 => '基隆市',
                10018 => '新竹市',
                10020 => '嘉義市',
                09020 => '金門縣',
                09007 => '連江縣',
                10012 => '高雄縣',
                10011 => '臺南縣',
                10006 => '臺中縣',
            );
        } else {
            return array(
                63000 => '臺北市',
                10001 => '新北市',
                10019 => '臺中市',
                10021 => '臺南市',
                64000 => '高雄市',
                10002 => '宜蘭縣',
                10003 => '桃園縣',
                10004 => '新竹縣',
                10005 => '苗栗縣',
                10007 => '彰化縣',
                10008 => '南投縣',
                10009 => '雲林縣',
                10010 => '嘉義縣',
                10013 => '屏東縣',
                10014 => '臺東縣',
                10015 => '花蓮縣',
                10016 => '澎湖縣',
                10017 => '基隆市',
                10018 => '新竹市',
                10020 => '嘉義市',
                09020 => '金門縣',
                09007 => '連江縣',
            );
        }
    }

    public function main($argv)
    {
        $type = $argv[1];
        $year = $argv[2];
        $month = $argv[3];
        if (!$year or !$month or !in_array($type, array('town', 'village'))) {
            throw new Exception("Usage: php crawler.php <town or village> <year> <month>");
        }

        $fp = fopen(__DIR__ . "/outputs/{$type}/{$year}-{$month}.csv", "w");
        foreach ($this->getCountryList($year) as $county_id => $county_name) {
            $towns = $this->getTownsFromCountry($county_id, $year);
            if ('village' == $type) {
                foreach ($towns as $town_id => $town_name) {
                    $ret = $this->getDataFromTown($year, $month, $county_id, $county_name, $town_id, $town_name);
                    foreach ($ret as $village_name => $count) {
                        error_log($village_name);
                        fputcsv($fp, array($county_id, $county_name, $town_id, $town_name, $village_name, $count));
                    }
                }
            } else {
                $ret = $this->getDataFromCounty($year, $month, $county_id, $county_name);
                foreach ($ret as $town_name => $count) {
                    $town_id = array_search($town_name, $towns);
                    fputcsv($fp, array($county_id, $county_name, $town_id, $town_name, '總計', $count));
                }
            }
        }
        fclose ($fp);
    }

    public function getDataFromCounty($year, $month, $county_id, $county_name)
    {
        $url = 'http://ecolife.epa.gov.tw/Cooler/effect/Electricity_Area.aspx';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIEFILE, 'cookie');
        curl_setopt($curl, CURLOPT_REFERER, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.66 Safari/537.36');

        $ret = curl_exec($curl);
        preg_match('#<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="([^"]*)"#', $ret, $matches);

        $params = array();
        $params['ctl00$ctl00$SCM'] = 'ctl00$ctl00$SCM|ctl00$ctl00$cphMain$cphMain$btnAdvanceSearch';
        $params['ctl00$ctl00$cphMain$yAxle$y_login$txtID'] = '';
        $params['ctl00$ctl00$cphMain$yAxle$y_login$txtPWD'] = '';
        $params['ctl00$ctl00$cphMain$yAxle$hDirtyID'] = '';
        $params['ctl00$ctl00$cphMain$yAxle$hUrl'] = '';
        $params['ctl00$ctl00$cphMain$cphMain$ucStatisticsType$chbMonthStatistics'] = 'on';
        $params['ctl00$ctl00$cphMain$cphMain$ucStatisticsType$ddlMonthStatistics_Year'] = $year;
        $params['ctl00$ctl00$cphMain$cphMain$ucStatisticsType$ddlMonthStatistics_Month'] = $month;
        $params['ctl00$ctl00$cphMain$cphMain$ucPubQryArea$cboCity'] = $county_id;
        $params['ctl00$ctl00$cphMain$cphMain$ucPubQryArea$cboDistrict'] = '';
        $params['ctl00$ctl00$cphMain$cphMain$ucPubQryArea$cddCity_ClientState'] = "{$county_id}:::{$county_name}";
        $params['ctl00$ctl00$cphMain$cphMain$ucPubQryArea$cddDistrict_ClientState'] = ":::";
        $params['ctl00$ctl00$cphMain$cphMain$ucPubQryArea$cddVillage_ClientState'] = '';
        $params['ctl00$ctl00$cphMain$cphMain$hidStatisticsType'] = '';
        $params['ctl00$ctl00$cphMain$cphMain$hidStatisticsYear'] = '';
        $params['ctl00$ctl00$cphMain$cphMain$hidStatisticsMonth'] = '';
        $params['hiddenInputToUpdateATBuffer_CommonToolkitScripts'] = '0';
        $params['__EVENTTARGET'] = '';
        $params['__EVENTARGUMENT'] = '';
        $params['__LASTFOCUS'] = '';
        $params['__VIEWSTATE'] = ($matches[1]);
        $params['__ASYNCPOST'] = "true";
        $params['__VIEWSTATEENCRYPTED'] = '';
        $params['ctl00$ctl00$cphMain$cphMain$btnAdvanceSearch'] = "查詢";

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-MicrosoftAjax' => 'Delta=true'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($curl, CURLOPT_REFERER, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.66 Safari/537.36');

        $content = curl_exec($curl);
        if (strpos($content, '月之資料，因此無法與去年同期用電量作比較！')) {
            throw new NoTownDataException();
        }

        $doc = new DOMDocument;
        @$doc->loadHTML('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $content . '</body></html>');

        $ret = array();
        foreach ($doc->getElementsByTagName('tr') as $tr_dom) {
            $td_doms = $tr_dom->getElementsByTagName('td');
            if ($td_doms->length == 5) {
                $ret[trim($td_doms->item(1)->nodeValue)] = trim(str_replace(',', '', $td_doms->item(2)->nodeValue));
            } elseif ($td_doms->length == 4) {
                $ret[trim($td_doms->item(0)->nodeValue)] = trim(str_replace(',', '', $td_doms->item(1)->nodeValue));
            }
        }
        return $ret;
    }

    public function getDataFromTown($year, $month, $county_id, $county_name, $town_id, $town_name)
    {
        $url = 'http://ecolife.epa.gov.tw/Cooler/effect/Electricity_Area.aspx';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIEFILE, 'cookie');
        curl_setopt($curl, CURLOPT_REFERER, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.66 Safari/537.36');

        $ret = curl_exec($curl);
        preg_match('#<input type="hidden" name="__VIEWSTATE" id="__VIEWSTATE" value="([^"]*)"#', $ret, $matches);

        $params = array();
        $params['ctl00$ctl00$SCM'] = 'ctl00$ctl00$SCM|ctl00$ctl00$cphMain$cphMain$btnAdvanceSearch';
        $params['ctl00$ctl00$cphMain$yAxle$y_login$txtID'] = '';
        $params['ctl00$ctl00$cphMain$yAxle$y_login$txtPWD'] = '';
        $params['ctl00$ctl00$cphMain$yAxle$hDirtyID'] = '';
        $params['ctl00$ctl00$cphMain$yAxle$hUrl'] = '';
        $params['ctl00$ctl00$cphMain$cphMain$ucStatisticsType$chbMonthStatistics'] = 'on';
        $params['ctl00$ctl00$cphMain$cphMain$ucStatisticsType$ddlMonthStatistics_Year'] = $year;
        $params['ctl00$ctl00$cphMain$cphMain$ucStatisticsType$ddlMonthStatistics_Month'] = $month;
        $params['ctl00$ctl00$cphMain$cphMain$ucPubQryArea$cboCity'] = $county_id;
        $params['ctl00$ctl00$cphMain$cphMain$ucPubQryArea$cboDistrict'] = $town_id;
        $params['ctl00$ctl00$cphMain$cphMain$ucPubQryArea$cddCity_ClientState'] = "{$county_id}:::{$county_name}";
        $params['ctl00$ctl00$cphMain$cphMain$ucPubQryArea$cddDistrict_ClientState'] = "{$town_id}:::{$town_name}";
        $params['ctl00$ctl00$cphMain$cphMain$ucPubQryArea$cddVillage_ClientState'] = '';
        $params['ctl00$ctl00$cphMain$cphMain$hidStatisticsType'] = '';
        $params['ctl00$ctl00$cphMain$cphMain$hidStatisticsYear'] = '';
        $params['ctl00$ctl00$cphMain$cphMain$hidStatisticsMonth'] = '';
        $params['hiddenInputToUpdateATBuffer_CommonToolkitScripts'] = '0';
        $params['__EVENTTARGET'] = '';
        $params['__EVENTARGUMENT'] = '';
        $params['__LASTFOCUS'] = '';
        $params['__VIEWSTATE'] = ($matches[1]);
        $params['__ASYNCPOST'] = "true";
        $params['__VIEWSTATEENCRYPTED'] = '';
        $params['ctl00$ctl00$cphMain$cphMain$btnAdvanceSearch'] = "查詢";

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-MicrosoftAjax' => 'Delta=true'));
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($curl, CURLOPT_REFERER, $url);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.66 Safari/537.36');

        $content = curl_exec($curl);
        if (strpos($content, '月之資料，因此無法與去年同期用電量作比較！')) {
            throw new NoTownDataException();
        }

        $doc = new DOMDocument;
        @$doc->loadHTML('<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body>' . $content . '</body></html>');

        $ret = array();
        foreach ($doc->getElementsByTagName('tr') as $tr_dom) {
            $td_doms = $tr_dom->getElementsByTagName('td');
            if ($td_doms->length == 6) {
                $ret[trim($td_doms->item(2)->nodeValue)] = trim(str_replace(',', '', $td_doms->item(3)->nodeValue));
            } elseif ($td_doms->length == 5) {
                $ret[trim($td_doms->item(1)->nodeValue)] = trim(str_replace(',', '', $td_doms->item(2)->nodeValue));
            } elseif ($td_doms->length == 4) {
                $ret[trim($td_doms->item(0)->nodeValue)] = trim(str_replace(',', '', $td_doms->item(1)->nodeValue));
            }
        }
        return $ret;
    }
}

$c = new Crawler;
$c->main($_SERVER['argv']);
