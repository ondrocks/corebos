<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
require_once 'include/utils/CommonUtils.php';
require_once 'include/database/PearDatabase.php';

/** Function to  returns the combo field values in array format
  * @param $combofieldNames -- combofieldNames:: Type string array
  * @returns $comboFieldArray -- comboFieldArray:: Type string array
 */
function getComboArray($combofieldNames) {
	global $log, $adb, $current_user;
	$log->debug('Entering getComboArray('.print_r($combofieldNames, true).') method ...');
	$roleid=$current_user->roleid;
	$comboFieldArray = array();
	foreach ($combofieldNames as $tableName => $arrayName) {
		$fldArrName= $arrayName;
		$arrayName = array();
		$sql = "select $tableName from vtiger_$tableName";
		$params = array();
		if (!is_admin($current_user)) {
			$subrole = getRoleSubordinates($roleid);
			if (count($subrole)> 0) {
				$roleids = $subrole;
				$roleids[] = $roleid;
			} else {
				$roleids = $roleid;
			}
			$sql = "select distinct $tableName,sortid
				from vtiger_$tableName
				inner join vtiger_role2picklist on vtiger_role2picklist.picklistvalueid = vtiger_$tableName.picklist_valueid
				where roleid in(". generateQuestionMarks($roleids) .') order by sortid';
			$params = array($roleids);
		}
		$result = $adb->pquery($sql, $params);
		while ($row = $adb->fetch_array($result)) {
			$val = $row[$tableName];
			$arrayName[$val] = getTranslatedString($val);
		}
		$comboFieldArray[$fldArrName] = $arrayName;
	}
	$log->debug('Exiting getComboArray method ...');
	return $comboFieldArray;
}

function getUniquePicklistID() {
	global $adb;
	return $adb->getUniqueID('vtiger_picklistvalues');
}
?>
