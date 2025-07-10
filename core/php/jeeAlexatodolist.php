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

require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";


if (!jeedom::apiAccess(init('apikey'), 'alexatodolist')) {
	echo __('Vous n\'êtes pas autorisé à effectuer cette action', __FILE__);
	log::add('alexatodolist_mqtt', 'debug', 'Clé Plugin Invalide');
	die();
}

if (init('test') != '') {
	echo 'OK';
	die();
}

$chaineRecuperee = file_get_contents("php://input");
$nom = $_GET["nom"];
log::add('alexatodolist', 'debug', 'Réception données sur jeeAlexatodolist [' . $nom . ']');
log::add('alexatodolist_mqtt', 'info', " -------------------------------------------------------------------------------------------------------------");
log::add('alexatodolist_widget', 'info', " -------------------------------------------------------------------------------------------------------------");

log::add('alexatodolist_mqtt', 'debug', "chaineRecuperee: " . $chaineRecuperee);

$debut = strpos($chaineRecuperee, "{");
$fin = strrpos($chaineRecuperee, "}");
$longeur = 1 + intval($fin) - intval($debut);
$chaineRecupereeCorrigee = substr($chaineRecuperee, $debut, $longeur);
log::add('alexatodolist_mqtt', 'debug', "->" . $chaineRecupereeCorrigee);

/*if ($nom !="commandesEnErreur") {
	$chaineRecupereeCorrigee=str_replace ("[", "", $chaineRecupereeCorrigee);
	$chaineRecupereeCorrigee=str_replace ("]", "", $chaineRecupereeCorrigee);
}*/

//log::add('alexatodolist_mqtt', 'debug',  "chaineRecupereeCorrigee: ".$chaineRecupereeCorrigee);
//log::add('alexatodolist_mqtt', 'debug',  "nom: ".$nom);

$result = json_decode($chaineRecupereeCorrigee, true);

//log::add('alexatodolist_mqtt', 'debug',  "result: ".$result); // Attention déclenche un PHP Notice:  Array to string conversion

if (!is_array($result)) {
	log::add('alexatodolist_mqtt', 'debug', 'Format Invalide');
	die();
}
//log::add('alexatodolist_mqtt', 'debug',  'deviceSerialNumber:'.$result['deviceSerialNumber']);
$logical_id = $result['deviceSerialNumber'] . "_player";

//$alexatodolist_player=eqLogic::byLogicalId($logical_id, 'alexaamazonmusic'); // PLAYER
$alexatodolist2 = eqLogic::byLogicalId($result['deviceSerialNumber'], 'alexatodolist'); // ECHO
//$alexatodolist3=alexaamazonmusic::byLogicalId($result['deviceSerialNumber']."_playlist", 'alexaamazonmusic'); // PLAYLIST

// Choix de ce qu'on doit mettre à jour
// ECHO
// PLAYER
// PLAYLIST

//log::add('alexatodolist_node', 'info',  'Alexa-jee: '.$nom);

