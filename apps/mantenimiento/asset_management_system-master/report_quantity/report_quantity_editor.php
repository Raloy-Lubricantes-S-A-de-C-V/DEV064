<?php
 include("../template/header.php");
 ?>
  <script	src="../js/main.js" type="text/javascript"></script>
  <script	src="../js/prototype.js" type="text/javascript"></script>
<script>
function IsDateRange(DateValue,idName)
{
	var DateValue1=document.getElementById("openingdate").value;
	var DateValue2=document.getElementById("closingdate").value;		
	var d1  = new Array();
	var d2  = new Array();
	var d3  = new Array();

	d1      = DateValue1.split("-");
	d2      = DateValue2.split("-");
	d3      = DateValue.split("-");		

	
	var Date1 = new Date(d1[0],d1[1],d1[2]);
	var Date2 = new Date(d2[0],d2[1],d2[2]);
	var Date3 =new Date(d3[0],d3[1],d3[2]);
	var openDate =  Date1.getTime();
	var closeDate=  Date2.getTime();
	var curDate=  Date3.getTime();

	if(curDate>=openDate&&curDate<=closeDate)
	{
	 document.getElementById("SerDate").value = document.getElementById("closingdate").value;
	}
	else
	{
		if(DateValue.length>0)
		  {
		  alert("Date must be within accounting period");
		  }
	     document.getElementById("idName").focus();
	}
}

 //check required fields of the form
    function checkRequired()
    {
	
      
        if(document.getElementById("option").value=="")
        {
            alert("Report option is a required field.");
            document.getElementById("option").focus();
            return false;
        }
		
        return true;
    }
</script>

<input type="hidden" name="openingdate" id="openingdate" value="<?=$_SESSION["opDate"]?>">
<input type="hidden" name="closingdate" id="closingdate" value="<?=$_SESSION["clDate"]?>">

 <b> Quantity Report </b><br />
<table cellspacing="3" cellpadding="3" border="0" class="bdr" width="100%">
    <tr>
        <td>
                        <table cellpadding="3" cellspacing="3" border="0" width="100%"  class="bodytext">
                            <tr>
                                <td>
                                    <!-- -->
                                    <form  name="report_quantity_frm" id="report_quantity_frm" method="post" action="" onsubmit=" return checkRequired();">
                                      <table>
									    <tr>
 											 <td>Date From</td>
											 <td>
											 <input type="text" name="date_from" id="date_from"  value="<?=$_SESSION["opDate"]?>"  class="textbox" onblur=" IsDateRange(this.value,'date_from');">
											 <a href="#" onclick="displayDatePicker('date_from');"><img src="../icons/calendar.gif" width="16" height="16" border="0" /></a>
											 </td>
										 </tr>
										 <tr>
 											 <td>Date To</td>
											 <td>
											 <input type="text" name="date_to" id="date_to" value="<?=$_SESSION["clDate"]?>"  class="textbox" onblur=" IsDateRange(this.value,'date_to');">
											 <a href="#" onclick="displayDatePicker('date_to');"><img src="../icons/calendar.gif" width="16" height="16" border="0" /></a>
											 </td>
										 </tr>
										 <tr>
										   <td colspan="2">
										   <table cellspacing="3" cellpadding="3" border="0" class="bdr" width="100%">
											<tr>
												<td valign="top">Report Option</td>
												<td >
												   <input type="radio" name="option" id="option"  value="Location" class="textbox" checked="checked"/>Location Wise<br />
												  <!--<input type="radio" name="option" id="option"  value="SubGroup" class="textbox" /> Asset SubGroup Wise<br />
												   <input type="radio" name="option" id="option"  value="DefGroup" class="textbox" />Asset Group Wise<br />
												   <input type="radio" name="option" id="option"  value="Project" class="textbox" />Asset Project Wise<br />
												   <input type="radio" name="option" id="option"  value="UserName" class="textbox" />Asset UserName Wise<br />
												   <input type="radio" name="option" id="option"  value="Location" class="textbox"/>Asset Location Wise<br />
												   <input type="radio" name="option" id="option"  value="Department" class="textbox" />Asset Department Wise<br />-->
												</td>
											</tr>
											</table>
											</td>
										 </tr>
										
										 <tr>
										 <td></td><td>
                                        <input type="hidden" name="cmd" id="cmd" value="make_report_quantity">
                                        <input align='center' type="submit" name="sub_btn" id="sub_btn"  value="Submit" >
										</td>
										</tr> 
										</table>
                                    </form>
                                </td>
                            </tr>
                        </table>
        </td>
    </tr>
</table>
<?php
 include("../template/footer.php");
?>



