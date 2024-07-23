document.addEventListener("DOMContentLoaded", function (event) {

	let panel= jQuery('#oes-search-panel'),
		body = jQuery(document.body);
	if (panel && panel.parent() !== body) {

		const trigger = document.createElement('a');
		trigger.setAttribute('class', 'oes-close button');
		trigger.setAttribute('onClick', "jQuery('#oes-search-panel').toggle()");
		panel.children(":first").children(":first").append(trigger);

		body.prepend(panel);
	}
});