switch ($nom) {

	case 'commandesEnErreur':
		log::add('alexatodolist_node', 'warning', "Alexa-jee: Il va falloir relancer: " . $chaineRecupereeCorrigee . " Pause 8s");
		sleep(8);
		$commandeaRelancer = json_decode($chaineRecupereeCorrigee, true);
		$queryEnErreur = $commandeaRelancer['queryEnErreur'];
		$listeCommandesEnErreur = $commandeaRelancer['listeCommandesEnErreur'];
		$listeCommandesEnErreur = str_replace("[", "", $listeCommandesEnErreur);
		$listeCommandesEnErreur = str_replace("]", "", $listeCommandesEnErreur);

		if (is_array($listeCommandesEnErreur)) { // s'il y a un groupe de commandes à relancer
			foreach ($listeCommandesEnErreur as $CommandesEnErreur) {
				$url = "http://" . config::byKey('internalAddr') . ":3456/" . $CommandesEnErreur['command'] . "?replay=1&" . http_build_query($queryEnErreur);
				$json = file_get_contents($url);
			}
		} else {                                // s'il n'y a qu'une commande à relancer
			//faudra surement ajouter un test ici pour voir si c'ets pas vide
			$url = "http://" . config::byKey('internalAddr') . ":3456/" . $listeCommandesEnErreur . "?replay=1&" . http_build_query($queryEnErreur);
			$json = file_get_contents($url);
		}
		break;
	/*
	case 'ws-bluetooth-state-change':
	if ($result['bluetoothEvent'] == 'DEVICE_CONNECTED') metAJour("bluetoothDevice", "Connexion en cours", 'bluetoothDevice', false , "ECHO", $result['deviceSerialNumber']);
	if ($result['bluetoothEvent'] == 'DEVICE_DISCONNECTED') metAJour("bluetoothDevice", "Déconnexion en cours", 'bluetoothDevice', false , "ECHO", $result['deviceSerialNumber']);
		metAJourBluetooth($result['deviceSerialNumber'], $result['audioPlayerState'], $alexatodolist2, "PLAYER", $result['deviceSerialNumber']);
	break;
	*/
	case 'ws-volume-change':
		metAJour("Volume", $result['volume'], 'volumeinfo', false, "PLAYER", $result['deviceSerialNumber']);
		metAJour("Volume", $result['volume'], 'volumeinfo', false, "ECHO", $result['deviceSerialNumber']);
		break;
	case 'ws-notification-change': //changement d'une alarme/rappel
		log::add('alexatodolist_node', 'info', 'Alexa-jee: notificationVersion: ' . $result['notificationVersion']);

		$alexatodolist2->refresh();    // Lance un refresh du device principal
		break;

	case 'ws-media-queue-change':
		// command=repeat&value=on  ==>loopMode": "LOOP_QUEUE"
		// command=repeat&value=off ==>"loopMode": "NORMAL",
		// command=shuffle&value=off ==>"playBackOrder": "NORMAL" trackOrderChanged": false
		// command=shuffle&value=on  ==>"playBackOrder": "SHUFFLE_ALL", "trackOrderChanged": true
		$shuffle = false;
		$repeat = false;
		if ($result['loopMode'] == 'LOOP_QUEUE') $repeat = true;
		if ($result['playBackOrder'] == 'SHUFFLE_ALL') $shuffle = true;
		metAJour("repeat", $repeat, 'repeat', false, "PLAYER", $result['deviceSerialNumber']);
		metAJour("shuffle", $shuffle, 'shuffle', false, "PLAYER", $result['deviceSerialNumber']);

		if (isset($result['audioPlayerState']))
			metAJourPlayList($result['deviceSerialNumber'], $result['audioPlayerState'], 'ws-media-queue-change');

		//break; // il ne faut pas s'arrêter mais aller tout mettre à jour.
	case 'ws-device-activity':
		//log::add('alexatodolist_mqtt', 'debug',  '1');

		//if (isset($result['description']['summary']) && ($result['description']['summary']!="") ){
		if ((isset($result['description']['summary'])) && ($result['description']['summary'] != "")) {
			//metAJour("Interaction", $result['description']['summary'], 'interactioninfo', true , "PLAYER", $result['deviceSerialNumber']);
			//metAJour("Interaction", $result['description']['summary'], 'interactioninfo', true , "ECHO", $result['deviceSerialNumber']);
			// Modif Thibaut
			metAJour("Interaction", $result['description']['summary'], 'interactioninfo', true, "PLAYER", $result['deviceSerialNumber']);
			metAJour("Interaction", $result['description']['summary'], 'interactioninfo', true, "ECHO", $result['deviceSerialNumber']);

			$alexatodolisteqlogic = eqLogic::byLogicalId($result['deviceSerialNumber'], 'alexatodolist');
			log::add('alexatodolist', 'debug', 'Interaction ' . $alexatodolisteqlogic->getConfiguration('interactionjeedom') == 1);
			if ($alexatodolisteqlogic->getConfiguration('interactionjeedom') == 1) {
				$demandeinteract = $result['description']['summary'];
				if (strpos($demandeinteract, 'jacques dit') === false && $demandeinteract != "alexa") {
					log::add('alexatodolist', 'debug', 'Interaction demande : ' . $demandeinteract);

					$parameters['plugin'] = 'alexatodolist';
					$reply = interactQuery::tryToReply(trim($demandeinteract), $parameters);
					log::add('alexatodolist', 'debug', 'Interaction ' . print_r($reply, true));
					if ($reply['reply'] != "Désolé je n'ai pas compris" && $reply['reply'] != "Désolé je n'ai pas compris la demande" && $reply['reply'] != "Désolé je ne comprends pas la demande" && $reply['reply'] != "Je ne comprends pas" && $reply['reply'] != "ceci est un message de test" && $reply['reply'] != "" && $reply['reply'] != " ") {
						log::add('alexatodolist', 'debug', "La reponse : " . $reply['reply'] . " est valide je vous l'ai donc renvoyée");
						$cmd = $alexatodolisteqlogic->getCmd('action', 'speak');
						$option = array('message' => $reply['reply']);
						$cmd->execute($option);
					} else {
						log::add('alexatodolist', 'debug', "La reponse : " . $reply['reply'] . " est une reponse générique je vous l'ai donc pas renvoyée");
					}
				}
			}
		}
		//log::add('alexatodolist_mqtt', 'debug',  '2');

		if (isset($result['activityStatus']))
			metAJour("activityStatus", $result['activityStatus'], 'activityStatus', true, "PLAYER", $result['deviceSerialNumber']);

		if (isset($result['domainAttributes']['nBestList']['stationCallSign']))
			metAJour("Radio", $result['domainAttributes']['nBestList']['stationCallSign'], 'radioinfo', false, "PLAYER", $result['deviceSerialNumber']);

		if (isset($result['domainAttributes']['nBestList']['stationName']))
			metAJour("Radio", $result['domainAttributes']['nBestList']['stationName'], 'radioinfo', false, "PLAYER", $result['deviceSerialNumber']);

		if (isset($result['domainAttributes']['nBestList']['playlistName'])) {
			metAJour("playlistName", $result['domainAttributes']['nBestList']['playlistName'], 'playlistName', false, "PLAYER", $result['deviceSerialNumber']);
			metAJour("playlistName", $result['domainAttributes']['nBestList']['playlistName'], 'playlistName', false, "PLAYLIST", $result['deviceSerialNumber']);
		}

		if (isset($result['audioPlayerState'])) {
			metAJourPlayer($result['deviceSerialNumber'], $result['audioPlayerState']);
			metAJourPlayList($result['deviceSerialNumber'], $result['audioPlayerState'], 'ws-device-activity');
			metAJourPlayer($result['deviceSerialNumber'], $result['audioPlayerState']); //par sécurité
		}

		//metAJour("songName", $result['domainAttributes']['nBestList']['songName'], 'songName', true , $alexatodolist);

		break;
	case 'message_add':
		//log::add('alexatodolist_node', 'info',  'Alexa-jee: '.$result['message']);
		message::add('alexatodolist', $result['message']);
		break;
	case 'ws-audio-player-state-change': // elle a visiblement disparue cette balise des logs mqtt
		metAJour("Audio Player State", $result['audioPlayerState'], 'audioPlayerState', true, "PLAYER", $result['deviceSerialNumber']);
	case 'refreshPlayer':
		//log::add('alexatodolist_widget', 'debug', '*********************************rrrrefreshPlayer avec '.$result['playerInfo']['miniArt']['url']."//".json_encode($result).'********************');

		metAJourPlayer($result['deviceSerialNumber'], $result['audioPlayerState']);
		metAJourPlayList($result['deviceSerialNumber'], $result['audioPlayerState'], 'refreshPlayer');
		break;

	default:

		if (!is_object($alexatodolist2)) {
			log::add('alexatodolist_mqtt', 'debug', 'Device non trouvé: ' . $logical_id);
			die();
		} else {
			log::add('alexatodolist_mqtt', 'debug', 'Device trouvé: ' . $logical_id);
		}
}
//log::add('alexatodolist_mqtt', 'info',  " ------------------------------------------------------------------------------------------------" );
log::add('alexatodolist_widget', 'info', " -------------------------------------------------------------------------------------------------");

