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
function makeTableSortable(tableId) {
    $(tableId).sortable({
        axis: "y",
        cursor: "move",
        items: ".cmd",
        placeholder: "ui-state-highlight",
        tolerance: "intersect",
        forcePlaceholderSize: true
    });
}

makeTableSortable("#table_cmd");
makeTableSortable("#table_infos");
makeTableSortable("#table_ptz");
makeTableSortable("#table_stats");

/* Fonction permettant l'affichage des commandes dans l'équipement */
function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = { configuration: {} }
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {}
    }

    if (isset(_cmd.logicalId) && _cmd.logicalId == 'action_http') {
        var editHTTP = true;
    } else {
        var editHTTP = false;
    }

    let logical = _cmd.logicalId.split('_');
    let type = logical[0];
    let subtype = logical[1];
    let editName = false;

    if (subtype === "preset" || subtype === "http") {
        editName = true;
    }

    if (type === 'link') {
        var tr = '<tr class="cmd hidden" data-cmd_id="' + init(_cmd.id) + '">';
    } else {

        var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    }
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="id" ></span>';
    if (editHTTP) {
        console.log("data-request= " + init(_cmd.configuration['request']));
        let request = init(_cmd.configuration['request']);
        tr += '<a class="btn btn-primary btn-xs cmdAction pull-right" onclick="editHTTP(this)" id="' + init(_cmd.id) + '" data-request="' + request + '"><i class="fa fa-edit"></i></a> ';
    }
    tr += '</td>';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
    if (editName) {
        tr += '<input class="cmdAttr input-xs col-xs-6" data-l1key="name" placeholder="{{Nom de la commande}}">'
    } else {
        tr += '<span class="cmdAttr" data-l1key="name" ></span>';
    }
    tr += '<span class="type hidden" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
    tr += '<span class="subType hidden" subType="' + init(_cmd.subType) + '"></span>';
    tr += '</td>';
    if (!isset(_cmd.type) || _cmd.type == 'action') {
        tr += '<td>';
        tr += '<span><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</span>';
        tr += '</td>';
        tr += '<td>';
        tr += '</td>';
    }
    if (!isset(_cmd.type) || _cmd.type == 'info' && _cmd.subType != 'string') {
        tr += '<td>';
        tr += '<span><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</span>';
        tr += '</td>';
        tr += '<td>';
        tr += '<span><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" />{{Historiser}}</span>';
        tr += '</td>';
    }

    if (_cmd.type == 'info' && _cmd.subType == 'string') {
        tr += '<td>';
        tr += '<span><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked/>{{Afficher}}</span>';
        tr += '</td>';
        tr += '<td>';
        tr += '</td>';
    }


    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>';
    tr += '</td>';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-primary btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-success btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }

    tr += '<i class="fas fa-minus-circle pull-right cmdAction cursor" data-action="remove" title="{{Supprimer la commande}}"></i>'
    tr += '</td>';
    tr += '</tr>';

    if (type === 'hide') {
        // Actions spécifiques pour le type 'hide'
    } else if (type === 'cameras' || type === 'gpu' || type === 'cpu' || type === 'detectors' || type === 'Recordings') {
        printTable(_cmd, tr, "table_stats");
    } else if (type === 'info' || type === 'enable' || type === 'link') {
        printTable(_cmd, tr, "table_infos");
    } else if (type === 'action') {
        if (subtype === 'ptz' || subtype === 'preset' || subtype === 'http') {
            printTable(_cmd, tr, "table_ptz");
        } else {
            printTable(_cmd, tr, "table_cmd");
        }
    }
}

