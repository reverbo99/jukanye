<?php
require_once(dirname(__FILE__).'/GatewayMpesa.php');

$isStore = isset($pluginData->store) && $pluginData->store;
$elemId = $pluginData->elemId;
?>

<script type="text/javascript">
	(function () {
		var isStore = <?php echo (isset($pluginData->store) && $pluginData->store) ? 'true' : 'false'; ?>;

		var baseUrl = $('base').attr('href');
		var getGatewayUrl = function (method) {
			return baseUrl + method + '/Mpesa';
		};

		window.showModal_<?php echo $elemId; ?> = function () {
			if (isStore && !$('#phone_<?php echo $elemId; ?>').val().trim()) {
				$.ajax({
					url: getGatewayUrl('getBillingData'),
					type: 'GET',
					dataType: 'json',
					contentType: 'application/json',
					success: function (response, status, xhr) {
						if (response?.phone) {
							$('#phone_<?php echo $elemId; ?>').val(response.phone);
						}
						if (response?.country) {
							$('#country_<?php echo $elemId; ?>').val(response.country);
						}
					}
				});
			}

			$('#mpesaCheckoutRender_<?php echo $elemId; ?>').hide();
			$('#mpesaBackBtn_<?php echo $elemId; ?>').hide();
			$('#mpesaCheckoutRender_<?php echo $elemId; ?>').html('');
			$('#phone_<?php echo $elemId; ?>').show();
			$('#country_<?php echo $elemId; ?>').show();
			$('#mpesaNextBtn_<?php echo $elemId; ?>').show();
			$('#mpesaModal_<?php echo $elemId; ?>').appendTo("body");
			$('#mpesaModal_<?php echo $elemId; ?>').modal('show');
			$('#mpesaForm_<?php echo $elemId; ?>').show();
			$('#mpesaError_<?php echo $elemId; ?>').hide();
		}

		window.throwError_<?php echo $elemId; ?>  = function (errorText) {
			$('#mpesaBackBtn_<?php echo $elemId; ?>').show();
			$('#mpesaCheckoutRender_<?php echo $elemId; ?>').hide();
			$('#mpesaError_<?php echo $elemId; ?>').show();
			$('#mpesaError_<?php echo $elemId; ?>').html('<b>Error: </b>' + errorText);
		}

		window.renderCheckout = function (data) {
			$('#mpesaCheckoutRender_<?php echo $elemId; ?>').show();
			$('#mpesaCheckoutRender_<?php echo $elemId; ?>').html(data);
		}

		window.startStorePaymentFlow_<?php echo $elemId; ?> = function ($form) {
			$('#mpesaForm_<?php echo $elemId; ?>').find('input[type="submit"]').trigger('click');
		}

		window.startPaymentFlow_<?php echo $elemId; ?> = function ($form) {
			$('#mpesaForm_<?php echo $elemId; ?>').hide();
			$('#mpesaNextBtn_<?php echo $elemId; ?>').hide();
			$('#mpesaBackBtn_<?php echo $elemId; ?>').hide();

			$('#mpesaCheckoutRender_<?php echo $elemId; ?>').show();
			$('#mpesaCheckoutRender_<?php echo $elemId; ?>').html('<?php echo SiteModule::__('Please wait until the transaction is completed'); ?>...');

			var data = {};
			data.phone = $("#phone_<?php echo $elemId; ?>").val();
			data.country = $("#country_<?php echo $elemId; ?>").val();
			data.providerCode = '<?php echo $pluginData->providerCode; ?>';
			<?php if (!$isStore): ?>
				data.isStore = false;
				data.price = '<?php echo $pluginData->price; ?>';
				data.itemName = '<?php echo isset($pluginData->itemName) ? $pluginData->itemName : ""; ?>';
			<?php else: ?>
				var form = document.getElementById('mpesaForm_<?php echo $elemId; ?>');
				data.isStore = true;
				data.price = form.elements['price'].value;
				data.transactionId = form.elements['transactionId'].value;
				data.returnUrl = form.elements['returnUrl'].value;
				data.callbackUrl = form.elements['callbackUrl'].value;
			<?php endif; ?>

			$.ajax({
				url: getGatewayUrl('createOrderRequest'),
				type: 'POST',
				data: JSON.stringify(data),
				dataType: 'json',
				contentType: 'application/json',
				success: function (response, status, xhr) {
					if (response && response.error) {
						throwError_<?php echo $elemId; ?>(response.error);
						return;
					}
					else if (response.message) {
						renderCheckout(response.message);
						if (response.callbackUrl) {
							location.href = response.callbackUrl;
						}
					}
				},
				error: function (response, status, xhr) {
					throwError_<?php echo $elemId; ?>(status);
				}
			})
		}

		if(isStore) {
			$('#mpesaForm_<?php echo $pluginData->elemId; ?>').on('<?php echo ($isStore ? 'beforesend' : 'submit'); ?>', function() {
				startPaymentFlow_<?php echo $elemId; ?>($(this));
			});
		}

		$('#<?php echo $elemId; ?>_payment_gateway_button').click(showModal_<?php echo $elemId; ?>);
		$('#mpesaBackBtn_<?php echo $elemId; ?>').click(showModal_<?php echo $elemId; ?>)
	})();

</script>
