il.CourseWizardModalFunctions = (function (scope) {

	let pub = {};
	let priv = {};
	priv.isInitialized = false;

	let storageEngine = localStorage;
	let currentWizardObj = null;

	priv.config = {
		saveConfigUrl: '',
		currentPage: '',
		nextPage: '',
		replaceSignal: ''
	}

	let executePostRequest = function(data, url) {
		$.post(url, data.post_data).done(function(response)
		{
			$(document).trigger(priv.config['replaceSignal'], data.signal_data)
		});
	};

	priv.triggerSignal = function(signal_id, event, triggerer, options) {
		$(document).trigger(signal_id, {
			id: signal_id,
			event: 'click',
			triggerer: triggerer,
			options: options
		});
	}

	pub.pushTemplateSelection = function (e) {
		let checked_id = $('#xcwi_template_selection_div_id').find('input:checked').val();

		if(checked_id != null) {
			let nextPageUrl = priv.config['nextPageUrl'] + '&template_ref_id=' + checked_id;
			currentWizardObj['templateRefId'] = checked_id;
			priv.storeCurrentWizardObj()

			priv.triggerSignal(priv.config['replaceSignal'], 'click', $(e), {url: nextPageUrl});

		} else {
		}
	};

	pub.introductionPageFinished = function(e) {
		let skip_introduction = $('#xcwi_skip_introduction').is(":checked");

		let nextPageUrl = priv.config['nextPageUrl'] + '&skip_intro=' + (skip_introduction ? '1' : '0');
		priv.triggerSignal(priv.config['replaceSignal'], 'click', $(e), {url: nextPageUrl});

	}

	pub.pushContentInheritanceSelection = function (e) {
		currentWizardObj.contentInheritance= {};
		 $("#coursewizard").find('form input:checked').each(function(key, value) {
			currentWizardObj.contentInheritance[value.name] = {
				id: value.id,
				value: value.value
			};
		 });
		priv.storeCurrentWizardObj();
		priv.triggerSignal(priv.config['replaceSignal'], 'click', $(e), {url: priv.config['nextPageUrl']});
	};

	pub.loadPreviousPage = function(e) {

		let previousPageUrl = priv.config['previousPageUrl']
		if(currentWizardObj['templateRefId']) {
			previousPageUrl += '&template_ref_id=' + currentWizardObj['templateRefId'];
		}
		priv.triggerSignal(priv.config['replaceSignal'], 'click', $(e), {url: previousPageUrl});
	}

	pub.executeImport = function(e) {
		currentWizardObj["specificSettings"] = 'hello world';
		priv.storeCurrentWizardObj();
		let data = {obj: JSON.stringify(currentWizardObj)};

		$.post(priv.config['executeImportUrl'], data).done(function(response)
		{
			console.log("Course imported!");

			console.log("Deleting obj with target ref: " + currentWizardObj.targetRefId);
			storageEngine.removeItem(currentWizardObj.targetRefId);
			location.reload();
		});
	}

	priv.loadCurrentWizardObj = function (ref_id) {
		let obj = storageEngine.getItem(ref_id);
		if(obj === null) {
			currentWizardObj = {'targetRefId': ref_id};
			priv.storeCurrentWizardObj();
		} else {
			currentWizardObj = JSON.parse(obj);
		}
	}

	priv.storeCurrentWizardObj = function() {
		let json_obj = JSON.stringify(currentWizardObj);
		storageEngine.setItem(currentWizardObj.targetRefId, json_obj);
	}

	pub.printCurrentObj = function() {
		console.log(currentWizardObj);
	}

	pub.printCurrentConf = function() {
		console.log(priv.config);
	}

	priv.closeModalTriggered = function(event, signalData) {
		console.log('Close Modal triggered');
	}

	pub.initNewModalPage = function (config) {
		priv.config = config;

		if(currentWizardObj === null || config.targetRefId !== currentWizardObj.targetRefId) {
			priv.loadCurrentWizardObj(config.targetRefId);
		}

		if(!priv.isInitialized) {
			$("#coursewizard").parents('.modal.il-modal-roundtrip').on("hide.bs.modal", function(){
				$.ajax(priv.config['dismissModalUrl']).done(function() {
					location.reload();
				});
			});
		}

		priv.isInitialized = true;
	}

	return pub;

})(il);