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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function alexatodolist_install()
{
    message::add('alexatodolist', 'Début initialisation plugin todo list Alexa.');
    $result = file_get_contents("http://" . config::byKey('internalAddr') . ":3466/getLists");
    message::add('alexatodolist','Actualisation des listes exécutée !'); 
    sleep(2);
    foreach(eqLogic::byLogicalId('list','alexatodolist',true) as $listJeedom){
        sleep(1);
        $listJeedom->save();
        $idList=$listJeedom->getConfiguration('itemId');
        message::add('alexatodolist','Actualisation de la liste : '.$idList.' exécutée'); 
        $result = file_get_contents("http://" . config::byKey('internalAddr') . ":3466/getListItems?idList=".$idList);
        
    }
    message::add('alexatodolist', 'Fin initialisation plugin todo list Alexa.');
    //$result = file_get_contents("http://" . config::byKey('internalAddr') . ":3466/getListItems?idList=".$idList);
    //log::add('alexatodolist', 'info', 'Actualisation des données des listes exécutée !'); 

}
function alexatodolist_update()
{
    //log::add('alexatodolist', 'info', 'Lancement mise à jour de alexatodolist');
    //$result = file_get_contents("http://" . config::byKey('internalAddr') . ":3466/getLists");
    //log::add('alexatodolist', 'info', 'Actualisation des listes (requete: ' . "http://" . config::byKey('internalAddr') . ":3466/getLists" . ')');
    //message::add('alexatodolist', 'Mise à jour du plugin todo list Alexa terminée.');
}
function alexatodolist_remove()
{
    $index=0;
    //message::add('alexatodolist', 'Début remove plugin todo list Alexa terminée.');
    foreach(eqLogic::byLogicalId('list','alexatodolist',true) as $listSup){
        log::add('alexatodolist', 'info',  'suppression de la liste : '.$listSup->getConfiguration('itemId'));
        $eqLogicList=eqLogic::byTypeAndSearchConfiguration('alexatodolist',array("fromList" => $listSup->getConfiguration('itemId')));
        foreach($eqLogicList as $item){
            log::add('alexatodolist', 'info',  "suppression de l'élément : ".$item->getName());
            $item->remove();
        }
        $eqRemove=eqLogic::byTypeAndSearchConfiguration('alexatodolist',array("itemId" => $listSup->getConfiguration('itemId') ));
        $eqRemove[0]->remove();
        $index++;
    }
    if($index>0){
        message::add('alexatodolist', 'Suppression des enregistrements du plugin todo list Alexa terminée.');
    }else{
        message::add('alexatodolist', 'Suppression des enregistrements du plugin todo list Alexa terminée (aucun enregistrement présent).');
    }


   //message::add('alexatodolist', 'Fin remove plugin todo list Alexa terminée.');
}

