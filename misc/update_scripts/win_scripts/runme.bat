set optimise=1


set limit=111111111111111111111111

:Top

CD..
php.exe update_binaries.php
php.exe update_releases.php
CD batch_scripts

set /a optimise=%optimise%+1
if %optimise%==300 goto optimise
:OptimiseDone

set /a tv=%tv%+1
if %tv%==20 goto tv
:TVDone

Sleep 120

GOTO TOP

:Optimise
CD..
php.exe optimise_db.php
set optimise=0
CD batch_scripts
GOTO OptimiseDone

:TV
CD..
php.exe update_tvschedule.php
set tv=0
CD batch_scripts
GOTO tvdone