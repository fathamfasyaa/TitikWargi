<?php

/*
|--------------------------------------------------------------------------
| TitikWargi settings
|--------------------------------------------------------------------------
*/

return [

    /*
     * Reports are only accepted inside this rectangle: roughly Kabupaten Cianjur.
     */
    'report_area' => [
        'south' => -7.55,
        'north' => -6.55,
        'west' => 106.75,
        'east' => 107.45,
    ],

    /*
     * Maximum number of reports one user may create per day.
     */
    'daily_report_limit' => 5,

    /*
     * Photos are resized so the longest side is at most this many pixels.
     */
    'photo_max_dimension' => 1600,

];
