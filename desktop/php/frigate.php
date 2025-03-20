<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
// Déclaration des variables obligatoires
$plugin = plugin::byId('frigate');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());

$url = config::byKey('URL', 'frigate');
$port = config::byKey('port', 'frigate');
$urlFrigate = "http://" . $url . ":" . $port;
sendVarToJS('frigateURL', $urlFrigate);
$urlExterne = config::byKey('URLexterne', 'frigate');
sendVarToJS('frigateURLexterne', $urlExterne);

$refresh = config::byKey('refresh_snapshot', 'frigate') * 1000;
sendVarToJS('refresh', $refresh);
?>
<style type="text/css">
    .jeedisable {
        display: none !important;
    }
</style>
<div class="row row-overflow">
    <!-- Page d'accueil du plugin -->
    <div class="col-xs-12 eqLogicThumbnailDisplay">
        <legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
        <!-- Boutons de gestion du plugin -->
        <div class="eqLogicThumbnailContainer">
            <div class="cursor eqLogicAction logoPrimary" id="searchAndCreate">
                <i class="fas fa-video"></i>
                <br>
                <span>{{Rechercher}}</span>
            </div>
            <div class="cursor eqLogicAction logoSecondary" id="gotoEvents">
                <i class="fas fa-calendar"></i>
                <br>
                <span>{{Events}}</span>
            </div>
            <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
                <i class="fas fa-wrench"></i>
                <br>
                <span>{{Configuration}}</span>
            </div>
            <div class="cursor eqLogicAction logoSecondary" id="restartFrigate">
                <i class="fas fa-sync"></i>
                <br>
                <span>{{Redémarrer Frigate}}</span>
            </div>
            <div class="cursor eqLogicAction logoSecondary" id="gotoFrigate" title="{{Disponible seulement depuis une connexion interne}}">
                <i class="fas fa-external-link-alt"></i>
                <br>
                <span>{{Serveur Frigate}}</span>
            </div>
            <div class="cursor eqLogicAction info" id="bt_discord" title="{{Posez vos questions dans le salon dédié, support officiel}}.">
                <i class="fab fa-discord"></i>
                <br>
                <span>{{aide Discord}}</span>
            </div>
            <div class="cursor eqLogicAction warning" id="editConfiguration" title="{{Editer le fichier de configuration Frigate}}.">
                <i class="far fa-file-alt"></i>
                <br>
                <span>{{Configuration Frigate}}</span>
            </div>
            <div class="cursor eqLogicAction logoSecondary" id="frigateLogs" title="{{Voir les différents logs du serveur  Frigate}}.">
                <i class="far fa-file-alt"></i>
                <br>
                <span>{{Logs Frigate}}</span>
            </div>
        </div>

        <?php
        if (count($eqLogics) != 0) {
            $version = config::byKey('frigate_version', 'frigate') ?? "version non trouvée";
            $maj = config::byKey('frigate_maj', 'frigate') ?? 0;
            echo '<legend>';
            if (!$maj) {
                echo 'Frigate <span class="success" style="font-size:1.2em;font-weight:bold;">' . $version . '</span>';
            } else {
                echo 'Frigate <span class="warning" style="font-size:1.2em;font-weight:bold;" title="{{une mise à jour est disponible}}">' . $version . '</span>';
            }
            echo '</legend>';
        }
        ?>
        <legend><i class="fas fa-table"></i> {{Mes équipements}}</legend>
        <?php
        if (count($eqLogics) == 0) {
            echo '<br><div class="text-center" style="font-size:1.2em;font-weight:bold;">{{Aucun équipement Frigate trouvé, cliquez sur "Rechercher" et patientez, cela peut être long si beaucoup de caméras}}</div>';
        } else {
            // Champ de recherche
            echo '<div class="input-group" style="margin:5px;">';
            echo '<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic">';
            echo '<div class="input-group-btn">';
            echo '<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>';
            echo '<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>';
            echo '</div>';
            echo '</div>';
            // Liste des équipements du plugin
            echo '<div class="eqLogicThumbnailContainer">';
            foreach ($eqLogics as $eqLogic) {
                $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
                echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
                echo '<img src="' . $eqLogic->getImage() . '"/>';
                echo '<br>';
                echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
                echo '<span class="hiddenAsCard displayTableRight hidden">';
                echo ($eqLogic->getIsVisible() == 1) ? '<i class="fas fa-eye" title="{{Equipement visible}}"></i>' : '<i class="fas fa-eye-slash" title="{{Equipement non visible}}"></i>';
                echo '</span>';
                echo '</div>';
            }
            echo '</div>';
        }
        ?>
    </div> <!-- /.eqLogicThumbnailDisplay -->

    <!-- Page de présentation de l'équipement -->
    <div class="col-xs-12 eqLogic" style="display: none;">
        <!-- barre de gestion de l'équipement -->
        <div class="input-group pull-right" style="display:inline-flex;">
            <span class="input-group-btn">
                <!-- Les balises <a></a> sont volontairement fermées à la ligne suivante pour éviter les espaces entre les boutons. Ne pas modifier -->
                <a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
                </a><a class="btn btn-sm btn-default eqLogicAction" data-action="copy"><i class="fas fa-copy"></i><span class="hidden-xs"> {{Dupliquer}}</span>
                </a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
                </a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}
                </a>
            </span>
        </div>
        <!-- Onglets -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
            <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
            <li role="presentation" class="eqActions"><a href="#actionsTab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-flag"></i> {{Action(s)}}</a></li>
            <li role="presentation"><a href="#infostab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-list"></i> {{Infos}}</a></li>
            <li role="presentation"><a href="#commandtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-list"></i> {{Commandes}}</a></li>
            <li role="presentation" class="eqFrigate"><a href="#ptztab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-list"></i> {{PTZ & HTTP}}</a></li>
            <li role="presentation" class="eqStats"><a href="#statstab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-list"></i> {{Statistiques}}</a></li>
        </ul>
        <div class="tab-content">
            <!-- Onglet de configuration de l'équipement -->
            <div role="tabpanel" class="tab-pane active" id="eqlogictab">
                <!-- Partie gauche de l'onglet "Equipements" -->
                <!-- Paramètres généraux et spécifiques de l'équipement -->
                <form class="form-horizontal">
                    <fieldset>
                        <div class="col-lg-6">
                            <legend><i class="fas fa-wrench"></i> {{Paramètres généraux}}</legend>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">{{Nom de l'équipement}}</label>
                                <div class="col-sm-6">
                                    <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display:none;">
                                    <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">{{Objet parent}}</label>
                                <div class="col-sm-6">
                                    <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                                        <option value="">{{Aucun}}</option>
                                        <?php
                                        $options = '';
                                        foreach ((jeeObject::buildTree(null, false)) as $object) {
                                            $options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
                                        }
                                        echo $options;
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">{{Catégorie}}</label>
                                <div class="col-sm-6">
                                    <?php
                                    foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                                        echo '<label class="checkbox-inline">';
                                        echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" >' . $value['name'];
                                        echo '</label>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-4 control-label">{{Options}}</label>
                                <div class="col-sm-6">
                                    <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked>{{Activer}}</label>
                                    <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked>{{Visible}}</label>
                                </div>
                            </div>
                            <legend class="eqFrigate"><i class="fas fa-cogs"></i> {{Paramètres de la caméra}}</legend>
                            <div class="form-group eqFrigate">
                                <label class="col-sm-4 control-label">{{Identifiant}}

                                    <sup><i class="fas fa-question-circle tooltips" title="{{Identifiant d'accès a votre caméra, seulement pour les commandes HTTP}}"></i></sup>
                                </label>
                                <div class="col-sm-6">
                                    <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="userName" placeholder="{{Identifiant}}">
                                </div>
                            </div>
                            <div class="form-group eqFrigate">
                                <label class="col-sm-4 control-label">{{Mot de passe}}
                                    <sup><i class="fas fa-question-circle tooltips" title="{{Mot de passe d'accès à votre caméra, seulement pour les commandes HTTP}}"></i></sup>

                                </label>
                                <div class="col-sm-6 input-group">
                                    <input type="text" class="inputPassword eqLogicAttr form-control roundedLeft" data-l1key="configuration" data-l2key="password" placeholder="Mot de passe" />
                                    <span class="input-group-btn">
                                        <a class="btn btn-default form-control bt_showPass roundedRight"><i class="fas fa-eye"></i></a>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group eqFrigate">
                                <label class="col-sm-4 control-label">{{Afficher sur le panel}}</label>
                                <div class="col-sm-6">
                                    <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="panel"></label>
                                </div>
                            </div>
                            <div class="form-group eqFrigate">
                                <label class="col-sm-4 control-label">{{Flux vidéo}}
                                    <sup><i class="fas fa-question-circle tooltips" title="{{Lien vers votre flux vidéo si celui par défaut ne convient pas. voir documentation.}}"></i></sup>
                                </label>
                                <div class="col-sm-6">
                                    <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cameraStreamAccessUrl" placeholder="{{Lien vers votre flux vidéo}}">
                                </div>
                            </div>
                            <div class="form-group eqFrigate ptz-options">
                                <label class="col-sm-4 control-label">{{Nombre de preset}}</label>
                                <div class="col-sm-6">
                                    <input type="number" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="presetMax" placeholder="{{Nombre de preset à importer}}" min="0" max="10">
                                </div>
                            </div>

                            <div class="form-group eqEvents">
                                <label class="col-sm-4 control-label">{{Autoriser les actions}}</label>
                                <div class="col-sm-6">
                                    <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="autorizeActions" title="{{les actions sont exécutées même s'il y en a sur les caméras}}"></label>
                                </div>
                            </div>
                        </div>

                        <!-- Partie droite de l'onglet " Équipement" -->
                        <!-- Affiche un champ de commentaire par défaut mais vous pouvez y mettre ce que vous voulez -->
                        <div class="col-lg-6 eqFrigate">
                            <div class="col-lg-12 pull-left">
                                <legend><i class="fas fa-info" title="{{sauvegarder après tout changement}}"></i> {{Visualisation}}</legend>
                            </div>


                            <div class="form-group eqFrigate">
                                <label class="col-sm-4 control-label bbox-configuration"><span>{{bbox}}</span>
                                    <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="bbox"></label>
                                <label class="col-sm-4 control-label timestamp-configuration"><span>{{timestamp}}</span>
                                    <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="timestamp"></label>
                                <label class="col-sm-4 control-label zones-configuration"><span>{{zones}}</span>
                                    <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="zones"></label>
                                <label class="col-sm-4 control-label mask-configuration"><span>{{mask}}</span>
                                    <input type="checkbox" class="eqLogicAttr mask-checkbox" data-l1key="configuration" data-l2key="mask"></label>
                                <label class="col-sm-4 control-label motion-configuration"><span>{{motion}}</span>
                                    <input type="checkbox" class="eqLogicAttr motion-checkbox" data-l1key="configuration" data-l2key="motion"></label>
                                <label class="col-sm-4 control-label regions-configuration"><span>{{régions}}</span>
                                    <input type="checkbox" class="eqLogicAttr regions-checkbox" data-l1key="configuration" data-l2key="regions"></label>
                            </div>

                            <div>
                                <?php

                                $name = '';
                                $conditionIf = '';
                                $evaluateExpression = '';

                                try {
                                    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                                        $eqLogicId = intval($_GET['id']);
                                        $equipment = eqLogic::byId($eqLogicId);

                                        if ($equipment) {
                                            $configuration = $equipment->getConfiguration();
                                            if (isset($configuration['name'])) {
                                                $name = $configuration['name'];
                                            }
                                            if (isset($configuration['conditionIf'])) {
                                                $conditionIf = $configuration['conditionIf'];
                                                $evaluateExpression = jeedom::evaluateExpression($conditionIf);
                                            }
                                        }
                                    }
                                } catch (Exception $e) {
                                    //echo "Erreur : " . $e->getMessage();
                                }

                                if ($name !== '') {
                                    echo '<div class="pull-right">
                                        <a class="btn btn-success eqLogicAction" onclick="gotoCameraEvents(\'' . $name . '\')" title="{{Afficher les évènements de la caméra}}">
                                        <i class="fas fa-window-restore"></i>&nbsp;Evènements</a>
                                    </div>';
                                }

                                ?>

                            </div>
                            <div class="form-group">
                                <div class="col-sm-12" style="display: none;">
                                    <input type="text" id="cameraUrlInput" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="img">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <img id="imgFrigate" class="img-responsive" src="" />
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div><!-- /.tabpanel #eqlogictab-->


            <!-- Onglet des commandes de l'équipement -->
            <div role="tabpanel" class="tab-pane" id="commandtab">
                <br><br>
                <div class="table-responsive">
                    <table id="table_cmd" class="table table-bordered table-condensed">
                        <thead>
                            <tr>
                                <th class="col-xs-1">{{ID}}</th>
                                <th class="col-xs-5">{{Nom}}</th>
                                <th class="col-xs-1">{{Paramètres}}</th>
                                <th class="col-xs-1"></th>
                                <th class="col-xs-3">{{Valeur}}</th>
                                <th class="col-xs-1">{{Action}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div><!-- /.tabpanel #commandtab-->

            <div role="tabpanel" class="tab-pane" id="infostab">
                <br><br>
                <div class="table-responsive">
                    <table id="table_infos" class="table table-bordered table-condensed">
                        <thead>
                            <tr>
                                <th class="col-xs-1">{{ID}}</th>
                                <th class="col-xs-5">{{Nom}}</th>
                                <th class="col-xs-1">{{Paramètres}}</th>
                                <th class="col-xs-1"></th>
                                <th class="col-xs-3">{{Valeur}}</th>
                                <th class="col-xs-1">{{Action}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div><!-- /.tabpanel #infostab-->

            <div role="tabpanel" class="tab-pane" id="ptztab">
                <a class="btn btn-primary btn-sm pull-right cmdAction" id="addCmdHttp" style="margin-top:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une commande HTTP}}</a>
                <br><br>
                <div class="table-responsive">
                    <table id="table_ptz" class="table table-bordered table-condensed">
                        <thead>
                            <tr>
                                <th class="col-xs-1">{{ID}}</th>
                                <th class="col-xs-5">{{Nom}}</th>
                                <th class="col-xs-1">{{Paramètres}}</th>
                                <th class="col-xs-1"></th>
                                <th class="col-xs-3">{{Valeur}}</th>
                                <th class="col-xs-1">{{Action}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div><!-- /.tabpanel #ptztab-->

            <div role="tabpanel" class="tab-pane" id="statstab">
                <br><br>
                <div class="table-responsive">
                    <table id="table_stats" class="table table-bordered table-condensed">
                        <thead>
                            <tr>
                                <th class="col-xs-1">{{ID}}</th>
                                <th class="col-xs-5">{{Nom}}</th>
                                <th class="col-xs-1">{{Paramètres}}</th>
                                <th class="col-xs-1"></th>
                                <th class="col-xs-3">{{Valeur}}</th>
                                <th class="col-xs-1">{{Action}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div><!-- /.tabpanel #statstab-->

            <div role="tabpanel" class="tab-pane eqActions" id="actionsTab">
                <div class="actionAttr form-group" id="actionTab">
                    <br>
                    <div class="form-group">
                        <label class="col-sm-1 control-label">{{Ne rien faire si}}</label>
                        <div class="col-sm-6">
                            <div class="input-group">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="conditionIf" />
                                <span class="input-group-btn">
                                    <a class="btn listCmdInfo btn-default roundedRight" data-atCaret="1"><i class="fa fa-list-alt"></i></a>
                                </span>
                            </div>
                        </div>
                        <label class="col-sm-1 control-label">{{Etat actuel : }}</label>
                        <label class="col-sm-2 control-label"><?= $evaluateExpression ?></label>
                    </div>
                    <br>
                    <br>
                    <div class="alert alert-info">
                        {{Vous pouvez utiliser les variables suivantes}} :<br>
                        #time#, #event_id#, #camera#, #cameraId#, #score#, #has_clip#, #has_snapshot#, #top_score#, #zones#, #label#, #start#, #end#, #duree#, #type#
                        <br>
                        #snapshot#, #clip#, #thumbnail#, #snapshot_path#, #clip_path#, #thumbnail_path#, #preview#, #jeemate#
                        <a class="btn btn-success btn-sm pull-right bt_addAction"><i class="fas fa-plus-circle"></i> {{Ajouter une action}}</a>
                    </div>
                    <form class="form-horizontal">
                        <fieldset>
                            <div id="div_action" class="col-xs-12" style="padding:10px;margin-bottom:15px;">
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div><!-- /.tabpanel  #actiontab-->


        </div><!-- /.tab-content -->
    </div><!-- /.eqLogic -->
</div><!-- /.row row-overflow -->

<?php include_file('desktop', 'select2', 'css', 'frigate'); ?>
<?php include_file('desktop', 'select2.custom', 'css', 'frigate'); ?>
<?php include_file('desktop', 'frigate', 'css', 'frigate'); ?>

<?php include_file('desktop', 'select2', 'js', 'frigate'); ?>
<?php include_file('desktop', 'frigate', 'js', 'frigate'); ?>
<?php include_file('desktop', 'fileSaver', 'js', 'frigate'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>