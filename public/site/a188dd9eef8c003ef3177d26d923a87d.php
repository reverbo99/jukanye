<?php if (!class_exists('SiteModule') || SiteModule::$lang == 'en') { ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<script type="text/javascript">
	window._spDefer = {
		queue: [],
		ready: false,
		add: function(fn) {
			if (this.ready) { fn(); }
			else { this.queue.push(fn); }
		},
		done: function() {
			this.ready = true;
			var fns = this.queue;
			this.queue = [];
			for (var i = 0; i < fns.length; i++) { fns[i](); }
		}
	};
	</script>
			<script type="text/javascript">
		(function(d) {
			var ciCollectedCookies = [];
			var cookieDesc =
				Object.getOwnPropertyDescriptor(Document.prototype, 'cookie') ||
				Object.getOwnPropertyDescriptor(HTMLDocument.prototype, 'cookie');

			var storage = null;
			function checkStorage() {
				if (storage === null) {
					var whitelist = ['__cookie_law__', 'PHPSESSID'];
					var cookies = JSON.parse(localStorage.getItem('allowedCookies') || '[]');
					cookies = cookies.map(cookie => {
						if (cookie.indexOf('*') >= 0) {
							return new RegExp(cookie.replace('*', '.+'));
						}
						return cookie;
					});
					storage = [].concat.apply(whitelist, cookies);
				}
				return storage;
			}
			d.cookieIsAllowed = function (c) {
				var cookie_law = document.cookie.match(/(?:^|;\ *)__cookie_law__=(\d+)/);
				if (cookie_law !== null) {
					cookie_law = parseInt(cookie_law[1]);
				}
				// Only if cookie accept enabled
				if (cookie_law == 2) {
					var all = checkStorage();
					for (var idx in all) {
						if (all[idx] instanceof RegExp && all[idx].test(c)) return true;
						if (all[idx] === c) return true;
					}
				}
				return false;
			}

			if (cookieDesc && cookieDesc.configurable) {
				Object.defineProperty(d, 'cookie', {
					get: function() {
						return cookieDesc.get.call(d);
					},
					set: function(val) {
						if (val.indexOf('__cookie_law__') >= 0) {
							cookieDesc.set.call(d, val);
							return;
						}

						var cookie_law = document.cookie.match(/(?:^|;\ *)__cookie_law__=(\d+)/);
						if (cookie_law !== null) {
							cookie_law = parseInt(cookie_law[1]);
						}

						// Only if cookie accept enabled
						if (cookie_law == 2) {
							var c = val.split('=')[0];
							// cookie marked for removal
							if (val[0] === '!') cookieDesc.set.call(d, val.slice(1));
							else if (d.cookieIsAllowed(c)) cookieDesc.set.call(d, val);
						}
						else if (cookie_law === null && ciCollectedCookies.indexOf(val) < 0) {
							ciCollectedCookies.push(val);
						}
					}
				});

				var savedFormData = localStorage.getItem('cookieConsentFormDataTmp');
				if (savedFormData) {
					localStorage.removeItem('cookieConsentFormDataTmp')
					savedFormData = JSON.parse(savedFormData);
					if (savedFormData) {
						function fillFormsFromData(data) {
							document.addEventListener("DOMContentLoaded", function() {
								Object.keys(data).forEach(formId => {
									var form = document.querySelector('form#' + formId);
									if (!form) return;

									Object.keys(data[formId]).forEach(name => {
										var value = data[formId][name];

										form.querySelectorAll('[name="'+CSS.escape(name) + '"]:not([type="hidden"])').forEach(el => {
											if (el.type === 'checkbox') {
												if (Array.isArray(value)) el.checked = value.includes(el.value);
												else el.checked = Boolean(value);
											} else if (el.type === 'radio') {
												el.checked = el.value === value;
											} else el.value = (value === null || value === undefined) ? '' : value;
										});
									});
								});
							});
						}

						fillFormsFromData(savedFormData);
					}
				}
			}
			d.cookieChangedCategories = function (cookieLaw, allowedCookies = []) {
				if (window.gtag) {
					var opts = {
						'ad_storage': !!cookieLaw && (!Array.isArray(allowedCookies) || allowedCookies.indexOf('gtag:ad_storage') >= 0) ? 'granted' : 'denied',
						'ad_user_data': !!cookieLaw && (!Array.isArray(allowedCookies) || allowedCookies.indexOf('gtag:ad_user_data') >= 0) ? 'granted' : 'denied',
						'ad_personalization': !!cookieLaw && (!Array.isArray(allowedCookies) || allowedCookies.indexOf('gtag:ad_personalization') >= 0) ? 'granted' : 'denied',
						'analytics_storage': !!cookieLaw && (!Array.isArray(allowedCookies) || allowedCookies.indexOf('gtag:analytics_storage') >= 0) ? 'granted' : 'denied',
					};
					gtag('consent', 'update', opts);
					if (!!cookieLaw && Array.isArray(allowedCookies) && Object.values(opts).indexOf('granted') >= 0) {
						allowedCookies = allowedCookies.concat(['DSID','test_cookie','ar_debug','IDE','FPLC','_ga','_gac_*','_gid','_gat*','__utma','__utmb','__utmc','__utmt','__utmz','__utmv','AMP_TOKEN','FPID','GA_OPT_OUT','_ga_*','_dc_gtm_*','_gaexp','_gaexp_rc','_opt_awcid','_opt_awmid','_opt_awgid','_opt_awkid','_opt_utmc']);
					}
				}
				if (Array.isArray(allowedCookies)) {
					localStorage.setItem('allowedCookies', JSON.stringify(allowedCookies));
				}

				document.cookie = '__cookie_law__=' + (2) + '; path=/; expires=Wed, 28 Jul 2027 18:59:05 GMT';

				var items = ciCollectedCookies;
				ciCollectedCookies = [];
				items.forEach(function (item) {
					d.cookie = item;
				})
			}

			d.consentChanged = function () {
				function collectAllFormsData() {
					var result = {};

					document.querySelectorAll('form.wb_form').forEach((form) => {
						var formId = form.id;
						result[formId] = {};

						form.querySelectorAll('input:not([type="hidden"]), textarea, select').forEach(el => {
							if (!el.name) return;

							if (el.type === 'checkbox') {
								if (!result[formId][el.name]) result[formId][el.name] = [];
								if (el.checked) result[formId][el.name].push(el.value || true);
								if (!el.checked && result[formId][el.name].length === 0) result[formId][el.name] = false;
							} else if (el.type === 'radio') {
								if (el.checked) result[formId][el.name] = el.value;
								else if (!(el.name in result[formId])) result[formId][el.name] = null;
							} else {
								if (result[formId][el.name]) {
									if (!Array.isArray(result[formId][el.name])) result[formId][el.name] = [result[formId][el.name]];
									result[formId][el.name].push(el.value);
								} else result[formId][el.name] = el.value;
							}
						});
					});

					return result;
				}

				localStorage.setItem('cookieConsentFormDataTmp', JSON.stringify(collectAllFormsData()));

				document.location.reload();
			}
		})(document);
	</script>
			<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title><?php echo htmlspecialchars((isset($seoTitle) && $seoTitle !== "") ? $seoTitle : "Custom Maintenance Page"); ?></title>
	<base href="{{base_url}}" />
	<?php echo isset($sitemapUrls) ? (generateCanonicalUrl($sitemapUrls)."\n") : ""; ?>	
		<link rel="alternate" hreflang="en" href="{{base_url}}{{lang_en}}" />
		<link rel="alternate" hreflang="x-default" href="{{base_url}}{{lang_en}}" />
			<link rel="alternate" hreflang="sw" href="{{base_url}}{{lang_sw}}" />
		
						<meta name="viewport" content="width=device-width, initial-scale=1" />
					<meta name="description" content="<?php echo htmlspecialchars((isset($seoDescription) && $seoDescription !== "") ? $seoDescription : "Custom Maintenance Page"); ?>" />
			<meta name="keywords" content="<?php echo htmlspecialchars((isset($seoKeywords) && $seoKeywords !== "") ? $seoKeywords : "Custom Maintenance Page"); ?>" />
			<meta name="robots" content="noindex" />
		
	<!-- Facebook Open Graph -->
		<meta property="og:title" content="<?php echo htmlspecialchars((isset($seoTitle) && $seoTitle !== "") ? $seoTitle : "Custom Maintenance Page"); ?>" />
			<meta property="og:description" content="<?php echo htmlspecialchars((isset($seoDescription) && $seoDescription !== "") ? $seoDescription : "Custom Maintenance Page"); ?>" />
			<meta property="og:image" content="<?php echo htmlspecialchars((isset($seoImage) && $seoImage !== "") ? "{{base_url}}".$seoImage : ""); ?>" />
			<meta property="og:type" content="article" />
			<meta property="og:url" content="__wb_curr_url__" />
		<!-- Facebook Open Graph end -->

		<meta name="generator" content="Website Builder" />
			<link href="css/common-bundle.css?ts=20260802185857" rel="stylesheet" type="text/css" />
	<link href="css/a188dd9eef8c003ef3177d26d923a87d-bundle.css?ts=20260802185857" rel="stylesheet" type="text/css" id="wb-page-stylesheet" />
	<ga-code/><meta name="undefined" content="Jukanye Festival" /><link rel="icon" type="image/png" href="gallery/favicons/favicon.png">
	<script type="text/javascript">
	window.useTrailingSlashes = true;
	window.disableRightClick = true;
	window.currLang = 'en';
</script>
		
	<!--[if lt IE 9]>
	<script src="js/html5shiv.min.js"></script>
	<![endif]-->

		<script type="text/javascript">
		window._spDefer.add(function() {
			if (window.gtag) {
				window.gtag('consent', 'default', {
					'ad_storage': document.cookieIsAllowed('gtag:ad_storage') ? 'granted' : 'denied',
					'ad_user_data': document.cookieIsAllowed('gtag:ad_user_data') ? 'granted' : 'denied',
					'ad_personalization': document.cookieIsAllowed('gtag:ad_personalization') ? 'granted' : 'denied',
					'analytics_storage': document.cookieIsAllowed('gtag:analytics_storage') ? 'granted' : 'denied',
					'wait_for_update': 500
				});
			}

			
			var cookie_law = document.cookie.match(/(?:^|;\ *)__cookie_law__=(\d+)/);
			if (cookie_law !== null) {
				cookie_law = parseInt(cookie_law[1]);
			}
			if (cookie_law !== 2 || <?php echo popSessionOrGlobalVar("wb_form_reaccept_cookie") ? 1 : 0; ?>) {
				var block = $('<div>')
					.addClass('wb_cookie_policy')
					.css({
						backgroundColor: "rgba(0, 0, 0, 0.66)",
						minHeight: "0%"					});
				let policyMessage = $('<div class="policy-message">')
						.html("<p>We use cookies (and gather certain personal information) to provide you with a better online experience. By visiting our website you accept our terms.<\/p>\n")
						.css({
							color: "#ffffff",
							fontFamily: "Arial,Helvetica,sans-serif",
							fontSize: 14						});
				var btnWrapper = $('<div class="policy-button">');

									policyMessage.appendTo(block);
					btnWrapper.appendTo(block);
				
				var cookiePolicyButtonText = "Got it";
				var cookiePolicyRejectButtonText = "Reject";
				var cookiePolicyCustomizeButtonText = "Customise";
				var cookiePolicyList = [];
				if (window.gtag) {
					cookiePolicyList = cookiePolicyList.concat([{"displayName":"Google Ad Storage","name":"gtag:ad_storage","description":"Enables storage, such as cookies (web) or device identifiers (apps), related to advertising.","necessary":false},{"displayName":"Google User Data","name":"gtag:ad_user_data","description":"Sets consent for sending user data to Google for online advertising purposes.","necessary":false},{"displayName":"Google Personalization","name":"gtag:ad_personalization","description":"Sets consent for personalized advertising.","necessary":false},{"displayName":"Google Analytics Storage","name":"gtag:analytics_storage","description":"Enables storage, such as cookies (web) or device identifiers (apps), related to analytics, for example, visit duration.","necessary":false}]);
				}
				if (cookiePolicyList && cookiePolicyList.length) {
					var modalHeader = $('<div>').addClass('modal-header')
						.append(
							$('<button>').addClass('close').attr('data-dismiss', 'modal').append(
								$('<i class="ti ti-x wb-close-icon"></i>')
							)
						).append(
							$('<h4>').text(cookiePolicyCustomizeButtonText)
						);
					var modalContent = $('<div>').addClass('modal-body').css({maxHeight: '80vh', overflow: 'auto'});

					cookiePolicyList.map(function (cookie) {
							modalContent.append(
								$('<div>')
									.attr('data-cookies', cookie.name)
									.addClass('checkbox material-switch')
									.append(
										$('<input>')
											.attr('id', 'cookie' + cookie.name)
											.attr('type', 'checkbox')
											.attr('value', 1)
											.attr('checked', true)
											.attr('name', cookie.name)
											.attr('disabled', cookie.necessary ? true : null)
									)
									.append($('<label>')
										.attr('for', 'cookie' + cookie.name)
									)
									.append(
										$('<div>')
											.addClass('text-left material-switch-label')
											.append(
												$('<div>')
													.css({display: 'inline-block', textAlign: 'left'})
													.append(cookie.name ?
														$('<b>')
															.text(cookie.displayName ? cookie.displayName : cookie.name)
															.append(cookie.necessary ? '<span style="color:red;margin-left:0.5em;font-size:0.7em;vertical-align:super" aria-hidden="true">*</span>' : '')
														: ''
													)
													.append(cookie.description ? $('<p>').addClass('small').html(cookie.description) : '')
											)
									)
							);
					});
					var modalFooter = $('<div>').addClass('modal-footer').append(
						$('<button type="submit" class="btn btn-primary"></button>')
							.text(cookiePolicyButtonText)
					);
					var modal = $('<div>').addClass('modal cookie-policy-modal fade').attr('role', 'dialog').append(
						$('<div>').addClass('modal-dialog modal-md').attr('role', 'document').append(
							$('<form id="customCookiesForm">').addClass('modal-content form-horizontal')
								.append(modalHeader)
								.append(modalContent)
								.append(modalFooter)
								.submit(function (e) {
									e.preventDefault();

									var names = cookiePolicyList.filter(cookie => cookie.necessary).map(function (field) {
										return field.name.split(',').map(function (item) { return item.trim(); });
									}).flat();

									var selectedNames = $(e.target).serializeArray().map(function (field) {
										if (typeof field.name === 'string') {
											return field.name.split(',').map(function (item) { return item.trim(); });
										}
										return field.name;
									}).flat();

									if (document.cookieChangedCategories) {
										document.cookieChangedCategories(1, names.concat(selectedNames));
									}

									modal.modal('hide');
									modal.on('hidden.bs.modal', function () {
										block.remove();
									});

									document.consentChanged();
									return false;
								})
						)
					).appendTo('body');
				}

				if (cookiePolicyRejectButtonText) {
				$('<button>')
						.attr({type: 'button'})
						.css({
							backgroundColor: "#cccccc",
							color: "#ffffff",
							fontFamily: "Arial,Helvetica,sans-serif",
							fontSize: 14,
							marginRight: '5px',
							marginLeft: '5px'
						})
						.text(cookiePolicyRejectButtonText)
						.on('click', function() {
							if (cookiePolicyList && cookiePolicyList.length && cookiePolicyCustomizeButtonText) {
								var names = cookiePolicyList.filter(cookie => cookie.necessary).map(function (field) {
									return field.name.split(',').map(function (item) { return item.trim(); });
								}).flat();
								// Save only required cookies
								if (document.cookieChangedCategories) document.cookieChangedCategories(1, names);
							} else {
								// Save no cookies
								if (document.cookieChangedCategories) document.cookieChangedCategories(0, []);
							}

							block.remove();
							document.consentChanged();
						})
						.appendTo(btnWrapper);
				}

				if (cookiePolicyList && cookiePolicyList.length && cookiePolicyCustomizeButtonText) {
					$('<button>')
						.attr({type: 'button'})
						.css({
							backgroundColor: "#5cb85c",
							color: "#ffffff",
							fontFamily: "Arial,Helvetica,sans-serif",
							fontSize: 14,
							marginRight: '5px',
							marginLeft: '5px'
						})
						.text(cookiePolicyCustomizeButtonText)
						.on('click', function () {
							modal.modal('toggle');
						})
						.appendTo(btnWrapper);
				}

				if (cookiePolicyButtonText) {
					$('<button>')
						.attr({type: 'button'})
						.css({
							backgroundColor: "#5cb85c",
							color: "#ffffff",
							fontFamily: "Arial,Helvetica,sans-serif",
							fontSize: 14,
							marginRight: '5px',
							marginLeft: '5px'
						})
						.text(cookiePolicyButtonText)
						.on('click', function () {
							if ($('#customCookiesForm').length) {
								$('#customCookiesForm').submit();
								return;
							}

							if (document.cookieChangedCategories) {
								document.cookieChangedCategories(1, ['*']);
							}

							block.remove();
							document.consentChanged();
						})
						.appendTo(btnWrapper);
				}

				$(document.body).append(block);

                if (block.height() >= $(window).height() * 0.4) {
                    block.addClass('center');
                }
			}
		});
	</script>
		<script type="text/javascript">
		window._spDefer.add(function() {
<?php $wb_form_send_success = popSessionOrGlobalVar("wb_form_send_success"); ?>
<?php if (($wb_form_send_state = popSessionOrGlobalVar("wb_form_send_state"))) { ?>
	<?php if (($wb_form_popup_mode = popSessionOrGlobalVar("wb_form_popup_mode")) && (isset($wbPopupMode) && $wbPopupMode)) { ?>
		if (window !== window.parent && window.parent.postMessage) {
			var data = {
				event: "wb_contact_form_sent",
				data: {
					state: "<?php echo str_replace('"', '\"', $wb_form_send_state); ?>",
					type: "<?php echo $wb_form_send_success ? "success" : "danger"; ?>"
				}
			};
			window.parent.postMessage(data, "<?php echo str_replace('"', '\"', popSessionOrGlobalVar("wb_target_origin")); ?>");
		}
	<?php $wb_form_send_success = false; $wb_form_send_state = null; $wb_form_popup_mode = false; ?>
	<?php } else { ?>
		wb_show_alert("<?php echo str_replace(array('"', "\r", "\n"), array('\"', "", "<br/>"), $wb_form_send_state); ?>", "<?php echo $wb_form_send_success ? "success" : "danger"; ?>");
	<?php } ?>
<?php } ?>
});    </script>
</head>


<body class="site site-lang-en<?php if (isset($wbPopupMode) && $wbPopupMode) echo ' popup-mode'; ?> " <?php ?> wb-maintenance-page="true"><div id="wb_root" class="root wb-layout-vertical"><div class="wb_sbg"></div><div id="wb_main_a188dd9eef8c003ef3177d26d923a87d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a189b043207e00f789d16f917c3c3fc1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a189b04320810001642be2002829b1c1" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="2.17%" viewBox="0 0 1921.02083 1793.982" style="direction: ltr; color:#0ca3a6"><text x="1.02083" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a189b0432088000f6d9cd9629e5d292b" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h1 class="wb-stl-heading1" style="text-align: center;"><span style="color:#000000;">Discover Jukanye: Your Gateway to Thriving Online Business Solutions</span></h1></div></div></div><div id="wb_footer_c" class="wb_element" data-plugin="WB_Footer" style="text-align: center; width: 100%;"><div class="wb_footer"></div><script>window._spDefer.add(function() {
			$(function() {
				var footer = $(".wb_footer");
				var html = (footer.html() + "").replace(/^\s+|\s+$/g, "");
				if (!html) {
					footer.parent().remove();
					footer = $("#footer, #footer .wb_cont_inner");
					footer.css({height: ""});
				}
			});
			});</script></div></div></div><script type="text/javascript">window._spDefer.add(function() { $(function() { wb_require(["store/js/StoreCartElement"], function(app) {}); }); });</script>
