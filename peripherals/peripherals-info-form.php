<?php
/*
 * @version $Id$
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2006 by the INDEPNET Development Team.
 
 http://indepnet.net/   http://glpi.indepnet.org
 ----------------------------------------------------------------------

 LICENSE

	This file is part of GLPI.

    GLPI is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    GLPI is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with GLPI; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 ------------------------------------------------------------------------
*/
 
// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

include ("_relpos.php");
include ($phproot . "/glpi/includes.php");
include ($phproot . "/glpi/includes_peripherals.php");
include ($phproot . "/glpi/includes_computers.php");
include ($phproot . "/glpi/includes_reservation.php");
include ($phproot . "/glpi/includes_tracking.php");
include ($phproot . "/glpi/includes_financial.php");
include ($phproot . "/glpi/includes_documents.php");
include ($phproot . "/glpi/includes_networking.php");
include ($phproot . "/glpi/includes_state.php");
include ($phproot . "/glpi/includes_users.php");
include ($phproot . "/glpi/includes_links.php");

if(isset($_GET)) $tab = $_GET;
if(empty($tab) && isset($_POST)) $tab = $_POST;
if(empty($tab["ID"])) $tab["ID"] = "";
if(!isset($tab["withtemplate"])) $tab["withtemplate"] = "";

$per=new Peripheral();

