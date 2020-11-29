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


$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
$("#table_app").sortable({axis: "y", cursor: "move", items: ".app", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});

$("#bt_serviceLog").click(function () {
	$('#md_modal').dialog({title: "{{Logs}}"});
	$('#md_modal').load('index.php?v=d&plugin=AndroidRemoteControl&modal=log.AndroidRemoteControl').dialog('open');
});

$("#bt_configureAdb").click(function () {
	$('#md_modal').dialog({title: "{{Configuration de votre appareil Android}}"});
	$('#md_modal').load('index.php?v=d&plugin=AndroidRemoteControl&modal=configureadb.AndroidRemoteControl').dialog('open');
});

/*
* Fonction pour l'ajout de commande, appellé automatiquement par plugin.template
*/

function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    if (_cmd.configuration.categorie == "commande") {
        var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
        tr += '<td>';
        tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}">';
        tr += '</td>';
        tr += '<td>';
        tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
        tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
        tr += '</td>';
       	tr += '<td>';
       if (_cmd.type == "action") {
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="commande" style="width: 90%;display: inherit" ></input>';
    } else {
         tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="commande" style="width: 90%;display: inherit" disabled></input>';
       }
        tr += '</td>';
        tr += '<td style="width: 150px;">';
        tr += '<span><input type="checkbox" class="cmdAttr" data-size="mini" data-l1key="isVisible" checked/> {{Afficher}}<br/></span>';
      	tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
        tr += '</td>';
        tr += '<td>';
        if (is_numeric(_cmd.id)) {
            tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
            tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';

        }

        tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
        tr += '</td>';
        tr += '</tr>';
        $('#table_cmd tbody').append(tr);
        $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
        if (isset(_cmd.type)) {
            $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
        }
        jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
    }else if (_cmd.configuration.categorie == "appli") {
        var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
        tr += '<td style="width: 60px;">';
        tr += '<img src="plugins/AndroidRemoteControl/desktop/images/'+ _cmd.logicalId +'.png" style="width:30px"; height:"30px"></a>';
        tr += '</td>';
        tr += '<td style="width: 400px;">';
        tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width: 90%;display: inherit" placeholder="{{Nom}}">';
        tr += '</td>';
        tr += '<td style="width: 100px;" class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType();
        tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span></td>';
       tr += '</td>';
       tr += '<td>';
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="commande" style="width: 90%;display: inherit" ></input>';
        tr += '</td>';
        tr += '<td style="width: 150px;">';
        tr += '<span><input type="checkbox" class="cmdAttr" data-size="mini" data-l1key="isVisible" checked/> {{Afficher}}<br/></span>';
        tr += '</td>';
        tr += '<td style="width: 150px;">';
        if (is_numeric(_cmd.id)) {
            tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
            tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';

        }

        tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
        tr += '</td>';
        tr += '</tr>';
        $('#table_appli tbody').append(tr);
        $('#table_appli tbody tr:last').setValues(_cmd, '.cmdAttr');
        if (isset(_cmd.type)) {
            $('#table_appli tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
        }
        jeedom.cmd.changeType($('#table_appli tbody tr:last'), init(_cmd.subType));
    }else{
         var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
        tr += '<td style="width: 60px;">';
        tr += '<img src="plugins/AndroidRemoteControl/desktop/images/unknown.png" style="width:30px"; height:"30px"></a>';
        tr += '</td>';
        tr += '<td style="width: 400px;">';
        tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width: 90%;display: inherit" placeholder="{{Nom}}">';
      	       tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="icon" style="width: 90%;display: " value="unknown.png"></input>';

        tr += '</td>';   
      tr += '<td class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType();
        tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span></td>';
       tr += '</td>';
       tr += '<td style="width: 400px;">';
        tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="commande" style="width: 90%;display: inherit" ></input>';
      	tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="categorie" style="width: 90%;display: none" value="appli" placeholder="appli"></input>';
         tr += '</td>';
        tr += '<td style="width: 150px;">';
        tr += '<span><input type="checkbox" class="cmdAttr" data-size="mini" data-l1key="isVisible" checked/> {{Afficher}}<br/></span>';
        
      tr += '</td>';
    
        tr += '<td style="width: 150px;">';
        if (is_numeric(_cmd.id)) {
            tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
            tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';

        }

        tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
        tr += '</td>';
        tr += '</tr>';
        $('#table_appli tbody').append(tr);
        $('#table_appli tbody tr:last').setValues(_cmd, '.cmdAttr');
        if (isset(_cmd.type)) {
            $('#table_appli tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
        }
        jeedom.cmd.changeType($('#table_appli tbody tr:last'), init(_cmd.subType));
    }
}
