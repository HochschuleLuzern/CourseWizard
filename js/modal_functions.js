il.CourseWizardModalFunctions = (function (scope) {



	var pub = {};

	pub.config = {
		postUrl: null
	}

	/*
	if(scope.CourseWizardModalFunctions != NULL)
	{
		pub.config = il.CourseWizardModalFunctions.config;
	}*/

	let executePostRequest = function(data) {
		console.log("ExecutingPostRequest");
	};

	pub.pushTemplateSelection = function () {
		console.log("PushingTemplateSelection");
	};

	pub.pushContentInheritanceSelection = function () {
		let data;
		console.log("Pushing Content Inheritance Selection");
		executePostRequest(data);
	};

	pub.getWizardModal = function () {

	};

	return pub;

})(il);