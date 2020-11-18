<?php
final class conf
{
    // Change before going live
    const SITE_TITLE = 'Stock Update';
    const SITE_URL = 'https://getstockupdate.com/';
    const URL = 'https://getstockupdate.com/';

    const DATABASE_HOST = 'localhost';
    const DATABASE_NAME = 'sync_itz';
    const DATABASE_USERNAME = 'root';
    const DATABASE_PASSWORD = 'dIgital@123sync';
    const DATABASE_PORT = '3306';

    const APP_API_KEY = 'dd67b49ee49d922de11b5a7816beb7f6';
    const APP_SERECT_KEY = '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';

    const APP_SCOPES = 'write_orders,write_products,write_content,write_script_tags,write_inventory';

    
    const MASTER_APP_USER_ID = 'dd67b49ee49d922de11b5a7816beb7f6';
    const SHOPIFY_SHOP_NAME = 'sync-itz';
    const SHOPIFY_STORE_ID = 'sync-itz.myshopify.com';
    const MASTER_APP_PASSWORD = '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';  

   
    // Change before live
    const EMAIL_FROM = 'partha.infotechsolz@gmail.com';
    const EMAIL_FROM_NAME = 'Stock Update';

    const FILE_UPLOADS_PATH = '/assets/uploads/';
    const ACTIVITY_LOG_PATH = '/uploads/activitylogfiles/';

    const DEFAULT_CONNECT_METHOD = 'ftp.php';

    private function __construct()
    {
        throw new Exception('GET OUT!');
    }

}
