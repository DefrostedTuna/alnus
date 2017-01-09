<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User Model Path
    |--------------------------------------------------------------------------
    |
    | This is used to locate your user model. By default it will look for the
    | model in the 'app/models' directory as this is where I generally put
    | my models. If you would like to change this, just specify a path.
    */

    'models' => [
        'user' => \App\Models\User::class

        /*
         * You may also pass a string for the path...
         */

        // 'user' => '\App\Models\User'
    ]

];