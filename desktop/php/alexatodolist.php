<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}

// Obtenir l'identifiant du plugin
$plugin = plugin::byId('alexatodolist');
// Charger le javascript
sendVarToJS('eqType', $plugin->getId());

// Accéder aux données du plugin
$eqLogics = eqLogic::byType('alexatodolist');
$eqLogics = eqLogic::byLogicalId('list','alexatodolist',true);
$logicalIdToHumanReadable = array();
foreach ($eqLogics as $eqLogic)
{
  $logicalIdToHumanReadable[$eqLogic->getLogicalId()] = $eqLogic->getHumanName(true, false);
}
?>

<script>
var logicalIdToHumanReadable = <?php echo json_encode($logicalIdToHumanReadable); ?>

function printEqLogic(data)
{
var str=data.logicalId
document.getElementById('img_device').src="/plugins/alexaapiv2/core/config/devices/"+data.configuration.type+".png";
  $('#multiroom-members').empty();
  if (data.configuration.members === undefined)
  {
	 $('#multiroom-members').parent().hide(); //ajouté
    return;
  }
  if (data.configuration.members.length === 0)
  {
    $('#multiroom-members').parent().hide();
    return;
  }
  var html = '<ul style="list-style-type: none;">';
  for (var i in data.configuration.members)
  {
    var logicalId = data.configuration.members[i]+"_player";
    if (logicalId in logicalIdToHumanReadable)
      html += '<li style="margin-top: 5px;">' + logicalIdToHumanReadable[logicalId] + '</li>';
    else
      html += '<li style="margin-top: 5px;"><span class="label label-default" style="text-shadow : none;"><i>(Non configuré)</i></span> ' + logicalId +'</li>';
  }
  html += '</ul>';
  $('#multiroom-members').parent().show();
  $('#multiroom-members').append(html);
}
</script>

