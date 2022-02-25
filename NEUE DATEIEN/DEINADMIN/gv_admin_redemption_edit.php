<?php
/**
 * Zen Cart German Specific
 * @copyright Copyright 2003-2022 Zen Cart Development Team
 * Zen Cart German Version - www.zen-cart-pro.at
 * @copyright Portions Copyright 2003 osCommerce
 * @license https://www.zen-cart-pro.at/license/3_0.txt GNU General Public License V3.0
 * @version $Id: gv_admin_redemption_edit.php 2022-02-25 18:10:24Z webchills $
 */

  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies(); 


  if (!isset($_GET['action'])) $_GET['action'] = '';
  $action = ($_GET['action'] ?? '');

  if (zen_not_null($action)) {
    switch ($action) {

      case 'savenew':
	// THIS COMMITS A NEW COUPON TO THE DATABASE
        $amount = zen_db_prepare_input($_POST['amount']);
        $coupon_code = zen_db_prepare_input($_POST['coupon_code']);
        $coupon_code = zen_db_prepare_input(strtolower($_POST['coupon_code']));
	$email_address = STORE_OWNER_EMAIL_ADDRESS;

	// IF the coupon code (gift voucher) provided already exists, this is a problem - bail!
	$gv_existing = $db->Execute("select coupon_code from " . TABLE_COUPONS . " 
					where coupon_code = '" . $coupon_code . "'
					and coupon_type = 'G'
			");

	if ($gv_existing->RecordCount() > 0) {
        	$messageStack->add_session(FAILURE_VOUCHER_EXISTS . $coupon_code , 'failure');
	} else {

		// Borrowed from the "NEW_SIGNUP_GIFT_VOUCHER" routines in add_customers.php - 6/4/2008 - rtw819
		$insert_query = $db->Execute("insert into " . TABLE_COUPONS . " (coupon_code, coupon_type, coupon_amount, date_created) values ('" . zen_db_input($coupon_code) . "', 'G', '" . (float)zen_db_input($amount) . "', now())");
		$insert_id = $db->Insert_ID();
		$db->Execute("insert into " . TABLE_COUPON_EMAIL_TRACK . " (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent) values ('" . $insert_id ."', '0', 'Admin', '" . $email_address . "', now() )");


		// Recheck for successful coupon creation
		$gv_existing = $db->Execute("select coupon_code from " . TABLE_COUPONS . " 
						where coupon_code = '" . $coupon_code . "'
						and coupon_type = 'G'
		  				limit 1
				");

		// Recheck for successful coupon creation
		if ($gv_existing->RecordCount() > 0) {
		        $messageStack->add_session(SUCCESS_VOUCHER_INSERTED . "[".$insert_id."] ".TEXT_GV_NEW_COUPON_TEXT." ".$coupon_code." = $" . number_format($amount, 2, '.', '') , 'success');
		} else {
        		$messageStack->add_session(FAILURE_VOUCHER_INSERT . $coupon_code, 'failure');
		}
	}
        //zen_redirect(zen_href_link(FILENAME_GV_ADMIN_REDEMPTION_EDIT, 'page=' . $_GET['page'] . '&gid=' . $insert_id));
        zen_redirect(zen_href_link(FILENAME_GV_ADMIN_REDEMPTION_EDIT));
        break;

      case 'save':
        $coupon_id = zen_db_prepare_input($_GET['gid']);
        $amount = zen_db_prepare_input($_POST['amount']);

        $db->Execute("update " . TABLE_COUPONS . "
                      set coupon_amount = '" . (float)$amount . "',
			date_modified=now()
                  	where coupon_type = 'G'
                  	and coupon_id = '" . $coupon_id . "'
		  	limit 1
		"); 
        $messageStack->add_session(SUCCESS_VOUCHER_EDITED, 'success');
        zen_redirect(zen_href_link(FILENAME_GV_ADMIN_REDEMPTION_EDIT, 'page=' . $_GET['page'] . '&gid=' . $coupon_id));
        break;

    case 'confirmdelete':      

	// Zero out the persons gift voucher available balance
	$db->Execute("update " . TABLE_COUPONS . "
                    set coupon_amount = '0',
			date_modified=now()
                        where coupon_id='".$_GET['gid']."'
                  	and coupon_type = 'G'
		  	limit 1 " );

      $messageStack->add_session(SUCCESS_VOUCHER_DELETED, 'success');
      zen_redirect(zen_href_link(FILENAME_GV_ADMIN_REDEMPTION_EDIT, 'page=' . $_GET['page']));
      break;

     } // end switch case

  } // end not null action

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
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_SENDERS_NAME; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_VOUCHER_VALUE; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TEXT_INFO_EMAIL_ADDRESS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_VOUCHER_CODE; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_DATE_SENT; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TEXT_HEADING_DATE_REDEEMED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>

              </tr>
<?php
  $gv_query_raw = "select c.coupon_amount, c.coupon_code, c.coupon_id, et.sent_firstname, et.sent_lastname, et.customer_id_sent, et.emailed_to, et.date_sent, crt.redeem_date, c.coupon_id, c.date_modified
                  from " . TABLE_COUPONS . " c
                  left join " . TABLE_COUPON_REDEEM_TRACK . " crt
                  on c.coupon_id= crt.coupon_id, " . TABLE_COUPON_EMAIL_TRACK . " et
                  where c.coupon_id = et.coupon_id " . "
                  and c.coupon_type = 'G'
                  and crt.redeem_date IS NULL
                  order by date_sent desc";
	// Only want NON-redeemed codes

  $gv_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $gv_query_raw, $gv_query_numrows);
  $gv_list = $db->Execute($gv_query_raw);

  while (!$gv_list->EOF) {
    if (((!isset ($_GET['gid'])) || (@$_GET['gid'] == $gv_list->fields['coupon_id'])) && (!isset ($gInfo))) {
    $gInfo = new objectInfo($gv_list->fields);
    }
    if ( (isset($gInfo)) && ($gv_list->fields['coupon_id'] == $gInfo->coupon_id) ) {
      echo '              <tr class="dataTableRowSelected" onmouseover="this.style.cursor=\'hand\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_GV_ADMIN_REDEMPTION_EDIT, zen_get_all_get_params(array('gid', 'action')) . 'gid=' . $gInfo->coupon_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '              <tr class="dataTableRow" onmouseover="this.className=\'dataTableRowOver\';this.style.cursor=\'hand\'" onmouseout="this.className=\'dataTableRow\'" onclick="document.location.href=\'' . zen_href_link(FILENAME_GV_ADMIN_REDEMPTION_EDIT, zen_get_all_get_params(array('gid', 'action')) . 'gid=' . $gv_list->fields['coupon_id']) . '\'">' . "\n";
    }
?>
                <td class="dataTableContent"><?php echo $gv_list->fields['sent_firstname'] . ' ' . $gv_list->fields['sent_lastname']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $currencies->format($gv_list->fields['coupon_amount']); 
		/* IF MODIFIED, DENOTE VALUE ACCORDINGLY */
		if (($gv_list->fields['date_modified']) != '0001-01-01 00:00:00') { echo ' <font color="#FF0000">*</font>'; } ?></td>
		<td class="dataTableContent" align="left"><?php echo $gv_list->fields['emailed_to']; ?></td>
                <td class="dataTableContent" align="center"><?php echo $gv_list->fields['coupon_code']; ?></td>
                <td class="dataTableContent" align="right"><?php echo zen_date_short($gv_list->fields['date_sent']); ?></td>
                <td class="dataTableContent" align="right"><?php echo (empty($gv_list->fields['redeem_date']) ? TEXT_INFO_NOT_REDEEMED : zen_date_short($gv_list->fields['redeem_date'])); ?></td>
                <td class="dataTableContent" align="right"><?php if ( (isset($gInfo)) && ($gv_list->fields['coupon_id'] == $gInfo->coupon_id) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . zen_href_link(FILENAME_GV_ADMIN_REDEMPTION_EDIT, 'page=' . $_GET['page'] . '&gid=' . $gv_list->fields['coupon_id']) . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
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
      $heading[] = array('text' => '[' . $gInfo->coupon_id . '] ' . ' ' . $currencies->format($gInfo->coupon_amount));

      $contents = array('form' => zen_draw_form('value', FILENAME_GV_ADMIN_REDEMPTION_EDIT , 'page=' . $_GET['page'] . '&gid=' . $gInfo->coupon_id  . '&action=save'));
      $contents[] = array('text' => TEXT_GV_EDIT_VALUE);
      $contents[] = array('text' => '<br>' . TEXT_GIFT_VOUCHER_NEW_AMOUNT . '<br>' . zen_draw_input_field('amount', number_format($gInfo->coupon_amount, 2, '.', '') ));
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE) . '&nbsp;<a href="' . zen_href_link(FILENAME_GV_ADMIN_REDEMPTION_EDIT, 'page=' . $_GET['page'] . '&gid=' . $gInfo->coupon_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;

    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_GV_EDIT . '</b>');
      $heading[] = array('text' => '[' . $gInfo->coupon_id . '] ' . ' ' . $currencies->format($gInfo->coupon_amount));

      $contents[] = array('align' => 'center', 'text' => TEXT_GV_DELETE);
      $contents[] = array('align' => 'center', 'text' => TEXT_GV_CONFIRM);
      $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_GV_ADMIN_REDEMPTION_EDIT, 'action=confirmdelete&gid=' . $gInfo->coupon_id,'NONSSL') . '">' . zen_image_button('button_confirm_red.gif', IMAGE_CONFIRM) . '</a> <a href="' . zen_href_link(FILENAME_GV_ADMIN_REDEMPTION_EDIT, 'page=' . $_GET['page'] . '&gid=' . $gInfo->coupon_id) . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;

    default:

      $heading[] = array('text' => '<b>' . TEXT_GV_EDIT . '</b>');
      $heading[] = array('text' => '[' . $gInfo->coupon_id . '] ' . ' ' . $currencies->format($gInfo->coupon_amount));

      if ($gv_list->RecordCount() == 0) {
        $contents[] = array('align' => 'center','text' => TEXT_GV_NONE);
      } else {

       $contents[] = array('align' => 'left', 'text' => TEXT_INFO_EMAIL_ADDRESS . ' <b> ' . $gInfo->emailed_to . '</b>');
       $contents[] = array('align' => 'left', 'text' => TEXT_GV_REDEEM . ':  <b>' . $gInfo->coupon_code . '</b>');
       if (($gInfo->date_modified) != '0001-01-01 00:00:00') { $contents[] = array('align' => 'left', 'text' => '<font color="#FF0000">*</font> '. TEXT_LAST_MODIFIED . $gInfo->date_modified); }
       $contents[] = array('align' => 'center', 'text' => '<br>( ' . $currencies->format($gInfo->coupon_amount) .' '. TEXT_COUPON_CODE .' )');
       $contents[] = array('align' => 'center', 'text' => '<br />' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif','','90%','3'));

	$contents[] = array('align' => 'center', 'text' =>'<a href="'.zen_href_link(FILENAME_GV_ADMIN_REDEMPTION_EDIT,'action=edit&gid='.$gInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL').'">'.zen_image_button('button_edit.gif','Edit ' . TEXT_GIFT_VOUCHER) .'</a>' );
	
	$contents[] = array('align' => 'center', 'text' => '<a href="'.zen_href_link(FILENAME_GV_ADMIN_REDEMPTION_EDIT,'action=delete&gid='.$gInfo->coupon_id . (isset($_GET['status']) ? '&status=' . $_GET['status'] : '') . (isset($_GET['page']) ? '&page=' . $_GET['page'] : ''),'NONSSL').'">'.zen_image_button('button_delete.gif','Delete ' . TEXT_GIFT_VOUCHER).'</a>' );
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