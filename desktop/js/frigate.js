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

/* Permet la réorganisation des commandes dans l'équipement */
$("#table_cmd").sortable({
    axis: "y",
    cursor: "move",
    items: ".cmd",
    placeholder: "ui-state-highlight",
    tolerance: "intersect",
    forcePlaceholderSize: true
})

/* Fonction permettant l'affichage des commandes dans l'équipement */
function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = { configuration: {} }
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {}
    }
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">'
    tr += '<td class="hidden-xs">'
    tr += '<span class="cmdAttr" data-l1key="id"></span>'
    tr += '</td>'
    tr += '<td>'
    tr += '<div class="input-group">'
    tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
    tr += '<span class="input-group-btn"><a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a></span>'
    tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
    tr += '</div>'
    tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande info liée}}">'
    tr += '<option value="">{{Aucune}}</option>'
    tr += '</select>'
    tr += '</td>'
    tr += '<td>'
    tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>'
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>'
    tr += '</td>'
    tr += '<td>'
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</label> '
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked/>{{Historiser}}</label> '
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label> '
    tr += '<div style="margin-top:7px;">'
    tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
    tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
    tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
    tr += '</div>'
    tr += '</td>'
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>';
    tr += '</td>';
    tr += '<td>'
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> '
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>'
    }
    tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i></td>'
    tr += '</tr>'
    $('#table_cmd tbody').append(tr)
    var tr = $('#table_cmd tbody tr').last()
    jeedom.eqLogic.buildSelectCmd({
        id: $('.eqLogicAttr[data-l1key=id]').value(),
        filter: { type: 'info' },
        error: function (error) {
            $('#div_alert').showAlert({ message: error.message, level: 'danger' })
        },
        success: function (result) {
            tr.find('.cmdAttr[data-l1key=value]').append(result)
            tr.setValues(_cmd, '.cmdAttr')
            jeedom.cmd.changeType(tr, init(_cmd.subType))
        }
    })

}

function addAction(_action, _type) {
    if (!isset(_action)) {
        _action = {}
    }
    if (!isset(_action.options)) {
        _action.options = {}
    }
    var div = '<div class="' + _type + '">'
    div += '<div class="form-group ">'
    div += '<div class="col-sm-1">'
    div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher la case pour désactiver l\'action}}">'
    div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="background" title="{{Cocher la case pour que la commande s\'exécute en parallèle des autres actions}}">'
    div += '</div>'
    div += '<div class="col-sm-5">'
    div += '<div class="input-group input-group-sm">'
    div += '<span class="input-group-btn">'
    div += '<a class="btn btn-default btn-sm bt_removeAction roundedLeft" data-type="' + _type + '"><i class="fas fa-minus-circle"></i></a>'
    div += '</span>'
    div += '<input class="expressionAttr form-control cmdAction input-sm" data-l1key="cmd" data-type="' + _type + '" />'
    div += '<span class="input-group-btn">'
    div += '<a class="btn btn-default btn-sm listAction" data-type="' + _type + '" title="{{Sélectionner un mot-clé}}"><i class="fas fa-tasks"></i></a>'
    div += '<a class="btn btn-default btn-sm listCmdAction roundedRight" data-type="' + _type + '" title="{{Sélectionner la commande}}"><i class="fas fa-list-alt"></i></a>'
    div += '</span>'
    div += '</div>'
    div += '</div>'
    var actionOption_id = jeedomUtils.uniqId()
    div += '<div class="col-sm-6 actionOptions" id="' + actionOption_id + '"></div>'

    $('#div_' + _type).append(div)
    $('#div_' + _type + ' .' + _type + '').last().setValues(_action, '.expressionAttr')

    if (is_array(actionOptions)) {
        actionOptions.push({
            expression: init(_action.cmd),
            options: _action.options,
            id: actionOption_id
        })
    }
}
$("#div_action").sortable({ axis: "y", cursor: "move", items: ".action", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true })

$('.bt_addAction').off('click').on('click', function () {
    addAction({}, 'action')
})


$("body").off('click', ".listAction").on('click', ".listAction", function () {
    var type = $(this).attr('data-type')
    var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]')
    jeedom.getSelectActionModal({}, function (result) {
        el.value(result.human)
        jeedom.cmd.displayActionOption(el.value(), '', function (html) {
            el.closest('.' + type).find('.actionOptions').html(html)
            jeedomUtils.taAutosize()
        })
    })
})