//	if (is_object($alexatodolist)) $alexatodolist_player->refreshWidget();


function metAJour($nom, $variable, $commandejeedom, $effaceSiNull, $_typeDevice, $_deviceSerialNumber)
{

	if ($_typeDevice == "ECHO") {
		$alexatodolist2 = eqLogic::byLogicalId($_deviceSerialNumber, 'alexatodolist'); // ECHO
		if (is_object($alexatodolist2)) metAJour2($nom, $variable, $commandejeedom, $effaceSiNull, $alexatodolist2);
	}

	if ($_typeDevice == "PLAYER") {
		foreach (alexatodolist::listePluginsAlexa() as $pluginAlexaUnparUn) {
			$alexatodolist = eqLogic::byLogicalId($_deviceSerialNumber . "_player", $pluginAlexaUnparUn); // PLAYER
			if (is_object($alexatodolist)) metAJour2($nom, $variable, $commandejeedom, $effaceSiNull, $alexatodolist);
		}
	}

	if ($_typeDevice == "PLAYLIST") {
		foreach (alexatodolist::listePluginsAlexa() as $pluginAlexaUnparUn) {
			$alexatodolist3 = eqLogic::byLogicalId($_deviceSerialNumber . "_playlist", $pluginAlexaUnparUn); // PLAYLIST
			if (is_object($alexatodolist3)) metAJour2($nom, $variable, $commandejeedom, $effaceSiNull, $alexatodolist3);
		}
	}
}

