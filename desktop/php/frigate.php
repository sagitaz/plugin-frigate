<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
// Déclaration des variables obligatoires
$plugin = plugin::byId('frigate');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
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
        </div>
        <legend><i class="fas fa-table"></i> {{Mes équipements}}</legend>
        <?php
        if (count($eqLogics) == 0) {
            echo '<br><div class="text-center" style="font-size:1.2em;font-weight:bold;">{{Aucun équipement Frigate trouvé, cliquer sur "Ajouter" pour commencer}}</div>';
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
            <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" id="gotoHome"><i class="fas fa-arrow-circle-left"></i></a></li>
            <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
            <li role="presentation"><a href="#commandtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-list"></i> {{Commandes}}</a></li>
            <li role="presentation" class="eqFrigate"><a href="#actionsTab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-flag"></i> {{Action(s)}}</a></li>
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
                                <label class="col-sm-4 control-label">{{bbox}}</label>
                                <div class="col-sm-8">
                                    <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="bbox">
                                </div>
                            </div>
                            <div class="form-group eqFrigate">
                                <label class="col-sm-4 control-label">{{timestamp}}</label>
                                <div class="col-sm-8">
                                    <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="timestamp">
                                </div>
                            </div>
                            <div class="form-group eqFrigate">
                                <label class="col-sm-4 control-label">{{zones}}</label>
                                <div class="col-sm-8">
                                    <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="zones">
                                </div>
                            </div>
                            <div class="form-group eqFrigate">
                                <label class="col-sm-4 control-label">{{mask}}</label>
                                <div class="col-sm-8">
                                    <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="mask">
                                </div>
                            </div>
                            <div class="form-group eqFrigate">
                                <label class="col-sm-4 control-label">{{motion}}</label>
                                <div class="col-sm-8">
                                    <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="motion">
                                </div>
                            </div>
                            <div class="form-group eqFrigate">
                                <label class="col-sm-4 control-label">{{regions}}</label>
                                <div class="col-sm-8">
                                    <input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="regions">
                                </div>
                            </div>
                        </div>

                        <!-- Partie droite de l'onglet "Équipement" -->
                        <!-- Affiche un champ de commentaire par défaut mais vous pouvez y mettre ce que vous voulez -->
                        <div class="col-lg-6 eqFrigate">
                            <div>
                                <div class="pull-left">
                                    <legend><i class="fas fa-info"></i> {{Visualisation}}</legend>
                                </div>
                                <?php

                                $name = '';
                                try {
                                    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                                        $eqLogicId = intval($_GET['id']);
                                        $equipment = eqLogic::byId($eqLogicId);

                                        if ($equipment) {
                                            $configuration = $equipment->getConfiguration();
                                            if (isset($configuration['name'])) {
                                                $name = $configuration['name'];
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
                <a class="btn btn-default btn-sm pull-right cmdAction" data-action="add" style="margin-top:5px;"><i class="fas fa-plus-circle"></i> {{Ajouter une commande}}</a>
                <br><br>
                <div class="table-responsive">
                    <table id="table_cmd" class="table table-bordered table-condensed">
                        <thead>
                            <tr>
                                <th class="hidden-xs" style="min-width:50px;width:70px;">ID</th>
                                <th style="min-width:200px;width:350px;">{{Nom}}</th>
                                <th>{{Type}}</th>
                                <th style="min-width:260px;">{{Options}}</th>
                                <th>{{Etat}}</th>
                                <th style="min-width:80px;width:200px;">{{Actions}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div><!-- /.tabpanel #commandtab-->
            <div role="tabpanel" class="tab-pane eqFrigate" id="actionsTab">
                <div class="actionAttr form-group" id="actionTab">
                    <br>
                    <div class="alert alert-info">
                        {{Vous pouvez utiliser les variables suivantes}} :<br>
                        #event_id#, #camera#, #score#, #has_clip#, #has_snapshot#, #top_score#, #zones#, #snapshot#, #clip#, #snapshot_path#, #clip_path#, #label#, #start#, #end#, #duree#
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

<!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, id_du_plugin) -->
<?php include_file('desktop', 'frigate', 'js', 'frigate'); ?>
<!-- Inclusion du fichier javascript du core - NE PAS MODIFIER NI SUPPRIMER -->
<?php include_file('core', 'plugin.template', 'js'); ?>