function printTable(_cmd, tr, tableName) {
    $('#' + tableName + ' tbody').append(tr);
    $('#' + tableName + ' tbody tr:last').setValues(_cmd, '.cmdAttr');
    if (isset(_cmd.type)) {
        $('#' + tableName + ' tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    }
    jeedom.cmd.changeType($('#' + tableName + ' tbody tr:last'), init(_cmd.subType));
}

function addAction(_action, _type) {
    if (!isset(_action)) {
        _action = {}
    }
    if (!isset(_action.options)) {
        _action.options = {}
    }

    var div = '<div class="' + _type + '">'
    div += '<div class="form-group rounded" style="margin:10px !important; padding:3px; background-color:var(--el-defaultColor) !important">'
    div += '<div class="col-sm-1">'
    div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="enable" checked title="{{Décocher la case pour désactiver l\'action}}">'
    div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="background" title="{{Cocher la case pour que l\'action s\'exécute en parallèle des autres actions}}">'
    div += '<input type="checkbox" class="expressionAttr" data-l1key="options" data-l2key="actionForced" title="{{Cocher la case pour que l\'action s\'exécute malgré la condition générale}}">'
    div += '</div>'
    div += '<div class="col-sm-1">'
    div += '<input class="expressionAttr form-control cmdAction input-sm"data-l1key="cmdLabelName" placeholder="{{Label}}" data-type="' + _type + '" />'
    div += '<input class="expressionAttr form-control cmdAction input-sm" style="margin-top:10px !important;"  data-l1key="cmdTypeName" placeholder="{{Type}}" data-type="' + _type + '" />'
    div += '</div>'
    div += '<div class="col-sm-1">'
    div += '<input class="expressionAttr form-control cmdAction input-sm" data-l1key="cmdZoneName" placeholder="{{Zone entrée}}" data-type="' + _type + '" />'
    div += '<input class="expressionAttr form-control cmdAction input-sm" style="margin-top:10px !important;" data-l1key="cmdZoneEndName" placeholder="{{Zone sortie}}" data-type="' + _type + '" />'
    div += '</div>'
    div += '<div class="col-sm-3">'
    div += '<div class="input-group">'
    div += '<span class="input-group-btn">'
    div += '<a class="btn btn-default btn-sm roundedLeft" data-type="' + _type + '" title="{{Action exécutée si la condition est remplie}}"><i class="fas fa-cog"></i></a>'
    div += '</span>'
    div += '<input type="text" class="expressionAttr cmdAction form-control input-sm" data-l1key="actionCondition" data-type="' + _type + '" placeholder="{{Condition}}" />'
    div += '<span class="input-group-btn">'
    div += '<a class="btn listCmdInfo btn-sm btn-default roundedRight" data-type="' + _type + '" data-atCaret="1"><i class="fa fa-list-alt"></i></a>'
    div += '</span>'
    div += '</div>'
    div += '</div>'
    div += '<div class="col-sm-2">'
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
    div += '<div class="col-sm-4 actionOptions" id="' + actionOption_id + '"></div>'

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

$('body').off('focusout', ".cmdAction.expressionAttr[data-l1key=cmd]").on('focusout', '.cmdAction.expressionAttr[data-l1key=cmd]', function (event) {
    var type = $(this).attr('data-type')
    var expression = $(this).closest('.' + type).getValues('.expressionAttr')
    var el = $(this)
    jeedom.cmd.displayActionOption($(this).value(), init(expression[0].options), function (html) {
        el.closest('.' + type).find('.actionOptions').html(html)
        jeedomUtils.taAutosize()
    })
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

document.getElementById('gotoFrigate').addEventListener('click', function () {
    if (isConnexionInterne()) {
        window.open(frigateURL, '_blank');
    } else {
        if (frigateURLexterne) {
            window.open(frigateURLexterne, '_blank');
        } else {
            $('#div_alert').showAlert({
                message: '{{Aucune URL externe n\'est configurée.}}',
                level: 'warning'
            });
        }
    }
});
// Vérifier la visibilité du bouton au chargement de la page
window.addEventListener('load', function () {
    const boutonFrigate = document.getElementById('gotoFrigate');

    // Si connexion interne ou URL externe configurée, montrer le bouton
    if (isConnexionInterne() || frigateURLexterne) {
        boutonFrigate.style.display = 'block';
    } else {
        boutonFrigate.style.display = 'none';
    }
});
function isConnexionInterne() {
    return window.location.hostname.startsWith('192.168') ||
        window.location.hostname === 'localhost';
}

document.getElementById('editConfiguration').addEventListener('click', function () {
    bootbox.confirm('{{Configuration avancée, à vos propres risques ! Aucun support ne sera donné !}}', function (result) {
        if (result) {
            $('#md_modal2').dialog({ title: "{{Edition du fichier de configuration Frigate}}" });
            $('#md_modal2').load('index.php?v=d&plugin=frigate&modal=editConfiguration.modal').dialog('open');
        }
    });
});

document.getElementById('frigateLogs').addEventListener('click', function () {
    $('#md_modal2').dialog({ title: "{{Affichage des logs du serveur Frigate}}" });
    $('#md_modal2').load('index.php?v=d&plugin=frigate&modal=frigateLogs.modal').dialog('open');
});

document.getElementById('bt_discord').addEventListener('click', function () {
    window.open('https://discord.gg/PGAPDHhdtC', '_blank');
});

$("#div_mainContainer").off('click', '.listCmdInfo').on('click', '.listCmdInfo', function () {
    var el = $(this).closest('.input-group').find('input.form-control');
    jeedom.cmd.getSelectModal({ cmd: { type: 'info' } }, function (result) {
        el.value(result.human);
    });
});

function gotoCameraEvents(cameraName) {
    jeedomUtils.loadPage("index.php?v=d&m=frigate&p=events&cameras=" + cameraName);
}

function updateMaskVisibility() {
    const maskLabel = document.querySelector('.mask-configuration');
    if (document.querySelector('.mask-checkbox').checked) {
        maskLabel.classList.add('mask-border');
    } else {
        maskLabel.classList.remove('mask-border');
    }
}

function updateMotionVisibility() {
    const motionLabel = document.querySelector('.motion-configuration');
    if (document.querySelector('.motion-checkbox').checked) {
        motionLabel.classList.add('motion-border');
    } else {
        motionLabel.classList.remove('motion-border');
    }
}

function updateRegionsVisibility() {
    const regionsLabel = document.querySelector('.regions-configuration');
    if (document.querySelector('.regions-checkbox').checked) {
        regionsLabel.classList.add('regions-border');
    } else {
        regionsLabel.classList.remove('regions-border');
    }
}

document.querySelector('.mask-checkbox').addEventListener('change', updateMaskVisibility);
document.querySelector('.motion-checkbox').addEventListener('change', updateMotionVisibility);
document.querySelector('.regions-checkbox').addEventListener('change', updateRegionsVisibility);

updateMotionVisibility();
updateRegionsVisibility();

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

function printEqLogic(_eqLogic) {

    let ptz = _eqLogic.configuration.ptz;
    if (ptz === undefined) {
        ptz = false;
    }
    if (ptz) {
        addOrRemoveClass('ptz-options', 'jeedisable', false);
    } else {
        addOrRemoveClass('ptz-options', 'jeedisable', true);
    }

    if (_eqLogic && _eqLogic.logicalId) {
        if (_eqLogic.logicalId === "eqFrigateStats") {
            addOrRemoveClass('eqFrigate', 'jeedisable', true);
            addOrRemoveClass('eqActions', 'jeedisable', true);
            addOrRemoveClass('eqEvents', 'jeedisable', true);
            addOrRemoveClass('eqStats', 'jeedisable', false);
        } else if (_eqLogic.logicalId === "eqFrigateEvents") {
            addOrRemoveClass('eqFrigate', 'jeedisable', true);
            addOrRemoveClass('eqActions', 'jeedisable', false);
            addOrRemoveClass('eqEvents', 'jeedisable', false);
            addOrRemoveClass('eqStats', 'jeedisable', true);
        } else {
            addOrRemoveClass('eqFrigate', 'jeedisable', false);
            addOrRemoveClass('eqActions', 'jeedisable', false);
            addOrRemoveClass('eqEvents', 'jeedisable', true);
            addOrRemoveClass('eqStats', 'jeedisable', false);
        }
    } else {
        addOrRemoveClass('eqFrigate', 'jeedisable', false);
        addOrRemoveClass('eqActions', 'jeedisable', false);
        addOrRemoveClass('eqEvents', 'jeedisable', false);
        addOrRemoveClass('eqStats', 'jeedisable', false);
    }




    if (_eqLogic.logicalId != "eqFrigateStats") {

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
    }

    if (_eqLogic.logicalId === "eqFrigateCamera_" + _eqLogic.configuration.name) {



        const img = $('.eqLogicAttr[data-l1key=configuration][data-l2key=img]').val();
        let imgSrc = "/plugins/frigate/core/ajax/frigate.proxy.php?url=" + img;
        const imgElement = document.getElementById('imgFrigate');
        let intervalId;

        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1 // image considérée visible si au moins 10% est visible
        };

        const observerCallback = (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    startImageFetchInterval();
                } else {
                    stopImageFetchInterval();
                }
            });
        };

        const observer = new IntersectionObserver(observerCallback, observerOptions);
        observer.observe(imgElement);

        function startImageFetchInterval() {
            if (!intervalId) {
                intervalId = setInterval(refreshImage, refresh);
            }
        }

        function stopImageFetchInterval() {
            if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;
            }
        }
        function extractFrigatePart(url) {
            // Définir l'expression régulière pour capturer la partie souhaitée de l'URL
            const regex = /\/api\/([^\/]+)\/latest\.jpg/;

            // Exécuter l'expression régulière sur l'URL
            const match = url.match(regex);

            // Si une correspondance est trouvée, retourner la partie capturée
            if (match && match[1]) {
                return match[1];
            } else {
                // Si aucune correspondance n'est trouvée, retourner null ou une valeur par défaut
                return null;
            }
        }
        function refreshImage() {
            let newSrc = imgSrc + encodeURIComponent("&t=" + new Date().getTime());
            console.log('Refreshing image with URL: ' + decodeURIComponent(newSrc));
            imgElement.src = newSrc;
        }
        refreshImage();
    }

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
            if (data.result == 'URL') {
                $('#div_alert').showAlert({
                    message: '{{L\'URL n\'est pas configurée.}}',
                    level: 'warning'
                });
                return;
            } else if (data.result == 'PORT') {
                $('#div_alert').showAlert({
                    message: '{{Le port n\'est pas configuré.}}',
                    level: 'warning'
                });
                return;
            } else if (data.result == '0') {
                $('#div_alert').showAlert({
                    message: '{{Aucune nouvelle caméra trouvée}}',
                    level: 'success'
                });
                $('#div_alert').showAlert({
                    message: '{{Mise à jour des commandes et statistiques.}}',
                    level: 'success'
                });
            } else {
                $('#div_alert').showAlert({
                    message: '{{Découverte de }}' + data.result + ' équipement(s) caméra réussie.',
                    level: 'success'
                });
                $('#div_alert').showAlert({
                    message: '{{Mise à jour des commandes et statistiques. Cela peut prendre du temps.}}',
                    level: 'success'
                });
                sleep(5000);
                window.location.reload(true);
            }
        }
    })
});

