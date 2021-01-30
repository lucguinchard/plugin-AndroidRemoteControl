<?php
/*
 * This file is part of the NextDom software (https://github.com/NextDom or http://nextdom.github.io).
 * Copyright (c) 2018 NextDom.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 2.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

if (!isConnect('admin')) {
	throw new \Exception('{{401 - Accès non autorisé}}');
}

$pluginName = init('m');
$plugin = plugin::byId($pluginName);
sendVarToJS('eqType', $plugin->getId());
$eqLogicList = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fa fa-cog"></i> {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction logoPrimary" data-action="gotoPluginConf">
				<i class="fa fa-wrench"></i>
				<br>
				<span>{{Configuration}}</span>
			</div>
			<div class="cursor eqLogicAction" data-action="add">
				<i class="fa fa-plus-circle"></i>
				<br>
				<span>{{Ajouter}}</span>
			</div>
		</div>
		<legend><img style="width:40px" src="<?= $plugin->getPathImgIcon() ?>"/> {{Mes appareils}}</legend>
		<?php if (count($eqLogicList) == 0) { ?>
			<center>
				<span style='color:#767676;font-size:1.2em;font-weight: bold;'>{{Vous n’avez pas encore d’appareil, cliquez sur configuration et cliquez sur synchroniser pour commencer}}</span>
			</center>
		<?php } else { ?>
			<input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
			<div class="eqLogicThumbnailContainer">
				<?php
				foreach ($eqLogicList as $eqLogic) {
					$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard'; ?>
					<div class="eqLogicDisplayCard cursor <?= $opacity ?>" data-eqLogic_id="<?= $eqLogic->getId() ?>">
					<img src="<?= $eqLogic->getImage() ?>" />
					<br/>
					<span class="name"><?= $eqLogic->getHumanName(true, true) ?></span>
					</div>
				<?php } ?>
			</div>
		<?php } ?>
	</div>

	<div class="col-xs-12 eqLogic"
		 style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
		<a class="btn btn-success eqLogicAction pull-right" data-action="save">
			<i class="fa fa-check-circle"></i> {{Sauvegarder}}
		</a>
		<a class="btn btn-danger eqLogicAction pull-right" data-action="remove">
			<i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
		<a class="btn btn-default eqLogicAction pull-right" data-action="configure">
			<i class="fa fa-cogs"></i> {{Configuration avancée}}
		</a>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation">
				<a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay">
					<i class="fa fa-arrow-circle-left"></i>
				</a>
			</li>
			<li role="presentation" class="active">
				<a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab">
					<i class="fas fa-tachometer-alt"></i> {{Equipement}}
				</a>
			</li>
			<li role="presentation">
				<a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab">
					<i class="fa fa-list-alt"></i> {{Commandes}}
				</a>
			</li>
			<li role="presentation">
				<a href="#apptab" aria-controls="profile" role="tab" data-toggle="tab">
					<i class="fa fa-icons"></i> {{Liste des applications}}
				</a>
			</li>
		</ul>
		<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br/>

				<div class="col-md-6">
					<div class="box">
						<div class="box-header backgroundColor">
							<h3 class="eqlogic-box-title">{{ Configuration générale }}</h3>
						</div>
						<div class="box-body">
							<form class="form-horizontal">
								<fieldset>
									<legend>{{Général}}</legend>
									<div class="form-group">
										<label class="col-sm-5 control-label">{{Nom de l'équipement}}</label>
										<div class="col-sm-7">
											<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
											<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-5 control-label" >{{Objet parent}}</label>
										<div class="col-sm-7">
											<select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
												<option value="">{{Aucun}}</option>
												<?php foreach (jeeObject::all() as $object) { ?>
													<option value="<?= $object->getId() ?>"><?= $object->getName() ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-5 control-label">{{Catégorie}}</label>
										<div class="col-sm-7">
											<?php foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) { ?>
												<label class="checkbox-inline">
													<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="<?= $key ?>" /> <?= $value['name'] ?>
												</label>
											<?php } ?>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-5 control-label">{{Commentaire}}</label>
										<div class="col-sm-7">
											<textarea class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="commentaire" ></textarea>
										</div>
									</div>
									<div class="form-group">
										<label class="col-md-5 control-label"></label>
										<div class="col-md-7">
											<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
											<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
										</div>
									</div>

								</fieldset>
							</form>
						</div>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="box">
						<div class="box-header backgroundColor">
							<h3 class="eqlogic-box-title">{{ Configuration équipement }}</h3>
						</div>
						<div class="box-body">
							<form class="form-horizontal">
								<fieldset>
									<legend>{{Paramètres}}</legend>
									<div class="form-group">
										<label class="col-sm-5 control-label">{{Assistant}}</label>
										<div class="col-sm-7">
											<a class="btn btn-infos" id="bt_configureAdb"><i class="fa fa-android"></i> {{Connecter un appareil Android}}
											</a>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-5 control-label">{{Methode de connection}}</label>
										<div class="col-sm-7">
											<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="type_connection" title="{{Veuillez préciser la methode de connection our votre appareil.}}">
												<option value="TCPIP">TCPIP</option>
												<option value="USB">USB</option>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-5 control-label">{{Adresse IP}}</label>
										<div class="col-sm-7">
											<input id="ip_address" data-inputmask="'alias': 'ip'" data-mask="" type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ip_address"/>
										</div>
									</div>
								</fieldset>
								</br>
								<fieldset>
									<legend>{{Informations}}</legend>
									<div class="alert alert-info">
										{{Le choix de la connexion dépend principalement de votre appareil Android. Il y a des avantages et inconvénients pour chaque :<br>
										- USB : nécessite un cable et par conséquent que votre Android soit à proximité de votre Jeedom<br>
										- ADB : Ne nécessite aucune application tierce sur votre Android mais en fonction des équipements la connexion peut être capricieuse<br>
										- SSH : A venir (en cours d’étude de faisabilité)<br>}}
									</div>
									<div class="alert alert-danger">
										{{Si vous choisissez la connexion USB, un seul périphérique peut-être contrôlé. Le plugin ne gère pas la connexion USB et TCPIP en même temps}}
									</div>
								</fieldset>
							</form>
						</div>
						<span id="serviceName" class="eqLogicAttr" data-l1key="configuration" data-l2key="serviceName" style="display:none;"></span>
					</div>

				</div>
			</div>
			<div role="tabpanel" class="tab-pane" id="commandtab">
				<table id="table_cmd" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th>{{Nom}}</th><th>{{Type}}</th><th>{{Afficher}}</th><th>{{Action}}</th>
							</tr>
						</thead>
								<tbody>
					</tbody>
				</table>
			</div>

			<div role="tabpanel" class="tab-pane" id="apptab">
				<div class="alert alert-info">
					{{Attention, il faut veiller à sélectionner le type "action" et le sous-type "defaut" lors de la création d'une nouvelle application}}
				</div>
				<a class="btn btn-success btn-sm cmdAction pull-right addAppli" data-action="add" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Applications}}</a><br/><br/>
				<table id="table_appli" class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th>{{Icon}}</th><th>{{Nom}}</th><th>{{Type}}</th><th>{{Commande}}</th><th>{{Afficher}}</th><th>{{Action}}</th>
										</tr>
									</thead>
									<tbody>
						</tbody>
					</table>
			</div>
		</div>
	</div>
</div>
</div>

<?php 
include_file('desktop', $pluginName, 'js', $pluginName);
include_file('core', 'plugin.template', 'js');