<div class="wb_pswp" tabindex="-1" role="dialog" aria-hidden="true">
</div>
</div><script src="js/jquery-3.5.1.min.js" type="text/javascript"></script>
	<script src="js/common-bundle.js?ts=20260802185857" type="text/javascript" defer></script>
	<script src="js/a188dd9eef8c003ef3177d26d923a87d-bundle.js?ts=20260802185857" type="text/javascript" defer></script>{{hr_out}}<script>
    document.addEventListener('DOMContentLoaded', function () {
        window._spDefer.done();
    });
</script>
</body>
</html>


<?php } else if (SiteModule::$lang == 'sw') { ?>

<!DOCTYPE html>
<html lang="sw">
<head>
	<script type="text/javascript">
	window._spDefer = {
		queue: [],
		ready: false,
		add: function(fn) {
			if (this.ready) { fn(); }
			else { this.queue.push(fn); }
		},
		done: function() {
			this.ready = true;
			var fns = this.queue;
			this.queue = [];
			for (var i = 0; i < fns.length; i++) { fns[i](); }
		}
	};
	</script>
			<script type="text/javascript">
		(function(d) {
			var ciCollectedCookies = [];
			var cookieDesc =
				Object.getOwnPropertyDescriptor(Document.prototype, 'cookie') ||
				Object.getOwnPropertyDescriptor(HTMLDocument.prototype, 'cookie');

			var storage = null;
			function checkStorage() {
				if (storage === null) {
					var whitelist = ['__cookie_law__', 'PHPSESSID'];
					var cookies = JSON.parse(localStorage.getItem('allowedCookies') || '[]');
					cookies = cookies.map(cookie => {
						if (cookie.indexOf('*') >= 0) {
							return new RegExp(cookie.replace('*', '.+'));
						}
						return cookie;
					});
					storage = [].concat.apply(whitelist, cookies);
				}
				return storage;
			}
			d.cookieIsAllowed = function (c) {
				var cookie_law = document.cookie.match(/(?:^|;\ *)__cookie_law__=(\d+)/);
				if (cookie_law !== null) {
					cookie_law = parseInt(cookie_law[1]);
				}
				// Only if cookie accept enabled
				if (cookie_law == 2) {
					var all = checkStorage();
					for (var idx in all) {
						if (all[idx] instanceof RegExp && all[idx].test(c)) return true;
						if (all[idx] === c) return true;
					}
				}
				return false;
			}

			if (cookieDesc && cookieDesc.configurable) {
				Object.defineProperty(d, 'cookie', {
					get: function() {
						return cookieDesc.get.call(d);
					},
					set: function(val) {
						if (val.indexOf('__cookie_law__') >= 0) {
							cookieDesc.set.call(d, val);
							return;
						}

						var cookie_law = document.cookie.match(/(?:^|;\ *)__cookie_law__=(\d+)/);
						if (cookie_law !== null) {
							cookie_law = parseInt(cookie_law[1]);
						}

						// Only if cookie accept enabled
						if (cookie_law == 2) {
							var c = val.split('=')[0];
							// cookie marked for removal
							if (val[0] === '!') cookieDesc.set.call(d, val.slice(1));
							else if (d.cookieIsAllowed(c)) cookieDesc.set.call(d, val);
						}
						else if (cookie_law === null && ciCollectedCookies.indexOf(val) < 0) {
							ciCollectedCookies.push(val);
						}
					}
				});

				var savedFormData = localStorage.getItem('cookieConsentFormDataTmp');
				if (savedFormData) {
					localStorage.removeItem('cookieConsentFormDataTmp')
					savedFormData = JSON.parse(savedFormData);
					if (savedFormData) {
						function fillFormsFromData(data) {
							document.addEventListener("DOMContentLoaded", function() {
								Object.keys(data).forEach(formId => {
									var form = document.querySelector('form#' + formId);
									if (!form) return;

									Object.keys(data[formId]).forEach(name => {
										var value = data[formId][name];

										form.querySelectorAll('[name="'+CSS.escape(name) + '"]:not([type="hidden"])').forEach(el => {
											if (el.type === 'checkbox') {
												if (Array.isArray(value)) el.checked = value.includes(el.value);
												else el.checked = Boolean(value);
											} else if (el.type === 'radio') {
												el.checked = el.value === value;
											} else el.value = (value === null || value === undefined) ? '' : value;
										});
									});
								});
							});
						}

						fillFormsFromData(savedFormData);
					}
				}
			}
			d.cookieChangedCategories = function (cookieLaw, allowedCookies = []) {
				if (window.gtag) {
					var opts = {
						'ad_storage': !!cookieLaw && (!Array.isArray(allowedCookies) || allowedCookies.indexOf('gtag:ad_storage') >= 0) ? 'granted' : 'denied',
						'ad_user_data': !!cookieLaw && (!Array.isArray(allowedCookies) || allowedCookies.indexOf('gtag:ad_user_data') >= 0) ? 'granted' : 'denied',
						'ad_personalization': !!cookieLaw && (!Array.isArray(allowedCookies) || allowedCookies.indexOf('gtag:ad_personalization') >= 0) ? 'granted' : 'denied',
						'analytics_storage': !!cookieLaw && (!Array.isArray(allowedCookies) || allowedCookies.indexOf('gtag:analytics_storage') >= 0) ? 'granted' : 'denied',
					};
					gtag('consent', 'update', opts);
					if (!!cookieLaw && Array.isArray(allowedCookies) && Object.values(opts).indexOf('granted') >= 0) {
						allowedCookies = allowedCookies.concat(['DSID','test_cookie','ar_debug','IDE','FPLC','_ga','_gac_*','_gid','_gat*','__utma','__utmb','__utmc','__utmt','__utmz','__utmv','AMP_TOKEN','FPID','GA_OPT_OUT','_ga_*','_dc_gtm_*','_gaexp','_gaexp_rc','_opt_awcid','_opt_awmid','_opt_awgid','_opt_awkid','_opt_utmc']);
					}
				}
				if (Array.isArray(allowedCookies)) {
					localStorage.setItem('allowedCookies', JSON.stringify(allowedCookies));
				}

				document.cookie = '__cookie_law__=' + (2) + '; path=/; expires=Wed, 28 Jul 2027 18:59:05 GMT';

				var items = ciCollectedCookies;
				ciCollectedCookies = [];
				items.forEach(function (item) {
					d.cookie = item;
				})
			}

			d.consentChanged = function () {
				function collectAllFormsData() {
					var result = {};

					document.querySelectorAll('form.wb_form').forEach((form) => {
						var formId = form.id;
						result[formId] = {};

						form.querySelectorAll('input:not([type="hidden"]), textarea, select').forEach(el => {
							if (!el.name) return;

							if (el.type === 'checkbox') {
								if (!result[formId][el.name]) result[formId][el.name] = [];
								if (el.checked) result[formId][el.name].push(el.value || true);
								if (!el.checked && result[formId][el.name].length === 0) result[formId][el.name] = false;
							} else if (el.type === 'radio') {
								if (el.checked) result[formId][el.name] = el.value;
								else if (!(el.name in result[formId])) result[formId][el.name] = null;
							} else {
								if (result[formId][el.name]) {
									if (!Array.isArray(result[formId][el.name])) result[formId][el.name] = [result[formId][el.name]];
									result[formId][el.name].push(el.value);
								} else result[formId][el.name] = el.value;
							}
						});
					});

					return result;
				}

				localStorage.setItem('cookieConsentFormDataTmp', JSON.stringify(collectAllFormsData()));

				document.location.reload();
			}
		})(document);
	</script>
			<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title><?php echo htmlspecialchars((isset($seoTitle) && $seoTitle !== "") ? $seoTitle : "Custom Maintenance Page"); ?></title>
	<base href="{{base_url}}" />
	<?php echo isset($sitemapUrls) ? (generateCanonicalUrl($sitemapUrls)."\n") : ""; ?>	
		<link rel="alternate" hreflang="en" href="{{base_url}}{{lang_en}}" />
		<link rel="alternate" hreflang="x-default" href="{{base_url}}{{lang_en}}" />
			<link rel="alternate" hreflang="sw" href="{{base_url}}{{lang_sw}}" />
		
						<meta name="viewport" content="width=device-width, initial-scale=1" />
					<meta name="description" content="<?php echo htmlspecialchars((isset($seoDescription) && $seoDescription !== "") ? $seoDescription : "Custom Maintenance Page"); ?>" />
			<meta name="keywords" content="<?php echo htmlspecialchars((isset($seoKeywords) && $seoKeywords !== "") ? $seoKeywords : "Custom Maintenance Page"); ?>" />
			<meta name="robots" content="noindex" />
		
	<!-- Facebook Open Graph -->
		<meta property="og:title" content="<?php echo htmlspecialchars((isset($seoTitle) && $seoTitle !== "") ? $seoTitle : "Custom Maintenance Page"); ?>" />
			<meta property="og:description" content="<?php echo htmlspecialchars((isset($seoDescription) && $seoDescription !== "") ? $seoDescription : "Custom Maintenance Page"); ?>" />
			<meta property="og:image" content="<?php echo htmlspecialchars((isset($seoImage) && $seoImage !== "") ? "{{base_url}}".$seoImage : ""); ?>" />
			<meta property="og:type" content="article" />
			<meta property="og:url" content="__wb_curr_url__" />
		<!-- Facebook Open Graph end -->

		<meta name="generator" content="Website Builder" />
			<link href="css/common-bundle.css?ts=20260802185857" rel="stylesheet" type="text/css" />
	<link href="css/a188dd9eef8c003ef3177d26d923a87d-bundle.css?ts=20260802185857" rel="stylesheet" type="text/css" id="wb-page-stylesheet" />
	<ga-code/><meta name="undefined" content="Jukanye Festival" /><link rel="icon" type="image/png" href="gallery/favicons/favicon.png">
	<script type="text/javascript">
	window.useTrailingSlashes = true;
	window.disableRightClick = true;
	window.currLang = 'sw';
</script>
		
	<!--[if lt IE 9]>
	<script src="js/html5shiv.min.js"></script>
	<![endif]-->

		<script type="text/javascript">
		window._spDefer.add(function() {
			if (window.gtag) {
				window.gtag('consent', 'default', {
					'ad_storage': document.cookieIsAllowed('gtag:ad_storage') ? 'granted' : 'denied',
					'ad_user_data': document.cookieIsAllowed('gtag:ad_user_data') ? 'granted' : 'denied',
					'ad_personalization': document.cookieIsAllowed('gtag:ad_personalization') ? 'granted' : 'denied',
					'analytics_storage': document.cookieIsAllowed('gtag:analytics_storage') ? 'granted' : 'denied',
					'wait_for_update': 500
				});
			}

			
			var cookie_law = document.cookie.match(/(?:^|;\ *)__cookie_law__=(\d+)/);
			if (cookie_law !== null) {
				cookie_law = parseInt(cookie_law[1]);
			}
			if (cookie_law !== 2 || <?php echo popSessionOrGlobalVar("wb_form_reaccept_cookie") ? 1 : 0; ?>) {
				var block = $('<div>')
					.addClass('wb_cookie_policy')
					.css({
						backgroundColor: "rgba(0, 0, 0, 0.66)",
						minHeight: "0%"					});
				let policyMessage = $('<div class="policy-message">')
						.html("<p>We use cookies (and gather certain personal information) to provide you with a better online experience. By visiting our website you accept our terms.<\/p>\n")
						.css({
							color: "#ffffff",
							fontFamily: "Arial,Helvetica,sans-serif",
							fontSize: 14						});
				var btnWrapper = $('<div class="policy-button">');

									policyMessage.appendTo(block);
					btnWrapper.appendTo(block);
				
				var cookiePolicyButtonText = "Nimeelewa";
				var cookiePolicyRejectButtonText = "Kataa";
				var cookiePolicyCustomizeButtonText = "Rekebisha";
				var cookiePolicyList = [];
				if (window.gtag) {
					cookiePolicyList = cookiePolicyList.concat([{"displayName":"Google Ad Storage","name":"gtag:ad_storage","description":"Enables storage, such as cookies (web) or device identifiers (apps), related to advertising.","necessary":false},{"displayName":"Google User Data","name":"gtag:ad_user_data","description":"Sets consent for sending user data to Google for online advertising purposes.","necessary":false},{"displayName":"Google Personalization","name":"gtag:ad_personalization","description":"Sets consent for personalized advertising.","necessary":false},{"displayName":"Google Analytics Storage","name":"gtag:analytics_storage","description":"Enables storage, such as cookies (web) or device identifiers (apps), related to analytics, for example, visit duration.","necessary":false}]);
				}
				if (cookiePolicyList && cookiePolicyList.length) {
					var modalHeader = $('<div>').addClass('modal-header')
						.append(
							$('<button>').addClass('close').attr('data-dismiss', 'modal').append(
								$('<i class="ti ti-x wb-close-icon"></i>')
							)
						).append(
							$('<h4>').text(cookiePolicyCustomizeButtonText)
						);
					var modalContent = $('<div>').addClass('modal-body').css({maxHeight: '80vh', overflow: 'auto'});

					cookiePolicyList.map(function (cookie) {
							modalContent.append(
								$('<div>')
									.attr('data-cookies', cookie.name)
									.addClass('checkbox material-switch')
									.append(
										$('<input>')
											.attr('id', 'cookie' + cookie.name)
											.attr('type', 'checkbox')
											.attr('value', 1)
											.attr('checked', true)
											.attr('name', cookie.name)
											.attr('disabled', cookie.necessary ? true : null)
									)
									.append($('<label>')
										.attr('for', 'cookie' + cookie.name)
									)
									.append(
										$('<div>')
											.addClass('text-left material-switch-label')
											.append(
												$('<div>')
													.css({display: 'inline-block', textAlign: 'left'})
													.append(cookie.name ?
														$('<b>')
															.text(cookie.displayName ? cookie.displayName : cookie.name)
															.append(cookie.necessary ? '<span style="color:red;margin-left:0.5em;font-size:0.7em;vertical-align:super" aria-hidden="true">*</span>' : '')
														: ''
													)
													.append(cookie.description ? $('<p>').addClass('small').html(cookie.description) : '')
											)
									)
							);
					});
					var modalFooter = $('<div>').addClass('modal-footer').append(
						$('<button type="submit" class="btn btn-primary"></button>')
							.text(cookiePolicyButtonText)
					);
					var modal = $('<div>').addClass('modal cookie-policy-modal fade').attr('role', 'dialog').append(
						$('<div>').addClass('modal-dialog modal-md').attr('role', 'document').append(
							$('<form id="customCookiesForm">').addClass('modal-content form-horizontal')
								.append(modalHeader)
								.append(modalContent)
								.append(modalFooter)
								.submit(function (e) {
									e.preventDefault();

									var names = cookiePolicyList.filter(cookie => cookie.necessary).map(function (field) {
										return field.name.split(',').map(function (item) { return item.trim(); });
									}).flat();

									var selectedNames = $(e.target).serializeArray().map(function (field) {
										if (typeof field.name === 'string') {
											return field.name.split(',').map(function (item) { return item.trim(); });
										}
										return field.name;
									}).flat();

									if (document.cookieChangedCategories) {
										document.cookieChangedCategories(1, names.concat(selectedNames));
									}

									modal.modal('hide');
									modal.on('hidden.bs.modal', function () {
										block.remove();
									});

									document.consentChanged();
									return false;
								})
						)
					).appendTo('body');
				}

				if (cookiePolicyRejectButtonText) {
				$('<button>')
						.attr({type: 'button'})
						.css({
							backgroundColor: "#cccccc",
							color: "#ffffff",
							fontFamily: "Arial,Helvetica,sans-serif",
							fontSize: 14,
							marginRight: '5px',
							marginLeft: '5px'
						})
						.text(cookiePolicyRejectButtonText)
						.on('click', function() {
							if (cookiePolicyList && cookiePolicyList.length && cookiePolicyCustomizeButtonText) {
								var names = cookiePolicyList.filter(cookie => cookie.necessary).map(function (field) {
									return field.name.split(',').map(function (item) { return item.trim(); });
								}).flat();
								// Save only required cookies
								if (document.cookieChangedCategories) document.cookieChangedCategories(1, names);
							} else {
								// Save no cookies
								if (document.cookieChangedCategories) document.cookieChangedCategories(0, []);
							}

							block.remove();
							document.consentChanged();
						})
						.appendTo(btnWrapper);
				}

				if (cookiePolicyList && cookiePolicyList.length && cookiePolicyCustomizeButtonText) {
					$('<button>')
						.attr({type: 'button'})
						.css({
							backgroundColor: "#5cb85c",
							color: "#ffffff",
							fontFamily: "Arial,Helvetica,sans-serif",
							fontSize: 14,
							marginRight: '5px',
							marginLeft: '5px'
						})
						.text(cookiePolicyCustomizeButtonText)
						.on('click', function () {
							modal.modal('toggle');
						})
						.appendTo(btnWrapper);
				}

				if (cookiePolicyButtonText) {
					$('<button>')
						.attr({type: 'button'})
						.css({
							backgroundColor: "#5cb85c",
							color: "#ffffff",
							fontFamily: "Arial,Helvetica,sans-serif",
							fontSize: 14,
							marginRight: '5px',
							marginLeft: '5px'
						})
						.text(cookiePolicyButtonText)
						.on('click', function () {
							if ($('#customCookiesForm').length) {
								$('#customCookiesForm').submit();
								return;
							}

							if (document.cookieChangedCategories) {
								document.cookieChangedCategories(1, ['*']);
							}

							block.remove();
							document.consentChanged();
						})
						.appendTo(btnWrapper);
				}

				$(document.body).append(block);

                if (block.height() >= $(window).height() * 0.4) {
                    block.addClass('center');
                }
			}
		});
	</script>
		<script type="text/javascript">
		window._spDefer.add(function() {
<?php $wb_form_send_success = popSessionOrGlobalVar("wb_form_send_success"); ?>
<?php if (($wb_form_send_state = popSessionOrGlobalVar("wb_form_send_state"))) { ?>
	<?php if (($wb_form_popup_mode = popSessionOrGlobalVar("wb_form_popup_mode")) && (isset($wbPopupMode) && $wbPopupMode)) { ?>
		if (window !== window.parent && window.parent.postMessage) {
			var data = {
				event: "wb_contact_form_sent",
				data: {
					state: "<?php echo str_replace('"', '\"', $wb_form_send_state); ?>",
					type: "<?php echo $wb_form_send_success ? "success" : "danger"; ?>"
				}
			};
			window.parent.postMessage(data, "<?php echo str_replace('"', '\"', popSessionOrGlobalVar("wb_target_origin")); ?>");
		}
	<?php $wb_form_send_success = false; $wb_form_send_state = null; $wb_form_popup_mode = false; ?>
	<?php } else { ?>
		wb_show_alert("<?php echo str_replace(array('"', "\r", "\n"), array('\"', "", "<br/>"), $wb_form_send_state); ?>", "<?php echo $wb_form_send_success ? "success" : "danger"; ?>");
	<?php } ?>
<?php } ?>
});    </script>
</head>


<body class="site site-lang-sw<?php if (isset($wbPopupMode) && $wbPopupMode) echo ' popup-mode'; ?> " <?php ?> wb-maintenance-page="true"><div id="wb_root" class="root wb-layout-vertical"><div class="wb_sbg"></div><div id="wb_main_a188dd9eef8c003ef3177d26d923a87d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a189b043207e00f789d16f917c3c3fc1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a189b04320810001642be2002829b1c1" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="2.17%" viewBox="0 0 1921.02083 1793.982" style="direction: ltr; color:#0ca3a6"><text x="1.02083" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a189b0432088000f6d9cd9629e5d292b" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h1 class="wb-stl-heading1" style="text-align: center;"><span style="color:#000000;">Discover Jukanye: Your Gateway to Thriving Online Business Solutions</span></h1></div></div></div><div id="wb_footer_c" class="wb_element" data-plugin="WB_Footer" style="text-align: center; width: 100%;"><div class="wb_footer"></div><script>window._spDefer.add(function() {
			$(function() {
				var footer = $(".wb_footer");
				var html = (footer.html() + "").replace(/^\s+|\s+$/g, "");
				if (!html) {
					footer.parent().remove();
					footer = $("#footer, #footer .wb_cont_inner");
					footer.css({height: ""});
				}
			});
			});</script></div></div></div><script type="text/javascript">window._spDefer.add(function() { $(function() { wb_require(["store/js/StoreCartElement"], function(app) {}); }); });</script>
<div class="wb_pswp" tabindex="-1" role="dialog" aria-hidden="true">
</div>
</div><script src="js/jquery-3.5.1.min.js" type="text/javascript"></script>
	<script src="js/common-bundle.js?ts=20260802185857" type="text/javascript" defer></script>
	<script src="js/a188dd9eef8c003ef3177d26d923a87d-bundle.js?ts=20260802185857" type="text/javascript" defer></script>{{hr_out}}<script>
    document.addEventListener('DOMContentLoaded', function () {
        window._spDefer.done();
    });
</script>
</body>
</html>


<?php } ?>
