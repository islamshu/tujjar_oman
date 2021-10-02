<?php

return [
    'driver' => env('FCM_PROTOCOL', 'http'),
    'log_enabled' => false,

    'http' => [
        'server_key' => env('FCM_SERVER_KEY', 'AAAADia8BQM:APA91bG5cmLD1ls85FkjFcB3xO4BWsoeOT48GI4j2tym0ZMhiEKmcK96H4Mh7Tl14AHfr9PM7P12DX3KjCmuogf2GC37M3W8hcx0LCfP51xsniXlrwUy3k0NpehRxACMm4P2ZDkUzwbc'),
        'sender_id' => env('FCM_SENDER_ID', '60779398403'),
        'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
        'server_group_url' => 'https://android.googleapis.com/gcm/notification',
        'timeout' => 30.0, // in second
    ],
];