function metAJour2($nom, $variable, $commandejeedom, $effaceSiNull, $_alexatodolist)
{
	try {
		if (isset($variable)) {
			if ($nom != 'playlisthtml') { // on supprime playlisthtml des logs sinon ils deviennent illisibles
				log::add('alexatodolist_widget', 'info', '   [' . $nom . ':' . $commandejeedom . '] find: ' . json_encode($variable) . " sur {" . $_alexatodolist->getName() . "}");
				log::add('alexatodolist_mqtt', 'info', '   [' . $nom . ':' . $commandejeedom . '] find: ' . json_encode($variable) . " sur {" . $_alexatodolist->getName() . "}");
			}
			$_alexatodolist->checkAndUpdateCmd($commandejeedom, $variable);
		} else {
			log::add('alexatodolist_widget', 'info', '   [' . $nom . ':' . $commandejeedom . '] non trouvé: ' . $variable);
			if ($effaceSiNull) {
				$_alexatodolist->checkAndUpdateCmd($commandejeedom, null);
				log::add('alexatodolist_widget', 'info', '   [' . $nom . ':' . $commandejeedom . '] non trouvé et vidé');
			}
		}
	} catch (Exception $e) {
		log::add('alexatodolist_mqtt', 'info', ' [' . $nom . ':' . $commandejeedom . '] erreur1: ' . $e);
	} catch (Error $e) {
		log::add('alexatodolist_mqtt', 'info', ' [' . $nom . ':' . $commandejeedom . '] erreur22: ' . $e);
	}
}

