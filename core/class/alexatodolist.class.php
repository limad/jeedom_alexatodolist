<?php
  
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
if(!file_exists( __DIR__ . '/../../../../plugins/alexaapiv2/core/api/alexa_Api.php')){
	log::add('alexatodolist', 'error', 'Le plugin Alexa-Premium est introuvable');
	throw new Exception(__('Le plugin Alexa-Premium est introuvable', __FILE__));
}
else if (!class_exists('alexa_Api')) {
	require_once __DIR__ . '/../../../../plugins/alexaapiv2/core/api/alexa_Api.php';
}

class alexatodolist extends eqLogic
{

// ####################################################################################			
public static $_widgetPossibility = array('custom' => true, 'custom::layout' => true);

// ####################################################################################			
// ####################################################################################		

	public static function removeAllEqLogics ($scan = true)
	{
		event::add('jeedom::alert', array('level' => 'success', 'page' => __CLASS__, 'message' => __('Suppression en cours ...', __FILE__)));
		foreach (alexaapiv2::byType(__CLASS__, false) as $eqLogic) {
			$eqLogic->remove();
		}
		return true;
		//if ($scan !== false) self::scanAllAlexa();
	}
	
// ####################################################################################			
	public static function cronHourly($_eqlogic_id = null)
	{
		//log::add('alexatodolist', 'debug', '---------------------------------------------CRON------------------------');
	} 

// ####################################################################################			
	public function refresh($_eqlogic_id = null)
	{
		log::add('alexatodolist', 'debug', __FUNCTION__ . " start... $_eqlogic_id");
      	self::scanlists($_eqlogic_id);
	}
  
// ####################################################################################			
	public static function scanlists($listId=null)
	{
		//log::add('alexatodolist', 'debug', __FUNCTION__ . ' start...');
		$lists = alexa_Api::get_listitems($listId);
		log::add('alexatodolist', 'debug', __FUNCTION__ . ' Résultat ' . json_encode($lists));
      	if($listId) $lists = [$lists];
      	foreach($lists as $logicalId=>$listData){
          	if($logicalId=="listItems" || $logicalId=="listMetadata") continue;
          	if(is_numeric($logicalId)) $logicalId = $listData['listInfo']['listId'] ?? null;
            $config = $listData['listInfo'] ?? [];
            $listType = $listData['listInfo']['listType'] ?? null;
            if($listType == "SHOPPING_LIST" || $listType == "TO_DO"){
                $listName = $listType;
                $config['listName'] = $listName;
            }
            else $listName = $listData['listInfo']['listName'] ?? 'list_'.$logicalId;
            log::add('alexatodolist', 'debug', '=>'.__FUNCTION__ . " [$listName] => " . json_encode($config));
            $eqLog = self::createEq($logicalId, $listName, $config);
			if(is_object($eqLog)){
              	$listItems = $listData['listItems'] ?? [];
              	//$listMetadata = $listData['listMetadata'] ?? [];
              	$eqLog->update_listItems($listItems);//, $listMetadata
            }
            
        }

		return true;
	}
	
// ####################################################################################			
	public static function createEq($logicalId, $listName, $config)
	{
		if(!$logicalId || $logicalId == '') {
          	log::add('alexatodolist', 'error', __FUNCTION__ . " logicalId ne peut être null ! $logicalId");
		}
        /*
            $createAt = $config['createAt'] ?? null;
            $updateAt = $config['updateAt'] ?? null;
            $shareType = $config['shareType'] ?? null;
            $listOfListIds = $config['listOfListIds'] ?? null;
            $isDefaultList = $config['defaultList'] ?? null;
            $customerId = $config['customerId'] ?? null;
            $version = $config['version'] ?? null;
        */
      	$isArchived = $config['archivedList'] ?? false;
		$eqLog = alexatodolist::byLogicalId($logicalId, 'alexatodolist');
        if (!is_object($eqLog)) {
            if (config::byKey('include_mode', 'alexatodolist') == 1) {
                log::add('alexatodolist', 'debug', __FUNCTION__ . " include_mode : ".config::byKey('include_mode', 'alexatodolist'));
				return false;
            }
            $eqLog = new alexatodolist();
            $eqLog->setEqType_name('alexatodolist');
            $eqLog->setLogicalId($logicalId);
            $eqLog->setName($listName);
          	if($isArchived) $eqLog->setIsEnable(0);
            else $eqLog->setIsEnable(1);
            $eqLog->setConfiguration('device', "AlexaList_$listName");
          	$eqLog->setConfiguration('type', $config['listType']);
          	
            foreach($config as $key=>$val){
              	$eqLog->setConfiguration($key, $val);
            }
            $type = $config['listType'];
			$imgPath = dirname(__FILE__) . '/../../../alexaapiv2/core/config/devices/';
          	if (file_exists($imgPath . $type . '.png')) {
				$logoImg = $type . '.png';
			} else $logoImg = 'defautList.png';
          	$eqLog->setConfiguration('icon', $logoImg);
          
          	//log::add('alexatodolist', 'debug', __FUNCTION__ . " start... ($logicalId, $listName, ".json_encode($config).')');
			$eqLog->save();
            
        } 
      	else {
            //log::add('alexatodolist', 'debug', __FUNCTION__ . " ($logicalId, $listName) exist ");
			if($isArchived) $eqLog->setIsEnable(0);
            if ($eqLog->getConfiguration('device') != "AlexaList_$listName") {
                $eqLog->setConfiguration('device', "AlexaList_$listName");
                
            }
          	foreach($config as $key=>$val){
              	$eqLog->setConfiguration($key, $val);
            }
            $eqLog->save();
        }
      	if (is_object(alexatodolist::byLogicalId($logicalId, 'alexatodolist'))) return alexatodolist::byLogicalId($logicalId, 'alexatodolist');
      	else return false;
	}
	
// ####################################################################################			
	public function update_listItems($listItems){
        $eqName = $this->getName();
      	log::add('alexatodolist', 'debug', '    '.__FUNCTION__ . " [$eqName] itemsList " . json_encode($listItems));
        $listId = $this->getLogicalId();
      	$eqId = $this->getId();
        $listItemPresent = [];
      	$items_listAr = [];
      	$items_Completed = [];
        $itemsUnCompleted = [];
        foreach ($listItems as $listItem) {
        	$itemId = $listItem['id'] ?? null;
          	if(!$itemId || $itemId == '') {
              	log::add('alexatodolist', 'warning', __FUNCTION__ . " [$eqName] No itemId $itemId");
            	continue;
            }
        	$itemName = $listItem['value'];
        	$items_listAr[] = $itemId.'|'.$itemName;
        	
          	$isCompleted = ($listItem['completed']) ? 'true' : 'false';
          	if($isCompleted == 'true') $items_Completed[] = $itemId.'|'.$itemName;
          	else if($isCompleted == 'false') $itemsUnCompleted[] = $itemId.'|'.$itemName;
			$parameters = array(
                    'id' => $itemId, 
                    'listId' => $listItem['listId'], 
                    'completed' => $listItem['completed'], 
                    "customerId" => $listItem['customerId'], 
                    "version" => $listItem['version'], 
                    "createdDateTime" => $listItem['createdDateTime'], 
                    "updatedDateTime" => $listItem['updatedDateTime']
            );
			
          	$cmdi = $this->getCmd('info', $itemId);
			if(!is_object($cmdi)){
                $cmdi = new alexatodolistCmd();
                $cmdDoublon = cmd::byEqLogicIdCmdName($this->getId(), $itemName);
              	if(is_object($cmdDoublon)){
                  	$itemName .= '::Doublon_'. explode('-',$itemId)[0];
                }
              	else log::add('alexatodolist', 'warning', '    '.__FUNCTION__ . " [$eqName][$listId] items $itemName ");
                $cmdi->setName($itemName);
              	$cmdi->setType('info');
                $cmdi->setSubType('string');
                $cmdi->setEqType('alexatodolist');
                $cmdi->setEqLogic_id($this->getId());
                $cmdi->setIsHistorized(0);
                $cmdi->setIsVisible(0);
                $cmdi->setConfiguration('itemId', $itemId);
                $cmdi->setConfiguration('listId', $listId);
                $cmdi->setConfiguration('type', 'item');
                $cmdi->setLogicalId($itemId);
            }
        	else {
				$cmdDoublon = cmd::byEqLogicIdCmdName($this->getId(), $itemName);
              	if(is_object($cmdDoublon)){
                  	$itemName .= '::Doublon_'. explode('-',$itemId)[0];
                }
              	$cmdi->setName($itemName);
				$cmdi->setLogicalId($itemId);
				$cmdi->setIsVisible(0);
              	$cmdi->setConfiguration('type', 'item');
            }
          	$listItemPresent[] = $itemId;
			$cmdi->setConfiguration('parameters', $parameters);
			$cmdi->save();
            log::add('alexatodolist', 'debug', '    '.__FUNCTION__ . " [$eqName] maj item : ".$itemName." => ".json_encode($parameters));
			$this->checkAndUpdateCmd($itemId, $isCompleted);
				
        }
      	//log::add('alexatodolist', 'debug', '    '.__FUNCTION__ . " [$eqName] listItemPresent ".json_encode($listItemPresent));
        //$cmdis = cmd::byEqLogicId($this->getId(), 'info');
      	$cmdis = $this->getCmd('info');
		foreach ($cmdis as $cmdi) {
          	if($cmdi->getConfiguration('type', '') != 'item') continue;
          	$cmdi_LogId = $cmdi->getLogicalId();
          	$cmdi_Name = $cmdi->getName();
          	if(!in_array($cmdi_LogId, $listItemPresent)){
              	log::add('alexatodolist', 'warning', '    '.__FUNCTION__ . " [$eqName][$cmdi_Name] itemcmd $cmdi_LogId n'est plus présent ");
              	$cmdi->remove();
            }
        }
      	
      	$this->checkAndUpdateCmd('date_maj', date("Y-m-d H:i"));
      	$this->checkAndUpdateCmd('items_list', implode(';', $items_listAr));
      
      	$cmd_deleteItem = $this->getCmd(null, 'deleteItem');
      	if(is_object($cmd_deleteItem)){
            $cmd_deleteItem->setConfiguration('listValue', implode(';', $items_listAr));
            $cmd_deleteItem->save();
        }
      
      	$cmd_completed_on = $this->getCmd(null, 'completed_on');
      	if(is_object($cmd_completed_on)){
            $cmd_completed_on->setConfiguration('listValue', implode(';', $items_Completed));
            $cmd_completed_on->save();
        }
      
      	$cmd_completed_off = $this->getCmd(null, 'completed_off');
      	if(is_object($cmd_completed_off)){
            $cmd_completed_off->setConfiguration('listValue', implode(';', $itemsUnCompleted));
            $cmd_completed_off->save();
        } 
    }

// ####################################################################################			
	public function afficheToutesCommandes($Position)
	{
		log::add('alexatodolist', 'debug', ' ' . __FUNCTION__ . " start");
		foreach ($this->getCmd('action') as $cmd) {
			log::add('alexatodolist', 'debug', $Position . '--cmd:' . $cmd->getLogicalId() . "/" . $cmd->getName());
		}
	}

// ####################################################################################			
	public static function deleteItem($listId, $itemId, $version)
	{
		//$eqName = $this->getName();
      	log::add('alexatodolist', 'debug', __CLASS__ . '::' . __FUNCTION__ . " start ($listId, $itemId, $version)");
		$eqSys = alexaapiv2::byLogicalId('AlexaSys', 'alexaapiv2');
		if (is_object($eqSys)) {
			$appId = $eqSys->getConfiguration('system_deviceSerial', null);
		}
		$result = alexa_Api::deleteItem ($listId, $itemId, $version, $appId);
      	log::add('alexatodolist', 'debug', __CLASS__ . '::' . __FUNCTION__ . " Résultat ($listId, $itemId, $version, $appId) => " . json_encode($result));
		if(isset($result['code']) && $result['code'] == 200){
          	self::scanlists($listId);
          	return true;
        }else{
          	$code = $result['code'] ?? '';
          	$errorMessage = ($result['body']['errorType'] ?? ''). ' => '.($result['body']['errorMessage'] ?? '');
          	$msg = "code($code) ";
          	$msg .= ($errorMessage !='') ? $errorMessage : ($result['body'] ?? '');
          	log::add('alexatodolist', 'warning', __CLASS__ . '::' . __FUNCTION__ . " $msg ");
			throw new Exception(__($msg, __FILE__));
          	return $msg;
        }
      	return true;
	}

// ####################################################################################			
	public static function addItem ($listId, $itemName)
	{
		log::add('alexatodolist', 'debug', __CLASS__ . '::' . __FUNCTION__ . " start ($listId, $itemName)");
		$result = alexa_Api::addItem($listId, $itemName);
    	if(isset($result['itemInfoList'][0]['itemName']) && $result['itemInfoList'][0]['itemName'] == $itemName){
          	self::scanlists($listId);
          	return $result['itemInfoList'][0]['itemId'];//true;
        }else{
          	$code = $result['code'] ?? '';
          	$errorMessage = ($result['body']['errorType'] ?? ''). ' => '.($result['body']['errorMessage'] ?? '');
          	$msg = "code($code) ";
          	$msg .= ($errorMessage !='') ? $errorMessage : ($result['body'] ?? '');
          	log::add('alexatodolist', 'debug', __CLASS__ . '::' . __FUNCTION__ . " Résultat" . json_encode($result));
			log::add('alexatodolist', 'warning', __CLASS__ . '::' . __FUNCTION__ . " $msg ");
			throw new Exception(__($msg, __FILE__));
          	return $msg;
        }
    }

// ####################################################################################			
	public static function addList($listName)
	{
		$eqSys = alexaapiv2::byLogicalId('AlexaSys', 'alexaapiv2');
		if (is_object($eqSys)) {
			$customerId = $eqSys->getConfiguration('OwnerCustomerId', null);
		}
		
      	log::add('alexatodolist', 'debug', __CLASS__ . '::' . __FUNCTION__ . " start ($listName)");
		$result = alexa_Api::addList($listName);
     	log::add('alexatodolist', 'debug', __CLASS__ . '::' . __FUNCTION__ . " Résultat => " . json_encode($result));
		if(isset($result['listInfo']['listName']) && $result['listInfo']['listName'] == $listName){
          	self::scanlists($listId);
          	return true;
        }else{
          	$code = $result['code'] ?? '';
          	$errorMessage = ($result['body']['errorType'] ?? ''). ' => '.($result['body']['errorMessage'] ?? '');
          	$msg = "code($code) ";
          	$msg .= ($errorMessage !='') ? $errorMessage : ($result['body'] ?? '');
          	log::add('alexatodolist', 'warning', __CLASS__ . '::' . __FUNCTION__ . " $msg ");
			throw new Exception(__($msg, __FILE__));
          	return $msg;
        }
    }

// ####################################################################################			
	public static function modifyItem($listId, $itemId, $idCmd, $completed, $text, $version)
	{
		log::add('alexatodolist', 'debug', __CLASS__ . '::' . __FUNCTION__ . " start ($listId, $itemId, $idCmd, $completed, $text, $version)");
		$data = array('listId' => $listId, 'itemId' => $itemId, 'completed' => $completed, 'text' => $text, 'version' => $version);
	if(isset($result['itemInfo']['listName']) && $result['itemInfo']['listName'] == $listName){
          	self::scanlists($listId);
          	return true;
        }else{
          	$code = $result['code'] ?? '';
          	$errorMessage = ($result['body']['errorType'] ?? ''). ' => '.($result['body']['errorMessage'] ?? '');
          	$msg = "code($code) ";
          	$msg .= ($errorMessage !='') ? $errorMessage : ($result['body'] ?? '');
          	log::add('alexatodolist', 'warning', __CLASS__ . '::' . __FUNCTION__ . " $msg ");
			throw new Exception(__($msg, __FILE__));
          	return $msg;
        }
    }
  
// ####################################################################################			
	public static function set_itemName ($listId, $itemId, $itemName, $version)
	{
		log::add('alexatodolist', 'debug', __CLASS__ . '::' . __FUNCTION__ . " start ($listId, $itemId, $itemName, $version)");
		$result = alexa_Api::set_itemName($listId, $itemId, $itemName, $version);
      	/*
        {
        	"id":"02c64b14-390e-401f-9046-aae3a8f4209e",
        	"listId":"e6b94ff8-cc61-4770-90cd-1534328ecea4",
        	"value":"el25",
            "encryptedValue":"AAAAAAAAAQDyx++7QVEMh\/9SGYa54Xf7IAAAAAAAAABzD1m6fBt6h6tnQt5Z\/CjUj12f6FWKsyiddJqGndIN8Q==",
            "updatedDateTime":1752327212192,
            "createdDateTime":1752310350150,
            "customerId":"A3BREYYLMVGASM",
            "version":3,
            "completed":false,
            "itemType":"KEYWORD",
            "listItemMetadata":[]
        }
        */
      	log::add('alexatodolist', 'debug', __CLASS__ . '::' . __FUNCTION__ . " Résultat" . json_encode($result));
		if(isset($result['itemInfo']['itemName']) && $result['itemInfo']['itemName'] == $itemName){
          	self::scanlists($listId);
          	return true;
        }else{
          	$code = $result['code'] ?? '';
          	$errorMessage = ($result['body']['errorType'] ?? ''). ' => '.($result['body']['errorMessage'] ?? '');
          	$msg = "code($code) ";
          	$msg .= ($errorMessage !='') ? $errorMessage : ($result['body'] ?? '');
          	log::add('alexatodolist', 'warning', __CLASS__ . '::' . __FUNCTION__ . " $msg ");
			throw new Exception(__($msg, __FILE__));
          	return $msg;
        }
		
	}
  
// ####################################################################################			
	public static function set_completed ($listId, $itemId, $itemStatus, $version)
	{
		$eqlogic = alexatodolist::byLogicalId($listId, __CLASS__);
      	log::add('alexatodolist', 'debug', __CLASS__ . '::' . __FUNCTION__ . " start ");
      	
		if(!$version){
        	$cmdItem = $eqlogic->getCmd(null, $itemId);
      		$version = $cmdItem->getConfiguration('parameters', [])['version'] ?? null;
        }
        if($itemStatus === 'true' || $itemStatus === true || $itemStatus === 1) $itemStatus = 'COMPLETE'; 
        elseif($itemStatus === 'false' || $itemStatus === false || $itemStatus === 0) $itemStatus = 'ACTIVE'; 
      
      	log::add('alexatodolist', 'debug',  __CLASS__ . '::' . __FUNCTION__ ." ($listId, $itemId, $itemStatus, $version)");
      
		$command = alexa_Api::set_itemComplete($listId, $itemId, $itemStatus, $version);
      	
        $commandStatus = $command['itemInfo']['itemStatus'] ?? null;
        if($commandStatus == $itemStatus) {
        	log::add('alexatodolist', 'debug',  __CLASS__ . '::' . __FUNCTION__ ." Success => ".json_encode($command));
        	$eqlogic->refresh($listId);
        	return true;
        }
        else log::add('alexatodolist', 'warning', __CLASS__ . '::' . __FUNCTION__ ." result => ".json_encode($command));
      
      
      
		
	}
  
// ####################################################################################			
	public function callApi($function, $data=array())
	{
		log::add('alexatodolist', 'debug', __CLASS__ . '::' . __FUNCTION__ . " start");
		$eqName = $this->getName();
      	$class = "alexa_Api";
      	if(method_exists("alexa_Api", $function)){
			
		}else if(method_exists("alexaapiv2", $function)){
			$class = "alexaapiv2";
		}else{
			echo "<br>function: $class::$function NOT exist";
          	return "function: $function NOT found in $class";
		} 
      	$return = call_user_func_array([$class, $function], $data);
      	return $return;
	}  
// ####################################################################################			
	public function postSave()
	{
		$eqName = $this->getName();
      	//log::add('alexatodolist', 'debug', ' ' . __FUNCTION__ . " [$eqName] start");
		if($this->getConfiguration('cmdsMaked', false) != true){
          	$this->makeCmds();
        }
	}
  
// ####################################################################################			
	public function makeCmds()
	{
      	$eqName = $this->getName();
      	log::add('alexatodolist', 'debug', ' ' . __FUNCTION__ . " [$eqName] start");
		$createCount = 0;
      	$updateCount = 0;
      	$cmd = $this->getCmd(null, 'refresh');
		if (!is_object($cmd)) {
			log::add('alexatodolist', 'debug',  __FUNCTION__ ." [$eqName] ajout commande Refresh");
			$cmd = new alexatodolistCmd();
			$cmd->setLogicalId('refresh');
			$cmd->setIsVisible(1);
			$cmd->setOrder("1");
			$cmd->setDisplay('icon', '<i class="fas fa-sync"></i>');
			$cmd->setName(__('Refresh', __FILE__));
			$cmd->setType('action');
			$cmd->setSubType('other');
			$cmd->setEqLogic_id($this->getId());
			$cmd->save();
          	$createCount++;
		}
      
		$cmd = $this->getCmd(null, 'items_list');
		if (!is_object($cmd)) {
			log::add('alexatodolist', 'debug',  __FUNCTION__ ." [$eqName] ajout commande itemsList");
			$cmd = new alexatodolistCmd();
			$cmd->setLogicalId('items_list');
			$cmd->setIsVisible(0);
			$cmd->setOrder("2");
			$cmd->setDisplay('icon', '<i class="fas fa-list"></i>');
			$cmd->setName(__('Liste des éléments', __FILE__));
			$cmd->setType('info');
			$cmd->setSubType('string');
			$cmd->setEqLogic_id($this->getId());
			$cmd->setTemplate('dashboard', "customtemp::iso_liste");
        	$cmd->save();
		  	$createCount++;
		}else $updateCount++;
      
		$cmd = $this->getCmd(null, 'date_maj');
		if (!is_object($cmd)) {
			log::add('alexatodolist', 'debug',  __FUNCTION__ ." [$eqName] ajout commande date_maj");
			$cmd = new alexatodolistCmd();
			$cmd->setLogicalId('date_maj');
			$cmd->setIsVisible(1);
			$cmd->setOrder("3");
			$cmd->setDisplay('icon', '<i class="fas fa-clock"></i>');
			$cmd->setName(__('Date mise à jour', __FILE__));
			$cmd->setType('info');
			$cmd->setSubType('string');
			$cmd->setEqLogic_id($this->getId());
			$cmd->setTemplate('dashboard', "customtemp::iso_line");
        	$cmd->save();
		  	$createCount++;
		}else $updateCount++;
      
      	   	
		$cmd = $this->getCmd(null, 'addItem');
		if (!is_object($cmd)) {
			log::add('alexatodolist', 'debug',  __FUNCTION__ ." [$eqName] ajout commande addItem");
			$cmd = new alexatodolistCmd();
			$cmd->setLogicalId('addItem');
			$cmd->setIsVisible(1);
			$cmd->setOrder("4");
			$cmd->setName(__('AddItem', __FILE__));
			$cmd->setType('action');
			$cmd->setSubType('message');
          	$cmd->setEqLogic_id($this->getId());
			$cmd->setConfiguration('listValue', '');
          	$cmd->setConfiguration('request', 'addItem?itemName=#title#');
			$cmd->setDisplay('message_disable', 1);
           	$cmd->setDisplay('title_placeholder', "Nom de l'élément");
           	$cmd->setTemplate('dashboard', "customtemp::iso_message");
        	$cmd->save();
		}else $updateCount++;
      
		$cmd = $this->getCmd(null, 'deleteItem');
		if (!is_object($cmd)) {
			log::add('alexatodolist', 'debug',  __FUNCTION__ ." [$eqName] ajout commande deleteItem");
			$cmd = new alexatodolistCmd();
			$cmd->setLogicalId('deleteItem');
			$cmd->setIsVisible(1);
			$cmd->setOrder("5");
			$cmd->setName(__('deleteItem', __FILE__));
			$cmd->setType('action');
			$cmd->setSubType('select');
			$cmd->setConfiguration('request', 'deleteItem?itemId=#itemId#');
			$cmd->setEqLogic_id($this->getId());
          	$cmd->setConfiguration('listValue', '');
          	$cmd->setTemplate('dashboard', "customtemp::iso_select");
        	$cmd->save();
		  	$createCount++;
		}else $updateCount++;
      	
        $cmd = $this->getCmd(null, 'updateItem');
		if (!is_object($cmd)) {
			log::add('alexatodolist', 'debug',  __FUNCTION__ ." [$eqName] ajout commande updateItem");
			$cmd = new alexatodolistCmd();
			$cmd->setLogicalId('updateItem');
			$cmd->setIsVisible(0);
			$cmd->setOrder("6");
			$cmd->setName(__('updateItem', __FILE__));
			$cmd->setType('action');
			$cmd->setSubType('other');
			$cmd->setConfiguration('request', 'updateItem?itemId=#itemId#&completed=#completed#&text=#text#');
			$cmd->setEqLogic_id($this->getId());
			$cmd->setTemplate('dashboard', "customtemp::iso_btn");
        	$cmd->save();
		  	$createCount++;
		}else $updateCount++;
      
		$cmd = $this->getCmd(null, 'completed_on');
		if (!is_object($cmd)) {
			log::add('alexatodolist', 'debug',  __FUNCTION__ ." [$eqName] ajout commande completed_on");
			$cmd = new alexatodolistCmd();
			$cmd->setLogicalId('completed_on');
			$cmd->setIsVisible(1);
			$cmd->setOrder("7");
			$cmd->setName(__('Elément accompli', __FILE__));
			$cmd->setType('action');
			$cmd->setSubType('select');
			$cmd->setConfiguration('request', 'updateItem?itemId=#select#&completed=true');
			$cmd->setEqLogic_id($this->getId());
          	$cmd->setConfiguration('listValue', '');
          	$cmd->setTemplate('dashboard', "customtemp::iso_switch");
        	$cmd->save();
		  	$createCount++;
		}else $updateCount++;
      
      	$cmd = $this->getCmd(null, 'completed_off');
		if (!is_object($cmd)) {
			log::add('alexatodolist', 'debug',  __FUNCTION__ ." [$eqName] ajout commande completed_off");
			$cmd = new alexatodolistCmd();
			$cmd->setLogicalId('completed_off');
			$cmd->setIsVisible(1);
			$cmd->setOrder("8");
			$cmd->setName(__('Elément Non accompli', __FILE__));
			$cmd->setType('action');
			$cmd->setSubType('select');
			$cmd->setConfiguration('request', 'modifyItem?itemId=#select#&completed=false');
			$cmd->setEqLogic_id($this->getId());
          	$cmd->setConfiguration('listValue', '');
          	$cmd->setTemplate('dashboard', "customtemp::iso_switch");
        	$cmd->save();
		  	$createCount++;
		}else $updateCount++;
      
      	$cmd = $this->getCmd(null, 'setArchived');
		if (!is_object($cmd)) {
			log::add('alexatodolist', 'debug',  __FUNCTION__ ." [$eqName] ajout commande updateItem");
			$cmd = new alexatodolistCmd();
			$cmd->setLogicalId('setArchived');
			$cmd->setIsVisible(1);
			$cmd->setOrder("9");
			$cmd->setName(__('Archiver', __FILE__));
			$cmd->setType('action');
			$cmd->setSubType('other');
			$cmd->setConfiguration('request', 'setArchived?itemId=#itemId#&completed=#completed#&text=#text#');
			$cmd->setEqLogic_id($this->getId());
			$cmd->setTemplate('dashboard', "customtemp::iso_btn");
        	$cmd->save();
		  	$createCount++;
		}else $updateCount++;
      
      	$this->setStatus('forceUpdate', false); //dans tous les cas, on repasse forceUpdate à false
		if($updateCount>0 || $createCount>0){
          	$this->setConfiguration('cmdsMaked', true);
        	$this->save();
      	}
      	return ($createCount + $updateCount);
    }

// ####################################################################################			
	public function toHtml($_version = 'dashboard')
	{
		$replace = $this->preToHtml($_version);
		//log::add('alexatodolist_widget','debug','************Début génération Widget de '.$replace['#logicalId#']);
		$typeWidget = "alexatodolist";

		$typeWidget = $this->getLogicalId();
		if (!is_array($replace)) {
			return $replace;
		}
		$version = jeedom::versionAlias($_version);
		if ($this->getDisplay('hideOn' . $version) == 1) {
			return '';
		}

		$replace['#Nom#'] = __('Nom', __FILE__);
		$replace['#Editer#'] = __('Editer', __FILE__);
		$replace['#Ajouté le#'] = __('Ajouté le', __FILE__);
		$replace['#Modifié le#'] = __('Modifié le', __FILE__);
		$replace['#Par#'] = __('Par', __FILE__);
		$replace['#Statut#'] = __('Statut', __FILE__);
		$replace["#Ajouter l'élément#"] = __("Ajouter l'élément", __FILE__);
		$replace['#Renseignez un nom...#'] = __('Renseignez un nom...', __FILE__);
		$replace["#Le nom de l'élément ne peut pas être vide...#"] = __("Le nom de l'élément ne peut pas être vide...", __FILE__);
		$replace["#Modifier l'élément#"] = __("Modifier l'élément", __FILE__);
		$replace['#Nouveau nom#'] = __('Nouveau nom', __FILE__);
		$replace["#Êtes-vous sûr de vouloir supprimer l'élément ?#"] = __("Êtes-vous sûr de vouloir supprimer l'élément ?", __FILE__);
		$replace['#Action#'] = __('Action', __FILE__);
		$dataTable = '';
		//$listId=$this->getId();
		$listItems = eqLogic::byTypeAndSearchConfiguration('alexatodolist', array("eqlogicList" => $this->getId()));
		foreach ($listItems as $item) {
			$dataTable .= "<tr>";
			$dataTable .= '<td id="name' . $item->getId() . '">' . $item->getName() . '</td>';
			$dataTable .= '<td><a class="btn btn-default btn-sm cmdAction" onclick="modifierItem(this)"' . " title=\"" . __("Modifier le nom de l'élément", __FILE__) . "\"" . ' data-itemId="' . $item->getConfiguration('id') . '" data-idEqlogic="' . $item->getId() . '" data-listId="' . $this->getConfiguration('itemId') . '" data-version="' . $item->getConfiguration('version') . '"><i class="fas fa-cog"></i></a></td>';
			$dataTable .= "<td>" . date('H\hi \l\e d/m/Y', ($item->getConfiguration('createdDateTime') / 1000)) . "</td>";
			$dataTable .= "<td>" . date('H\hi \l\e d/m/Y', ($item->getConfiguration('updatedDateTime') / 1000)) . "</td>";
			$dataTable .= "<td>" . $item->getConfiguration('customerId') . "</td>";
			$dataTable .= '<td><input id="completed' . $item->getId() . '" onclick="modifierItemCompleted(this)" type="checkbox" class="" data-l1key="configuration" data-l2key="completed" data-itemId="' . $item->getConfiguration('id') . '" data-idEqlogic="' . $item->getId() . '" data-listId="' . $this->getConfiguration('itemId') . '" data-version="' . $item->getConfiguration('version') . '" ' . ($item->getConfiguration('completed') == '1' ? 'checked="checked"' : '') . '></td>';
			$dataTable .= "<td>";
			$dataTable .= '<a class="btn btn-danger btn-sm cmdAction" onclick="supprimerItem(this)" data-itemId="' . $item->getConfiguration('id') . '" data-idEqlogic="' . $item->getId() . '" data-listId="' . $this->getConfiguration('itemId') . '" data-version="' . $item->getConfiguration('version') . '"><i class="fas fa-minus-circle"></i> ' . __("Supprimer", __FILE__) . '</a>';
			$dataTable .= "</td>";
			$dataTable .= "</tr>";
		}

		$replace['#dataTable#'] = $dataTable;
		foreach ($this->getCmd('info') as $cmd) {
			//log::add('alexatodolist_widget','debug',  __FUNCTION__ ." $typeWidget dans boucle génération Widget");
			$replace['#' . $cmd->getLogicalId() . '_history#'] = '';
			$replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
			$replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
			$replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
			if ($cmd->getLogicalId() == 'encours') {
				$replace['#thumbnail#'] = $cmd->getDisplay('icon');
			}
			if ($cmd->getIsHistorized() == 1) {
				$replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
			}
		}
		$replace['#height#'] = '800';
		//log::add('alexatodolist_widget','debug',$typeWidget.'*******'.$version.'********************************************************************Fin génération Widget');
		return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, $typeWidget, 'alexatodolist')));
	}
}

