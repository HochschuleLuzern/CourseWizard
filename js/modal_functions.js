il.CourseWizardModalFunctions = (function (scope) {

	var pub = {};

	pub.config = {
		saveConfigUrl: '',
		currentPage: '',
		nextPage: '',
		replaceSignal: ''
	}

	/*
	if(scope.CourseWizardModalFunctions != NULL)
	{
		pub.config = il.CourseWizardModalFunctions.config;
	}*/

	let executePostRequest = function(data, url) {
		$.post(url, data.post_data).done(function(response)
		{
			console.log("Data Loaded: " + toString(this.data) + "replace: " + pub.config['replaceSignal']);
			$(document).trigger(pub.config['replaceSignal'], data.signal_data)
		});
		console.log("ExecutingPostRequest");
	};

	pub.pushTemplateSelection = function (e) {
		let data;
		let checked_id = $('#xcwi_template_selection_div_id').find('input:checked').val();
		if(checked_id != null) {
			console.log("Pushing Content Inheritance Selection");
			data = {
				post_data: {
					template_id: checked_id,
					currentPage: pub.config.currentPage
				},
				signal_data: {
					id: pub.config['replace_signal'],
					event: 'click',
					trigerrer: $(e),
					options: {
						url: pub.config['nextPageUrl']
					}
				}
			};
			executePostRequest(data, pub.config.saveConfigUrl);
			console.log("PushingTemplateSelection");
		} else {
			console.log("No template selected");
		}
	};

	pub.pushContentInheritanceSelection = function (e) {
		let data;
		let radio_values = {};
		let checked_id = $("#coursewizard").find('input[type=radio]:checked').each(function () {
			let radio_btn = $(this);
			radio_values[radio_btn.attr("id")] = radio_btn.attr("value");
			console.log(radio_btn.attr("id"), radio_btn.attr("value"));
		});

		data = {
			post_data: {
				radio_values: radio_values,
				currentPage: pub.config.currentPage
			},
			signal_data: {
				id: pub.config['replace_signal'],
				event: 'click',
				trigerrer: $(e),
				options: {
					url: pub.config['nextPageUrl']
				}
			}
		};
		executePostRequest(data, pub.config.saveConfigUrl);
	};

	pub.getWizardModal = function () {

	};

	return pub;

})(il);