function metAJourImage($nom, $variable, $commandejeedom, $effaceSiNull, $_deviceSerialNumber)
{
	//log::add('alexatodolist_mqtt', 'debug',  'metAJourImage >>>>>>>'.$_deviceSerialNumber);
	foreach (alexatodolist::listePluginsAlexa() as $pluginAlexaUnparUn) {
		$alexatodolist = eqLogic::byLogicalId($_deviceSerialNumber . "_player", $pluginAlexaUnparUn); // PLAYER
		metAJourImage2($nom, $variable, $commandejeedom, $effaceSiNull, $alexatodolist);
	}
}

function metAJourImage2($nom, $variable, $commandejeedom, $effaceSiNull, $_alexatodolist)
{
	//log::add('alexatodolist_mqtt', 'debug',  'metAJourImage2 '.$nom."/". $variable."/".$commandejeedom);

	try {
		if (isset($variable)) {
			log::add('alexatodolist_widget', 'info', '   [' . $nom . ':' . $commandejeedom . '] find: ' . json_encode($variable));
			log::add('alexatodolist_mqtt', 'info', '   [' . $nom . ':' . $commandejeedom . '] find: ' . json_encode($variable));
			$_alexatodolist->checkAndUpdateCmd($commandejeedom, $variable);
		} else {
			log::add('alexatodolist_mqtt', 'debug', '[' . $nom . ':' . $commandejeedom . '] non trouvé');
			$_alexatodolist->checkAndUpdateCmd($commandejeedom, "plugins/alexatodolist/core/img/vide.gif");
		}
	} catch (Exception $e) {
		log::add('alexatodolist_mqtt', 'info', ' [' . $nom . ':' . $commandejeedom . '] erreur1: ' . $e);
	} catch (Error $e) {
		log::add('alexatodolist_mqtt', 'info', ' [' . $nom . ':' . $commandejeedom . '] erreur21: ' . $e);
	}
}

function metAJourStatusPlayer($_Status, $_deviceSerialNumber)
{
	foreach (alexatodolist::listePluginsAlexa() as $pluginAlexaUnparUn) {
		$alexatodolist = eqLogic::byLogicalId($_deviceSerialNumber . "_player", $pluginAlexaUnparUn); // PLAYER
		if (is_object($alexatodolist)) {
			$alexatodolist->setStatus('Playing', $_Status);
			$alexatodolist->refreshWidget(); //refresh Tuile Player
		}
	}
}

function metAJourPlayer($serialdevice, $audioPlayerState)
{
	log::add('alexatodolist_widget', 'info', " ***********************[metAJourPlayer]*********************************" . $serialdevice);

	try {
		$json = file_get_contents("http://" . config::byKey('internalAddr') . ":3456/playerInfo?device=" . $serialdevice);
		$result = json_decode($json, true);
		log::add('alexatodolist_widget', 'debug', ' JSON:' . $json);

		if (isset($result['playerInfo']['infoText']['subText1'])) metAJour("subText1", $result['playerInfo']['infoText']['subText1'], 'subText1', true, "PLAYER", $serialdevice);
		if (isset($result['playerInfo']['infoText']['subText2'])) metAJour("subText2", $result['playerInfo']['infoText']['subText2'], 'subText2', true, "PLAYER", $serialdevice);
		if (isset($result['playerInfo']['infoText']['title'])) metAJour("title", $result['playerInfo']['infoText']['title'], 'title', true, "PLAYER", $serialdevice);
		if (isset($result['playerInfo']['mainArt']['url'])) metAJourImage("url", $result['playerInfo']['mainArt']['url'], 'url', true, $serialdevice);
		if (isset($result['playerInfo']['progress']['mediaLength'])) metAJour("mediaLength", $result['playerInfo']['progress']['mediaLength'], 'mediaLength', true, "PLAYER", $serialdevice);
		if (isset($result['playerInfo']['progress']['mediaProgress'])) metAJour("mediaProgress", $result['playerInfo']['progress']['mediaProgress'], 'mediaProgress', true, "PLAYER", $serialdevice);
		if (isset($result['playerInfo']['provider']['providerName'])) metAJour("providerName", $result['playerInfo']['provider']['providerName'], 'providerName', true, "PLAYER", $serialdevice);
		if (isset($result['playerInfo']['state'])) metAJour("state", $result['playerInfo']['state'], 'state', false, "PLAYER", $serialdevice);
		if (isset($result['playerInfo']['state'])) metAJourStatusPlayer($result['playerInfo']['state'] == "PLAYING", $serialdevice);
		//$alexatodolist_player->setStatus('Playing', ($result['playerInfo']['state']=="PLAYING"));

	} catch (Exception $e) {
		log::add('alexatodolist_widget', 'info', ' [' . $serialdevice . '] erreur1: ' . $e);
	} catch (Error $e) {
		log::add('alexatodolist_widget', 'info', ' [' . $serialdevice . '] erreur25: ' . $e);
	}
	//if (is_object($alexatodolist_player)) $alexatodolist_player->refreshWidget(); //refresh Tuile Player
	log::add('alexatodolist_widget', 'debug', '** Mise à jour Tuile du Player **');
}

