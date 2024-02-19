<?php
 include("../template/header.php");
 ?>
  <script	src="../js/main.js" type="text/javascript"></script>
  <script	src="../js/prototype.js" type="text/javascript"></script>
<script>
    function getHTML(value)
    {
        //Device
        var url = 'report_asset_aquisition.php';
        var pars = 'cmd=make_asset_aquisition_item&Group='+value;

        var myAjax = new Ajax.Updater(
        {success: 'div_individual_item_id'},
        url,
        {
            method: 'post',
            parameters: pars,
            onFailure: report_speedError
        });
    }
	
    function report_speedError(request)
    {
        alert('Sorry. There was an error.');
    }

    //check required fields of the form
    function checkRequired()
    {

        if(document.getElementById("Assetat").value=="")
        {
            alert("Assetat is a required field.");
            document.getElementById("Assetat").focus();
            return false;
        }

        
        return true;
    }

  
//Comparing date
function IsDateRange(DateValue)
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
	 return true;
	}
	else
	{ if(DateValue.length>0)
	  {
	  alert("Date must be within accounting period");
	  document.getElementById("Assetat").focus();	
           
	  }
	}
}
</script>

<input type="hidden" name="openingdate" id="openingdate" value="<?=$_SESSION["opDate"]?>">
<input type="hidden" name="closingdate" id="closingdate" value="<?=$_SESSION["clDate"]?>">
 <b> Asset Aquisition </b><br />
<table cellspacing="3" cellpadding="3" border="0" class="bdr" width="100%">
    <tr>
        <td>
                        <table cellpadding="3" cellspacing="3" border="0" width="100%"  class="bodytext">
                            <tr>
                                <td>
                                    <!-- -->
                                    <form  name="report_asset_aquisition_frm" id="report_asset_aquisition_frm" method="post" action="" onsubmit="return checkRequired();">
                                      <table>
									     <tr>
 											 <td>Asset at</td>
											 <td>
											 <input type="text" name="Assetat" id="Assetat"   class="textbox" onblur="IsDateRange(this.value)">
											 <a href="#" onclick="displayDatePicker('Assetat');"><img src="../icons/calendar.gif" width="16" height="16" border="0" /></a>											
											 </td>
										 </tr>
										 <tr>
										    <td>Select</td>
										    <td >
											    <select  name="Group" id="Group"  class="textbox" onchange="getHTML(this.value)">
											      <option value="">--Select--</option>
												  <option value="ACode">Asset Code</option>
												  <option value="DefGroup">Default Group</option>
												  <option value="SubGroup">Sub Group</option>
												  <option value="Department">Department</option>
												  <option value="Location">Location</option>
												  <option value="UserName">User</option>
												  <option value="Project">Project</option>
												</select>
											</td>
										 </tr>
										 <tr>
											 <td valign="top">Individual Item</td>
											 <td><div name="individual_item" id="div_individual_item_id"></div></td>
										 </tr>
										 <tr>
										 <td></td><td>
                                        <input type="hidden" name="cmd" id="cmd" value="make_report_asset_aquisition">
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



