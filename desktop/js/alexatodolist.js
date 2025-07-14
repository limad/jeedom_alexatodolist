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

    document.querySelector('#bt_resetEqlogicSearch').addEventListener("click", function (event) {
        document.querySelector('#in_searchEqlogic').value = "";
        document.querySelector("#in_searchEqlogic").dispatchEvent(new Event("keyup"));
    });

    document.querySelector('#bt_removeAll')?.addEventListener("click", function (event) {
        var dialog_title = '{{Supprimer les équipements du plugin Alexa TodoList}}';
        var dialog_message = '<form class="form-horizontal"> ';
        dialog_message += '<label class="lbl lbl-warning" for="name">{{Voulez-vous suprimer tous les équipements du plugin Alexa ?}}</label> ';
        dialog_message += '</form>';
        bootbox.dialog({
            title: dialog_title,
            message: dialog_message,
            buttons: {
                "{{Annuler}}": {
                    className: "btn-warning",
                    callback: function () { }
                },
                success: {
                    label: "{{Supprimer tous}}",
                    className: "btn-danger",
                    callback: function () {
                        domUtils.ajax({
                            type: "POST",
                            url: "plugins/alexatodolist/core/ajax/alexatodolist.ajax.php",
                            data: {
                                action: "removeAll",
                            },
                            dataType: 'json',
                            error: function (request, status, error) {
                                handleAjaxError(request, status, error);
                            },
                          	success: function(data) {
                                if (data.state != 'ok') {
                                    jeedomUtils.showAlert({
                                        message: data.result,
                                        level: 'danger'
                                    });
                                    return;
                                }
                                setTimeout(() => {location.reload()}, 3000);
                                console.log('bt_removeAll: ' + data.result);
                            }
                        });
                    }

                },
            }
        });
    });

    document.querySelector('#bt_addAction')?.addEventListener("click", function (event) {
        var _cmd = {
            type: 'action',
            subType: 'select',
            eqType: 'alexatodolist',

        };
        addCmdToTable(_cmd, true);
        modifyWithoutSave = true
    });

    document.querySelector('#bt_sante')?.addEventListener("click", function (event) {
        jeeDialog.dialog({
            id: 'modalHealth',
            title: '{{Santé des devices}}',
            contentUrl: 'index.php?v=d&plugin=alexatodolist&modal=health'
        });
    });

	document.querySelector('#bt_addList')?.addEventListener("click", function(event) {
		jeeDialog.prompt({
				title: "{{Ajouter une liste}}",
				message: '{{Nom}}',
				inputType: 'input',
				placeholder: '{{Renseignez un nom...}}',
				value: '',
				pattern: '^(")',
				callback: function(result, event) {
					if (result == null || result == '') {
						if (event == 'confirm') jeedomUtils.showAlert({
							message: "{{Le nom de la liste ne peut pas être vide...}}",
							level: 'warning'
						})
					} else {
						domUtils.ajax({
							type: 'POST',
							url: 'plugins/alexatodolist/core/ajax/alexatodolist.ajax.php',
							async: false,
							global: false,
							data: {
								action: "addList",
								listName: result,
                              	listId: this.getAttribute('data-listId'),
							},
							dataType: 'json',
							error: function(request, status, error) {
								console.log('erreur desktop/js/alexatodolist.js');

								//handleAjaxError(request, status, error);
							},
							success: function(data) {
                                if (data.state != 'ok') {
                                    jeedomUtils.showAlert({
                                        message: data.result,
                                        level: 'danger'
                                    });
                                    return;
                                }
                                setTimeout(() => {location.reload()}, 3000);
                                console.log('bt_addList: ' + data.result);
                            }
                    	});
					}
				},
			},
			function(result) {
				if (result !== null) {
					var name = result
					console.log('resultat: ' + name)
				}
			});
       
});

	document.querySelector('#bt_scanlists')?.addEventListener("click", function(event) {
		domUtils.ajax({
			type: 'POST',
			url: 'plugins/alexatodolist/core/ajax/alexatodolist.ajax.php',
			async: false,
			global: false,
			data: {
				action: "scanlists"
			},
			dataType: 'json',
			error: function(request, status, error) {
				console.log('erreur desktop/js/alexatodolist.js');
				//handleAjaxError(request, status, error);
			},
			success: function(data) {
				if (data.state != 'ok') {
					jeedomUtils.showAlert({
						message: data.result,
						level: 'danger'
                    });
                    return;
				}
				setTimeout(() => {location.reload()}, 3000);
				console.log('bt_scanlists: ' + data.result);
            }
                    
		});
	});

	document.querySelector('#bt_additem')?.addEventListener("click", function(event) {
      	let listId = this.getAttribute('data-listId'),
        	customerId = this.getAttribute('data-customerId');
        console.log("bt_additem listId : " + listId);
        jeeDialog.prompt({
				title: "{{Ajouter l'élément}}",
				message: '{{Nom}}',
				inputType: 'input',
				placeholder: '{{Renseignez un nom...}}',
				value: '',
				pattern: '^(")',
				callback: function(result, event) {
					if (result == null || result == '') {
						if (event == 'confirm') jeedomUtils.showAlert({
							message: "{{Le nom de l'élément ne peut pas être vide...}}",
							level: 'warning'
						})
					} else {
						domUtils.ajax({
							type: 'POST',
							url: 'plugins/alexatodolist/core/ajax/alexatodolist.ajax.php',
							async: false,
							global: false,
							data: {
								action: "addItem",
								listId: listId,
								itemName: result,
                              	customerId: customerId
							},
							dataType: 'json',
							error: function(request, status, error) {
								console.warn('erreur additem desktop/js/alexatodolist.js');

								//handleAjaxError(request, status, error);
							},
							success: function(data) {
                                if (data.state != 'ok') {
                                    jeedomUtils.showAlert({
                                        message: data.result,
                                        level: 'danger'
                                    });
                                    return;
                                }
                                setTimeout(() => {location.reload()}, 3000);
                                console.log('bt_additem: ' + data.result);
                            }
						});
					}
				},
			},
			function(result) {
				if (result !== null) {
					var name = result
					console.log('resultat: ' + name)
				}
			});
	});	
	
	document.querySelectorAll("#updateItem").forEach(el => {
      	el.addEventListener("click", function(event) {
            let listId = this.getAttribute('data-listId'),
            	itemId = this.getAttribute('data-itemId'),
                idCmd = this.getAttribute('data-idCmd'),
                itemName = this.getAttribute('data-itemName'),
          		customerId = this.getAttribute('data-customerId'),
            	version = this.getAttribute('data-version'),
          		isChecked = this.checked;
          
          	domUtils.ajax({
                type: 'POST',
                url: 'plugins/alexatodolist/core/ajax/alexatodolist.ajax.php',
                async: false,
                global: false,
                data: {
                    action: "updateItem",
                    listId: listId,
                    itemId: itemId,
                    idCmd: idCmd,
                    customerId: customerId,
                  	completed: isChecked,
                    itemName: itemName,
                    version: Number(version)
                },
                dataType: 'json',
                error: function(request, status, error) {
                    console.warn('erreur updateItem() desktop/js/alexatodolist.js');

                    //handleAjaxError(request, status, error);
                },
                success: function(data) {
                    if (data.state != 'ok') {
                        jeedomUtils.showAlert({
                            message: data.result,
                            level: 'danger'
                        });
                        return;
                    }
                    setTimeout(() => {location.reload()}, 3000);
                    console.log('updateItem: ' + data.result);
                }
            });
        });
	});

	document.querySelectorAll("#set_itemName").forEach(el => {
        el.addEventListener("click", function(event) {
            let listId = this.getAttribute('data-listId'),
            	itemId = this.getAttribute('data-itemId'),
                idCmd = this.getAttribute('data-idCmd'),
                //itemName = this.getAttribute('data-itemName'),
          		//customerId = this.getAttribute('data-customerId'),
            	version = this.getAttribute('data-version'),
          		isChecked = this.checked;
          	jeeDialog.prompt({
                    title: "{{Modifier l'élément}}",
                    message: '{{Nouveau nom}}', //@required
                    inputType: 'input', //Default: input'. 'input', 'date', 'time', 'select', 'textarea'
                    placeholder: '{{Renseignez un nom...}}',
                    value: document.querySelector('#name' + idCmd).innerHTML, //Default value for inputType
                    pattern: '^(")', //Validation pattern. Default pattern if inputType 'time' : '[0-9]{4}-[0-9]{2}-[0-9]{2}'
                    /*backdrop: Boolan, //Default: true
                    buttons: {},*/
                    callback: function(result, event) { //@required
                        if (result == null || result == '') {
                            if (event == 'confirm') jeedomUtils.showAlert({
                                message: "{{Le nom de l'élément ne peut pas être vide...}}",
                                level: 'warning'
                            })
                        } else {
                            domUtils.ajax({
                                type: 'POST',
                                url: 'plugins/alexatodolist/core/ajax/alexatodolist.ajax.php',
                                //async: false,
                                //global: false,
                                dataType: 'json',
                                data: {
                                    action: "set_itemName",
                                    listId: listId,
                                    itemId: itemId,
                                    itemName: result,
                                    idCmd: idCmd,
                                    completed: isChecked,
                                    version: Number(version)
                                },
                                error: function(request, status, error) {
                                    console.warn('erreur set_itemName() desktop/js/alexatodolist.js');
									handleAjaxError(request, status, error);
                                },
                                success: function(data) {
                                    if (data.state != 'ok') {
                                        jeedomUtils.showAlert({
                                            message: data.result,
                                            level: 'danger'
                                        });
                                        return;
                                    }
									console.log("set_itemName" + data.result);
                                    document.querySelector('#name' + idCmd).innerHTML = result;
                                    //setTimeout(() => {location.reload()}, 3000);
                                }
                            });
                        }
                    },
                },
            function(result) {
                if (result !== null) {
                    var name = result
                    console.log('resultat: ' + name)
                }
            });	
      	});
	});

	document.querySelectorAll("#deleteItem").forEach(el => {
      	el.addEventListener("click", function(event) {
            let listId = this.getAttribute('data-listId'),
            	itemId = this.getAttribute('data-itemId'),
                version = this.getAttribute('data-version'),
          		idCmd = this.getAttribute('data-idCmd'),
                itemName = this.getAttribute('data-itemName'),
          		customerId = this.getAttribute('data-customerId');
            console.log("deleteItem " + listId + " " + version);
            jeeDialog.confirm("{{Êtes-vous sûr de vouloir supprimer l'élément ?}}", function(result) {
                if (result) {
                    //Do stuff
                    domUtils.ajax({
                        type: 'POST',
                        url: 'plugins/alexatodolist/core/ajax/alexatodolist.ajax.php',
                        async: false,
                        global: false,
                        data: {
                            action: "deleteItem",
                            listId: listId,
                            itemId: itemId,
                            version: version,
                          	idCmd: idCmd,
                          	itemName: itemName,
                          	customerId: customerId
                          
                        },
                        dataType: 'json',
                        error: function(request, status, error) {
                            console.log('erreur deleteItem desktop/js/alexatodolist.js');

                            //handleAjaxError(request, status, error);
                        },
                        success: function(data) {
							if (data.state != 'ok') {
								jeedomUtils.showAlert({
									message: data.result,
									level: 'danger'
								});
								return;
							}
							setTimeout(() => {location.reload()}, 3000);
							console.log('deleteItem: ' + data.result);
                        }
                    });
                }
            });
         });
    });

	
	document.querySelector('#bt_eqConfigRaw')?.addEventListener("click", function (event) {
  	//let eqLogId = document.querySelector('.eqLogicAttr[data-l1key=logicalId]').innerHTML;
        let eqid = document.querySelector('.eqLogicAttr[data-l1key=id]').innerHTML;
        jeeDialog.dialog({
            id: 'ConfigRaw',
            title: '{{Informations brutes}}',
            contentUrl: 'index.php?v=d&modal=object.display&class=eqLogic&id='+eqid,
        });
    });

    document.querySelector(".eqLogicAttr[data-l1key='configuration'][data-l2key='type']")?.addEventListener("change", function (event) {
        var icon = document.querySelector(".eqLogicAttr[data-l1key='configuration'][data-l2key='icon']").innerHTML;

        if (icon != '' && icon != null){
            document.querySelector('#img_device').src = 'plugins/alexaapiv2/core/config/devices/' + icon;
        }
        var id = document.querySelector('.eqLogicAttr[data-l1key=id]').innerHTML;
        if (id) {
            jeedom.eqLogic.byId({
                id: id,
                noCache: true,
                success: function (obj) {
                    if (obj && obj.configuration && obj.configuration.capabilities && obj.configuration.capabilities.length && obj.configuration.capabilities instanceof Array) {
                        /* !! Non conforme changement array to string
                        document.querySelector(".eqLogicAttr[data-l1key='configuration'][data-l2key='capabilities']").innerHTML = obj.configuration.capabilities.join(', ');
                        */
                    }
                }
            });
        }
        //var intType = document.querySelector('.eqLogicAttr[data-l2key=intType]').innerHTML;
    });

    



    $("#table_cmda, #table_cmdi").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});


	function addCmdToTable(_cmd, newCmd=false) {
  	let test = document.querySelector('#bt_oldView')?.classList.contains('actif');
    //if (test) return jeeFrontEnd.pluginTemplate.addCmdToTableDefault(_cmd);
	if (test) return addCmdToTablePrem(_cmd);
	if (!isset(_cmd)) var _cmd = {configuration: {}};
	if (!isset(_cmd.configuration)) _cmd.configuration = {};
	
  
	if (init(_cmd.type) == 'info' && _cmd.configuration.type == 'item') {
    }
  	else if (init(_cmd.type) == 'info') {
	  	var tr = '<tr class="cmd cmdi" data-cmd_id="'+init(_cmd.id)+'" data-cmd_LogId="'+init(_cmd.logicalId)+'" title="'+init(_cmd.logicalId)+'" >';
		tr += '<legend><i class="fas fa-info"></i> Commandes Infos</legend>';
		tr += '<td>';
		tr += '<span class="cmdAttr" data-l1key="id" ></span>';
		tr += '</td>';
		
		tr += '<td>';
		tr += '<div class="input-group">'
			tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
			tr += '<span class="input-group-btn">'
			tr += '<a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a>'
			tr += '</span>'
			tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
		tr += '</div>'
		tr += '</td>';
	   
		tr += '<td>';
      	if(newCmd){
			tr += '<span class="cmdAttr hidden" data-l1key="type"></span>';
			tr += '<span class="subType newCmd" subType="' + init(_cmd.subType) + '"></span>'
          	/*tr += '<span class="subType" subtype="other">';
          			tr += '<select class="cmdAttr form-control input-sm" data-l1key="subType" style="width: 120px; margin-top: 5px;">';
                      tr += '<option value="other">Défaut</option>';
                      tr += '<option value="slider">Curseur</option>';
                      tr += '<option value="message">Message</option>';
                      tr += '<option value="color">Couleur</option>';
                      tr += '<option value="select">Liste</option>';
          			tr += '</select>';
          			tr += '</span>';*/
		}
      	else{
			tr += '<span class="cmdAttr hidden" data-l1key="type"></span>';
			tr += '<span class="cmdAttr" data-l1key="subType"></span>';
		}
      	tr += '</td>';
        tr += '<td>';
			
  		if (typeof jeeFrontEnd !== 'undefined' && jeeFrontEnd.jeedomVersion !== 'undefined') {
			tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>';
			
		}
		tr += '</td>';
  
		tr += '<td>';
		tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" id="isVisible_'+init(_cmd.logicalId)+'" checked/>{{Afficher}}</label></span> ';
		if (init(_cmd.subType) == 'numeric' || init(_cmd.subType) == 'binary') {
			tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
		}
	  
		tr += '</td>';
		tr += '<td>';
		if (is_numeric(_cmd.id)) {
			tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fas fa-cogs"></i></a> ';
			//tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Evaluer}}</a>';
		}
	
		tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" style="margin-top: 3px"></i></td>';
		tr += '</tr>';

		$('#table_cmdi tbody').append(tr);
		$('#table_cmdi tbody tr:last').setValues(_cmd, '.cmdAttr');
   	
	}
	else if (init(_cmd.type) == 'action') {
	  	var tr = '<tr class="cmd cmda" data-cmd_id="'+init(_cmd.id)+'" data-cmd_LogId="'+init(_cmd.logicalId)+'" title="'+init(_cmd.logicalId)+'" >';
		//var tr = '<tr class="cmd cmda" data-cmd_id="' + init(_cmd.id) + '">';
		//tr += '<legend><i class="fas fa-info"></i> Commandes Actions</legend>';
		tr += '<td>';
		tr += '<span class="cmdAttr" data-l1key="id" ></span>';
		tr += '</td>';
		
		tr += '<td>';
		tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" >';
		tr += '</td>';
	   
		tr += '<td>';
		if(newCmd){
			tr += '<span class="cmdAttr hidden" data-l1key="type"></span>';
			tr += '<span class="subType newCmd" subType="' + init(_cmd.subType) + '">';
          			tr += '<select class="cmdAttr form-control input-sm" data-l1key="subType" style="width: 120px; margin-top: 5px;">';
                      tr += '<option value="other">Défaut</option>';
                      tr += '<option value="slider">Curseur</option>';
                      tr += '<option value="message">Message</option>';
                      tr += '<option value="color">Couleur</option>';
                      tr += '<option value="select">Liste</option>';
          			tr += '</select>';
          			tr += '</span>';
		}
      	else{
			tr += '<span class="cmdAttr hidden" data-l1key="type"></span>';
			tr += '<span class="cmdAttr" data-l1key="subType"></span>';
		}
		tr += '</td>';
	   	tr += '<td>';
		if (init(_cmd.type) == 'action' && init(_cmd.logicalId) != 'refresh') {
			tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="request">';
		}
		tr += '</td>';
		tr += '<td>';
		tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
		if (init(_cmd.subType) == 'numeric' || init(_cmd.subType) == 'binary') {
			tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
		}
	  
		tr += '</td>';
		tr += '<td>';
		if (is_numeric(_cmd.id)) {
			tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fas fa-cogs"></i></a> ';
			tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>';
		}
	
		tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i></td>';
		tr += '</tr>';
	  
		$('#table_cmda tbody').append(tr);
		$('#table_cmda tbody tr:last').setValues(_cmd, '.cmdAttr');
   	} 
  	
}

	function printEqLogic0(data) {
		var str = data.logicalId
		document.getElementById('img_device').src = "/plugins/alexaapiv2/core/config/devices/" + data.configuration.type + ".png";
		$('#multiroom-members').empty();
		if (data.configuration.members === undefined) {
			$('#multiroom-members').parent().hide(); //ajouté
			return;
		}
		if (data.configuration.members.length === 0) {
			$('#multiroom-members').parent().hide();
			return;
		}
		var html = '<ul style="list-style-type: none;">';
		for (var i in data.configuration.members) {
			var logicalId = data.configuration.members[i] + "_player";
			if (logicalId in logicalIdToHumanReadable)
				html += '<li style="margin-top: 5px;">' + logicalIdToHumanReadable[logicalId] + '</li>';
			else
				html += '<li style="margin-top: 5px;"><span class="label label-default" style="text-shadow : none;"><i>(Non configuré)</i></span> ' + logicalId + '</li>';
		}
		html += '</ul>';
		$('#multiroom-members').parent().show();
		$('#multiroom-members').append(html);
	}
	jeedomUtils.initTableSorter();

	
	$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

	
    function addCmdToTable0(_cmd) {
        if (!isset(_cmd)) {
            var _cmd = {
                configuration: {}
            };
        }
        if (_cmd.display.parameters == undefined) {
            if (init(_cmd.type) == 'info' && _cmd.logicalId == 'date_maj') {
                var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">'
                    + '<td>'
                    + '<span class="cmdAttr" data-l1key="id">' + init(_cmd.id) + '</span>'
                    + '</td>'
                    + '<td class="flexClass">'
                    + '<input class="cmdAttr form-control input-sm" data-l1key="name" placeholder="{{Nom de la commande}}">'
                    + '<span class="cmdAttr" data-l1key="display" data-l2key="icon" style="margin-left : 10px;width: 15px;"></span>'
                    + '</td>'
                    + '<td>'
                    + '<input class="cmdAttr form-control type input-sm" data-l1key="type" value="info" disabled style="" />'
                    + '<div style="display:none">'
                    + '<span class="subType" subType="' + init(_cmd.subType) + '"></span>'
                    + '</div>'
                    + '</td>'
                    + '<td>'
                    + '<input style="display:none" class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="request">'
                    + '</td>'
                    + '<td>'
                    + _cmd.state
                    + '</td>'
                    + '<td style="display:none">'
                    + '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> '
                    + '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> '
                    + '</td>'
                    + '<td>';
                if (is_numeric(_cmd.id)) {
                    tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fas fa-cogs"></i></a> '
                        + '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>';
                }
                tr += '<i style="display:none" class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>'
                    + '</td>'
                    + '</tr>';
                //document.querySelector('#table_cmd tbody tr:last').getAttribute('data-src');
                //document.querySelector('#table_cmd tbody').appendChild(tr);
                $('#table_cmd tbody').append(tr);
                $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
            }

            if (init(_cmd.type) == 'action') {
                var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">'
                    + '<td>'
                    + '<span class="cmdAttr" data-l1key="id"></span>'
                    + '</td>'
                    + '<td class="flexClass">'
                    + '<input class="cmdAttr form-control input-sm" data-l1key="name">'
                    + '<span class="cmdAttr" data-l1key="display" data-l2key="icon" style="margin-left : 10px;width: 15px;"></span>'
                    + '</td>'
                    + '<td>'
                    + '<input class="cmdAttr form-control type input-sm" data-l1key="type" value="action" disabled />'
                    + '<div style="display:none">'
                    + '<span class="subType" subType="' + init(_cmd.subType) + '"></span>'
                    + '</div>'
                    + '</td>'
                    + '<td>'
                    + '<input class="cmdAttr form-control input-sm"';
                if (init(_cmd.logicalId) == "refresh") {
                    tr += ' style="display:none;" ';
                }
                tr += ' data-l1key="configuration" data-l2key="request">';
                if (init(_cmd.subType) == 'select') {
                    tr += '<input class="tooltips cmdAttr form-control input-sm expertModeVisible" data-l1key="configuration" data-l2key="listValue" placeholder="{{Liste de valeur|texte séparé par ;}}" title="{{Liste}}">';
                }
                tr += '</td>';
                tr += '<td>';
                /*
                if ((init(_cmd.logicalId) == "") || (init(_cmd.logicalId) == "volume")) {
                    tr += '<input class="cmdAttr form-control input-sm" data-l1key="unite"  style="width : 100px;" placeholder="{{Unité}}" title="{{Unité}}" >'
                    +'<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}"  title="{{Min}} style="margin-top : 3px;"> '
                    +'</td>'
                    +'<td>'
                    +'<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}} style="margin-top : 3px;">';
                } else {
                    tr += '</td>';
                }*/
                tr += '</td>';
                tr += '<td style="display:none">'
                    + '<input class="tooltips cmdAttr form-control input-sm expertModeVisible" data-l1key="configuration" data-l2key="listValue" placeholder="{{Liste de valeur|texte séparé par ;}}" title="{{Liste}}" style="margin-top : 5px;">'
                    + '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> '
                    + '</td>'
                    + '<td>';
                tr += '<a class="btn btn-default btn-xs cmdAction expertModeVisible" data-action="configure"><i class="fas fa-cogs"></i></a> ';
                //if (!((init(_cmd.name) == "Routine") || (init(_cmd.name) == "xxxxxxxx"))) //Masquer le bouton Tester
                if (init(_cmd.logicalId) == "refresh") {
                    //tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>';
                }
              	 tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>';
                tr += '<i style="display:none" class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>'
                    + '</td>'
                    + '</tr>';
                $('#table_cmd tbody').append(tr);
            }
        }

        var tr = $('#table_cmd tbody tr:last');
        jeedom.eqLogic.buildSelectCmd({
            id: $(".li_eqLogic.active").attr('data-eqLogic_id'),
            filter: {
                type: 'i'
            },
            error: function (error) {
                jeedomUtils.showAlert({
                    message: error.message,
                    level: 'danger'
                });
            },
            success: function (result) {
                tr.find('.cmdAttr[data-l1key=value]').append(result);
                tr.setValues(_cmd, '.cmdAttr');
                jeedom.cmd.changeType(tr, init(_cmd.subType));
            }
        });
    }