class alexatodolistCmd extends cmd
{
	public function execute($_options = array())
	{
		$eqLogic = $this->getEqLogic();
      	$eqLogicId = $eqLogic->getLogicalId();
      	$eqName = $eqLogic->getName();
        $listId = $eqLogic->getConfiguration('listId');
      	$cmdLogId = $this->getLogicalId();

		log::add('alexatodolist', 'debug', ' ' . __FUNCTION__ . " [$eqName] $cmdLogId start ");
		switch ($cmdLogId) {
			case 'refresh':
				$eqLogic->refresh($eqLogicId);
				break;
			case 'addItem':
            	$itemName = $_options['title'];
            	log::add('alexatodolist', 'debug',  __FUNCTION__ ." $cmdLogId -> ($listId, $itemName)");
				//$command = $eqLogic->callApi('addItem', array($listId, $itemName));
            	$command = alexatodolist::addItem($listId, $_options['title']);
				log::add('alexatodolist', 'debug',  __FUNCTION__ ." $cmdLogId  result => ".json_encode($command));
           	break;
			case 'updateItem':
            	log::add('alexatodolist', 'debug',  __FUNCTION__ ." $cmdLogId -> ($listId, $itemName)");
				$command = $eqLogic->callApi('set_itemName', array($listId, $itemId, $itemName));
				log::add('alexatodolist', 'debug',  __FUNCTION__ ." $cmdLogId  result => ".json_encode($command));
				break;
			case 'deleteItem':
            	$itemId = $_options['select'];
            	$cmdItem = $eqLogic->getCmd(null, $itemId);
            	$version = $cmdItem->getConfiguration('parameters', [])['version'] ?? null;
            	log::add('alexatodolist', 'debug',  __FUNCTION__ ." $cmdLogId -> ($listId, $itemId, $version)");
				$command = alexatodolist::deleteItem($listId, $itemId, $version);
				log::add('alexatodolist', 'debug',  __FUNCTION__ ." $cmdLogId  result => ".json_encode($command));
            
            	break;
			case 'completed_off':
            	$itemId = $_options['select'];
            	$command = alexatodolist::set_completed_off($listId, $_options['select'], 'ACTIVE');
            	break;
			case 'completed_on':
				$itemId = $_options['select'];
            	log::add('alexatodolist', 'debug',  __FUNCTION__ ." $cmdLogId -> ($listId, $itemsId, 'COMPLETE')");
				$cmdItem = $eqLogic->getCmd(null, $itemId);
            	$version = $cmdItem->getConfiguration('parameters', [])['version'] ?? null;
            	$command = $eqLogic->callApi('set_itemComplete', array($listId, $itemId, 'COMPLETE', $version));
            	$commandStatus = $command['itemInfo']['itemStatus'] ?? null;
            	if($commandStatus=="COMPLETE") {
					log::add('alexatodolist', 'debug',  __FUNCTION__ ." $cmdLogId Success => ".json_encode($command));
                  	$eqLogic->refresh($listId);
                  	return true;
                }else log::add('alexatodolist', 'warning',  __FUNCTION__ ." $cmdLogId  result => ".json_encode($command));
				break;
			case 'setArchived':
            	log::add('alexatodolist', 'debug',  __FUNCTION__ ." [$eqName] $cmdLogId => ($listId, 'ARCHIVED')");
				$command = $eqLogic->callApi('set_listArchived', array($listId, 'ARCHIVED'));
				$commandStatus = $command['listInfo']['listStatus'] ?? null;
            	if($commandStatus=="ARCHIVED") {
					log::add('alexatodolist', 'debug',  __FUNCTION__ ." $cmdLogId Success => ".json_encode($command));
                  	$eqLogic->refresh($listId);
                  	return true;
                }else log::add('alexatodolist', 'warning',  __FUNCTION__ ." $cmdLogId  result => ".json_encode($command));
				
                  
                  log::add('alexatodolist', 'debug',  __FUNCTION__ ." $cmdLogId  result => ".json_encode($command));
				break;
        	default:
				log::add('alexatodolist', 'error', ' ' . __FUNCTION__ . " [$eqName] Commande inconnue '$cmdLogId' ");
				break;
		}
		return true;
	}

	public function dontRemoveCmd()
	{
		if ($this->getLogicalId() == 'refresh') {
			return true;
		}
		return false;
	}

	public function postSave()
	{
	}

	public function preSave()
	{
		if ($this->getLogicalId() == 'refresh') {
			return;
		}
	}

	private function buildRequest($_options = array())
	{
		log::add('alexatodolist', 'debug', __CLASS__ . '::' . __FUNCTION__ . " start " . json_encode($_options));
		if ($this->getType() != 'action') return escapeshellcmd($this->getConfiguration('request'));
		$cmdANDarg = explode('?', escapeshellcmd($this->getConfiguration('request')), 2);
		if (count($cmdANDarg) > 1) {
			list($command, $arguments) = $cmdANDarg;
		} else {
			$command = escapeshellcmd($this->getConfiguration('request'));
			$arguments = "";
		}

		return true;
	}

}