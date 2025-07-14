<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugName ='Alexa - Todo List';
$plugin = plugin::byId('alexatodolist');
if(!file_exists( __DIR__ . '/../../../../plugins/alexaapiv2/core/api/alexa_Api.php')){
	log::add('alexatodolist', 'error', "Le plugin Alexa-Premium est introuvable, Il est indispensable au fonctionnement de 'alexatodolist'");
	throw new Exception(__("Le plugin Alexa-Premium est introuvable, Il est indispensable au fonctionnement de 'alexatodolist'", __FILE__));
}



sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType('alexatodolist');
$logicalIdToHumanReadable = array();
foreach ($eqLogics as $eqLogic) {
	$logicalIdToHumanReadable[$eqLogic->getLogicalId()] = $eqLogic->getName();//$eqLogic->getHumanName(true, false);
}
sendVarToJS('logicalIdToHumanReadable', $logicalIdToHumanReadable);

?>


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

			<!-- Bouton d accès à la configuration -->
			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
				<i class="fas fa-wrench"></i>
				<br />
				<span>{{Configuration}}</span>
			</div>
  			
  			<!-- Bouton de scan des lists -->
			<div class="cursor logoPrimary" id="bt_scanlists">
				<i class="fas fa-bullseye" style="font-size: 3em;color: #00caff;"></i>
				<br />
				<span style="color: #00caff;">{{Scan}}</span>
			</div>

			  			
  			<!-- Bouton Ajouter une liste -->
			<div class="cursor eqLogicAction logoSecondary">
				<a id="bt_addList"><i class="fas fa-plus-circle logoPrimary" style="margin-bottom: 20px;font-size: 38px;"></i>
					<br />
					<span style="color:#96c927">{{Ajouter une liste}}</span></a>
			</div>
			  			
			<!-- Bouton removeAll -->
			<div class="cursor eqLogicAction logoDefault" data-action="removeAll" id="bt_removeAll">
				<i class="fas fa-minus-circle" style="color: #FA5858;"></i>
				<br/><span>{{Supprimer tout}}</span>
			</div>
  
  			<!-- Bouton Documentation -->
			<div class="cursor eqLogicAction logoSecondary">
				<a target="_blank" href="https://youdom.net/alexa-premium-todolist-documentation/" alt="Documentation" title="Documentation"><i class="fas fa-book-open" style="margin-bottom: 20px;font-size: 38px;"></i>
					<br>
					<span>{{Documentation}}</span></a>
			</div>
			<!-- Bouton Communauté -->
			<div class="cursor eqLogicAction logoSecondary">
				<a target="_blank" href="https://www.facebook.com/groups/entraidejeedom/" alt="Communauté d'entraide Jeedom" title="Communauté d'entraide Jeedom"><i class="fas fa-users" style="color:#3b5998;margin-bottom: 20px;font-size: 38px;"></i>
					<br>
					<span>{{Communauté Entraide Jeedom}}</span></a>
			</div>
		</div>
		<!-- Début de la liste des objets -->

		<!-- Container de la liste -->

		<legend><i class="fas fa-table"></i> {{Mes listes}}</legend>
		<div class="input-group" style="margin-bottom:5px;">
			<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
			<div class="input-group-btn">
				<a id="bt_resetEqlogicSearch" class="btn roundedRight" style="width:30px"><i class="fas fa-times"></i></a>
			</div>
		</div>
		<!-- Container de la liste -->
		<div class="panel">
			<div class="panel-body">
				<div style="position: relative;" class="eqLogicThumbnailContainer second">
					<?php
  					$arch_eqLogic = [];
					$index = 0;
					foreach ($eqLogics as $eqLogic) {
                      	//log::add('alexatodolist', 'debug', " desktop/alexatodolist ".$eqLogic->getName());
		
						if (!$eqLogic->getConfiguration('archivedList')) {
							$datetimecreation = new DateTime($eqLogic->getConfiguration('createtime'));
							$datetimeaujourdhui = new DateTime(date('Y-m-d'));
							$interval = $datetimecreation->diff($datetimeaujourdhui);
							$opacity = ($eqLogic->getIsEnable()) ? '' : ' disableCard';
							echo '<div class="eqLogicDisplayCard cursor second ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '" style="height:168px;" onclick="document.location.href=\'/index.php?v=d&p=alexatodolist&m=alexatodolist&id=' . $eqLogic->getId() . '\'" >';
                          	$alternateImg = $eqLogic->getConfiguration('icon', '');
							if ($alternateImg != '') echo '<img class="lazy" src="plugins/alexaapiv2/core/config/devices/' . $alternateImg . '" style=" !important;" />';
							else echo '<img class="lazy" src="' . $plugin->getPathImgIcon() . '" style=" !important;" />';
                          	
							echo '<br />';
							echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
							echo '</div>';
							$index++;
						}
                      	else $arch_eqLogic[] = $eqLogic;
					}
					if ($index == 0) {
						echo '<div style="margin-bottom: 40px;margin-top: 40px;width:100%;">' . __('Aucune liste...', __FILE__) . '</div>';
					}
					?>
				</div>
			</div>
		</div>

		<legend><i class="fas fa-table"></i> {{Mes listes archivées}}</legend>
		<!-- Container listes archivées -->
		<div class="panel">
			<div class="panel-body">
				<div style="position: relative;" class="eqLogicThumbnailContainer second">
					<?php
					$index = 0;
					/*
                    foreach ($arch_eqLogic as $eqLogic) {
						if ($eqLogic->getConfiguration('archivedList')) {
						}	
						$datetimecreation = new DateTime($eqLogic->getConfiguration('createtime'));
						$datetimeaujourdhui = new DateTime(date('Y-m-d'));
						$interval = $datetimecreation->diff($datetimeaujourdhui);
						$opacity = ($eqLogic->getIsEnable()) ? '' : ' disableCard';
						echo '<div class="eqLogicDisplayCard cursor second ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '" style="height:168px;" onclick="document.location.href=\'/index.php?v=d&p=alexatodolist&m=alexatodolist&id=' . $eqLogic->getId() . '\'" >';
						echo '<img class="lazy" src="plugins/alexaapiv2/core/config/devices/' . $eqLogic->getConfiguration('type') . '.png" style="min-height:75px !important;width:100px !important;padding-top: 0px;" />';
						echo '<br />';
						echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
						echo '</div>';
						$index++;
						
					}
					*/
					if ($index == 0) {
						echo '<div style="margin-bottom: 40px;margin-top: 40px;width:100%;">' 
                          	. __("Aucune liste archivée...Vous devez les activer depuis l'application Mobile", __FILE__) . '</div>';
					}
					?>
				</div>
			</div>
		</div>


	</div>
	<!-- Container du panneau de contrôle -->
	<div class="col-lg-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
				<span class="input-group-btn">
					<a class="btn btn-info btn-sm roundedLeft" id="bt_eqConfigRaw" style="min-width: 25px;margin-left: 2px;"><i class="fas fa-info"> </i></a>
					<a class="btn btn-sm btn-success" id="bt_oldView" title="Mode edition des commandes"><i class="icon far fa-edit"></i> </a>
					<a class="btn btn-sm btn-default eqLogicAction" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span></a>
					<a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
					<a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
					
                 </span>
		</div>
                      
		<!-- Bouton configuration par défaut -->
		
		             
                      
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
				<br /><br />
				<div class="row">
					<div class="col-sm-7">
						<form class="form-horizontal">
							<fieldset>
								<div class="form-group">
									<label class="col-sm-4 control-label">{{Nom de l'équipement Jeedom}}</label>
									<div class="col-sm-6">
										<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}" />
									</div>
								</div>

								<!-- Onglet "Objet Parent" -->
								<div class="form-group">
									<label class="col-sm-4 control-label">{{Objet parent}}</label>
									<div class="col-sm-6">
										<span class="eqLogicAttr" data-l1key="id" style="display: none;"></span>
										<span class="eqLogicAttr" data-l1key="configuration" data-l2key="icon" style="display: none;"></span>
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
									<div class="col-sm-6">
										<?php
										foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
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
									<div class="col-sm-6">
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked />{{Activer}}</label>
										<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked />{{Visible}}</label>
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
										<span style="position:relative;top:+5px;left:+5px;" class="eqLogicAttr" data-l1key="configuration" data-l2key="listId"></span>
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
										<img src="core/img/no_image.gif" data-original=".jpg" id="img_device" class="img-responsive" style="max-height : 250px;" onerror="this.src='plugins/alexaapiv2/core/config/devices/default.png'" />
									</div>
								</div>

							</fieldset>
						</form>
					</div>
				</div>
			</div>
                                          
                                          
				<!-- Panel commandes  -->
				<div role="tabpanel" class="tab-pane" id="commandtab">


					<legend style="/*background: var(--el-defaultColor) !important*/; font-weight: 500; font-size: 1.4em;">
						<center class="title_cmdtable">{{Commandes }}<?php echo ' - ' . $plugName . ' : '; ?>
							<span class="eqName"></span>
						</center>
					</legend>
					<br>

					<div id="cmdtab" style="display:none">                  
					<table id="table_cmd" class="table table-bordered table-condensed ">
								<th class="hidden-xs" style="min-width:50px;width:70px;">ID</th>
								<th style="min-width:300px;width:320px;">{{Nom}}</th>
								<th style="width: 140px;">Type</th>
								<th>{{Etat}}</th>
								<th style="min-width:300px;width:300px;">{{Options}}</th>
								<th style="min-width:100px;width:150px;">{{Actions}}</th>
						<tbody></tbody>
					</table>
                    </div> 
                                      
                    <div id="cmditab">                  
					<legend  id="leg_cmdi"><i class="fas fa-list-alt"></i> {{Commandes Infos}}</legend>
					<table id="table_cmdi" class="table table-bordered table-condensed ">
						<thead>
							<tr>
								<th style="width: 80px;">Id</th>
								<th style="width: 360px;">Nom</th>
								<th style="width: 140px;">Type</th>
								<th style="">Etat</th>
								<th style="width: 200px;">Options</th>
								<th style="width: 80px;">Action</th>

							</tr>
						</thead>

						<tbody></tbody>
					</table>
					</div>
                    <!--  commandes en mode edition -->                  
					<div id="cmdatab">                  
                        <legend id="leg_cmda"><i class="fas fa-list-alt"></i> {{Commandes Actions}}</legend>

                        <table id="table_cmda" class="table table-bordered table-condensed">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Id</th>
                                    <th style="width: 360px;">Nom</th>
                                    <th style="width: 140px;">Type</th>
                                    <th style="">Etat</th>
                                    <th style="width: 200px;">Options</th>
                                    <th style="width: 130px;">Action</th>

                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
					</div>
					

			</div>
            <!-- END Panel commandes  -->                              
			<div role="tabpanel" class="tab-pane" id="listtab">
				<div style="width:100%;text-align:center;font-size:20px;margin-top:40px;margin-bottom:20px; ">
					<?php
					if (isset($_GET['id']) && is_numeric($_GET['id'])) {
						$eqLogicList = eqLogic::byId($_GET['id']);
						$listItems = cmd::byEqLogicId($_GET['id'], 'info');
						echo $eqLogicList->getName() . ' (' . (count($listItems) - 1) . ' ' . __('éléments', __FILE__) . ')';
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
							<th>{{Utilisateur}}</th>
							<th>{{Version}}</th>
							<th>{{Accompli}}</th>
							<th>{{Action}}</th>
						</tr>
					</thead>
					<tbody>
						<?php
						if (isset($_GET['id']) && is_numeric($_GET['id'])) {
							$nbr = 1;
							foreach ($listItems as $item) {
                              	
								if (is_array($item->getConfiguration('parameters',''))) {
                                  	$parameters = $item->getConfiguration('parameters');
									echo "<tr>";
									echo "<td>" . $nbr . "</td>";
									echo '<td id="name' . $item->getId() . '">' . $item->getName() . '</td>';
									echo '<td><a class="btn btn-default btn-sm cmdAction" id="set_itemName" title="{{Modifier le nom de l\'élément}} : ' . $item->getId() . '" data-itemId="' . $parameters['id'] . '" data-idCmd="' . $item->getId() . '" data-listId="' . $parameters['listId'] . '" data-version="' . $parameters['version'] . '"><i class="fas fa-cog"></i></a></td>';
									echo "<td>" . date('d/m/Y H:i', intval($parameters['createdDateTime'] / 1000)) . "</td>";
									echo "<td>" . date('d/m/Y H:i', intval($parameters['updatedDateTime'] / 1000)) . "</td>";
									echo "<td title='" . $parameters['customerId'] . "'>" . alexaapiv2::searchUser($parameters['customerId']) . "</td>";
									echo "<td title='" . $parameters['version'] . "'>" . $parameters['version'] . "</td>";
									echo '<td><input class="" type="checkbox" id="updateItem" data-l1key="configuration" data-l2key="completed' 
                                      . '" data-itemId="' . $parameters['id']  
                                      . '" data-idCmd="' . $item->getId() 
                                      . '" data-listId="' . $parameters['listId'] 
                                      . '" data-version="' . $parameters['version'] 
                                      . '" data-customerId="'. $parameters['customerId']
                                      . '" data-itemName="' . $item->getName() .'" ' 
                                      . ($parameters['completed'] == '1' ? 'checked="checked"' : '') 
                                      . '></td>';
									
                                  	echo "<td>";
									echo '<a class="btn btn-danger btn-sm cmdAction" id="deleteItem" '
                                      . '" data-itemId="' . $parameters['id'] 
                                      . '" data-idCmd="' . $item->getId() 
                                      . '" data-listId="' . $parameters['listId'] 
                                      . '" data-itemName="' .$item->getName()
                                      . '" data-version="' .$parameters['version'] 
                                      . '" data-customerId="'. $parameters['customerId']
                                      . '"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>';
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
				if (isset($_GET['id']) && is_numeric($_GET['id'])) {
					echo '<a class="btn btn-success btn-sm cmdAction pull-right" id="bt_additem" data-listId="' 
                      . $eqLogicList->getConfiguration('listId') 
                      . '" data-customerId="'. $eqLogicList->getConfiguration('customerId')
                      . '" ><i class="fa fa-plus-circle"></i> Ajouter un élément à la liste</a>';
				}
				?>
			</div>





		</div>
	</div>
</div>
<script>
	
	
   	/*
    <?php
    	if (isset($_GET['id']) && is_numeric($_GET['id'])) {
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
    */
      
</script>
<?php include_file('desktop', 'alexatodolist', 'js', 'alexatodolist'); ?>
<?php include_file('desktop', 'alexatodolist', 'css', 'alexatodolist'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>