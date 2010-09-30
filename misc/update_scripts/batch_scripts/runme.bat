set a=1

set limit=111111111111111111111111

:Top

CD..

php.exe update_binaries.php

php.exe update_releases.php

CD batch_scripts

sleep 120

if %a%==300 goto optimise

set /a a=%a%+1

GOTO TOP

:Optimise

CD..

php.exe optimise_db.php

set a=1

CD batch_scripts

sleep 120

GOTO TOP