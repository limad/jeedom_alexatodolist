<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */
try {
	require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
	include_file('core', 'authentification', 'php');
	/*
		$eqLogics = alexatodolist::byType('alexatodolist');
		foreach ($eqLogics as $eqLogic) {
			log::add('alexatodolist', 'info', $eqLogic->getConfiguration('ip'));
		}
	*/
	if (!isConnect('admin')) {
		throw new \Exception('401 Unauthorized');
	}
	//$('.deamonCookieState').empty().append('<span class="label label-success" style="font-size:1em;">00012300</span>');
	//log::add('alexatodolist', 'info', 'Lancement Serveur pour Cookie - action='.init('action'));
  	$action = init('action');
	switch ($action) {
		case 'removeAll':
        	$return = alexatodolist::removeAllEqLogics(false);
        	if($return === true) ajax::success($return);
        	else{
              log::add('alexatodolist', 'warning', "alexatodolist::Ajax::$action => $return");
              ajax::error($return);
            }
			break;
      	case 'set_completed':
			log::add('alexatodolist', 'debug', "alexatodolist::Ajax::$action "
					. " listId: ".init('listId')
					. " itemId: ".init('itemId')
					. " itemName: ".init('itemName')
					. " customerId: ".init('customerId')
					. " version: ".init('version')
                    . " completed: ".init('completed')
					. " idCmd: ".init('idCmd')
					
            );
        	
        	$return = alexatodolist::set_completed( init('listId'), init('itemId'), init('completed'), init('version') );
			if($return === true) ajax::success($return);
        	else{
              log::add('alexatodolist', 'warning', "alexatodolist::Ajax::$action => $return");
              ajax::error($return);
            }
			break;
		case 'set_itemName':
			log::add('alexatodolist', 'debug', "alexatodolist::Ajax::$action ");
        	$return = alexatodolist::set_itemName(init('listId'), init('itemId'), init('itemName'), init('version'));
			if($return === true) ajax::success($return);
        	else{
              log::add('alexatodolist', 'warning', "alexatodolist::Ajax::$action => $return");
              ajax::error($return);
            }
			break;
		case 'deleteItem':
			log::add('alexatodolist', 'debug', "alexatodolist::Ajax::$action ".init('version'));
        	$return = alexatodolist::deleteItem(init('listId'), init('itemId'), init('version'));
			if($return === true) ajax::success($return);
        	else{
              log::add('alexatodolist', 'warning', "alexatodolist::Ajax::$action => $return");
              ajax::error($return);
            }
			break;
		case 'addItem':
			log::add('alexatodolist', 'debug', "alexatodolist::Ajax::$action");
        	$return = alexatodolist::addItem(init('listId'), init('itemName'));
			ajax::success();
			break;
		case 'addList':
			log::add('alexatodolist', 'debug', "alexatodolist::Ajax::$action");
        	$return = alexatodolist::addList(init('listName'));
			if($return === true) ajax::success($return);
        	else{
              log::add('alexatodolist', 'warning', "alexatodolist::Ajax::$action => $return");
              ajax::error($return);
            }
			break;
		case 'removeList':
			log::add('alexatodolist', 'debug', "alexatodolist::Ajax::$action");
        	$return = alexaapiv2::removeList(init('listName'));
			ajax::success();
			break;
		case 'scanlists':
			log::add('alexatodolist', 'debug', "alexatodolist::Ajax::$action");
        	$return = alexatodolist::scanlists();
			if($return === true) ajax::success($return);
        	else{
              log::add('alexatodolist', 'warning', "alexatodolist::Ajax::$action => $return");
              ajax::error($return);
            }
			break;
		case 'getLists':
			log::add('alexatodolist', 'debug', "Ajax::$action");
        	$return = alexaapiv2::getLists();
			if($return === true) ajax::success($return);
        	else{
              log::add('alexatodolist', 'warning', "alexatodolist::Ajax::$action => $return");
              ajax::error($return);
            }
			break;
		case 'getList':
        	log::add('alexatodolist', 'debug', "alexatodolist::Ajax::$action");
        	$listId = init('listId');
        	if($listId == '') ajax::error("l'id de la liste ne peut être vide !");
			$return = alexaapiv2::getList($listId);
			if($return === true) ajax::success($return);
        	else{
              log::add('alexatodolist', 'warning', "alexatodolist::Ajax::$action => $return");
              ajax::error($return);
            }
			break;
	}
	throw new \Exception("Aucune methode correspondante à $action");
} catch (\Exception $e) {
	ajax::error(displayException($e), $e->getCode());
	log::add('alexatodolist', 'error', $e);
}