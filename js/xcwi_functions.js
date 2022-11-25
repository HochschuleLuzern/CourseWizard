il.CourseWizardFunctions = (function (scope) {

	let pub = {};
	let priv = {};
	priv.isInitialized = false;

	let storageEngine = localStorage;
	let currentWizardObj = null;

	priv.wizardModalConfig = {
		saveConfigUrl: '',
		currentPage: '',
		nextPage: '',
		replaceSignal: ''
	}

	let executePostRequest = function(data, url) {
		$.post(url, data.post_data).done(function(response)
		{
			$(document).trigger(priv.wizardModalConfig['replaceSignal'], data.signal_data)
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
		let checked_id = $('#' + priv.wizardModalConfig['wizardStepContentDivId']).find('input:checked').val();

		if(checked_id != null) {
			let nextPageUrl = priv.wizardModalConfig['nextPageUrl'] + '&template_ref_id=' + checked_id;
			currentWizardObj['templateRefId'] = checked_id;
			priv.storeCurrentWizardObj()

			priv.showLoadingAnimation(e.target.id);
			priv.triggerSignal(priv.wizardModalConfig['replaceSignal'], 'click', $(e), {url: nextPageUrl});

		} else {
		}
	};

	pub.introductionPageFinished = function(e) {
		let skip_introduction = $('#xcwi_skip_introduction').is(":checked");

		let nextPageUrl = priv.wizardModalConfig['nextPageUrl'] + '&skip_intro=' + (skip_introduction ? '1' : '0');
		priv.showLoadingAnimation(e.target.id);
		priv.triggerSignal(priv.wizardModalConfig['replaceSignal'], 'click', $(e), {url: nextPageUrl});

	}

	pub.pushContentInheritanceSelection = function (e) {
		currentWizardObj.contentInheritance= {};
		 $('#' + priv.wizardModalConfig['wizardStepContentDivId']).find('form input:checked').each(function(key, value) {
			currentWizardObj.contentInheritance[value.name] = {
				id: value.id,
				value: value.value
			};
		 });
		priv.storeCurrentWizardObj();
		priv.showLoadingAnimation(e.target.id);

		priv.triggerSignal(priv.wizardModalConfig['replaceSignal'], 'click', $(e), {url: priv.wizardModalConfig['nextPageUrl']});
	};

	pub.loadPreviousPage = function(e) {

		let previousPageUrl = priv.wizardModalConfig['previousPageUrl']
		if(currentWizardObj['templateRefId']) {
			previousPageUrl += '&template_ref_id=' + currentWizardObj['templateRefId'];
		}
		priv.showLoadingAnimation(e.target.id);
		priv.triggerSignal(priv.wizardModalConfig['replaceSignal'], 'click', $(e), {url: previousPageUrl});
	};

	pub.executeImport = function(e) {
		currentWizardObj["specificSettings"] = {};
		$('#form_'+priv.wizardModalConfig['settingsForm']+' :input').each(function() {
			currentWizardObj["specificSettings"][this.name] = $(this).val()
		});
		priv.storeCurrentWizardObj();
		let data = {obj: JSON.stringify(currentWizardObj)};

		priv.showLoadingAnimation(e.target.id, true);

		$.post(priv.wizardModalConfig['executeImportUrl'], data).done(function(response)
		{
			$('#' + priv.wizardModalConfig['wizardDivId']).html(response);
			storageEngine.removeItem(currentWizardObj.targetRefId);
		});
	};

	priv.showLoadingAnimation = function(button_id, show_spinner = false) {
		il.UI.button.activateLoadingAnimation(button_id);
		$('.modal-footer').children().each(function(number, element) {
			$( element ).addClass('disabled');
		});
		if(show_spinner) {
			$('.xcwi_loading_container').show();
			$('#' + priv.wizardModalConfig['wizardDivId']  + ' .xcwi_step_presentation').hide();
		}
	};

	priv.loadCurrentWizardObj = function (ref_id) {
		let obj = storageEngine.getItem(ref_id);
		if(obj === null) {
			currentWizardObj = {'targetRefId': ref_id};
			priv.storeCurrentWizardObj();
		} else {
			currentWizardObj = JSON.parse(obj);
		}
	};

	priv.storeCurrentWizardObj = function() {
		let json_obj = JSON.stringify(currentWizardObj);
		storageEngine.setItem(currentWizardObj.targetRefId, json_obj);
	};

	pub.initNewModalPage = function (wizardModalConfig) {
		priv.wizardModalConfig = wizardModalConfig;

		if(currentWizardObj === null || wizardModalConfig.targetRefId !== currentWizardObj.targetRefId) {
			priv.loadCurrentWizardObj(wizardModalConfig.targetRefId);
		}

		if(!priv.isInitialized) {
			$('#' + priv.wizardModalConfig['wizardDivId'])
			.parents('.modal.il-modal-roundtrip')
			.on(
				"hide.bs.modal",
				function(){
					$.ajax(priv.wizardModalConfig['dismissModalUrl'])
					.done(
						function() {
							location.reload();
						}
					);
				}
			);
		}

		priv.isInitialized = true;
	};

	pub.switchViewControlContent = function(e, id) {
		// e['target'] is the id for the button which was clicked (e.g. 'button#il_ui_fw_1234')
		obj = $(e['target']);
		// Sets all buttons to the 'unclicked' state
		obj.siblings().removeClass('engaged disabled ilSubmitInactive').attr('aria-pressed', 'false');
		obj.siblings().removeAttr('disabled');
		// Sets the clicked button into the 'clicked' state
		obj.addClass('engaged ilSubmitInactive').attr('aria-pressed', 'true');
		// Hide all instruction divs at first
		$('.xcwi-template-selection__subpage').hide();
		// Show the div which is given as an argument
		$('#'+id).show();
	};

	pub.switchSelectedTemplate = function(obj) {
		selected_obj = $(obj);
		let container = selected_obj.closest('.xcwi-template-selection__radio-group');
		container.find('.crs_tmp_checked').removeClass('crs_tmp_checked');
		selected_obj.parents('.crs_tmp_option').addClass('crs_tmp_checked');
	};

	pub.addInfoMessageToPage = function(messageUrl) {
		if((typeof messageUrl) == 'string' && messageUrl != '') {
			$.get(messageUrl, function(data, status)
				{
					if(status === "success") {
						let adminRow = $("div.ilAdminRow");
						let msgBox;

						if(adminRow.count > 0) {
							msgBox = $(data)
							msgBox.hide();
							adminRow.append(msgBox);
						} else {
							adminRow = '<div class="ilAdminRow">'+data+'</div>';
							msgBox = $(adminRow);
							msgBox.hide();
							$('#ilSubTab').after(msgBox);
						}

						msgBox.fadeIn(800);
					}
				}
			);

		}

	};

	return pub;

})(il);