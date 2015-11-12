<?php
//print_r($_REQUEST);
$pay_date=date('l jS \of F Y h:i:s A');


if($_REQUEST['action']=="sucess")
{

echo $item_strng = '';
$cnt = $_REQUEST['custom'];
for($k=1;$k<=$cnt;$k++){
        $item_strng .= "<tr>
                                <td width='50%' align='right' valign='middle' class='td'>Name:&nbsp;</td>
                                <td width='50%' align='left' valign='middle'>".$_REQUEST['item_name'.$k]."</td>
                        </tr>
                        <tr>
                                <td width='50%' align='right' valign='middle' class='td'>Quantity:&nbsp;</td>
                                <td width='50%' align='left' valign='middle'>".$_REQUEST['quantity'.$k]."</td>
                        </tr>
                        <tr>
                                <td width='50%' align='right' valign='middle' class='td'>Price:&nbsp;</td>
                                <td width='50%' align='left' valign='middle'>".$_REQUEST['mc_gross_'.$k]."</td>
                        </tr>";
}
			
echo $message="<table width='100%' border='0' cellspacing='0' cellpadding='0'>
        <tr>
        <td align='left' valign='top'>
          <fieldset style=' margin-left:8px; margin-right:8px;padding-left:10px; padding-right:10px;'>
                  <LEGEND  ACCESSKEY=L><span class='heading_txt'>Billing / Shipping </span></LEGEND>
                  <table width='100%' border='0' cellspacing='0' cellpadding='0'>
        <tr>
              <td width='50%' align='left' valign='middle'><table width='90%' border='1' align='left' cellpadding='0' cellspacing='0' bordercolor='#CCCCCC' style=' border-collapse: collapse;'>".$item_strng.
              
                "<tr>
                      <td width='50%' align='right' valign='middle' class='td'> Payer Email:&nbsp;</td>
                      <td width='50%' align='left' valign='middle'>".$_REQUEST['payer_email']."</td>
                </tr>
                <tr>
                      <td width='50%' align='right' valign='middle' class='td'> Shipping Price:&nbsp;</td>
                      <td width='50%' align='left' valign='middle'>".$_REQUEST['mc_handling']."</td>
                </tr>
                <tr>
                      <td width='50%' align='right' valign='middle' class='td'> Discusount:&nbsp;</td>
                      <td width='50%' align='left' valign='middle'>".$_REQUEST['discount']."</td>
                </tr>
                <tr>
                      <td width='50%' align='right' valign='middle' class='td'>&nbsp;</td>
                      <td width='50%' align='left' valign='middle'>&nbsp;</td>
                </tr>
              </table></td>
              <td width='50%' align='right' valign='top'><table width='90%' border='1' align='right' cellpadding='0' cellspacing='0' bordercolor='#CCCCCC' style=' border-collapse: collapse;'>
                <tr>
                      <td width='50%' align='right' valign='middle' class='td'>Order Date </td>
                      <td width='50%' class='td'>".$pay_date."</td>
                </tr>
      
              </table></td>
        </tr>
        
        
      <tr>
              <td width='50%' align='left' valign='middle' >&nbsp;</td>
              <td width='50%'>&nbsp;</td>
        </tr>
        <tr>
              <td width='50%' align='right' valign='middle'>&nbsp;</td>
              <td width='50%'>&nbsp;</td>
        </tr>
        <tr>
              <td align='right' valign='middle'>&nbsp;</td>
              <td><table width='90%' border='0' align='right' cellpadding='0' cellspacing='0'>
                <tr>
                      <td width='45%' align='right' valign='middle' class='td'>Total </td>
                      <td width='45%' align='left' valign='middle' class='td'> $".$_REQUEST['payment_gross']."</td>
                </tr>
              </table></td>
        </tr>
        <tr>
              <td align='right' valign='middle'>&nbsp;</td>
              <td>&nbsp;</td>
        </tr>
      </table>
      
                </fieldset>
              </td>
        </tr>
        <tr>
              <td align='left' valign='top'>&nbsp;</td>
        </tr>
        <tr>
              <td align='left' valign='top'>&nbsp;</td>
        </tr>
        <tr>
              <td align='left' valign='top'>&nbsp;</td>
        </tr>
      </table>";
}
else{
    echo '<div>Your payment is cancelled.</div>';
}

?>
<a href="http://www.phppowerhousedemo.com/webroot/team13/payment_gateway/paypal/standard/">Click here</a>