function metAJourPlaylist($serialdevice, $audioPlayerState, $_quiMetaJour = 'personne')
{
	//log::add('alexatodolist_widget', 'debug', '*********************************metAJourPlaylist par '.$_quiMetaJour.'********************');
	//log::add('alexatodolist_widget', 'debug', '*********************************metAJourPlaylist AVEC '.$result.'********************');
	try {
		//		if (($audioPlayerState!="FINISHED") && (isset($result['playerInfo']['miniArt']['url']))) 	{	//modif Sigalou 24/08/2020 ? Pourquoi 2eme partie du test ?
		if (($audioPlayerState != "FINISHED")) {
			//Pour avoir la piste en cours, on va aller chercher la valeur de playerinfo/mainArt/url pour pouvoir la comparer aux images de la playlist
			//sleep(2);
			//log::add('alexatodolist_widget', 'debug', '*********************************1metAJourPlaylist par '.$_quiMetaJour.'********************');
			$json = file_get_contents("http://" . config::byKey('internalAddr') . ":3456/playerinfo?device=" . $serialdevice);
			$result = json_decode($json, true);
			if (isset($result['playerInfo'])) {
				$imageURLenCoursdeLecture = $result['playerInfo']['miniArt']['url']; //Modif 09/12/2019 proposée par Aidom, annulée 10/12/2019
				$artist_enCoursdeLecture = $result['playerInfo']['infoText']['subText1'];
				$title_enCoursdeLecture = $result['playerInfo']['infoText']['title'];
				$album_enCoursdeLecture = $result['playerInfo']['infoText']['subText2'];
				$etatPlayer = $result['playerInfo']['state'];
			} else {
				$imageURLenCoursdeLecture = "";
				$artist_enCoursdeLecture = "";
				$title_enCoursdeLecture = "";
				$album_enCoursdeLecture = "";
				$etatPlayer = "";
			}

			//log::add('alexatodolist_widget', 'debug', '-----------------subText1:'.$result['playerInfo']['infoText']['subText1']);
			//log::add('alexatodolist_widget', 'debug', '-----------------subText2:'.$result['playerInfo']['infoText']['subText2']);
			//log::add('alexatodolist_widget', 'debug', '-----------------title:'.$result['playerInfo']['infoText']['title']);


			$json = file_get_contents("http://" . config::byKey('internalAddr') . ":3456/media?device=" . $serialdevice);
			$result = json_decode($json, true);
			//log::add('alexatodolist_widget', 'debug', '-----------------result:'.json_encode($result));
		} else {
			//log::add('alexatodolist_widget', 'debug', '*********************************1BISmetAJourPlaylist par '.$_quiMetaJour.'********************');
		}

		if (isset($result)) {
			//log::add('alexatodolist_widget', 'debug', '*********************************2metAJourPlaylist par '.$_quiMetaJour.'********************');

			//ON RECUPERE CE QUIE ST AU D2BUT DE MEDIA
			if (isset($result['contentId']))

				metAJour("contentId", $result['contentId'], 'contentId', true, "PLAYER", $serialdevice);

			$html = "<table style='border-collapse: separate; border-spacing : 10px; ' border='0' width='100%'>";
			$compteurQueue = 1;
			if (isset($result['queue'])) {
				foreach ($result['queue'] as $key => $value) {
					//log::add('alexatodolist_widget', 'debug', '>>>>>>>>>>>>>>>>>album:'.$value['album']."/".$album_enCoursdeLecture);
					//log::add('alexatodolist_widget', 'debug', '>>>>>>>>>>>>>>>>>artist:'.$value['artist']."/".$artist_enCoursdeLecture);
					//log::add('alexatodolist_widget', 'debug', '-----------------imageURL:'.$value['imageURL']);
					//log::add('alexatodolist_widget', 'debug', '>>>>>>>>>>>>>>>>>title:'.$value['title']."/".$title_enCoursdeLecture);
					//log::add('alexatodolist_widget', 'debug', '>>>>>>>>>>>>>>>>>album:'.$value['album']."/".$album_enCoursdeLecture);
					//log::add('alexatodolist_widget', 'debug', '>>>>>>>>>>>>>>>>>artist:'.$value['artist']."/".$artist_enCoursdeLecture);
					//log::add('alexatodolist_widget', 'debug', '-----------------durationSeconds:'.$value['durationSeconds']);
					//if (($value['album']==$album_enCoursdeLecture) && ($value['artist']==$artist_enCoursdeLecture) && ($value['title']==$title_enCoursdeLecture))
					if (($value['artist'] == $artist_enCoursdeLecture) && ($value['title'] == $title_enCoursdeLecture)) {
						//if (($value['imageURL']==$imageURLenCoursdeLecture) && $compteurQueue>3){
						$html = "<table style='border-collapse: separate; border-spacing : 10px; ' border='0' width='100%'>";
					}

					//$html.="<tr><td style='padding: 8px;'  rowspan='2' width='50'><a href='' onclick=\"ttttt('55')\">";
					$html .= "<tr><td style='padding: 8px;'  rowspan='2' width='50'>";
					//if (($value['imageURL']==$imageURLenCoursdeLecture) && $etatPlayer=="PLAYING")
					if (($value['artist'] == $artist_enCoursdeLecture) && ($value['title'] == $title_enCoursdeLecture) && $etatPlayer == "PLAYING")
						$html .= "<img style='position:absolute' src='plugins/alexatodolist/core/img/playing_petit.gif' />";
					$html .= "
				<object data='" . $value['imageURL'] . "' style='height: 60px;width: 60px;border-radius: 30%;' type='image/png'>
				<img style='height: 60px;width: 60px;border-radius: 30%;'  src='plugins/alexatodolist/core/img/musique.png'/></object></a></td>
					<td width='100%'>" . $value['title'] . "</td>
				</tr>
				<tr>
					<td width='100%'><small>" . $value['artist'] . " - <font size=1><em>" . date('i:s', $value['durationSeconds']) . "</em></font></small></td>
				</tr>";

					$compteurQueue++;
				}
			}
			$html .= "</table>";
		} else {
			$html = "<br>";
			//log::add('alexatodolist_widget', 'debug', '*********************************2BISmetAJourPlaylist par '.$_quiMetaJour.'********************');

		}
		metAJour("playlisthtml", $html, 'playlisthtml', true, "PLAYLIST", $serialdevice);
		foreach (alexatodolist::listePluginsAlexa() as $pluginAlexaUnparUn) {
			$alexatodolist3 = eqLogic::byLogicalId($serialdevice . "_playlist", $pluginAlexaUnparUn); // PLAYLIST
			$alexatodolist3->refreshWidget(); //refresh Tuile Playlist
		}
	} catch (Exception $e) {
		log::add('alexatodolist_widget', 'info', ' [' . $serialdevice . '] erreur1: ' . $e);
	} catch (Error $e) {
		log::add('alexatodolist_widget', 'info', ' [' . $serialdevice . '] erreur26: ' . $e);
	}

	//log::add('alexatodolist_widget', 'debug', '*********************************FINmetAJourPlaylist par '.$_quiMetaJour.'********************');
	//log::add('alexatodolist_widget', 'debug', '*********************************FINmetAJourPlaylist par '.$_quiMetaJour.'********************');

}

