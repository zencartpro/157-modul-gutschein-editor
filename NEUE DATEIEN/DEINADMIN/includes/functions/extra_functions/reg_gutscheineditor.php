<?php
if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}
if (function_exists('zen_register_admin_page')) {
    if (!zen_page_key_exists('ToolsVoucherEdit')) {        
        zen_register_admin_page('ToolsVoucherEdit', 'BOX_GV_ADMIN_VOUCHER_EDIT','FILENAME_GV_ADMIN_VOUCHER_EDIT', '', 'tools', 'Y', 110);        
    }
    if (!zen_page_key_exists('ToolsVoucherCodesEdit')) {        
        zen_register_admin_page('ToolsVoucherCodesEdit', 'BOX_GV_ADMIN_REDEMPTION_EDIT','FILENAME_GV_ADMIN_REDEMPTION_EDIT', '', 'tools', 'Y', 111);
    }
}