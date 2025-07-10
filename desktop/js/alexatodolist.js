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


function addCmdToTable(_cmd) {
	if (!isset(_cmd)) {
		var _cmd = {
			configuration: {}
		};
	}

	//console.log("addCmdToTable : " + init(_cmd.logicalId));
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
				tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>';
			}
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

document.querySelector('#bt_resetEqlogicSearch').addEventListener("click", function (event) {
	document.querySelector('#in_searchEqlogic').value = "";
	document.querySelector("#in_searchEqlogic").dispatchEvent(new Event("keyup"));
});

