set optimise=1
set scrape=1

set limit=111111111111111111111111

:Top

CD..
php.exe update_binaries.php
php.exe update_releases.php
CD batch_scripts

set /a scrape=%scrape%+1
if %scrape%==5 goto scrape
:ScrapeDone

set /a optimise=%optimise%+1
if %optimise%==300 goto optimise
:OptimiseDone

Sleep 120

GOTO TOP

:Optimise
CD..
php.exe optimise_db.php
set optimise=0
CD batch_scripts
GOTO OptimiseDone

:Scrape
CD..
CD..
CD reqscraper
php scrape.php
set scrape=0
CD..
CD update_scripts
CD batch_scripts
GOTO ScrapeDone
