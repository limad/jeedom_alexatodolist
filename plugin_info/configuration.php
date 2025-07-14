<?php
if (!isConnect()) {
	include_file('desktop', '404', 'php');
	die();
}
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


?>
  
  
<?php
            if(!file_exists( __DIR__ . '/../../../plugins/alexaapiv2/core/api/alexa_Api.php')){
              	$errMsg = 'ATTENTION Le plugin Alexa-Premium est introuvable, il est indispensable au fonctionnement de "Alexa - Todo List"';
                echo '<div id="div_pageContainer">'
					.' <div class="alert alert-danger div_alert">'
					.' <span id="span_errorMessage">'.$errMsg.'</span>'
					.' </div>'
					.' <script type="text/javascript" injext="1">document.title = "Alexatodolist - Jeedom"</script>'
					.' </div>';
            }
	
?>

<legend>{{Gestion du stock}}</legend>
<fieldset>
	<div class="col-sm-9">
  		<div class="input-group input-group-sm">
			<span class="input-group-btn">
				<a class="btn btn-success listCmdAction input-group-addon roundedLeft" data-type="info">
					<i class="fa fa-list-alt"></i>
				</a>
			</span>
			<input type="text" class="eqLogicAttr form-control CmdAction" data-l1key="configuration" data-l2key="UpStateCmd" placeholder="Sélectionner une commande" style="width: 250px">
		</div>
	</div>
</fieldset>