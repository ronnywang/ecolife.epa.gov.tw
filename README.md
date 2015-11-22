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
* outputs/village/YYYY-MM.csv 單一月份各村里用電量，資料是由台電用帳單地址透過戶政司查詢
村里，因此資料並不會很準確
* outputs/town/YYYY-MM.csv 單一月份各鄉鎮市區用電量，資料是透過帳單郵遞區號三碼推算出來
，因此是準確的，但是嘉義市和新竹市的鄉鎮市區郵遞區號是統一的，因此無法判斷這兩個省轄市
的數據
* avg-water-YYYY.csv 各年份各鄉鎮市區平均用水數據，以兩個月為單位(水費也是兩個月抄表一次)
，再搭來自社會經濟資料庫當年份的人口和家戶數資料，計算平均家戶用水量
* water/village/YYYY-MM.csv 單一月份各村里用水量，資料是由台電用帳單地址透過戶政司查詢
村里，因此資料並不會很準確
* water/town/YYYY-MM.csv 單一月份各鄉鎮市區用電量，資料是透過帳單郵遞區號三碼推算出來
，因此是準確的，但是嘉義市和新竹市的鄉鎮市區郵遞區號是統一的，因此無法判斷這兩個省轄市
的數據
* crawler.php 爬出 output/YYYY-MM.csv 用電資料的 script ，用法是 php crawler.php {town or village} {YYYY} {MM}
* crawler-water.php 爬出 output/YYYY-MM.csv 用水資料的 script ，用法是 php crawler.php {town or village} {YYYY} {MM}
* count-avg.php 計算出一年各鄉鎮平均用電和家戶用電資料的 script ，用法是 php count-avg YYYY
* count-water-avg.php 計算出一年各鄉鎮平均用水和家戶用水資料的 script ，用法是 php count-avg YYYY

授權
----
* 程式授權以 BSD License
* 資料授權以 CC0 1.0 通用 (CC0 1.0)
http://creativecommons.org/publicdomain/zero/1.0/deed.zh\_TW
