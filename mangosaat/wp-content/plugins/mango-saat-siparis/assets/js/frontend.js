(function ($) {
	'use strict';

	function buildSingleWhatsappUrl($form) {
		var href = $('.mango-saat-whatsapp-single').attr('href');
		if (!href || !$form.length) {
			return href;
		}

		var productTitle = $('.product_title').text().trim();
		var variationId = $form.find('input[name="variation_id"]').val();
		var price = $('.woocommerce-variation-price:visible .price, .summary .price').first().text().trim();
		var link = window.location.href.split('#')[0];

		if ($form.hasClass('variations_form') && (!variationId || variationId === '0')) {
			return href;
		}

		var withBox = $form.find('.mango-saat-box-checkbox').is(':checked');
		var boxText = '';
		if (withBox) {
			var $offer = $form.find('.mango-saat-box-offer');
			if ($offer.data('in-stock') === 0) {
				alert(mangoSaat.i18n.boxOutOfStock);
				return href;
			}
			boxText = '\nKutu: ' + $offer.find('.mango-saat-box-name').text().trim() + ' (+' + $offer.find('.mango-saat-box-price').text().trim() + ')';
		}

		var message =
			'Merhaba, sipariş vermek istiyorum.\nÜrün: ' +
			productTitle +
			(price ? '\nFiyat: ' + price : '') +
			boxText +
			'\nLink: ' +
			link;

		var phone = href.match(/^https:\/\/wa\.me\/([^?]+)/);
		if (!phone) {
			return href;
		}
		return 'https://wa.me/' + phone[1] + '?text=' + encodeURIComponent(message);
	}

	$(document).on('click', '.mango-saat-whatsapp-single', function (e) {
		var $form = $('form.cart');
		var next = buildSingleWhatsappUrl($form);
		if (next) {
			this.href = next;
		}
		if ($form.hasClass('variations_form')) {
			var variationId = $form.find('input[name="variation_id"]').val();
			if (!variationId || variationId === '0') {
				e.preventDefault();
				alert(mangoSaat.i18n.selectVariation);
			}
		}
	});

	$(document).on('change', '.mango-saat-box-checkbox:not(.mango-saat-cart-box-checkbox)', function () {
		var $offer = $(this).closest('.mango-saat-box-offer');
		if ($(this).is(':checked') && $offer.data('in-stock') === 0) {
			$(this).prop('checked', false);
			alert(mangoSaat.i18n.boxOutOfStock);
		}
	});

	$(document).on('change', '.mango-saat-cart-box-checkbox', function () {
		var $checkbox = $(this);
		var $offer = $checkbox.closest('.mango-saat-box-offer');
		var cartKey = $checkbox.data('cart-key');

		if ($checkbox.is(':checked') && $offer.data('in-stock') === 0) {
			$checkbox.prop('checked', false);
			alert(mangoSaat.i18n.boxOutOfStock);
			return;
		}

		if (!cartKey) {
			return;
		}

		$checkbox.prop('disabled', true);
		$.post(mangoSaat.ajaxUrl, {
			action: 'mango_saat_toggle_cart_box',
			nonce: mangoSaat.nonce,
			cart_key: cartKey,
			with_box: $checkbox.is(':checked') ? 1 : 0,
		})
			.done(function () {
				window.location.reload();
			})
			.fail(function () {
				alert(mangoSaat.i18n.boxToggleFailed || 'Kutu seçimi güncellenemedi.');
				window.location.reload();
			});
	});
})(jQuery);