document.getElementById('restartFrigate').addEventListener('click', function () {
    $.ajax({
        type: "POST",
        url: "plugins/frigate/core/ajax/frigate.ajax.php",
        data: {
            action: "restartFrigate"
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            $('#div_alert').showAlert({
                message: '{{Le redémarrage de Frigate est en cours.}}',
                level: 'info'
            });
        }
    })
});

document.getElementById('addCmdHttp').addEventListener('click', function () {
    jeedomUtils.hideAlert()
    const eqlogicId = $('.eqLogicAttr[data-l1key=id]').val();
    let content = '<input class="promptAttr" data-l1key="newCmdName" autocomplete="off" type="text" placeholder="{{Nom de la commande}}">'
    content += '<input class="promptAttr" data-l1key="newLinkHTTP" autocomplete="off" type="text" placeholder="{{URL HTTP de votre commande}}">'
    jeeDialog.prompt({
        title: "{{Ajouter une commande HTTP}}",
        message: content,
        inputType: false,
        callback: function (result) {
            if (result !== null) {
                $.ajax({
                    type: "POST",
                    url: "plugins/frigate/core/ajax/frigate.ajax.php",
                    data: {
                        action: "addCmdHttp",
                        id: eqlogicId,
                        name: result.newCmdName,
                        link: result.newLinkHTTP

                    },
                    dataType: 'json',
                    error: function (request, status, error) {
                        handleAjaxError(request, status, error);
                    },
                    success: function (data) {
                        $('#div_alert').showAlert({
                            message: '{{Création de la commande réussie.}}',
                            level: 'info'
                        });
                    }
                })
            }
        }
    })
});

function editHTTP(cmd) {
    var id = cmd.id; // Récupère l'id
    var data = cmd.getAttribute('data-request'); // Récupère la valeur de data-request

    console.log('ID:', id);
    console.log('Data:', data);

    jeeDialog.prompt({
        title: "{{Modifier la commande HTTP}}",
        inputType: 'input',
        value: data,
        callback: function (result) {
            if (result !== null) {
                $.ajax({
                    type: "POST",
                    url: "plugins/frigate/core/ajax/frigate.ajax.php",
                    data: {
                        action: "editHTTP",
                        id: id,
                        link: result

                    },
                    dataType: 'json',
                    error: function (request, status, error) {
                        handleAjaxError(request, status, error);
                    },
                    success: function (data) {
                        $('#div_alert').showAlert({
                            message: '{{Modification de la commande réussie.}}',
                            level: 'info'
                        });
                    }
                })
            }
        }
    })
}


$(document).ready(function () {
    $('.eqLogicAttr[data-l1key=object_id]').select2();
});