$("body").off('click', ".listCmdAction").on('click', ".listCmdAction", function () {
    var type = $(this).attr('data-type')
    var el = $(this).closest('.' + type).find('.expressionAttr[data-l1key=cmd]')
    jeedom.cmd.getSelectModal({
        cmd: {
            type: 'action'
        }
    }, function (result) {
        el.value(result.human)
        jeedom.cmd.displayActionOption(el.value(), '', function (html) {
            el.closest('.' + type).find('.actionOptions').html(html)
            jeedomUtils.taAutosize()
        })
    })
})

$("body").off('click', '.bt_removeAction').on('click', '.bt_removeAction', function () {
    var type = $(this).attr('data-type')
    $(this).closest('.' + type).remove()
})


function saveEqLogic(_eqLogic) {
    if (!isset(_eqLogic.configuration)) {
        _eqLogic.configuration = {}
    }

    _eqLogic.configuration.actions = []
    $('#div_action').each(function () {
        let actions = $(this).getValues('.actionAttr')
        actions = $(this).find('.action').getValues('.expressionAttr')
        _eqLogic.configuration.actions.push(actions)
    })
    return _eqLogic
}

document.getElementById('gotoEvents').addEventListener('click', function () {
    jeedomUtils.loadPage("index.php?v=d&m=frigate&p=events");
});





function addOrRemoveClass(element, className, isAdd) {
    const tabs = document.getElementsByClassName(element);
    for (const tab of tabs) {
        if (isAdd) {
            tab.classList.add(className);
        } else {
            tab.classList.remove(className);
        }
    }
}

function updateImage() {
    var imgUrl = document.getElementById('cameraUrlInput').value;
    var imgElement = document.getElementById('imgFrigate');

    if (imgUrl) {
        // Add a query parameter to force image reload
        imgElement.src = imgUrl + "?timestamp=" + new Date().getTime();
    }
}


function printEqLogic(_eqLogic) {
    if (_eqLogic && _eqLogic.logicalId) {
        if (_eqLogic.logicalId === "eqFrigateEvents" || _eqLogic.logicalId === "eqFrigateStats") {
            addOrRemoveClass('eqFrigate', 'jeedisable', true);
        } else {
            addOrRemoveClass('eqFrigate', 'jeedisable', false);
        }
    } else {
        addOrRemoveClass('eqFrigate', 'jeedisable', false);
    }

    $('#div_action').empty()
    ACTIONS_LIST = []
    if (isset(_eqLogic.configuration) && isset(_eqLogic.configuration.actions)) {
        actionOptions = []
        console.log(_eqLogic.configuration.actions);
        for (var i in _eqLogic.configuration.actions[0]) {
            addAction(_eqLogic.configuration.actions[0][i], "action")
        }
        ACTIONS_LIST = null
        jeedom.cmd.displayActionsOption({
            params: actionOptions,
            async: false,
            error: function (error) {
                $('#div_alert').showAlert({ message: error.message, level: 'danger' })
            },
            success: function (data) {
                for (var i in data) {
                    $('#' + data[i].id).append(data[i].html.html)
                }
                jeedomUtils.taAutosize()
            }
        })
    }

   // var cameraUrl = $('.eqLogicAttr[data-l1key=configuration][data-l2key=img]').value();
   // $('#imgFrigate').attr("src", cameraUrl);


    // Update the image immediately when the input value changes
    document.getElementById('cameraUrlInput').addEventListener('input', updateImage);

    var imageUpdateInterval;
    // Update the image immediately when the page loads
    document.addEventListener('DOMContentLoaded', function () {
        updateImage();

        // Set an interval to update the image every 0 seconds
        imageUpdateInterval = setInterval(updateImage, 2000);
        // Clear the interval when the page is being unloaded
        window.addEventListener('beforeunload', function () {
            clearInterval(imageUpdateInterval);
        });
    });
}

document.getElementById('searchAndCreate').addEventListener('click', function () {
    $.ajax({
        type: "POST",
        url: "plugins/frigate/core/ajax/frigate.ajax.php",
        data: {
            action: "searchAndCreate"
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({ message: data.result, level: 'danger' });
                return;
            } else {
                $('#div_alert').showAlert({
                    message: '{{Création des équipementsréussi.}}',
                    level: 'success'
                });
                window.location.reload(true);
            }
        }
    })
});
