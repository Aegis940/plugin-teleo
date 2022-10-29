<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('teleo');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoPrimary" data-action="add">
				<i class="fas fa-plus-circle"></i>
				<br>
				<span>{{Ajouter}}</span>
			</div>
			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
				<i class="fas fa-wrench"></i>
				<br>
				<span>{{Configuration}}</span>
			</div>
		</div>
		<legend><i class="fas fa-table"></i> {{Mes comptes Veolia}}</legend>
		<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
		<div class="eqLogicThumbnailContainer">
			<?php
			// Affiche la liste des équipements
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
				echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
				echo '<br>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '</div>';
			}
			?>
		</div>
	</div>

	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
		</ul>
		
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br/>
				<form class="form-horizontal">
					<fieldset>
						<div class="col-lg-6">
						<legend><i class="fas fa-wrench"></i> {{Général}}</legend>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Nom de l'équipement Veolia}}</label>
								<div class="col-sm-7">
									<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
									<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement Veolia}}"/>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label" >{{Objet parent}}</label>
								<div class="col-sm-7">
									<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
										<option value="">{{Aucun}}</option>
										<?php
											foreach (jeeObject::all() as $object) {
												echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
											}
										?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Catégorie}}</label>
								<div class="col-sm-9">
									<?php
										foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
										echo '<label class="checkbox-inline">';
										echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
										echo '</label>';
										}
									?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Options}}</label>
								<div class="col-sm-7">
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
								</div>
							</div>
							<br>
							
							<legend><i class="fas fa-user-circle"></i> {{Compte}}</legend>
							
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Site Veolia}}</label>
								<div class="col-sm-7">
									<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="type" id="item-25-1">
										<option value="IDF">{{Veolia Ile de France}}</option>
										<!--<option value="Other">{{Autre site Veolia}}</option>-->
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Identifiant Veolia}}</label>
								<div class="col-sm-7">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="login" placeholder="Identifiant du compte Veolia"/>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Mot de passe Veolia}}</label>
								<div class="col-sm-7">
									<input type="password" autocomplete="new-password" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="password" placeholder="Mot de passe du compte Veolia IDF"/>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Contrat}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Dans le cas où plusieurs contrats sont rattachés au compte, indiquer le numéro de contrat à utiliser sinon laisser le champ vide}}"></i></sup>
								</label>
								<div class="col-sm-7">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="contract_id" placeholder="N° de contrat">
								</div>
							</div>							
							<br>
							
							<legend><i class="fas fa-cogs"></i> {{Paramètres}}</legend>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Répertoire d'export des données}}</label>
								<div class="col-sm-7">
									<input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="outputData" placeholder="Répertoire d'export des données"/>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Connexion au site Veolia}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Cocher la case si la connexion au site Veolia est faite depuis cette machine}}"></i></sup>
								</label>
								<div class="col-sm-1">
									<input type="checkbox" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="connectToVeoliaWebsiteFromThisMachine"/>
								</div>
							</div> 
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Ignorer les relevés estimés}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Les relevés marqués comme estimés ne seront pas importés}}"></i></sup>
								</label>
								<div class="col-sm-1">
									<input type="checkbox" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ignoreEstimation"/>
								</div>
							</div>	
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Forcer la récupération des données}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Cocher la case pour forcer la récupération des données même si déjà présentes}}"></i></sup>
								</label>							
								<div class="col-sm-1">
									<input type="checkbox" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="forceRefresh"/>
								</div>
							</div>
						</div>
						<div class="col-lg-5">
							<legend><i class="fas fa-camera-retro"></i> {{Widget}}</legend>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Template de widget}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Cocher la case pour utiliser le template de widget aux couleurs du compteur Teleo}}"></i></sup>
								</label>
								<div class="col-sm-1">
									<input type="checkbox" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="widgetTemplate"/>
								</div>
							</div>
							<br>
							<div class="form-group" id="templateParams">
								<label class="col-sm-5 control-label">{{Couleur de fond}}
									<sup><i class="fas fa-question-circle tooltips" title="{{Sélectionner la couleur de fond du template de widget}}"></i></sup>
								</label>
								<div class="col-sm-3">
									<input type="color" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="widgetBGColor"/>
								</div>
								<label class="col-sm-2 control-label">{{Transparent}} </label>
								<div class="col-sm-2">
									<input type="checkbox" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="widgetTransparent"/>
								</div>
							</div>
						</div>	
					</fieldset>
				</form>

			</div>
				<div role="tabpanel" class="tab-pane" id="commandtab">
				<!--<a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;">
				<i class="fa fa-plus-circle"></i> {{Commandes}}</a><br/> -->
				<br/>
				<table id="table_cmd" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th>{{Id}}</th>
							<th>{{Nom}}</th>
							<th>{{Type}}</th>
							<th>{{Options}}</th>
							<th>{{Paramètres}}</th>
							<th>{{Action}}</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, nom_du_plugin) -->
<?php include_file('desktop', 'teleo', 'js', 'teleo');?>
<!-- Inclusion du fichier javascript du core - NE PAS MODIFIER NI SUPPRIMER -->
<?php include_file('core', 'plugin.template', 'js');?>