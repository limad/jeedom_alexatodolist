<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class alexatodolist extends eqLogic
{

	// ####################################################################################			
	// ####################################################################################			
	public static $_widgetPossibility = array('custom' => true, 'custom::layout' => true);

	// ####################################################################################			
	public static function cron($_eqlogic_id = null)
	{
		//log::add('alexatodolist', 'debug', '---------------------------------------------CRON------------------------');
	} 
*/

	// ####################################################################################			
	public static function modifyItem($idList, $idItem, $idCmd, $completed, $text, $version)
	{
		$log::add('alexatodolist', 'debug', __CLASS__ . '::' . __FUNCTION__ . " start ($idList, $idItem, $idCmd, $completed, $text, $version)");
		data = array('idList' => $idList, 'idItem' => $idItem, 'completed' => $completed, 'text' => $text, 'version' => $version);
		$result = self::rqstDaemon("modifyItem?" . http_build_query($data));
		log::add('alexatodolist', 'debug', 'Résultat modifyItem (' . json_encode($result) . ')');
		return true;
	}

	// ####################################################################################			
	public static function modifyItemCompleted($idList, $idItem, $idCmd, $completed, $text, $version)
	{
		log::add('alexatodolist', 'debug', __CLASS__ . '::' . __FUNCTION__ . " start ($idList, $idItem, $idCmd, $completed, $text, $version)");
		$data = array('idList' => $idList, 'idItem' => $idItem, 'completed' => $completed, 'text' => $text, 'version' => $version);
		$result = self::rqstDaemon("modifyItem?" . http_build_query($data));
		log::add('alexatodolist', 'debug', 'Résultat modifyItemCompleted (' . json_encode($result) . ')');
		return true;
	}

	// ####################################################################################			
	public static function deleteItem($idList, $idItem, $idCmd)
	{
		log::add('alexatodolist', 'debug', __CLASS__ . '::' . __FUNCTION__ . " start ($idList, $idItem, $idCmd)");
		$data = array('idList' => $idList, 'idItem' => $idItem);
		$result = self::rqstDaemon("deleteItem?" . http_build_query($data));
		log::add('alexatodolist', 'debug', 'Résultat deleteItem (' . json_encode($result) . ')');
		cmd::byId($idCmd)->remove();
		return true;
	}

	// ####################################################################################			
	public static function addItem($idList, $text)
	{
		log::add('alexatodolist', 'debug', __CLASS__ . '::' . __FUNCTION__ . " start ($idList, $text)");
		$data = array('idList' => $idList, 'text' => $text);
		$result = self::rqstDaemon("addItem?" . http_build_query($data));
		log::add('alexatodolist', 'debug', 'Résultat addItem (' . json_encode($result) . ')');
		return true;
	}

	// ####################################################################################			
	public static function addList($text)
	{
		log::add('alexatodolist', 'debug', __CLASS__ . '::' . __FUNCTION__ . " start ($text)");
		$data = array('nameList' => $text);
		$result = self::rqstDaemon("addList?" . http_build_query($data));
		log::add('alexatodolist', 'debug', 'Résultat addList (' . json_encode($result) . ')');
		return true;
	}

	// ####################################################################################			
	public function updateCmd($forceUpdate, $LogicalId, $Type, $SubType, $RunWhenRefresh, $Name, $IsVisible, $title_disable, $setDisplayicon, $infoNameArray, $setTemplate_lien, $request, $infoName, $listValue, $Order, $Test)
	{
		log::add('alexatodolist', 'debug', __CLASS__ . '::' . __FUNCTION__ . " start ");

		//self::afficheToutesCommandes("updateCmd");
		if ($Test) {
			//log::add('alexatodolist', 'info', 'ajout commande FORCAGE de ' . $LogicalId);
			try {
				$cmd = $this->getCmd(null, $LogicalId);
				if ((!is_object($cmd)) || $forceUpdate) {

					//log::add('alexatodolist', 'info', 'ajout commande FORCAGE forceUpdate :' . $forceUpdate);
					if (!is_object($cmd)) $cmd = new alexatodolistCmd();
					$cmd->setType($Type);
					$cmd->setLogicalId($LogicalId);
					$cmd->setSubType($SubType);
					$cmd->setEqLogic_id($this->getId());
					if (empty($Name)) $Name = $LogicalId; // déplacé le 19/09/2020
					$cmd->setName($Name);
					$cmd->setIsVisible((($IsVisible) ? 1 : 0));
					if (!empty($setTemplate_lien)) {
						$cmd->setTemplate("dashboard", $setTemplate_lien);
						$cmd->setTemplate("mobile", $setTemplate_lien);
					}
					if (!empty($setDisplayicon)) $cmd->setDisplay('icon', '<i class="' . $setDisplayicon . '"></i>');
					if (!empty($request)) $cmd->setConfiguration('request', $request);
					if (!empty($infoName)) $cmd->setConfiguration('infoName', $infoName);
					if (!empty($infoNameArray)) $cmd->setConfiguration('infoNameArray', $infoNameArray);
					if (!empty($listValue)) $cmd->setConfiguration('listValue', $listValue);
					$cmd->setConfiguration('RunWhenRefresh', $RunWhenRefresh);
					$cmd->setDisplay('title_disable', $title_disable);
					$cmd->setDisplay('showNameOndashboard', !$title_disable);
					$cmd->setOrder($Order);
					//cas particulier
					if (($LogicalId == 'speak') || ($LogicalId == 'announcement')) {
						//$cmd->setDisplay('title_placeholder', 'Options');
						$cmd->setDisplay('message_placeholder', 'Phrase à faire lire par Alexa');
					}
					if (($LogicalId == 'reminder')) {
						//$cmd->setDisplay('title_placeholder', 'Options');
						$cmd->setDisplay('message_placeholder', 'Texte du rappel');
					}
					if (($LogicalId == 'volumeinfo') || ($LogicalId == 'volume')) {
						$cmd->setConfiguration('minValue', '0');
						$cmd->setConfiguration('maxValue', '100');
						$cmd->setDisplay('forceReturnLineBefore', true);
					}
					$cmd->save(); // déplacé le 19/09/2020
					//log::add('alexatodolist', 'info', 'Enregistre Logical ID :' . $cmd->getLogicalId());
				}
			} catch (Exception $exc) {
				log::add('alexatodolist', 'error', __('Erreur pour ', __FILE__) . ' : ' . $exc->getMessage());
			}
		} else {
			//log::add('alexatodolist', 'debug', 'PAS de **'.$LogicalId.'*********************************');

			$cmd = $this->getCmd(null, $LogicalId);
			if (is_object($cmd)) {
				$cmd->remove();
			}
		}
	}

	// ####################################################################################			
	public function afficheToutesCommandes($Position)
	{
		log::add('alexatodolist', 'info', ' ' . __FUNCTION__ . " start");
		foreach ($this->getCmd('action') as $cmd) {
			log::add('alexatodolist', 'info', $Position . '--cmd:' . $cmd->getLogicalId() . "/" . $cmd->getName());
		}
	}

	// ####################################################################################			
	public function postSave()
	{
		//self::afficheToutesCommandes("postSave");

		log::add('alexatodolist', 'info', ' ' . __FUNCTION__ . " start");
		$F = $this->getStatus('forceUpdate'); // forceUpdate permet de recharger les commandes à valeur d'origine, mais sans supprimer/recréer les commandes
		$capa = $this->getConfiguration('capabilities', '');
		$type = $this->getConfiguration('type', '');
		$cmd = $this->getCmd(null, 'refresh');
		if (!is_object($cmd)) {
			log::add('alexatodolist', 'info', 'ajout commande Refresh');
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
		}
		$cmd_maj = $this->getCmd(null, 'date_maj');
		if (!is_object($cmd_maj)) {
			log::add('alexatodolist', 'info', 'ajout commande date_maj');
			$cmd = new alexatodolistCmd();
			$cmd->setLogicalId('date_maj');
			$cmd->setIsVisible(1);
			$cmd->setOrder("2");
			$cmd->setDisplay('icon', '<i class="fas fa-clock"></i>');
			$cmd->setName(__('Date mise à jour', __FILE__));
			$cmd->setType('info');
			$cmd->setSubType('string');
			$cmd->setEqLogic_id($this->getId());
			$cmd->save();
		}
		$cmd_maj = $this->getCmd(null, 'addItem');
		if (!is_object($cmd_maj)) {
			log::add('alexatodolist', 'action', 'ajout commande addItem');
			$cmd = new alexatodolistCmd();
			$cmd->setLogicalId('addItem');
			$cmd->setIsVisible(1);
			$cmd->setOrder("3");
			$cmd->setName(__('AddItem', __FILE__));
			$cmd->setType('action');
			$cmd->setSubType('other');
			$cmd->setConfiguration('request', '/addItem?text=#text#');
			$cmd->setEqLogic_id($this->getId());
			$cmd->save();
		}
		$cmd_maj = $this->getCmd(null, 'modifyItem');
		if (!is_object($cmd_maj)) {
			log::add('alexatodolist', 'action', 'ajout commande modifyItem');
			$cmd = new alexatodolistCmd();
			$cmd->setLogicalId('modifyItem');
			$cmd->setIsVisible(1);
			$cmd->setOrder("4");
			$cmd->setName(__('modifyItem', __FILE__));
			$cmd->setType('action');
			$cmd->setSubType('other');
			$cmd->setConfiguration('request', '/modifyItem?idItem=#idItem#&completed=#completed#&text=#text#');
			$cmd->setEqLogic_id($this->getId());
			$cmd->save();
		}
		$cmd_maj = $this->getCmd(null, 'deleteItem');
		if (!is_object($cmd_maj)) {
			log::add('alexatodolist', 'action', 'ajout commande deleteItem');
			$cmd = new alexatodolistCmd();
			$cmd->setLogicalId('deleteItem');
			$cmd->setIsVisible(1);
			$cmd->setOrder("5");
			$cmd->setName(__('deleteItem', __FILE__));
			$cmd->setType('action');
			$cmd->setSubType('other');
			$cmd->setConfiguration('request', '/deleteItem?idItem=#idItem#');
			$cmd->setEqLogic_id($this->getId());
			$cmd->save();
		}

		$this->setStatus('forceUpdate', false); //dans tous les cas, on repasse forceUpdate à false

	}

	// ####################################################################################			
	public function preUpdate()
	{
		//log::add('alexatodolist', 'debug', '-------------------------------preUpdate '.$this->getName().'***********************************');
		//self::afficheToutesCommandes("preUpdate");

	}

	// ####################################################################################			
	public function preRemove()
	{
		if ($this->getConfiguration('devicetype') == "Player") { // Si c'est un type Player, il faut supprimer le Device Playlist
			$device_playlist = str_replace("_player", "", $this->getConfiguration('serial')) . "_playlist"; //Nom du device de la playlist
			$eq = eqLogic::byLogicalId($device_playlist, 'alexatodolist');
			if (is_object($eq)) $eq->remove();
		}
		//log::add('alexatodolist', 'debug', '-------------------------------preRemove '.$this->getName().'***********************************');
	}

	// ####################################################################################			
	public function preSave()
	{
		//log::add('alexatodolist', 'debug', '-------------------------------preSave '.$this->getName().'***********************************');
		//self::afficheToutesCommandes("preSave");
	}

	// https://github.com/NextDom/NextDom/wiki/Ajout-d%27un-template-a-votre-plugin
	// https://jeedom.github.io/documentation/dev/fr_FR/widget_plugin

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
		//$idList=$this->getId();
		$listItems = eqLogic::byTypeAndSearchConfiguration('alexatodolist', array("eqlogicList" => $this->getId()));
		foreach ($listItems as $item) {
			$dataTable .= "<tr>";
			$dataTable .= '<td id="name' . $item->getId() . '">' . $item->getName() . '</td>';
			$dataTable .= '<td><a class="btn btn-default btn-sm cmdAction" onclick="modifierItem(this)"' . " title=\"" . __("Modifier le nom de l'élément", __FILE__) . "\"" . ' data-idItem="' . $item->getConfiguration('id') . '" data-idEqlogic="' . $item->getId() . '" data-idList="' . $this->getConfiguration('itemId') . '" data-version="' . $item->getConfiguration('version') . '"><i class="fas fa-cog"></i></a></td>';
			$dataTable .= "<td>" . date('H\hi \l\e d/m/Y', ($item->getConfiguration('createdDateTime') / 1000)) . "</td>";
			$dataTable .= "<td>" . date('H\hi \l\e d/m/Y', ($item->getConfiguration('updatedDateTime') / 1000)) . "</td>";
			$dataTable .= "<td>" . $item->getConfiguration('customerId') . "</td>";
			$dataTable .= '<td><input id="completed' . $item->getId() . '" onclick="modifierItemCompleted(this)" type="checkbox" class="" data-l1key="configuration" data-l2key="completed" data-idItem="' . $item->getConfiguration('id') . '" data-idEqlogic="' . $item->getId() . '" data-idList="' . $this->getConfiguration('itemId') . '" data-version="' . $item->getConfiguration('version') . '" ' . ($item->getConfiguration('completed') == '1' ? 'checked="checked"' : '') . '></td>';
			$dataTable .= "<td>";
			$dataTable .= '<a class="btn btn-danger btn-sm cmdAction" onclick="supprimerItem(this)" data-idItem="' . $item->getConfiguration('id') . '" data-idEqlogic="' . $item->getId() . '" data-idList="' . $this->getConfiguration('itemId') . '" data-version="' . $item->getConfiguration('version') . '"><i class="fas fa-minus-circle"></i> ' . __("Supprimer", __FILE__) . '</a>';
			$dataTable .= "</td>";
			$dataTable .= "</tr>";
		}

		$replace['#dataTable#'] = $dataTable;
		foreach ($this->getCmd('info') as $cmd) {
			//log::add('alexatodolist_widget','debug',$typeWidget.'dans boucle génération Widget');
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
		/*if ($this->getLogicalId() == 'refresh') {
			$result = file_get_contents("http://" . config::byKey('internalAddr') . ":3466/getListItems?idList=".$this->getEqLogic()->getConfiguration('itemId'));
			log::add('alexatodolist', 'debug', 'Lancement requete maj de la liste (cmd refresh) "'.$this->getEqLogic()->getName().'" (requete: ' . "http://" . config::byKey('internalAddr') . ":3466/getListItems?idList=".$this->getEqLogic()->getConfiguration('itemId') . ')');
			$this->getEqLogic()->refresh();
			return true;
		}*/
		log::add('alexatodolist', 'info', ' ' . __FUNCTION__ . " start");

		$listId = $this->getEqLogic()->getConfiguration('itemId', '');
		switch ($this->getLogicalId()) {
			case 'refresh':
				$result = file_get_contents("http://" . config::byKey('internalAddr') . ":3466/getListItems?idList=" . $listId);
				log::add('alexatodolist', 'debug', 'Mise à jour de la liste "' . $this->getEqLogic()->getName());
				$this->getEqLogic()->refresh();
				break;
			case 'addItem':
				//$result = file_get_contents("http://" . config::byKey('internalAddr') . ":3466/addItem?idList=".$listId);
				//log::add('alexatodolist', 'debug', 'Ajout élément "'.urlencode().'" dans la liste "'.$this->getEqLogic()->getName());
				log::add('alexatodolist', 'debug', 'Ajout élément "" dans la liste "' . $this->getEqLogic()->getName());
				log::add('alexatodolist', 'debug', '--->' . print_r($_options, true));

				break;
			case 'modifyItem':
				break;
			case 'deleteItem':
				break;
		}
		return true;
	}

	public function dontRemoveCmd()
	{
		//		log::add('alexatodolist', 'debug', 'LANCE dontRemoveCmd cmd');

		if ($this->getLogicalId() == 'refresh') {
			return true;
		}
		return false;
	}

	public function postSave()
	{
		//log::add('alexatodolist', 'debug', '**********************postSave '.$this->getName().'***********************************'.$this->getLogicalId());

	}

	public function preSave()
	{
		//   log::add('alexatodolist', 'debug', '**********************preSave '.$this->getName().'***********************************'.$this->getLogicalId());

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

		//$_resultat=$this->lanceCommande("test",$command);


		/*
		
		switch ($command) {
			case 'volume':
				$request = $this->build_ControledeSliderSelectMessage($_options, '50');
				break;
			case 'playlist':
			case 'routine':
				$request = $this->build_ControledeSliderSelectMessage($_options, "");
				break;
			case 'playmusictrack':
				$request = $this->build_ControledeSliderSelectMessage($_options, "53bfa26d-f24c-4b13-97a8-8c3debdf06f0");
				break;
			case 'speak':
			case 'announcement':
			case 'push':
			case 'multiplenext':
				$request = $this->build_ControledeSliderSelectMessage($_options);
				break;
			case 'reminder':
			case 'alarm':
				$now = date("Y-m-d H:i:s", strtotime('+3 second'));
				$request = $this->build_ControleWhenTextRecurring($now, "Ceci est un essai", $_options);
				break;
			case 'radio':
				$request = $this->build_ControledeSliderSelectMessage($_options, 's2960');
				break;
			case 'SmarthomeCommand':
				$request = $this->build_ControledeSliderSelectMessage();
				break;
			case 'command':
				$request = $this->build_ControledeSliderSelectMessage($_options, 'pause');
				break;
			case 'whennextalarm':
			case 'whennextmusicalalarm':
			case 'musicalalarmmusicentity':
			case 'whennextreminderlabel':
			case 'whennextreminder':
				$request = $this->build_ControlePosition($_options);
				break;
			case 'updateallalarms':
				$request = $this->build_ControleRien($_options);
				break;
			case 'deleteallalarms':
				$request = $this->buildDeleteAllAlarmsRequest($_options);
				break;
			case 'deleteReminder':
				$request = $this->buildDeleteReminderRequest($_options);
				break;
			case 'restart':
				$request = $this->buildRestartRequest($_options);
				break;
			default:
				$request = '';
				break;
		}
		//log::add('alexatodolist_debug', 'debug', '----RequestFinale:'.$request);
		$request = scenarioExpression::setTags($request);
		if (trim($request) == '') throw new Exception(__('Commande inconnue ou requête vide : ', __FILE__) . print_r($this, true));
		$device = str_replace("_player", "", $this->getEqLogic()->getConfiguration('serial'));
		return 'http://' . config::byKey('internalAddr') . ':3466/' . $request . '&device=' . $device;
		*/
		return true;
	}


	private function build_ControledeSliderSelectMessage($_options = array(), $default = "Ceci est un message de test")
	{
		log::add('alexatodolist', 'debug', __CLASS__ . '::' . __FUNCTION__ . " start " . json_encode($_options));
		$cmd = $this->getEqLogic()->getCmd(null, 'volumeinfo');
		if (is_object($cmd))
			$lastvolume = $cmd->execCmd();

		$request = escapeshellcmd($this->getConfiguration('request'));
		//log::add('alexatodolist_node', 'info', '---->Request2:'.$request);
		//log::add('alexatodolist_node', 'debug', '---->getName:'.$this->getEqLogic()->getCmd(null, 'volumeinfo')->execCmd());
		if ((isset($_options['slider'])) && ($_options['slider'] == "")) $_options['slider'] = $default;
		if ((isset($_options['select'])) && ($_options['select'] == "")) $_options['select'] = $default;
		if ((isset($_options['message'])) && ($_options['message'] == "")) $_options['message'] = $default;
		if (!(isset($_options['slider']))) $_options['slider'] = "";
		if (!(isset($_options['select']))) $_options['select'] = "";
		if (!(isset($_options['message']))) $_options['message'] = "";
		if (!(isset($_options['volume']))) $_options['volume'] = "";
		//log::add('alexatodolist_node', 'info', 'xxxxxxxxxxxxx---->_options:'.json_encode($_options));
		// Si on est sur une commande qui utilise volume, on va remettre après execution le volume courant
		if (strstr($request, '&volume=')) $request = $request . '&lastvolume=' . $lastvolume;
		$request = str_replace(
			array('#slider#', '#select#', '#message#', '#volume#'),
			array($_options['slider'], $_options['select'], urlencode(self::decodeTexteAleatoire($_options['message'])), $_options['volume']),
			$request
		);
		//log::add('alexatodolist_node', 'info', '---->RequestFinale:'.$request);
		return $request;
	}

	//private function trouveVolumeDevice() {
	//	$logical_id = $this->getEqLogic()->getCmd(null, 'volumeinfo')->getValue();
	//	$alexatodolist=alexatodolist::byLogicalId($logical_id, 'alexatodolist');getValue
	//}


	public static function decodeTexteAleatoire($_text)
	{
		$return = $_text;
		if (strpos($_text, '|') !== false && strpos($_text, '[') !== false && strpos($_text, ']') !== false) {
			$replies = interactDef::generateTextVariant($_text);
			$random = rand(0, count($replies) - 1);
			$return = $replies[$random];
		}
		preg_match_all('/{\((.*?)\) \?(.*?):(.*?)}/', $return, $matches, PREG_SET_ORDER, 0);
		$replace = array();
		if (is_array($matches) && count($matches) > 0) {
			foreach ($matches as $match) {
				if (count($match) != 4) {
					continue;
				}
				$replace[$match[0]] = (jeedom::evaluateExpression($match[1])) ? trim($match[2]) : trim($match[3]);
			}
		}
		return str_replace(array_keys($replace), $replace, $return);
	}


	private function build_ControleWhenTextRecurring($defaultWhen, $defaultText, $_options = array())
	{
		$request = escapeshellcmd($this->getConfiguration('request'));
		//log::add('alexatodolist', 'debug', '----build_ControledeSliderSelectMessage RequestFinale:'.$request);
		//log::add('alexatodolist', 'debug', '----build_ControledeSliderSelectMessage _optionsAVANT:'.json_encode($_options));
		if ((!isset($_options['sound'])) && (!isset($_options['message'])) && (!isset($_options['when']))) {
			if (isset($_options['select'])) { // On est dans le cas d'un son d'alarme envoyé depuis le widget
				$_options['sound'] = urlencode($_options['select']);
				$_options['select'] = "";
			}
		}
		if ($_options['when'] == "") $_options['when'] = $defaultWhen;
		if ($_options['message'] == "") $_options['message'] = $defaultText;
		if ($_options['sound'] == "") $_options['sound'] = 'system_alerts_melodic_01';
		$request = str_replace(array('#when#', '#message#', '#recurring#', '#sound#'), array(urlencode($_options['when']), urlencode($_options['message']), urlencode($_options['select']), $_options['sound']), $request);
		return $request;
	}

	private function build_ControlePosition($_options = array())
	{
		$request = escapeshellcmd($this->getConfiguration('request'));
		$request = str_replace('#position#', urlencode($_options['position']), $request);
		return $request;
	}

	private function build_ControleRien($_options = array())
	{
		return escapeshellcmd($this->getConfiguration('request')) . "?truc=vide";
	}

	private function buildDeleteAllAlarmsRequest($_options = array())
	{
		$request = $this->getConfiguration('request');
		log::add('alexatodolist', 'debug', '----buildDeleteAllAlarmsRequest RequestFinale:' . $request);
		if ($_options['type'] == "") $_options['type'] = "alarm";
		if ($_options['status'] == "") $_options['status'] = "ON";
		return str_replace(array('#type#', '#status#'), array($_options['type'], $_options['status']), $request);
	}

	private function builddeleteReminderRequest($_options = array())
	{
		$request = $this->getConfiguration('request');
		if ($_options['id'] == "") $_options['id'] = "ManqueID";
		if ($_options['status'] == "") $_options['status'] = "ON";
		return str_replace(array('#id#', '#status#'), array($_options['id'], $_options['status']), $request);
	}

	private function buildRestartRequest($_options = array())
	{
		log::add('alexatodolist_debug', 'debug', '------buildRestartRequest---UTILISE QUAND ???--A simplifier--------------------------------------');
		$request = $this->getConfiguration('request') . "?truc=vide";
		return str_replace('#volume#', $_options['slider'], $request);
	}
}