/*
// Faudra le tester !!!!!!!!!!!!!!
function metAJourBluetooth($serialdevice, $audioPlayerState, $alexatodolist2, $alexatodolist_player) {
		//log::add('alexatodolist_widget', 'debug',  'zzzzzzzzzzzzzzzzz metAJourPlayer:');

	try {

		//Pour avoir la piste en cours, on va aller chercher la valeur de playerinfo/mainArt/url pour pouvoir la comparer aux images de la playlist
		$json=file_get_contents("http://" . config::byKey('internalAddr') . ":3456/bluetooth");
		$result = json_decode($json,true);

//log::add('alexatodolist_widget', 'debug', '-->--->--->--->--deviceSerialNumber:'.$result['bluetoothStates']['0']['deviceSerialNumber']);


		//$result=array_filter($result, "odd");

		//$imageURLenCoursdeLecture=$result['playerInfo']['miniArt']['url'];
		//$etatPlayer=$result['playerInfo']['state'];

		//log::add('alexatodolist_widget', 'debug',  '------------->'.json_encode($result));

		//if (($audioPlayerState=="PLAYING") || ($audioPlayerState=="REFRESH") || ($audioPlayerState=="PAUSED"))	{

		foreach ($result['bluetoothStates'] as $key => $value) {
				//log::add('alexatodolist_widget', 'debug', '-------------------------------------------------------------------------------');
				//log::add('alexatodolist_widget', 'debug', '-----------------deviceType:'.$value['deviceType']);
				//log::add('alexatodolist_widget', 'debug', '-----------------friendlyName:'.$value['friendlyName']);
				//log::add('alexatodolist_widget', 'debug', '-----------------online:'.$value['online']);
				//log::add('alexatodolist_widget', 'debug', '-----------------pairedDeviceList:'.$value['pairedDeviceList']);
				if (is_array($value['pairedDeviceList'])) {
					foreach ($value['pairedDeviceList'] as $key2 => $value2) {
						if ($value['deviceSerialNumber'] == $serialdevice) {
						//log::add('alexatodolist_widget', 'debug', '-----------------$serialdevice:'.$serialdevice);
						//log::add('alexatodolist_widget', 'debug', '-----------------deviceSerialNumber:'.$value['deviceSerialNumber']);
						//log::add('alexatodolist_widget', 'debug', '********** friendlyName:'.$value2['friendlyName']);
						//log::add('alexatodolist_widget', 'debug', '********** connected:'.$value2['connected']);
							if (isset($value2['connected']) && (($value2['connected']) == '1')) {
								metAJour("bluetoothDevice", $value2['friendlyName'], 'bluetoothDevice', false , "ECHO", $result['deviceSerialNumber']);
								}
								else {
								metAJour("bluetoothDevice", "", 'bluetoothDevice', false , "ECHO", $result['deviceSerialNumber']);
								}
						}
					}


				}

		}

	} catch (Exception $e) {
			log::add('alexatodolist_widget', 'info',  ' ['.$nom.':'.$commandejeedom.'] erreur1: '.$e);

	} catch (Error $e) {
			log::add('alexatodolist_widget', 'info',  ' ['.$nom.':'.$commandejeedom.'] erreur27: '.$e);

	}

}
	*/
