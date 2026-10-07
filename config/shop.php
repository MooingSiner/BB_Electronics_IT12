<?php

return [

    /*
    | A word of 10 different letters. Each letter stands for a digit, so a product's
    | cost price can be shown to staff as a code that customers cannot read.
    | The first letter is 1, the second 2, and so on, with the last letter as 0.
    */
    'cost_code_key' => env('COST_CODE_KEY', 'CHRISTYNED'),

    /*
    | The mysqldump program used by the backup:database command. Leave it as it is when
    | mysqldump can be run from any folder, or give its full path, for example
    | C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin/mysqldump.exe
    */
    'mysqldump_path' => env('MYSQLDUMP_PATH', 'mysqldump'),

];
