/*
 * Backend JS for RRZE Contact
 */
jQuery(document).ready(function ($) {

	$('#rrze_contact_showhint').hide();
	$(document).on('change', '#rrze_contact_contactselect', function () {

		var value = $('#rrze_contact_contactselect').val();
		var shortcode = '[contact id="' + value + '"]';

		$('#rrze_contact_showhint').show();
		$('#copyshortcode').text(shortcode);
	});


	$('#rrze_contact_cp_shortcode').bind('click', function (event) {
		var $tempElement = $("<input>");
		$("body").append($tempElement);
		var copyText = $('#copyshortcode').text();
		$tempElement.val(copyText).select();
		document.execCommand("copy");
		$tempElement.remove();
	});


	if ($("#rrze_contact_standort_sync").is(":checked")) {
		$(".cmb2-id-rrze-contact-streetAddress").hide();
		$(".cmb2-id-rrze-contact-postalCode").hide();
		$(".cmb2-id-rrze-contact-addressLocality").hide();
		$(".cmb2-id-rrze-contact-addressCountry").hide();
	}

	$("#rrze_contact_standort_sync").click(function () {
		if ($(this).is(":checked")) {
			$(".cmb2-id-rrze-contact-streetAddress").hide(300);
			$(".cmb2-id-rrze-contact-postalCode").hide(300);
			$(".cmb2-id-rrze-contact-addressLocality").hide(300);
			$(".cmb2-id-rrze-contact-addressCountry").hide(300);
		} else {
			$(".cmb2-id-rrze-contact-streetAddress").show(200);
			$(".cmb2-id-rrze-contact-postalCode").show(200);
			$(".cmb2-id-rrze-contact-addressLocality").show(200);
			$(".cmb2-id-rrze-contact-addressCountry").show(200);
		}
	});

});