<!-- Container global (Ligne bootstrap) -->
<div class="row row-overflow">
  <!-- Container des listes de commandes / éléments -->
  <div class="col-xs-12 eqLogicThumbnailDisplay">
    <legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
    <div class="eqLogicThumbnailContainer">
      <!-- Bouton d accès à la configuration -->
            <a href="index.php?v=d&amp;m=alexaapiv2&amp;p=alexaapiv2">
                <div class="cursor eqLogicAction logoSecondary">
                    <img style="margin-top: -14px;width: 40px !important;" src="plugins/alexaapiv2/plugin_info/alexaapiv2_icon.png">
                    <br>
                    <span style="color:#42d4eb">Lien Alexa-Premium</span>
                </div>
            </a>
            
      <div class="cursor eqLogicAction logoSecondary">
        <a id="bt_addList"><i class="fas fa-plus-circle logoPrimary" style="margin-bottom: 20px;font-size: 38px;" ></i>
        <br />
        <span style="color:#96c927">{{Ajouter une liste}}</span></a>
      </div>
      <!-- Bouton d accès à la configuration -->
      <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
        <i class="fas fa-wrench" ></i>
        <br />
        <span>{{Configuration}}</span>
      </div>
      <div class="cursor eqLogicAction logoSecondary" >
				<a target="_blank" href="https://youdom.net/alexa-premium-todolist-documentation/" alt="Documentation" title="Documentation"><i class="fas fa-book-open" style="margin-bottom: 20px;font-size: 38px;" ></i>
				<br>
				<span >{{Documentation}}</span></a>
			</div>
			<div class="cursor eqLogicAction logoSecondary" >
				<a target="_blank" href="https://youdom.net/" alt="Site Youdom" title="Site Youdom" ><i class="fas fa-home" style="color:#8cc63f;margin-bottom: 20px;font-size: 38px;"></i>
				<br>
				<span >{{Site internet Youdom}}</span></a>
			</div>
			<div class="cursor eqLogicAction logoSecondary" >
				<a target="_blank" href="https://www.facebook.com/groups/entraidejeedom/" alt="Communauté d'entraide Jeedom" title="Communauté d'entraide Jeedom"><i class="fas fa-users" style="color:#3b5998;margin-bottom: 20px;font-size: 38px;"></i>
				<br>
				<span >{{Communauté Entraide Jeedom}}</span></a>
			</div>
    </div>
    <!-- Début de la liste des objets -->

    <!-- Container de la liste -->
	
	<legend><i class="fas fa-table"></i> {{Mes listes}}</legend>
	<div class="input-group" style="margin-bottom:5px;">
		<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic"/>
		<div class="input-group-btn">
			<a id="bt_resetEqlogicSearch" class="btn roundedRight" style="width:30px"><i class="fas fa-times"></i></a>
		</div>
	</div>	
    <!-- Container de la liste -->
	<div class="panel">
		<div class="panel-body">
			<div style="position: relative;" class="eqLogicThumbnailContainer second">
        <?php
        $index=0;
        foreach($eqLogics as $eqLogic) {
        if(!$eqLogic->getConfiguration('archived')){
            $datetimecreation = new DateTime($eqLogic->getConfiguration('createtime'));
            $datetimeaujourdhui = new DateTime(date('Y-m-d'));
            $interval = $datetimecreation->diff($datetimeaujourdhui);
            $opacity = ($eqLogic->getIsEnable()) ? '' : ' disableCard';
            echo '<div class="eqLogicDisplayCard cursor second '.$opacity.'" data-eqLogic_id="'.$eqLogic->getId().'" style="height:168px;" onclick="document.location.href=\'/index.php?v=d&p=alexatodolist&m=alexatodolist&id='.$eqLogic->getId().'\'" >';
            echo '<img class="lazy" src="plugins/alexaapiv2/core/config/devices/'.$eqLogic->getConfiguration('type').'.png" style="min-height:75px !important;width:100px !important;padding-top: 0px;" />';
            echo '<br />';
            echo '<span class="name">'.$eqLogic->getHumanName(true, true).'</span>';
            echo '</div>';
            $index++;
          }
        }
        if($index==0){
          echo '<div style="margin-bottom: 40px;margin-top: 40px;width:100%;">'.__('Aucune liste...', __FILE__).'</div>';
        }
        ?>
			</div>
		</div>
    </div>

  <legend><i class="fas fa-table"></i> {{Mes listes archivées}}</legend>
    <!-- Container de la liste -->
	<div class="panel">
		<div class="panel-body">
			<div style="position: relative;" class="eqLogicThumbnailContainer second">
        <?php
        $index=0;
        foreach($eqLogics as $eqLogic) {
          if($eqLogic->getConfiguration('archived')){
            $datetimecreation = new DateTime($eqLogic->getConfiguration('createtime'));
            $datetimeaujourdhui = new DateTime(date('Y-m-d'));
            $interval = $datetimecreation->diff($datetimeaujourdhui);
            $opacity = ($eqLogic->getIsEnable()) ? '' : ' disableCard';
            echo '<div class="eqLogicDisplayCard cursor second '.$opacity.'" data-eqLogic_id="'.$eqLogic->getId().'" style="height:168px;" onclick="document.location.href=\'/index.php?v=d&p=alexatodolist&m=alexatodolist&id='.$eqLogic->getId().'\'" >';
            echo '<img class="lazy" src="plugins/alexaapiv2/core/config/devices/'.$eqLogic->getConfiguration('type').'.png" style="min-height:75px !important;width:100px !important;padding-top: 0px;" />';
            echo '<br />';
            echo '<span class="name">'.$eqLogic->getHumanName(true, true).'</span>';
            echo '</div>';
            $index++;
            }
        }
        if($index==0){
          echo '<div style="margin-bottom: 40px;margin-top: 40px;width:100%;">'.__('Aucune liste archivée...', __FILE__).'</div>';
        }
        ?>
			</div>
		</div>
    </div>
	
	
  </div>
  <!-- Container du panneau de contrôle -->
  <div class="col-lg-12 eqLogic" style="display: none;">

    <!-- Bouton sauvegarder -->
    <a style="display:none" class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
    <!-- Bouton Supprimer -->
    <a style="display:none" class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
    <!-- Bouton configuration par défaut -->
    <a style="display:none" id="bt_forcerDefaultCmd" class="btn btn-warning pull-right"><i class="fas fa-search"></i> {{Recharger configuration par défaut}}</a>
    <!-- Bouton configuration avancée -->
    <a class="btn btn-default eqLogicAction pull-right" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a>
    <!-- Bouton documentation -->
    <a class="btn btn-success pull-right" target="_blank" href="https://youdom.net/alexa-premium-todolist-documentation/" ><i class="fas fas fa-book-open"></i> {{Documentation}}</a>
    <!-- Liste des onglets -->
    <ul class="nav nav-tabs" role="tablist">
    <!-- Bouton de retour -->
    <li role="presentation"><a class="eqLogicAction cursor" aria-controls="home" role="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
    <!-- Onglet "Equipement" -->
    <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
    <!-- Onglet "Commandes" -->
    <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
    <!-- Onglet "Liste" -->
    <li role="presentation"><a href="#listtab" aria-controls="liste" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Elements de la liste}}</a></li>
    </ul>
    <!-- Container du contenu des onglets -->
    <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
      <div role="tabpanel" class="tab-pane active" id="eqlogictab">
        <br/>
        <div class="row">
          <div class="col-sm-7">
            <form class="form-horizontal">
              <fieldset>
                <div class="form-group">
                  <label class="col-sm-4 control-label">{{Nom de l'équipement Jeedom}}</label>
                  <div class="col-sm-8">
                    <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
                  </div>
                </div>

                <!-- Onglet "Objet Parent" -->
                <div class="form-group">
                  <label class="col-sm-4 control-label">{{Objet parent}}</label>
                  <div class="col-sm-6">
                    <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;"/>
                    <select class="eqLogicAttr form-control" data-l1key="object_id">
                    <option value="">{{Aucun}}</option>
                        <?php
                        foreach (jeeObject::all() as $object)
                            echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                        ?>
                    </select>
                  </div>
                </div>
                <!-- Catégorie" -->
                <div class="form-group">
                  <label class="col-sm-4 control-label">{{Catégorie}}</label>
                  <div class="col-sm-8">
                    <?php
                    foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value)
                    {
                        echo '<label class="checkbox-inline">';
                        echo '  <input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                        echo '</label>';
                    }
                    ?>
                  </div>
                </div>
                <!-- Onglet "Active Visible" -->
                <div class="form-group">
                  <label class="col-sm-4 control-label"></label>
                  <div class="col-sm-8">
                    <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                    <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
                  </div>
                </div>
              </fieldset>
            </form>
          </div>
<?php

?>		
          <div class="col-sm-5">
            <form class="form-horizontal">
              <fieldset>
                <div class="form-group">
                  <label class="col-sm-2 control-label">{{ID List}}</label>
                  <div class="col-sm-8">
                      <span style="position:relative;top:+5px;left:+5px;" class="eqLogicAttr" data-l1key="configuration" data-l2key="itemId"></span>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-2 control-label">{{Type}}</label>
                  <div class="col-sm-8">
                      <span style="position:relative;top:+5px;left:+5px;" class="eqLogicAttr" data-l1key="configuration" data-l2key="type"></span>
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-10" style="text-align:center;display: flex;flex-direction: row;flex-wrap: nowrap;justify-content: center;">
                      <img src="core/img/no_image.gif" data-original=".jpg" id="img_device" class="img-responsive" style="max-height : 250px;"  onerror="this.src='plugins/alexaapiv2/core/config/devices/default.png'"/>
                  </div>
                </div>

              </fieldset>
            </form>
          </div>
        </div>
      </div>

      <div role="tabpanel" class="tab-pane" id="commandtab">
        

        <table id="table_cmd" class="table table-bordered table-condensed">
          <thead>
            <tr>
              <th class="col-lg-1">#</th>
              <th class="col-lg-3" style="padding-left: calc(2em + 21px);">{{Nom}}</th>
              <th class="col-lg-1">{{Type}}</th>
              <th class="col-lg-4">{{Commande & Variable}}</th>
              <th class="col-lg-2">{{Valeur}}</th>
              <th class="col-lg-1">{{Paramètres}}</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
		

      </div>
      <div role="tabpanel" class="tab-pane" id="listtab">
        <div style="width:100%;text-align:center;font-size:20px;margin-top:40px;margin-bottom:20px; ">
        <?php
        if(isset($_GET['id']) && is_numeric($_GET['id'])){
          $eqLogicList=eqLogic::byId($_GET['id']);
          $listItems=cmd::byEqLogicId($_GET['id'],'info');
          echo $eqLogicList->getName().' ('.(count($listItems)-1).' '.__('éléments', __FILE__).')';
        }
        ?>
        </div>
        <table id="table_items" class="table table-bordered table-condensed tablesorter">
          <thead class="tablesorter-header-inner">
            <tr>
              <th>#</th>
              <th>{{Nom}}</th>
              <th>{{Editer}}</th>
              <th>{{Ajouté le}}</th>
              <th>{{Modifié le}}</th>
              <th>{{Par}}</th>
              <th>{{Statut}}</th>
              <th>{{Action}}</th>
            </tr>
          </thead>
          <tbody>
          <?php
            if(isset($_GET['id']) && is_numeric($_GET['id'])){
              $nbr=1;
              foreach($listItems as $item){
                if(is_array($item->getDisplay('parameters'))){
                  echo "<tr>";
                  echo "<td>".$nbr."</td>";
                  echo '<td id="name'.$item->getId().'">'.$item->getName().'</td>';
                  echo '<td><a class="btn btn-default btn-sm cmdAction" onclick="modifierItem(this)" title="{{Modifier le nom de l\'élément}} : '.$item->getId().'" data-idItem="'.$item->getDisplay('parameters')['id'].'" data-idCmd="'.$item->getId().'" data-idList="'.$item->getDisplay('parameters')['idList'].'" data-version="'.$item->getDisplay('parameters')['version'].'"><i class="fas fa-cog"></i></a></td>';
                  echo "<td>".date('d/m/Y H:i',($item->getDisplay('parameters')['createdDateTime']/1000))."</td>";
                  echo "<td>".date('d/m/Y H:i',($item->getDisplay('parameters')['updatedDateTime']/1000))."</td>";
                  echo "<td title='".$item->getDisplay('parameters')['customerId']."'>".alexaapiv2::searchUser($item->getDisplay('parameters')['customerId'])."</td>";
                  echo '<td><input id="completed'.$item->getId().'" onclick="modifierItemCompleted(this)" type="checkbox" class="" data-l1key="configuration" data-l2key="completed" data-idItem="'.$item->getDisplay('parameters')['id'].'" data-idCmd="'.$item->getId().'" data-idList="'.$item->getDisplay('parameters')['idList'].'" data-version="'.$item->getDisplay('parameters')['version'].'" '.($item->getDisplay('parameters')['completed'] == '1' ? 'checked="checked"':'').'></td>';
                  echo "<td>";
                  echo '<a class="btn btn-danger btn-sm cmdAction" onclick="supprimerItem(this)" data-idItem="'.$item->getDisplay('parameters')['id'].'" data-idCmd="'.$item->getId().'" data-idList="'.$item->getDisplay('parameters')['idList'].'" data-version="'.$item->getDisplay('parameters')['version'].'"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>';
                  echo "</td>";
                  echo "</tr>";
                  $nbr++;
                }
              }
            }
          ?> 
          </tbody>
        </table>
        
        <?php
        if(isset($_GET['id']) && is_numeric($_GET['id'])){
          echo '<a class="btn btn-success btn-sm cmdAction pull-right" onclick="ajoutItem(this)"  data-idList="'.$eqLogicList->getConfiguration('itemId').'" ><i class="fa fa-plus-circle"></i> Ajouter un élément</a>';
        }  
        ?>
      </div>





    </div>
  </div>
</div>

<?php include_file('desktop', 'alexatodolist', 'js', 'alexatodolist'); ?>
<?php include_file('desktop', 'alexatodolist', 'css', 'alexatodolist'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
<script>
          
document.querySelector('#bt_addList')?.addEventListener("click", function (event) {
		jeeDialog.dialog({
			id: 'modaladdList',
			title: '{{Ajouter une liste}}',
			contentUrl: 'index.php?v=d&plugin=alexaapiv2&modal=addList'
		});
});


jeedomUtils.initTableSorter();

function addList(item){
    jeeDialog.prompt({
    title: "{{Ajouter une liste}}",
    message: '{{Nom}}', 
    inputType: 'input', 
    placeholder: '{{Renseignez un nom...}}',
    value: '', 
    pattern: '^(")', 
    callback: function(result,event) { 
        if(result==null || result==''){
            if(event=='confirm') jeedomUtils.showAlert({message: "{{Le nom de la liste ne peut pas être vide...}}",level: 'warning'})
        }else{ 
          domUtils.ajax({
            type: 'POST',
            url: 'plugins/alexaapiv2/core/ajax/alexaapiv2.ajax.php',
            async: false,
            global: false,
            data: {
                action: "addList",
                text: result
            },
            dataType: 'json',
            error: function (request, status, error) {
                console.log('erreur desktop/php/alexatodolist.php');

                //handleAjaxError(request, status, error);
            },
            success: function (data) { 
                    console.log(data.result);
                    location.reload();
                    //document.querySelector('#name'+$(item)[0].getAttribute('data-idEqlogic')).innerHTML=result;
                    //jeeDialog.get('#formAddReminder').destroy()
                    //document.querySelector('.refreshAction[data-action=refresh]').click();
            }	
          });
        }
      },
    },
    function(result) {
        if (result !== null) {
            var name = result
            console.log('resultat: '+name)
        }
    });
}

function ajoutItem(item){
    jeeDialog.prompt({
    title: "{{Ajouter l'élément}}",
    message: '{{Nom}}', 
    inputType: 'input', 
    placeholder: '{{Renseignez un nom...}}',
    value: '', 
    pattern: '^(")', 
    callback: function(result,event) { 
        if(result==null || result==''){
            if(event=='confirm') jeedomUtils.showAlert({message: "{{Le nom de l'élément ne peut pas être vide...}}",level: 'warning'})
        }else{ 
          domUtils.ajax({
            type: 'POST',
            url: 'plugins/alexaapiv2/core/ajax/alexaapiv2.ajax.php',
            async: false,
            global: false,
            data: {
                action: "addItem",
                idList: $(item)[0].getAttribute('data-idList'),
                text: result
            },
            dataType: 'json',
            error: function (request, status, error) {
                console.warn('erreur ajoutItem() desktop/php/alexatodolist.php');

                //handleAjaxError(request, status, error);
            },
            success: function (data) { 
                    console.log(data.result);
                    //document.querySelector('#name'+$(item)[0].getAttribute('data-idEqlogic')).innerHTML=result;
                    //jeeDialog.get('#formAddReminder').destroy()
                    //document.querySelector('.refreshAction[data-action=refresh]').click();
            }	
          });
        }
      },
    },
    function(result) {
        if (result !== null) {
            var name = result
            console.log('resultat: '+name)
        }
    });
}

function modifierItemCompleted(item){
  domUtils.ajax({
            type: 'POST',
            url: 'plugins/alexaapiv2/core/ajax/alexaapiv2.ajax.php',
            async: false,
            global: false,
            data: {
                action: "modifyItemCompleted",
                idList: $(item)[0].getAttribute('data-idList'),
                idItem: $(item)[0].getAttribute('data-idItem'),
                idCmd: $(item)[0].getAttribute('data-idCmd'),
                completed: document.querySelector('#completed'+$(item)[0].getAttribute('data-idCmd')).checked,
                text: document.querySelector('#name'+$(item)[0].getAttribute('data-idCmd')).innerHTML,
                version: Number($(item)[0].getAttribute('data-version'))
            },
            dataType: 'json',
            error: function (request, status, error) {
                console.warn('erreur modifierItemCompleted() desktop/php/alexatodolist.php');

                //handleAjaxError(request, status, error);
            },
            success: function (data) { 
                    console.log(data.result);
                    //document.querySelector('#name'+$(item)[0].getAttribute('data-idEqlogic')).innerHTML=result;
                    //jeeDialog.get('#formAddReminder').destroy()
                    //document.querySelector('.refreshAction[data-action=refresh]').click();
            }	
          });
}
function modifierItem(item){
    jeeDialog.prompt({
    title: "{{Modifier l'élément}}",
    message: '{{Nouveau nom}}', //@required
    /*width: String,
    height: String,
    top: String,*/
    inputType: 'input', //Default: input'. 'input', 'date', 'time', 'select', 'textarea'
    /*inputOptions: [ //Options for inputType: 'select'
        {text: String, value: String},
    ],*/
    placeholder: '{{Renseignez un nom...}}',
    value: document.querySelector('#name'+$(item)[0].getAttribute('data-idCmd')).innerHTML, //Default value for inputType
    pattern: '^(")', //Validation pattern. Default pattern if inputType 'time' : '[0-9]{4}-[0-9]{2}-[0-9]{2}'
    /*backdrop: Boolan, //Default: true
    buttons: {},*/
    callback: function(result,event) { //@required
        if(result==null || result==''){
            if(event=='confirm') jeedomUtils.showAlert({message: "{{Le nom de l'élément ne peut pas être vide...}}",level: 'warning'})
        }else{ 
          domUtils.ajax({
            type: 'POST',
            url: 'plugins/alexaapiv2/core/ajax/alexaapiv2.ajax.php',
            async: false,
            global: false,
            data: {
                action: "modifyItem",
                idList: $(item)[0].getAttribute('data-idList'),
                idItem: $(item)[0].getAttribute('data-idItem'),
                idCmd: $(item)[0].getAttribute('data-idCmd'),
                completed: document.querySelector('#completed'+$(item)[0].getAttribute('data-idCmd')).checked,
                text: result,
                version: Number($(item)[0].getAttribute('data-version'))
            },
            dataType: 'json',
            error: function (request, status, error) {
               console.warn('erreur modifierItem() desktop/php/alexatodolist.php');

                

                //handleAjaxError(request, status, error);
            },
            success: function (data) { 
                    console.log(data.result);
                    document.querySelector('#name'+$(item)[0].getAttribute('data-idCmd')).innerHTML=result;
                    //jeeDialog.get('#formAddReminder').destroy()
                    //document.querySelector('.refreshAction[data-action=refresh]').click();
            }	
          });
        }
      },
    },
    function(result) {
        if (result !== null) {
            var name = result
            console.log('resultat: '+name)
        }
    });
}

function supprimerItem(item){
    jeeDialog.confirm("{{Êtes-vous sûr de vouloir supprimer l'élément ?}}", function(result) {
        if (result) {
            //Do stuff
            domUtils.ajax({
                    type: 'POST',
                    url: 'plugins/alexaapiv2/core/ajax/alexaapiv2.ajax.php',
                    async: false,
                    global: false,
                    data: {
                        action: "deleteItem",
                        idList: $(item)[0].getAttribute('data-idList'),
                        idItem: $(item)[0].getAttribute('data-idItem'),
                        idCmd: $(item)[0].getAttribute('data-idCmd')
                    },
                    dataType: 'json',
                    error: function (request, status, error) {
                        console.log('erreur desktop/php/alexatodolist.php');

                        //handleAjaxError(request, status, error);
                    },
                    success: function (data) { 
                            console.log(data.result);
                            //document.querySelector('#name'+$(item)[0].getAttribute('data-idEqlogic')).innerHTML=result;
                            //jeeDialog.get('#formAddReminder').destroy()
                            //document.querySelector('.refreshAction[data-action=refresh]').click();
                    }	
            });
        }
    });
}




</script>
  
<?php 
  if(isset($_GET['id']) && is_numeric($_GET['id'])){
    $cmd = $eqLogicList->getCmd(null, 'date_maj');
    if (is_object($cmd)) {
    ?>
    jeedom.cmd.update[<?php echo $cmd->getId(); ?>] = function(_options) { 
      setTimeout(() => {location.reload()}, 1000);
    }
    <?php
    }
  }  
?>