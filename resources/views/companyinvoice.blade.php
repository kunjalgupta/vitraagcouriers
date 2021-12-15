
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
	
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1"/>
	<title></title>
	<meta name="generator" content="LibreOffice 5.4.7.2 (Linux)"/>
	<meta name="author" content="mitul patel"/>
	<meta name="created" content="2020-12-12T15:32:05"/>
	<meta name="changedby" content="mitul patel"/>
	<meta name="changed" content="2020-12-18T17:10:08"/>
	<meta name="AppVersion" content="15.0300"/>
	<meta name="DocSecurity" content="0"/>
	<meta name="HyperlinksChanged" content="false"/>
	<meta name="LinksUpToDate" content="false"/>
	<meta name="ScaleCrop" content="false"/>
	<meta name="ShareDoc" content="false"/>
	
	<style type="text/css">
		body,div,table,thead,tbody,tfoot,tr,th,td,p { font-family:"Calibri"; font-size:x-small }
		a.comment-indicator:hover + comment { background:#ffd; position:absolute; display:block; border:1px solid black; padding:0.5em;  } 
		a.comment-indicator { background:red; display:inline-block; border:1px solid black; width:0.5em; height:0.5em;  } 
		comment { display:none;  } 
	</style>
	
</head>

<body>
<table cellspacing="0" border="0" width="750">
	
	
	
	
	
	
	
	
	
	<tr>
		<table style="width:100%;border-collapse: collapse;">
			 <tr>
				<td colspan="2" style="width:100%;border-left:1px solid black;border-top:1px solid black;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size: 15px;text-align: left;"><img src="https://vitraagcourier.com/vt/public/Vitraag-logo.png"  height="100" hspace=50 vspace=2></td>
				<td colspan="2" style="width:100%;border-right:1px solid black;border-top:1px solid black;line-height: 20px;padding-left: 10px;padding-right: 10px;font-size: 15px;text-align: left;"> 
					
					<b>Head office</b> : 79, Mahadev Industrial Estate 5 <br> Nr. Ramol Police Station, Ahmedabad : 382449.
         
					<br><b>+91 9904840607</b><br>
					 <b>www.vitraagcourier.com</b><br>
					 <b>info.vitraagcourier@gmail.com</b>
					</td>
					
			</tr> 
			
			<tr>
				 <td colspan="3" style="width:100%;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size: 15px;">To : <br><b>Name</b> : {{$franchise_name}}</td>
				 <td colspan="1" style="width:30%;border-left:1px solid black;border-right:1px solid black;border-top:1px solid black;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size: 12px;"><b>TAX INVOICE NO</b> :  </td>
			</tr>
			<tr>
				 <td colspan="3" style="width:100%;border-left:1px solid black;border-right:1px solid black;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size: 15px;"><b>Mobile</b> : {{$franchise_mobile}}</td>
				 <td colspan="1" style="width:30%;border-left:1px solid black;border-right:1px solid black;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size: 12px;"><b>Start Date</b> : {{$start_date}}</td>
			</tr>
			<tr>
				 <td colspan="3" style="width:100%;border-left:1px solid black;border-right:1px solid black;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size: 15px;"><b>ADDRESS</b> : {{$franchise_address}}</td>
				 <td colspan="1" style="width:30%;border-left:1px solid black;border-right:1px solid black;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size: 12px;"><b>End Date</b> : {{$end_date}}</td>
			</tr>
			<tr>
				 <td colspan="3" style="width:100%;border-left:1px solid black;border-right:1px solid black;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size: 15px;"><b>PINCODE</b> : {{$franchise_pincode}}<br><b>GST NO</b> : {{$gst_number}}</td> 
				 <td colspan="1" style="width:30%;border-left:1px solid black;border-right:1px solid black;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size: 15px;"></td>
			</tr>
   
    <tr>
      <th style="width:20%;border:1px solid black;line-height: 25px;text-align: center;padding-left: 10px;padding-right: 10px;font-size:14px;">Date</th>
      <th style="width:20%;border:1px solid black;line-height: 25px;text-align: center;padding-left: 10px;padding-right: 10px;font-size:14px;">Courier Type</th>
      <th style="width:20%;border:1px solid black;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size:14px;">Destination</th>
      <th style="width:20%;border:1px solid black;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size:14px;">Amount</th>
     
    </tr>
    <?php $i=1; foreach ($resultSet as $key => $value) { ?>
      <tr>
        <td style="width:25%;border:1px solid black;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size: 12px;"><?php echo $value['created_at']; ?></td>
         <td style="width:25%;border:1px solid black;line-height: 25px;text-align: center;padding-left: 10px;padding-right: 10px;font-size: 12px;"><?php echo $value['courier_type']; ?></td>
        <td style="width:25%;border:1px solid black;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size: 12px;"><?php echo $value['destination']; ?></td>
       
        <td style="width:25%;border:1px solid black;line-height: 25px;text-align: center;padding-left: 10px;padding-right: 10px;font-size: 12px;"><?php echo $value['total_amount']; ?></td>
       
               
      </tr>
    <?php } ?>
    <tr>
        <td style="width:25%;border-left:1px solid black;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size: 12px;"><b></b></td>
        <td style="width:25%;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size: 12px;"><b></b></td>
        <td style="width:25%;line-height: 25px;text-align: right;padding-left: 10px;padding-right: 10px;font-size: 12px;"><b>Gross Value</b></td>
        <td style="width:25%;border:1px solid black;line-height: 25px;text-align: center;padding-left: 10px;padding-right: 10px;font-size: 12px;"><b><?php echo $total_amount['net_value']; ?></b></td>
               
      </tr> <tr>
        <td style="width:25%;border-left:1px solid black;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size: 12px;"><b></b></td>
        <td style="width:25%;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size: 12px;"><b></b></td>
        <td style="width:25%;line-height: 25px;text-align: right;padding-left: 10px;padding-right: 10px;font-size: 12px;"><b>SGST 9%</b></td>
        <td style="width:25%;border:1px solid black;line-height: 25px;text-align: center;padding-left: 10px;padding-right: 10px;font-size: 12px;"><b><?php echo $total_amount['sgst']; ?></b></td>
               
      </tr> <tr>
        <td style="width:25%;border-left:1px solid black;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size: 12px;"><b></b></td>
        <td style="width:25%;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size: 12px;"><b></b></td>
        <td style="width:25%;line-height: 25px;text-align: right;padding-left: 10px;padding-right: 10px;font-size: 12px;"><b>CGST 9%</b></td>
        <td style="width:25%;border:1px solid black;line-height: 25px;text-align: center;padding-left: 10px;padding-right: 10px;font-size: 12px;"><b><?php echo  $total_amount['cgst']; ?></b></td>
               
      </tr> 
      <tr>
        <td style="width:25%;border-left:1px solid black;border-bottom:1px solid black;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size: 12px;"><b></b></td>
        <td style="width:25%;border-bottom:1px solid black;line-height: 25px;padding-left: 10px;padding-right: 10px;font-size: 12px;"><b></b></td>
        <td style="width:25%;border-bottom:1px solid black;line-height: 25px;text-align: right;padding-left: 10px;padding-right: 10px;font-size: 12px;"><b>Total</b></td>
        <td style="width:25%;border:1px solid black;line-height: 25px;text-align: center;padding-left: 10px;padding-right: 10px;font-size: 12px;"><b><?php echo  $total_amount['total_value']; ?></b></td>
               
      </tr>
</table>
		</tr>

	<tr>
		<td style="border-top: 1px solid black; border-left: 1px solid black; border-right: 0px solid black" colspan=7 height="20" align="center" valign=bottom bgcolor="#FFFFFF"><font color="black"><br></font></td>
		<td style="border-top: 1px solid black; border-left: 1px solid black; border-right: 1px solid black" colspan=3 rowspan=2 align="center" valign=bottom bgcolor="#FFFFFF"><font color="black"><br></font></td>
		<td style="border-top: 1px solid black; border-left: 0px solid black;border-bottom: 1px solid black" colspan=2 align="center" valign=bottom bgcolor="#FFFFFF"><b><font size="3" color="black">Office :</font></b></td>
		<td style="border-top: 1px solid black; border-right: 1px solid black;border-bottom: 1px solid black" colspan=4 align="left" valign=bottom bgcolor="#FFFFFF"><font style="font-family: DejaVu Sans; sans-serif;" size="3" color="black">Vitraag Courier</font></td>
		</tr>
	<tr>
		<td colspan="6" style="border-left: 1px solid black" height="20" align="left" valign=bottom bgcolor="#FFFFFF"><font color="black"><b> Bank : kotak mahindra bank A/c.  : 3245075703 Ifsc. : KKBK0002563 . GSTIN : 24ASEPJ5924C2Z3</b></font></td>
		
		<td style="border-right: 0px solid black" align="left" valign=bottom bgcolor="#FFFFFF"><font color="black"><br></font></td>
		<td style="border-right: 0px solid black" colspan=3 align="center" valign=bottom bgcolor="#FFFFFF"><font color="black">Help No : +91 95105 69263</font></td>
		<td style="border-right: 1px solid black" colspan=3  valign=bottom bgcolor="#FFFFFF"><font color="black"></font></td>
		</tr>
	<tr>
		<td style="border-bottom: 1px solid black; border-left: 1px solid black; border-right: 0px solid black" colspan=7 height="20" valign=bottom bgcolor="#FFFFFF"><font color="black">Note - Cash,Gold,Silver,Diamond,Liquid &amp; IATA Rest Item are not accepted By Us.</font></td>
		<td style="border-bottom: 1px solid black; border-left: 1px solid black; border-right: 1px solid black" colspan=3 align="center" valign=bottom><font color="black">Customer Sign</font> <br><font color="black">SAC 996819 : Other Delivery Services n.e.c. 996719 : Other cargo and baggage handling services. 996519 : Other land transport services of goods n.e.c.</font></td>
		<td style="border-bottom: 1px solid black" colspan=3 align="center" valign=bottom><u><font size="2" color="#0563C1"><a href="http://www.vitraagcourier.com/">www.VitraagCourier.com</a></font></u></td>
		<td style="border-bottom: 1px solid black; border-right: 1px solid black" colspan=3 align="center" valign=bottom><u><font size="2" color="#0563C1"><a href="mailto:info.vitraagcourier@gmail.com">info.vitraagcourier@gmail.com</a></font></u></td>
		</tr>
</table>
<!-- ************************************************************************** -->
</body>

</html>
