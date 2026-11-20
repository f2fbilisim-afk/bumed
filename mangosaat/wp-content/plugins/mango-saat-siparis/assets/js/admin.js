(function ($) {
	'use strict';

	function initBoxSearch() {
		var $el = $('.mango-saat-box-search');
		if (!$el.length || !$el.selectWoo) {
			return;
		}

		$el.selectWoo({
			ajax: {
				url: mangoSaatAdmin.ajaxUrl,
				dataType: 'json',
				delay: 250,
				data: function (params) {
					return {
						action: 'mango_saat_search_boxes',
						term: params.term,
						nonce: mangoSaatAdmin.nonce,
					};
				},
				processResults: function (data) {
					return data;
				},
			},
			minimumInputLength: 1,
			placeholder: 'Kutu ürünü ara…',
			allowClear: true,
			width: '100%',
		});
	}

	$(initBoxSearch);
})(jQuery);