if (isset($_POST["add"]))
{
	checkRight("peripheral","w");

	$newID=$per->add($_POST);
	logEvent($newID, "peripherals", 4, "inventory", $_SESSION["glpiname"]." ".$lang["log"][20]." ".$_POST["name"].".");
	glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset($tab["delete"]))
{
	checkRight("peripheral","w");

	if (!empty($tab["withtemplate"]))
		$per->delete($tab,1);
	else $per->delete($tab);

	logEvent($tab["ID"], "peripherals", 4, "inventory", $_SESSION["glpiname"]." ".$lang["log"][22]);
	if(!empty($tab["withtemplate"])) 
		glpi_header($cfg_glpi["root_doc"]."/setup/setup-templates.php");
	 else 
	glpi_header($cfg_glpi["root_doc"]."/peripherals/");
}
else if (isset($_POST["restore"]))
{
	checkRight("peripheral","w");

	$per->restore($_POST);
	logEvent($tab["ID"], "peripherals", 4, "inventory", $_SESSION["glpiname"]." ".$lang["log"][23]);
	glpi_header($cfg_glpi["root_doc"]."/peripherals/");
}
else if (isset($tab["purge"]))
{
	checkRight("peripheral","w");

	$per->delete($tab,1);
	logEvent($tab["ID"], "peripherals", 4, "inventory", $_SESSION["glpiname"]." ".$lang["log"][24]);
	glpi_header($cfg_glpi["root_doc"]."/peripherals/");
}
else if (isset($_POST["update"]))
{
	checkRight("peripheral","w");

	$per->update($_POST);
	logEvent($_POST["ID"], "peripherals", 4, "inventory", $_SESSION["glpiname"]." ".$lang["log"][21]);
	glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset($tab["disconnect"]))
{
	checkRight("peripheral","w");

	Disconnect($tab["ID"]);
	logEvent(0, "peripherals", 5, "inventory", $_SESSION["glpiname"]." ".$lang["log"][27]);
	glpi_header($_SERVER['HTTP_REFERER']);
}
else if(isset($tab["connect"])&&isset($tab["item"])&&$tab["item"]>0)
{

	checkRight("peripheral","w");

	Connect($_SERVER["PHP_SELF"],$tab["sID"],$tab["item"],PERIPHERAL_TYPE);
	logEvent($tab["sID"], "peripherals", 4, "inventory", $_SESSION["glpiname"]." ".$lang["log"][26]);
	glpi_header($cfg_glpi["root_doc"]."/peripherals/peripherals-info-form.php?ID=".$tab["sID"]);
}
else
{
	checkRight("peripheral","r");
	
	if (!isset($_SESSION['glpi_onglet'])) $_SESSION['glpi_onglet']=1;
	if (isset($_GET['onglet'])) {
		$_SESSION['glpi_onglet']=$_GET['onglet'];
//		glpi_header($_SERVER['HTTP_REFERER']);
	}
	
	
	commonHeader($lang["title"][7],$_SERVER["PHP_SELF"]);
	
	if ($per->getFromDB($tab["ID"]))
		$per->showOnglets($_SERVER["PHP_SELF"]."?ID=".$tab["ID"], $tab["withtemplate"],$_SESSION['glpi_onglet'] );
		
	if (!empty($tab["withtemplate"])) {

		if (showPeripheralForm($_SERVER["PHP_SELF"],$tab["ID"], $tab["withtemplate"])){
		if (!empty($tab["ID"])){

			switch($_SESSION['glpi_onglet']){
				case 4 :
					showInfocomForm($cfg_glpi["root_doc"]."/infocoms/infocoms-info-form.php",PERIPHERAL_TYPE,$tab["ID"],1,$tab["withtemplate"]);
					showContractAssociated(PERIPHERAL_TYPE,$tab["ID"],$tab["withtemplate"]);
					break;
				case 5 :
					showDocumentAssociated(PERIPHERAL_TYPE,$tab["ID"],$tab["withtemplate"]);
					break;
				
				default :
					if (!display_plugin_action(PERIPHERAL_TYPE,$tab["ID"],$_SESSION['glpi_onglet'],$tab["withtemplate"])){
						showPorts($tab["ID"], PERIPHERAL_TYPE,$tab["withtemplate"]);
						if ($tab["withtemplate"]!=2)	showPortsAdd($tab["ID"],PERIPHERAL_TYPE);
					}

					break;
			}
		}
		}
		
	} else {

		if (haveRight("delete_ticket","1")&&isset($_POST["delete_inter"])&&!empty($_POST["todel"])){
			foreach ($_POST["todel"] as $key => $val){
				if ($val==1) {
					deleteTracking($key);
				}
			}
		}

		if (showPeripheralForm($_SERVER["PHP_SELF"],$tab["ID"])){
			switch($_SESSION['glpi_onglet']){
				case -1:
					showConnect($_SERVER["PHP_SELF"],$tab["ID"],PERIPHERAL_TYPE);
					showPorts($tab["ID"], PERIPHERAL_TYPE,$tab["withtemplate"]);
					showPortsAdd($tab["ID"],PERIPHERAL_TYPE);
					showInfocomForm($cfg_glpi["root_doc"]."/infocoms/infocoms-info-form.php",PERIPHERAL_TYPE,$tab["ID"]);
					showContractAssociated(PERIPHERAL_TYPE,$tab["ID"]);
					showDocumentAssociated(PERIPHERAL_TYPE,$tab["ID"]);
					showJobListForItem($_SESSION["glpiname"],PERIPHERAL_TYPE,$tab["ID"]);
					showOldJobListForItem($_SESSION["glpiname"],PERIPHERAL_TYPE,$tab["ID"]);
					showLinkOnDevice(PERIPHERAL_TYPE,$tab["ID"]);
					display_plugin_action(PERIPHERAL_TYPE,$tab["ID"],$_SESSION['glpi_onglet'],$tab["withtemplate"]);
					break;
				case 4 :
					showInfocomForm($cfg_glpi["root_doc"]."/infocoms/infocoms-info-form.php",PERIPHERAL_TYPE,$tab["ID"]);
					showContractAssociated(PERIPHERAL_TYPE,$tab["ID"]);
					break;
				case 5 :
					showDocumentAssociated(PERIPHERAL_TYPE,$tab["ID"]);
					break;
				case 6 :
					showJobListForItem($_SESSION["glpiname"],PERIPHERAL_TYPE,$tab["ID"]);
					showOldJobListForItem($_SESSION["glpiname"],PERIPHERAL_TYPE,$tab["ID"]);
					break;
				case 7 :
					showLinkOnDevice(PERIPHERAL_TYPE,$tab["ID"]);
					break;	
				case 10 :
					showNotesForm($_SERVER["PHP_SELF"],PERIPHERAL_TYPE,$tab["ID"]);
					break;	
				case 11 :
					showDeviceReservations($_SERVER["PHP_SELF"],PERIPHERAL_TYPE,$tab["ID"]);
					break;
				case 12 :
					showHistory(PERIPHERAL_TYPE,$tab["ID"]);
				break;		
				default :
					if (!display_plugin_action(PERIPHERAL_TYPE,$tab["ID"],$_SESSION['glpi_onglet'],$tab["withtemplate"])){
						showConnect($_SERVER["PHP_SELF"],$tab["ID"],PERIPHERAL_TYPE);
						showPorts($tab["ID"], PERIPHERAL_TYPE,$tab["withtemplate"]);
						showPortsAdd($tab["ID"],PERIPHERAL_TYPE);
					}
					break;
			}
			
			
			
			
			
		}
	}
	commonFooter();
}


?>
