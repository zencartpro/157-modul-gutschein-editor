<?php
/**
 * Zen Cart German Specific
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * Zen Cart German Version - www.zen-cart-pro.at
 * @copyright Portions Copyright 2003 osCommerce
 * @license https://www.zen-cart-pro.at/license/3_0.txt GNU General Public License V3.0
 * @version $Id: gv_voucher_edit.php 2022-02-25 18:06:24Z webchills $
 */

  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  if (!isset($_GET['action'])) $_GET['action'] = '';
  $action = ($_GET['action'] ?? '');

  if (zen_not_null($action)) {
    switch ($action) {

      case 'save':
        $customers_id = zen_db_prepare_input($_GET['gid']);
        $amount = zen_db_prepare_input($_POST['amount']);

        $db->Execute("update " . TABLE_COUPON_GV_CUSTOMER . "
                      set amount = '" . (float)$amount . "'
                          where customer_id = '" . (int)$customers_id . "'");
        $messageStack->add_session(SUCCESS_VOUCHER_EDITED, 'success');
        zen_redirect(zen_href_link(FILENAME_GV_ADMIN_VOUCHER_EDIT, 'page=' . $_GET['page'] . '&gid=' . $customers_id));
        break;

    case 'confirmdelete':      

      // Zero out the persons gift voucher available balance
      $db->Execute("update " . TABLE_COUPON_GV_CUSTOMER . "
                    set amount = '0'
                    where customer_id='".$_GET['gid']."'");

      $messageStack->add_session(SUCCESS_VOUCHER_DELETED, 'success');
      zen_redirect(zen_href_link(FILENAME_GV_ADMIN_VOUCHER_EDIT, 'page=' . $_GET['page']));
      break;


    } // end action switch

  } // end action not null

?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
  </head>
  <body>
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <div class="container-fluid">
      <!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS_ID; ?></td>
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CUSTOMERS_EMAIL_ADDRESS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_VOUCHER_VALUE; ?></td>
		<td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $gv_query_raw = "select c.customers_firstname, c.customers_lastname, c.customers_id, c.customers_email_address, gv.customer_id, gv.amount from " . TABLE_CUSTOMERS . " c, " . TABLE_COUPON_GV_CUSTOMER . " gv where (gv.customer_id = c.customers_id) " . " order by gv.customer_id";
  $gv_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $gv_query_raw, $gv_query_numrows);
  $gv_list = $db->Execute($gv_query_raw);
  while (!$gv_list->EOF) {
    if (((!isset ($_GET['gid'])) || (@$_GET['gid'] == $gv_list->fields['customers_id'])) && (!isset ($gInfo))) {
      $gInfo = new objectInfo($gv_list->fields);
    }
    if ( (isset($gInfo)) && ($gv_list->fields['customers_id'] == $gInfo->customers_id) ) {
      echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_GV_ADMIN_VOUCHER_EDIT, zen_get_all_get_params(array('gid', 'action')) . 'gid=' . $gInfo->customers_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_GV_ADMIN_VOUCHER_EDIT, zen_get_all_get_params(array('gid', 'action')) . 'gid=' . $gv_list->fields['customers_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $gv_list->fields['customers_firstname'] . ' ' . $gv_list->fields['customers_lastname']; ?></td>
                <td class="dataTableContent"><?php echo $gv_list->fields['customers_id']; ?></td>
                <td class="dataTableContent"><?php echo $gv_list->fields['customers_email_address']; ?></td>
                <td class="dataTableContent" align="right"><?php echo $currencies->format($gv_list->fields['amount']); ?></td>
                <td class="dataTableContent" align="right"><?php if ( (isset($gInfo)) && ($gv_list->fields['customers_id'] == $gInfo->customers_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . zen_href_link(FILENAME_GV_ADMIN_VOUCHER_EDIT, 'page=' . $_GET['page'] . '&gid=' . $gv_list->fields['customers_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
    $gv_list->MoveNext();
  }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $gv_split->display_count($gv_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_GIFT_VOUCHERS); ?></td>
                    <td class="smallText" align="right"><?php echo $gv_split->display_links($gv_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();
  switch ($_GET['action']) {
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_GV_EDIT . '</b>');
      $heading[] = array('text' => '[' . $gInfo->customers_id . '] ' . ' ' . $currencies->format($gInfo->amount));

      $contents = array('form' => zen_draw_form('value', FILENAME_GV_ADMIN_VOUCHER_EDIT , 'page=' . $_GET['page'] . '&gid=' . $gInfo->customers_id  . '&action=save'));
      $contents[] = array('text' => TEXT_GV_EDIT_VALUE);
      $contents[] = array('text' => '<br>' . TEXT_GIFT_VOUCHER_NEW_AMOUNT . '<br>' . zen_draw_input_field('amount', number_format($gInfo->amount, 2, '.', '') ));
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . zen_href_link(FILENAME_GV_ADMIN_VOUCHER_EDIT, 'page=' . $_GET['page'] . '&gid=' . $gInfo->customers_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');

      break;

    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_GV_EDIT . '</b>');
      $heading[] = array('text' => '[' . $gInfo->customers_id . '] ' . ' ' . $currencies->format($gInfo->amount));

      $contents[] = array('align' => 'center', 'text' => TEXT_GV_DELETE);
      $contents[] = array('align' => 'center', 'text' => TEXT_GV_CONFIRM);
      $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_GV_ADMIN_VOUCHER_EDIT, 'action=confirmdelete&gid=' . $gInfo->customers_id,'NONSSL') . '">' . zen_image_button('button_confirm_red.gif', IMAGE_CONFIRM) . '</a> <a href="' . zen_href_link(FILENAME_GV_ADMIN_VOUCHER_EDIT, 'page=' . $_GET['page'] . '&gid=' . $gInfo->customers_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;

    default:

      $heading[] = array('text' => '<b>' . TEXT_GV_EDIT . '</b>');
      $heading[] = array('text' => '[' . $gInfo->customers_id . '] ' . ' ' . $currencies->format($gInfo->amount));

      if ($gv_list->RecordCount() == 0) {
        $contents[] = array('align' => 'center','text' => TEXT_GV_NONE);
      } else {

       $contents[] = array('align' => 'center', 'text' => "<b>" . $gInfo->customers_email_address . "</b>");
       $contents[] = array('align' => 'center', 'text' => '<br>'  . $currencies->format($gInfo->amount) .  TEXT_UNREDEEMED_CREDIT);
       $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif','','90%','3'));

	$contents[] = array('align' => 'center', 'text' =>'<a href="'.zen_href_link(FILENAME_GV_ADMIN_VOUCHER_EDIT,'action=edit&gid='.$gInfo->customers_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL').'">'.zen_image_button('button_edit.gif','Edit ' . TEXT_GIFT_VOUCHER) .'</a>' );
	$contents[] = array('align' => 'center', 'text' => '<a href="'.zen_href_link(FILENAME_GV_ADMIN_VOUCHER_EDIT,'action=delete&gid='.$gInfo->customers_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL').'">'.zen_image_button('button_delete.gif','Delete ' . TEXT_GIFT_VOUCHER).'</a>' );
      }
      break;
   }

  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table></td>

  </tr>
</table>
<!-- body_text_eof //-->
      </div>
      <!-- body_eof //-->
      <!-- footer //-->
  <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
      <!-- footer_eof //-->
    </body>
  </html>
