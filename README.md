ecolife.epa.gov.tw
==================

ecolife.epa.gov.tw 是行政院環保署的綠色生活網，網站目標是要呈現台灣對於節電節水
等政策的成效，裡面有一些數據對於分析台灣的用電情況會滿有幫助的，這個 repository
是在收集一些裡面的數據成機器友善格式

操作介面可以在 http://ecolife.epa.gov.tw/Cooler/effect/Electricity_Area.aspx 使用

檔案介紹
--------
* avg-YYYY.csv 各年份各鄉鎮市區的平均用電量數據，以兩個月為一個單位(因為電費是兩
個月收一次)，數字是來自台電電費帳單內三碼郵遞區號推算出來，因此數字還算準確
* outputs/YYYY-MM.csv 單一月份各村里用電量，資料是由台電用帳單地址透過戶政司查詢
村里，因此資料並不會很準確，不過裡面村里是「總計」的部份就是準確的
* crawler.php 爬出 output/YYYY-MM.csv 資料的 script ，用法是 php crawler.php {YYYY} {MM}

授權
----
* 程式授權以 BSD License
* 資料授權以 CC0 1.0 通用 (CC0 1.0)
http://creativecommons.org/publicdomain/zero/1.0/deed.zh_TW
