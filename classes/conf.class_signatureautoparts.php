<?php

final class conf
{
    // Change before going live
    const URL = 'http://157.245.94.223/';

    const DATABASE_HOST = 'localhost';
    const DATABASE_NAME = 'sync_itz';
    const DATABASE_USERNAME = 'root';
    const DATABASE_PASSWORD = 'dIgital@123sync';
    const DATABASE_PORT = '3306';

    // const MASTER_APP_USER_ID = '0a7d75dc7093b89974780ab44f915e06';
    // const MASTER_APP_PASSWORD = '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';
    // const SHOPIFY_STORE_ID = 'infotechsolz-store-app.myshopify.com';

    // const SHOPIFY_SHOP_NAME = 'signatureautoparts';
    // const MASTER_APP_USER_ID = 'dd67b49ee49d922de11b5a7816beb7f6';
    // const MASTER_APP_PASSWORD = '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';
    // const SHOPIFY_STORE_ID = 'signatureautoparts.myshopify.com';

    // const MASTER_APP_PASSWORD = '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';

    
    const SHOPIFY_SHOP_NAME = 'signatureautoparts';
    const MASTER_APP_USER_ID = 'dd67b49ee49d922de11b5a7816beb7f6';
    const MASTER_APP_PASSWORD = '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';
    const SHOPIFY_STORE_ID = 'signatureautoparts.myshopify.com';

    
    // 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
    
    const FILE_UPLOADS_PATH = '/assets/uploads/';
   
    // Change before live
    const EMAIL_FROM = 'soumen.infotechsolz@gmail.com';
    const EMAIL_FROM_NAME = 'Sync ITZ';

    const SITE_TITLE = 'Sync ITZ';
    const SITE_URL = 'http://157.245.94.223/';

    const DEFAULT_CONNECT_METHOD = 'ftp.php';

    private function __construct()
    {
        throw new Exception('GET OUT!');
    }

}
