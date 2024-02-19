<?php
       session_start();
       include("../common/lib.php");
	   include("../lib/class.db.php");
	   include("../common/config.php");
	   
	   
	   if(empty($_SESSION['userid']))
	   {
	     Header("Location: ../login/login.php");
	   }
	   
	   $cmd = $_REQUEST['cmd'];
	   switch($cmd)
	   {
	     
		  case 'add': 
		                   $info['table']    = "company";
						   $data['ComName'] = $_REQUEST['ComName'];  
						   $data['Address1'] = $_REQUEST['Address1'];  
						   $data['Address2'] = $_REQUEST['Address2'];      	   
						   $data['Country'] = $_REQUEST['Country'];  
						   $data['tel'] = $_REQUEST['tel'];						   
						    $data['Fax'] = $_REQUEST['Fax'];      	   
						   $data['opDate'] = $_REQUEST['opDate'];  
						   $data['clDate'] = $_REQUEST['clDate']; 
						     
						   $info['data']     =  $data;
						  
						   if(empty($_REQUEST['id']))
						   {
						    $db->insert($info);
						   }
						   else
						   {
						    $Id            = $_REQUEST['id'];
							$info['where'] = "Id='".$Id."'";
						    $db->update($info);
							updateMasterOpAssOpDispose($db,$Id,$_REQUEST['opDate'],$_REQUEST['clDate']);
						   }
						   
			include("../company/company_list.php");
						   
						   break; 
						   
						   
		case "edit":       $Id               = $_REQUEST['id'];
		                    if( !empty($Id ))
							{
							   $info['table']    = "company";
							   $info['fields']   = array("*");   	   
							   $info['where']    =  "Id='".$Id."'";
							   
							   $res  =  $db->select($info);
							   
							   $ComName  = $res[0]['ComName'];
							   $Address1 = $res[0]['Address1'];  
							   $Address2 = $res[0]['Address2'];      	   
							   $Country = $res[0]['Country'];  
							   $tel = $res[0]['tel'];							   
							   $Fax = $res[0]['Fax'];      	   
							   $opDate = $res[0]['opDate'];  
							   $clDate = $res[0]['clDate'];
							  
							   
						   }
						   
						   
						   
                           	include("../company/company_editor.php");
						  
						   break;
						   
         case 'delete': 
		                   $Id               = $_REQUEST['id'];
		                   
						   $info['table']    = "company";
						   $data['where']    = "id='".$Id."'";
						  
						   if($Id)
						   {
						    $db->delete($info['table'],$data['where']);
						   }
				include("../company/company_list.php");
						   
						   break; 
						   
						   
          case "list" :    	 if(!empty($_REQUEST['page'])&&$_SESSION["search"]=="yes")
		                    {
							  $_SESSION["search"]="yes";
							 							}
							else
							{
		                       $_SESSION["search"]="no";
								unset($_SESSION["search"]);
								unset($_SESSION['field_name']);
								unset($_SESSION["field_value"]); 
							}
		                    include("../company/company_list.php");
						   break; 
          case "search_company":
		                     $_REQUEST['page'] = 1;  
		  					$_SESSION["search"]="yes";
							$_SESSION['field_name'] = $_REQUEST['field_name'];
							$_SESSION["field_value"] = $_REQUEST['field_value'];
		  				    include("../company/company_list.php");
						   break;  						    						   
						
	default   :    include("../company/company_list.php");		   
      
	   }
function 	updateMasterOpAssOpDispose($db,$ComID,$opDate,$clDate)
{
       set_time_limit(0);
       //All Company   
       $info['table']    = "master";
	   $info['fields']   = array("*");   	   
	   $info['where']    =  "ComID='".$ComID."'";
	   $res  =  $db->select($info);
	  
	   for($i=0;$i<count($res);$i++)
	   {   //ACode and details
	       unset($info);
		   $info['table']    = "details";
		   $info['fields']   = array("*");   	   
		   $info['where']    =  " ComID='".$ComID."' AND ACode='".$res[$i]["ACode"]."' AND TDate<'".$opDate."'";
		   $resdetails  =  $db->select($info);
		   
		   $Credit=0;
		   $Debit=0;
		   for($j=0;$j<count($resdetails);$j++)
		   {
		     if($resdetails[$j]["Credit"]>0)
			 {
		        $Credit = $resdetails[$j]["Credit"];
			 }
			 if($resdetails[$j]["Debit"]>0)
			 {
		        $Debit = $resdetails[$j]["Debit"];			 
			 }
		   }
		     unset($info);
			 unset($data);
		  $info['table']    = "master";
	      $data['OpAssets']   = $Debit-$Credit;
		  $data['OpDep']   = $Credit;
		  $info['data'] = $data;	   
	      $info['where']    =  "id='".$res[$i]["id"]."'";
	      $resupdate  =  $db->update($info); 
	   }
}
?>
