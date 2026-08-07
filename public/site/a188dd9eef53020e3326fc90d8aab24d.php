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

				document.cookie = '__cookie_law__=' + (2) + '; path=/; expires=Wed, 28 Jul 2027 18:59:16 GMT';

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
	<title><?php echo htmlspecialchars((isset($seoTitle) && $seoTitle !== "") ? $seoTitle : "Contacts"); ?></title>
	<base href="{{base_url}}" />
	<?php echo isset($sitemapUrls) ? (generateCanonicalUrl($sitemapUrls)."\n") : ""; ?>	
		<link rel="alternate" hreflang="en" href="{{base_url}}{{lang_en}}" />
		<link rel="alternate" hreflang="x-default" href="{{base_url}}{{lang_en}}" />
			<link rel="alternate" hreflang="sw" href="{{base_url}}{{lang_sw}}" />
		
						<meta name="viewport" content="width=device-width, initial-scale=1" />
					<meta name="description" content="<?php echo htmlspecialchars((isset($seoDescription) && $seoDescription !== "") ? $seoDescription : "Contacts"); ?>" />
			<meta name="keywords" content="<?php echo htmlspecialchars((isset($seoKeywords) && $seoKeywords !== "") ? $seoKeywords : "Contacts"); ?>" />
			
	<!-- Facebook Open Graph -->
		<meta property="og:title" content="<?php echo htmlspecialchars((isset($seoTitle) && $seoTitle !== "") ? $seoTitle : "Contacts"); ?>" />
			<meta property="og:description" content="<?php echo htmlspecialchars((isset($seoDescription) && $seoDescription !== "") ? $seoDescription : "Contacts"); ?>" />
			<meta property="og:image" content="<?php echo htmlspecialchars((isset($seoImage) && $seoImage !== "") ? "{{base_url}}".$seoImage : ""); ?>" />
			<meta property="og:type" content="article" />
			<meta property="og:url" content="__wb_curr_url__" />
		<!-- Facebook Open Graph end -->

		<meta name="generator" content="Website Builder" />
			<link href="css/common-bundle.css?ts=20260802185857" rel="stylesheet" type="text/css" />
	<link href="css/a188dd9eef53020e3326fc90d8aab24d-bundle.css?ts=20260802185857" rel="stylesheet" type="text/css" id="wb-page-stylesheet" />
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


<body class="site site-lang-en<?php if (isset($wbPopupMode) && $wbPopupMode) echo ' popup-mode'; ?> " <?php ?>><div id="wb_root" class="root wb-layout-vertical"><div class="wb_sbg"></div><div id="wb_header_a188dd9eef53020e3326fc90d8aab24d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc3858a7a4bf4599d6087d14" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc38596f36338d0b0d66657b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1986657436700dbe63ba0cbad5bbe2c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fb4223ec700f33f6a6750b25b7549" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fc26ad9c300737c8a0c139e48b498" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc385abbb04767f5aaa74a38" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/63a123b911049cc657f1d0f2a9cc7765_fit.png?ts=1785686355"></div></div></div><div id="a19fb4297212030bdabc97de04dae2a0" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom49">JULIUS KAMBARAGE NYERERE INTERNATIONAL FESTIVAL</h2>
</div></div></div><div id="a19fb8f3ccdb0019df519e762dfdd698" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fb916af1600f5376dfc3a8a6285a9" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc385c9ffc3832657b56d9e6" class="wb_element wb-menu wb-prevent-layout-click wb-menu-mobile" data-plugin="Menu"><span class="btn btn-default btn-collapser"><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></span><?php MenuElement::render((object) array(
	'type' => 'hmenu onclick',
	'dir' => 'ltr',
	'items' => array(
		(object) array(
			'id' => 1,
			'href' => '{{base_url}}',
			'name' => 'Home',
			'class' => '',
			'children' => array()
		),
		(object) array(
			'id' => 2,
			'href' => 'Sponsors/',
			'name' => 'Sponsors',
			'class' => '',
			'children' => array()
		),
		(object) array(
			'id' => 3,
			'href' => 'Donate/',
			'name' => 'Donate',
			'class' => '',
			'children' => array()
		),
		(object) array(
			'id' => 4,
			'href' => 'Register/',
			'name' => 'Register',
			'class' => '',
			'children' => array()
		),
		(object) array(
			'id' => 5,
			'href' => 'Download/',
			'name' => 'Upload / Download',
			'class' => '',
			'children' => array()
		),
		(object) array(
			'id' => 6,
			'href' => 'Award-Nominees/',
			'name' => 'Award Nominees',
			'class' => '',
			'children' => array()
		),
		(object) array(
			'id' => 7,
			'href' => 'Schedule/',
			'name' => 'Schedule',
			'class' => '',
			'children' => array()
		),
		(object) array(
			'id' => 8,
			'href' => 'Event-Products/',
			'name' => 'Event Products',
			'class' => '',
			'children' => array()
		),
		(object) array(
			'id' => 9,
			'href' => 'About-Us/',
			'name' => 'About Us',
			'class' => '',
			'children' => array()
		),
		(object) array(
			'id' => 10,
			'href' => 'Contacts/',
			'name' => 'Contacts',
			'class' => 'wb_this_page_menu_item active',
			'children' => array()
		)
	)
)); ?><div class="clearfix"></div></div><div id="a1986261ec820076e3adda0fc40023c6" class="wb_element wb-prevent-layout-click" data-plugin="Languages"><div data-type="names" class="lang-selector"><a class="btn btn-default active" href="%7B%7Blang_en%7D%7D" title="English" data-lang="en">English</a><a class="btn btn-default" href="%7B%7Blang_sw%7D%7D" title="Kiswahili" data-lang="sw">Kiswahili</a></div></div><div id="a19889a6cc28008a37408cde7fa661d8" class="wb_element" data-plugin="countdown">
	<style>
		#a19889a6cc28008a37408cde7fa661d8_countdown_timer {
		    width: 100%;
		    height: 100%;
			font-family: 'DM Serif Display',Arial,serif;
			font-size: 38.333333333333px;
			color: #fbf9f9;
			text-align: center;
			line-height: 100%;
			display: flex;
			justify-content: space-around;
			align-items: center;
			flex-wrap: nowrap;
			 font-style: normal; 
			 font-weight: normal; 
			 text-decoration: none; 
	   	}
		@media all and (max-width: 320px) {
			#a19889a6cc28008a37408cde7fa661d8_countdown_timer {
				font-size: px;
			}
			#a19889a6cc28008a37408cde7fa661d8_countdown_timer .dlmtr {
				display: none;
			}
		}
		#a19889a6cc28008a37408cde7fa661d8_countdown_timer .dlmtr {
			display: inline-block;
			position: relative;
		    vertical-align: middle;
				margin-top: calc(15px + 12px);
				margin-bottom: calc(15px + 12px);
	    }
		#a19889a6cc28008a37408cde7fa661d8_countdown_timer .numblock {
			display: inline-block;
			position: relative;
			vertical-align: middle;
				margin-top: calc(15px + 12px);
				margin-bottom: calc(15px + 12px);
	    }
		#a19889a6cc28008a37408cde7fa661d8_countdown_timer .numblock .num {
	    	position: absolute;
	   		display: block;
			top: 0;
			left: 50%;
		    transform: translateX(-50%);
	    }
		#a19889a6cc28008a37408cde7fa661d8_countdown_timer .numblock .plchldr {
			color: transparent !important;
			opacity: 0;
			 font-style: normal; 
			 font-weight: normal; 
			 text-decoration: none; 
	    }
		#a19889a6cc28008a37408cde7fa661d8_countdown_timer .numblock:after {
			font-family: Helvetica,Arial,sans-serif;
			font-size: 12px;
			color: #dfc91b;
			text-transform: capitalize;
			text-align: center;
			line-height: 100%;
			position: absolute;
				top: -15px;
				bottom: -15px;
			left: 50%;
			transform: translateX(-50%);
			 font-style: normal; 
			 font-weight: normal; 
			 text-decoration: none; 
		}
		#a19889a6cc28008a37408cde7fa661d8_countdown_timer .numblock.days:after {
			content: "days";
	    }
		#a19889a6cc28008a37408cde7fa661d8_countdown_timer .numblock.hours:after {
			content: "hours";
		}
		#a19889a6cc28008a37408cde7fa661d8_countdown_timer .numblock.mins:after {
			content: "minutes";
		}
		#a19889a6cc28008a37408cde7fa661d8_countdown_timer .numblock.secs:after {
			content: "seconds";
		}
		#a19889a6cc28008a37408cde7fa661d8_wb_caption {
		    width: 100%;
		    height: 100%;
			background-color: transparent !important;
			display: flex;
		    justify-content: center;
			align-items: center;
	    }
		#a19889a6cc28008a37408cde7fa661d8_wb_caption:before {
			content: "";
			display: inline-block;
			vertical-align: middle;
			height: auto;
		}
	</style>
	<div class="wb_caption smaller" style="position: relative" id="a19889a6cc28008a37408cde7fa661d8_wb_caption">
		<div id="a19889a6cc28008a37408cde7fa661d8_countdown_timer" style="opacity: 0">
			<div class="numblock days"><span class="plchldr">8</span><span class="num"></span></div>
			<div class="dlmtr">:</div>
			<div class="numblock hours"><span class="plchldr">88</span><span class="num"></span></div>
			<div class="dlmtr">:</div>
			<div class="numblock mins"><span class="plchldr">88</span><span class="num"></span></div>
			<div class="dlmtr">:</div>
			<div class="numblock secs"><span class="plchldr">88</span><span class="num"></span></div>
		</div>
	</div>

<script>window._spDefer.add(function() {
	(function () {
		var countDown_a19889a6cc28008a37408cde7fa661d8 = {
			start: function() {
				this.countDownBlock = $("#a19889a6cc28008a37408cde7fa661d8_countdown_timer");
				this.textAfterBlock = $("#a19889a6cc28008a37408cde7fa661d8_countdown_text_after");
				this.daysBlock = this.countDownBlock.find(".days .num");
				this.hoursBlock = this.countDownBlock.find(".hours .num");
				this.minsBlock = this.countDownBlock.find(".mins .num");
				this.secsBlock = this.countDownBlock.find(".secs .num");

				var timerDate = new Date(1818745560000);
				var currDate = new Date();

				var diff = timerDate.getTime() - currDate.getTime();
				this.diffDays = Math.floor(diff / (1000 * 60 * 60 * 24));
				diff = diff - 1000 * 60 * 60 * 24 * this.diffDays;
				this.diffHours = Math.floor(diff / (1000 * 60 * 60));
				diff = diff - 1000 * 60 * 60 * this.diffHours;
				this.diffMins = Math.floor(diff / (1000 * 60));
				diff = diff - 1000 * 60 * this.diffMins;
				this.diffSecs = Math.floor(diff / 1000);

				if (this.diffDays < 0 || this.diffHours < 0 || this.diffMins < 0 || this.diffSecs < 0
					|| (this.diffDays === 0 && this.diffHours === 0 && this.diffMins === 0 && this.diffSecs === 0))
				{
					if (window.countDownInterval_a19889a6cc28008a37408cde7fa661d8) clearInterval(window.countDownInterval_a19889a6cc28008a37408cde7fa661d8);
					this.daysBlock.text("0");
					this.hoursBlock.text("00");
					this.minsBlock.text("00");
					this.secsBlock.text("00");
				}
				else {
					this.daysBlock.text(this.diffDays);
					this.countDownBlock.find('.days .plchldr').text(this.diffDays);
					this.hoursBlock.text(this.pad(this.diffHours));
					this.minsBlock.text(this.pad(this.diffMins));
					this.secsBlock.text(this.pad(this.diffSecs));
					this.countDownBlock.show();
					this.textAfterBlock.hide();

					var self = this;
					if (window.countDownInterval_a19889a6cc28008a37408cde7fa661d8) clearInterval(window.countDownInterval_a19889a6cc28008a37408cde7fa661d8);
					window.countDownInterval_a19889a6cc28008a37408cde7fa661d8 = setInterval(function () {
						var ended = self.tick();
						if (ended) {
							clearInterval(window.countDownInterval_a19889a6cc28008a37408cde7fa661d8);
						};
						self.daysBlock.text(self.diffDays);
						self.hoursBlock.text(self.pad(self.diffHours));
						self.minsBlock.text(self.pad(self.diffMins));
						self.secsBlock.text(self.pad(self.diffSecs));
					}, 1000);
				}
			},
			pad: function(val) {
				if (("" + val).length === 1) {
					return '0' + val;
				}
				return val;
			},
			tick: function() {
				if (this.diffDays === 0 && this.diffHours === 0 && this.diffMins === 0 && this.diffSecs === 0) {
					return true;
				}
				else {
					if (this.diffSecs > 0) {
						this.diffSecs--;
					} else {
						this.diffSecs = 59;
						if (this.diffMins > 0) {
							this.diffMins--;
						} else {
							this.diffMins = 59;
							if (this.diffHours > 0) {
								this.diffHours--;
							} else {
								this.diffHours = 23;
								if (this.diffDays > 0) {
									this.diffDays--;
								}
							}
						}
					}
				}
				return false;
			}
		};
		countDown_a19889a6cc28008a37408cde7fa661d8.start();

		var cBlock = $('#a19889a6cc28008a37408cde7fa661d8_countdown_timer');
		var cChildren = cBlock.children();

		var elem = $('[data-id=a19889a6cc28008a37408cde7fa661d8], #a19889a6cc28008a37408cde7fa661d8');
		var isAutoLayout = "69" === 'auto';
		var height = parseFloat('69');
		var resizeFn = function (repeat) {
			cBlock.css('opacity', 0);
			if (isAutoLayout) {
				cBlock.css('fontSize', 1);

				var innerWidth;
				var maxIterations = 100;
				do {
					cBlock.css('fontSize', parseInt(cBlock.css('fontSize')) + 1);
					innerWidth = cChildren.toArray().reduce(function (sum, item) {
						return sum + item.offsetWidth;
					}, 0);
					if (maxIterations > 0) maxIterations--; else break;
				} while (innerWidth < cBlock.width() * 0.8);
			} else {
				var h = cBlock.outerHeight();
				h -= 15 + 12;
				cBlock.css('fontSize', h);

				let innerWidth = cChildren.toArray().reduce(function (sum, item) {
					return sum+item.offsetWidth;
				}, 0);

				if (innerWidth > cBlock.width()) h *= cBlock.width() / innerWidth;

				cBlock.css('fontSize', h);
			}
			cBlock.css('opacity', 1);

			if (!repeat) {
				setTimeout(function () {
					resizeFn(true)
				}, 500);
			}
		}

		var timer = null;
		$(window).resize(function () {
			if (timer) {
				clearTimeout(timer);
				timer = null;
			}
			timer = setTimeout(resizeFn, 200);
		});
		$(window).resize();
	})();
});</script></div></div></div><div id="a19fb8f3c64000b0747d009eda7d1a44" class="wb_element wb-prevent-layout-click wb_gallery" data-plugin="Gallery"><script type="text/javascript">
			window._spDefer.add(function() {
				$(function() {
					(function(GalleryLib) {
						var el = document.getElementById("a19fb8f3c64000b0747d009eda7d1a44");
						var lib = new GalleryLib({"id":"a19fb8f3c64000b0747d009eda7d1a44","height":"auto","type":"slideshow","trackResize":true,"interval":5,"speed":1000,"images":[{"thumb":"gallery_gen\/9147f62c31174403cafdbe5847fd40e4_301.5x134_fill.png","src":"gallery_gen\/a0295deaa452d91f264f568d7ace6a7c_fit.png?ts=1785686355","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/f3e0a489b3b22ccf940c58dffbcd2ad4_301.5x134_fill.jpg","src":"gallery_gen\/2a406b85dd90631c40b79158c1877d4f_fit.jpg?ts=1785686355","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/da8e24800b8f72dd8eed800429e1a18b_301.5x134_fill.jpg","src":"gallery_gen\/3c456088697ef08011819b714ae09234_fit.jpg?ts=1785686355","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/d362c813c5330d042dde3a964f0bfed1_301.5x134_fill.jpg","src":"gallery_gen\/30ce731cc7b1cc1edd84ddce750a6366_fit.jpg?ts=1785686355","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/33145b78952db630d35b79ec91eed8d5_301.5x134_fill.jpg","src":"gallery_gen\/47e964e8cdbbdbffac1cc75dec2c4369_fit.jpg?ts=1785686356","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/72018fdb993c6ceb781c0740d2917da8_301.5x134_fill.jpg","src":"gallery_gen\/a55bfef5daf82a78f393f684c67908ca_fit.jpg?ts=1785686356","width":1881,"height":836,"title":"","link":null,"description":"","address":""}],"border":{"border":"5px none #00008c"},"padding":10,"thumbWidth":100,"thumbHeight":100,"thumbAlign":"center","thumbPadding":6,"thumbAnim":"","thumbShadow":"","imageCover":true,"disablePopup":false,"controlsArrow":"chevron","controlsArrowSize":14,"controlsArrowStyle":{"normal":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}},"hover":{"color":"#DDDDDD","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#222222","forText":true,"css":{"text-shadow":"0px 0px 1px #222222"}}},"active":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}}},"slideOpacity":100,"showPictureCaption":"always","captionIncludeDescription":false,"captionPosition":"center bottom","mapTypeId":"roadmap","markerIconTypeId":"thumbs","zoom":2,"mapCenter":{"latLng":{"lat":41.244772343082,"lng":-5.2734375},"text":"41.244772343082076, -5.2734375"},"key":"AIzaSyD-9xtp38UunEmx7XfJ7eBh-K4w8qQ6SEw","theme":"default","color":"#eeeeee","showSatellite":true,"showZoom":true,"showStreetView":true,"showFullscreen":true,"allowDragging":true,"showRoads":true,"showLandmarks":true,"showLabels":true,"locale":"en_US","pauseOnHover":true});
						lib.appendTo(el);
					})(window.wbmodGalleryLib);
				});
			});
		</script></div></div></div></div></div><div id="a19fb429722400fb62f16c17777e0dbd" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19fb42971f400a1d073d65740953b98" class="wb_element" data-plugin="Button"><a class="wb_button" href="Register/"><span>Register to participate</span></a></div><div id="a19fb429722c00d2df553faa4f96bb89" class="wb_element" data-plugin="Button"><a class="wb_button" href="Event-Products/"><span>Products</span></a></div><div id="a19fb429721202bf4c948ac3d6dde212" class="wb_element" data-plugin="Button"><a class="wb_button" href="Award-Nominees/"><span>Award Nominees</span></a></div></div></div></div></div></div></div></div></div></div></div><div id="wb_main_a188dd9eef53020e3326fc90d8aab24d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc385f200426b5174f4def70" class="wb_element wb-elm-orient-horizontal" data-plugin="Line"><div class="wb-elm-line"></div></div><div id="a188dd9ebc3861eea39af9b8f837bd73" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc3862b43a37cf14d603271c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc3863b7110ee5a5df862bcb" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc38642f410f4dd4a096c5aa" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h1 class="wb-stl-heading1" style="text-align: center;"><span style="color:rgba(12,163,166,1);">Get in Touch with Jukanye Festival</span></h1>

<p style="text-align: center;"> </p>

<p class="wb-stl-normal" style="text-align: center;">Discover more, be part of it. Let's be together to make a memorable event.</p>
</div><div id="a188dd9ebc38650b2d8c80406082d00a" class="wb_element wb-elm-orient-horizontal" data-plugin="Line"><div class="wb-elm-line"></div></div><div id="a188dd9ebc386adeab519ec36f708f28" class="wb_element" data-plugin="Form"><form id="a188dd9ebc386adeab519ec36f708f28_form" class="wb_form wb_mob_form wb_form_ltr wb_form_vertical" method="post" enctype="multipart/form-data" action="__wb_curr_url__"><input type="hidden" name="wb_form_id" value="9197457e"><input type="hidden" name="wb_form_uuid" value="f55fba5c"><input type="hidden" name="secure_token" value="<?php echo session_id() ? ('a188dd9ebc386adeab519ec36f708f28:' . ($_SESSION['wb_form_secure_token_a188dd9ebc386adeab519ec36f708f28'] = md5(microtime()."a188dd9eef53020e3326fc90d8aab24d"))) : ''?>"><textarea name="message" rows="3" cols="20" class="hpc" autocomplete="off"></textarea><table><tr><th>Name<span class="text-danger">&nbsp;*</span></th><td><input type="hidden" name="wb_input_0" value="Name"><div><input class="form-control form-field" type="text" value="" placeholder="" maxlength="255" name="wb_input_0" required="required"></div></td></tr><tr><th>Email<span class="text-danger">&nbsp;*</span></th><td><input type="hidden" name="wb_input_1" value="Email"><div><input class="form-control form-field" type="text" value="" placeholder="" maxlength="255" name="wb_input_1" required="required"></div></td></tr><tr><th>City<span class="text-danger">&nbsp;*</span></th><td><input type="hidden" name="wb_input_2" value="City"><div><input class="form-control form-field" type="text" value="" placeholder="" maxlength="255" name="wb_input_2" required="required"></div></td></tr><tr class="area-row"><th>Message<span class="text-danger">&nbsp;*</span></th><td><input type="hidden" name="wb_input_3" value="Message"><div><textarea class="form-control form-field form-area-field" rows="4" placeholder="" name="wb_input_3" required="required"></textarea></div></td></tr><tr class="form-footer"><td colspan="2" class="text-right"><button type="submit" class="btn btn-default"><span>Submit</span></button></td></tr></table><?php if (isset($wbPopupMode) && $wbPopupMode): ?><input type="hidden" name="wb_popup_mode" value="1" /><?php endif; ?></form><script>window._spDefer.add(function() {
			<?php $wb_form_id = sessionOrGlobalVar("wb_form_id"); if ($wb_form_id == "9197457e") { ?>
				<?php popSessionOrGlobalVar("wb_form_id"); ?>
				var formValues = <?php echo json_encode(popSessionOrGlobalVar("post")); ?>;
				var formErrors = <?php echo json_encode(popSessionOrGlobalVar("formErrors")); ?>;
				wb_form_validateForm("9197457e", formValues, formErrors);
			<?php } ?>
			});</script><script>window._spDefer.add(function() {
	$('#a188dd9ebc386adeab519ec36f708f28 form').on('submit', function (e) {
		if (document.cookieIsAllowed && !document.cookieIsAllowed("_GRECAPTCHA")) {
			e.stopPropagation();
			$(this).find('button[type=submit]').append($('<input>').attr('type', 'hidden').attr('name', 'cookieDontAllow').val('1'));
			return true;
		}		;return true;
	});
});
</script></div></div></div></div></div></div></div></div></div><div id="wb_footer_a188dd9eef53020e3326fc90d8aab24d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fc2149cd000eb3b8848562ec6f176" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc386d7d4d77961b3399b7e7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19fb429723a03cab5671bd0692f5610" class="wb_element" data-plugin="Button"><a class="wb_button" href="{{base_url}}"><span><svg xmlns="http://www.w3.org/2000/svg" width="1793.982" height="1793.982" viewBox="0 0 1793.982 1793.982" style="display: inline-block; vertical-align: middle; position: relative; top: -1px; height: 1em; width: 1em; overflow: visible; direction: ltr;"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg>&nbsp;Home Page</span></a></div><div id="a188dd9ebc386e9c761088b65418f7a1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc386f7f651dc7e4d0792624" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="Homeb/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#4be6e6"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a188dd9ebc38700f452a2fef2fcabe01" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="Homeb/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1024 1024" style="direction: ltr; color:#ffffff"><text x="64" y="960" font-size="1024" fill="currentColor" style='font-family: "builder-ui-icons-plugins"'></text></svg></a></div></div></div><div id="a188dd9ebc3871cfcba1a4cf7091cb6d" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="Homeb/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#ffffff"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div></div></div><div id="a19fc20bdb7e00c6080e244c0b41b351" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-custom16" style="text-align: center;">ADDRESS:</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">P,O BOX  DAR- ES - SALAAM, TANZANIA</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">PHONE: +255 746 174403 +255 789  388232 +255 719 083050</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">EMAIL: jukanyefestival@gmail.com</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">info@jukanye.com</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">WEBSITE: www.jukanye.com</h3>
</div><div id="a188dd9ebc38721835f60daecdc81bab" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/3a0fa4358ae2f4fb06a94eaab03b4403_fit.png?ts=1785686356"></div></div></div><div id="a19fc20a045f00e06f9422396398c49c" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-footer">© 2025 <a href="http://jukanye.com">jukanye.com</a> - Honoring Africa’s True Patriots and Heroes.</p>
</div></div></div></div></div><div id="wb_footer_c" class="wb_element" data-plugin="WB_Footer" style="text-align: center; width: 100%;"><div class="wb_footer"></div><script>window._spDefer.add(function() {
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
	<script src="js/a188dd9eef53020e3326fc90d8aab24d-bundle.js?ts=20260802185857" type="text/javascript" defer></script>{{hr_out}}<script>
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

				document.cookie = '__cookie_law__=' + (2) + '; path=/; expires=Wed, 28 Jul 2027 18:59:16 GMT';

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
	<title><?php echo htmlspecialchars((isset($seoTitle) && $seoTitle !== "") ? $seoTitle : "Mawasiliano"); ?></title>
	<base href="{{base_url}}" />
	<?php echo isset($sitemapUrls) ? (generateCanonicalUrl($sitemapUrls)."\n") : ""; ?>	
		<link rel="alternate" hreflang="en" href="{{base_url}}{{lang_en}}" />
		<link rel="alternate" hreflang="x-default" href="{{base_url}}{{lang_en}}" />
			<link rel="alternate" hreflang="sw" href="{{base_url}}{{lang_sw}}" />
		
						<meta name="viewport" content="width=device-width, initial-scale=1" />
					<meta name="description" content="<?php echo htmlspecialchars((isset($seoDescription) && $seoDescription !== "") ? $seoDescription : "Mawasiliano"); ?>" />
			<meta name="keywords" content="<?php echo htmlspecialchars((isset($seoKeywords) && $seoKeywords !== "") ? $seoKeywords : "Mawasiliano"); ?>" />
			
	<!-- Facebook Open Graph -->
		<meta property="og:title" content="<?php echo htmlspecialchars((isset($seoTitle) && $seoTitle !== "") ? $seoTitle : "Mawasiliano"); ?>" />
			<meta property="og:description" content="<?php echo htmlspecialchars((isset($seoDescription) && $seoDescription !== "") ? $seoDescription : "Mawasiliano"); ?>" />
			<meta property="og:image" content="<?php echo htmlspecialchars((isset($seoImage) && $seoImage !== "") ? "{{base_url}}".$seoImage : ""); ?>" />
			<meta property="og:type" content="article" />
			<meta property="og:url" content="__wb_curr_url__" />
		<!-- Facebook Open Graph end -->

		<meta name="generator" content="Website Builder" />
			<link href="css/common-bundle.css?ts=20260802185857" rel="stylesheet" type="text/css" />
	<link href="css/a188dd9eef53020e3326fc90d8aab24d-bundle.css?ts=20260802185857" rel="stylesheet" type="text/css" id="wb-page-stylesheet" />
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


<body class="site site-lang-sw<?php if (isset($wbPopupMode) && $wbPopupMode) echo ' popup-mode'; ?> " <?php ?>><div id="wb_root" class="root wb-layout-vertical"><div class="wb_sbg"></div><div id="wb_header_a188dd9eef53020e3326fc90d8aab24d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc3858a7a4bf4599d6087d14" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc38596f36338d0b0d66657b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1986657436700dbe63ba0cbad5bbe2c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fb4223ec700f33f6a6750b25b7549" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fc26ad9c300737c8a0c139e48b498" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc385abbb04767f5aaa74a38" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/63a123b911049cc657f1d0f2a9cc7765_fit.png?ts=1785686356"></div></div></div><div id="a19fb4297212030bdabc97de04dae2a0" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom49">TAMASHA LA KIMATAIFA LA JULIUS KAMBARAGE NYERERE</h2>
</div></div></div><div id="a19fb8f3ccdb0019df519e762dfdd698" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fb916af1600f5376dfc3a8a6285a9" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc385c9ffc3832657b56d9e6" class="wb_element wb-menu wb-prevent-layout-click wb-menu-mobile" data-plugin="Menu"><span class="btn btn-default btn-collapser"><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></span><?php MenuElement::render((object) array(
	'type' => 'hmenu onclick',
	'dir' => 'ltr',
	'items' => array(
		(object) array(
			'id' => 1,
			'href' => 'sw/',
			'name' => 'Mwanzo',
			'class' => '',
			'children' => array()
		),
		(object) array(
			'id' => 2,
			'href' => 'sw/Wadhamini/',
			'name' => 'Wadhamini',
			'class' => '',
			'children' => array()
		),
		(object) array(
			'id' => 3,
			'href' => 'sw/Changia/',
			'name' => 'Changia',
			'class' => '',
			'children' => array()
		),
		(object) array(
			'id' => 4,
			'href' => 'sw/Jisajiri/',
			'name' => 'Jisajiri',
			'class' => '',
			'children' => array()
		),
		(object) array(
			'id' => 5,
			'href' => 'sw/Pakua/',
			'name' => 'Pakua',
			'class' => '',
			'children' => array()
		),
		(object) array(
			'id' => 6,
			'href' => 'sw/Waliopendekezwa-kupewa-Tuzo/',
			'name' => 'Washiriki',
			'class' => '',
			'children' => array()
		),
		(object) array(
			'id' => 7,
			'href' => 'sw/Schedule/',
			'name' => 'Ratiba',
			'class' => '',
			'children' => array()
		),
		(object) array(
			'id' => 8,
			'href' => 'sw/Bidhaa-za-Tamasha/',
			'name' => 'Bidhaa',
			'class' => '',
			'children' => array()
		),
		(object) array(
			'id' => 9,
			'href' => 'sw/Shughuli-Zetu/',
			'name' => 'Shughuli Zetu',
			'class' => '',
			'children' => array()
		),
		(object) array(
			'id' => 10,
			'href' => 'sw/Mawasiliano/',
			'name' => 'Mawasiliano',
			'class' => 'wb_this_page_menu_item active',
			'children' => array()
		)
	)
)); ?><div class="clearfix"></div></div><div id="a1986261ec820076e3adda0fc40023c6" class="wb_element wb-prevent-layout-click" data-plugin="Languages"><div data-type="names" class="lang-selector"><a class="btn btn-default" href="%7B%7Blang_en%7D%7D" title="English" data-lang="en">English</a><a class="btn btn-default active" href="%7B%7Blang_sw%7D%7D" title="Kiswahili" data-lang="sw">Kiswahili</a></div></div><div id="a19889a6cc28008a37408cde7fa661d8" class="wb_element" data-plugin="countdown">
	<style>
		#a19889a6cc28008a37408cde7fa661d8_countdown_timer {
		    width: 100%;
		    height: 100%;
			font-family: 'DM Serif Display',Arial,serif;
			font-size: 38.333333333333px;
			color: #fbf9f9;
			text-align: center;
			line-height: 100%;
			display: flex;
			justify-content: space-around;
			align-items: center;
			flex-wrap: nowrap;
			 font-style: normal; 
			 font-weight: normal; 
			 text-decoration: none; 
	   	}
		@media all and (max-width: 320px) {
			#a19889a6cc28008a37408cde7fa661d8_countdown_timer {
				font-size: px;
			}
			#a19889a6cc28008a37408cde7fa661d8_countdown_timer .dlmtr {
				display: none;
			}
		}
		#a19889a6cc28008a37408cde7fa661d8_countdown_timer .dlmtr {
			display: inline-block;
			position: relative;
		    vertical-align: middle;
				margin-top: calc(15px + 12px);
				margin-bottom: calc(15px + 12px);
	    }
		#a19889a6cc28008a37408cde7fa661d8_countdown_timer .numblock {
			display: inline-block;
			position: relative;
			vertical-align: middle;
				margin-top: calc(15px + 12px);
				margin-bottom: calc(15px + 12px);
	    }
		#a19889a6cc28008a37408cde7fa661d8_countdown_timer .numblock .num {
	    	position: absolute;
	   		display: block;
			top: 0;
			left: 50%;
		    transform: translateX(-50%);
	    }
		#a19889a6cc28008a37408cde7fa661d8_countdown_timer .numblock .plchldr {
			color: transparent !important;
			opacity: 0;
			 font-style: normal; 
			 font-weight: normal; 
			 text-decoration: none; 
	    }
		#a19889a6cc28008a37408cde7fa661d8_countdown_timer .numblock:after {
			font-family: Helvetica,Arial,sans-serif;
			font-size: 12px;
			color: #dfc91b;
			text-transform: capitalize;
			text-align: center;
			line-height: 100%;
			position: absolute;
				top: -15px;
				bottom: -15px;
			left: 50%;
			transform: translateX(-50%);
			 font-style: normal; 
			 font-weight: normal; 
			 text-decoration: none; 
		}
		#a19889a6cc28008a37408cde7fa661d8_countdown_timer .numblock.days:after {
			content: "days";
	    }
		#a19889a6cc28008a37408cde7fa661d8_countdown_timer .numblock.hours:after {
			content: "hours";
		}
		#a19889a6cc28008a37408cde7fa661d8_countdown_timer .numblock.mins:after {
			content: "minutes";
		}
		#a19889a6cc28008a37408cde7fa661d8_countdown_timer .numblock.secs:after {
			content: "seconds";
		}
		#a19889a6cc28008a37408cde7fa661d8_wb_caption {
		    width: 100%;
		    height: 100%;
			background-color: transparent !important;
			display: flex;
		    justify-content: center;
			align-items: center;
	    }
		#a19889a6cc28008a37408cde7fa661d8_wb_caption:before {
			content: "";
			display: inline-block;
			vertical-align: middle;
			height: auto;
		}
	</style>
	<div class="wb_caption smaller" style="position: relative" id="a19889a6cc28008a37408cde7fa661d8_wb_caption">
		<div id="a19889a6cc28008a37408cde7fa661d8_countdown_timer" style="opacity: 0">
			<div class="numblock days"><span class="plchldr">8</span><span class="num"></span></div>
			<div class="dlmtr">:</div>
			<div class="numblock hours"><span class="plchldr">88</span><span class="num"></span></div>
			<div class="dlmtr">:</div>
			<div class="numblock mins"><span class="plchldr">88</span><span class="num"></span></div>
			<div class="dlmtr">:</div>
			<div class="numblock secs"><span class="plchldr">88</span><span class="num"></span></div>
		</div>
	</div>

<script>window._spDefer.add(function() {
	(function () {
		var countDown_a19889a6cc28008a37408cde7fa661d8 = {
			start: function() {
				this.countDownBlock = $("#a19889a6cc28008a37408cde7fa661d8_countdown_timer");
				this.textAfterBlock = $("#a19889a6cc28008a37408cde7fa661d8_countdown_text_after");
				this.daysBlock = this.countDownBlock.find(".days .num");
				this.hoursBlock = this.countDownBlock.find(".hours .num");
				this.minsBlock = this.countDownBlock.find(".mins .num");
				this.secsBlock = this.countDownBlock.find(".secs .num");

				var timerDate = new Date(1818745560000);
				var currDate = new Date();

				var diff = timerDate.getTime() - currDate.getTime();
				this.diffDays = Math.floor(diff / (1000 * 60 * 60 * 24));
				diff = diff - 1000 * 60 * 60 * 24 * this.diffDays;
				this.diffHours = Math.floor(diff / (1000 * 60 * 60));
				diff = diff - 1000 * 60 * 60 * this.diffHours;
				this.diffMins = Math.floor(diff / (1000 * 60));
				diff = diff - 1000 * 60 * this.diffMins;
				this.diffSecs = Math.floor(diff / 1000);

				if (this.diffDays < 0 || this.diffHours < 0 || this.diffMins < 0 || this.diffSecs < 0
					|| (this.diffDays === 0 && this.diffHours === 0 && this.diffMins === 0 && this.diffSecs === 0))
				{
					if (window.countDownInterval_a19889a6cc28008a37408cde7fa661d8) clearInterval(window.countDownInterval_a19889a6cc28008a37408cde7fa661d8);
					this.daysBlock.text("0");
					this.hoursBlock.text("00");
					this.minsBlock.text("00");
					this.secsBlock.text("00");
				}
				else {
					this.daysBlock.text(this.diffDays);
					this.countDownBlock.find('.days .plchldr').text(this.diffDays);
					this.hoursBlock.text(this.pad(this.diffHours));
					this.minsBlock.text(this.pad(this.diffMins));
					this.secsBlock.text(this.pad(this.diffSecs));
					this.countDownBlock.show();
					this.textAfterBlock.hide();

					var self = this;
					if (window.countDownInterval_a19889a6cc28008a37408cde7fa661d8) clearInterval(window.countDownInterval_a19889a6cc28008a37408cde7fa661d8);
					window.countDownInterval_a19889a6cc28008a37408cde7fa661d8 = setInterval(function () {
						var ended = self.tick();
						if (ended) {
							clearInterval(window.countDownInterval_a19889a6cc28008a37408cde7fa661d8);
						};
						self.daysBlock.text(self.diffDays);
						self.hoursBlock.text(self.pad(self.diffHours));
						self.minsBlock.text(self.pad(self.diffMins));
						self.secsBlock.text(self.pad(self.diffSecs));
					}, 1000);
				}
			},
			pad: function(val) {
				if (("" + val).length === 1) {
					return '0' + val;
				}
				return val;
			},
			tick: function() {
				if (this.diffDays === 0 && this.diffHours === 0 && this.diffMins === 0 && this.diffSecs === 0) {
					return true;
				}
				else {
					if (this.diffSecs > 0) {
						this.diffSecs--;
					} else {
						this.diffSecs = 59;
						if (this.diffMins > 0) {
							this.diffMins--;
						} else {
							this.diffMins = 59;
							if (this.diffHours > 0) {
								this.diffHours--;
							} else {
								this.diffHours = 23;
								if (this.diffDays > 0) {
									this.diffDays--;
								}
							}
						}
					}
				}
				return false;
			}
		};
		countDown_a19889a6cc28008a37408cde7fa661d8.start();

		var cBlock = $('#a19889a6cc28008a37408cde7fa661d8_countdown_timer');
		var cChildren = cBlock.children();

		var elem = $('[data-id=a19889a6cc28008a37408cde7fa661d8], #a19889a6cc28008a37408cde7fa661d8');
		var isAutoLayout = "69" === 'auto';
		var height = parseFloat('69');
		var resizeFn = function (repeat) {
			cBlock.css('opacity', 0);
			if (isAutoLayout) {
				cBlock.css('fontSize', 1);

				var innerWidth;
				var maxIterations = 100;
				do {
					cBlock.css('fontSize', parseInt(cBlock.css('fontSize')) + 1);
					innerWidth = cChildren.toArray().reduce(function (sum, item) {
						return sum + item.offsetWidth;
					}, 0);
					if (maxIterations > 0) maxIterations--; else break;
				} while (innerWidth < cBlock.width() * 0.8);
			} else {
				var h = cBlock.outerHeight();
				h -= 15 + 12;
				cBlock.css('fontSize', h);

				let innerWidth = cChildren.toArray().reduce(function (sum, item) {
					return sum+item.offsetWidth;
				}, 0);

				if (innerWidth > cBlock.width()) h *= cBlock.width() / innerWidth;

				cBlock.css('fontSize', h);
			}
			cBlock.css('opacity', 1);

			if (!repeat) {
				setTimeout(function () {
					resizeFn(true)
				}, 500);
			}
		}

		var timer = null;
		$(window).resize(function () {
			if (timer) {
				clearTimeout(timer);
				timer = null;
			}
			timer = setTimeout(resizeFn, 200);
		});
		$(window).resize();
	})();
});</script></div></div></div><div id="a19fb8f3c64000b0747d009eda7d1a44" class="wb_element wb-prevent-layout-click wb_gallery" data-plugin="Gallery"><script type="text/javascript">
			window._spDefer.add(function() {
				$(function() {
					(function(GalleryLib) {
						var el = document.getElementById("a19fb8f3c64000b0747d009eda7d1a44");
						var lib = new GalleryLib({"id":"a19fb8f3c64000b0747d009eda7d1a44","height":"auto","type":"slideshow","trackResize":true,"interval":5,"speed":1000,"images":[{"thumb":"gallery_gen\/9147f62c31174403cafdbe5847fd40e4_301.5x134_fill.png","src":"gallery_gen\/a0295deaa452d91f264f568d7ace6a7c_fit.png?ts=1785686356","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/f3e0a489b3b22ccf940c58dffbcd2ad4_301.5x134_fill.jpg","src":"gallery_gen\/2a406b85dd90631c40b79158c1877d4f_fit.jpg?ts=1785686356","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/da8e24800b8f72dd8eed800429e1a18b_301.5x134_fill.jpg","src":"gallery_gen\/3c456088697ef08011819b714ae09234_fit.jpg?ts=1785686356","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/d362c813c5330d042dde3a964f0bfed1_301.5x134_fill.jpg","src":"gallery_gen\/30ce731cc7b1cc1edd84ddce750a6366_fit.jpg?ts=1785686356","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/33145b78952db630d35b79ec91eed8d5_301.5x134_fill.jpg","src":"gallery_gen\/47e964e8cdbbdbffac1cc75dec2c4369_fit.jpg?ts=1785686356","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/72018fdb993c6ceb781c0740d2917da8_301.5x134_fill.jpg","src":"gallery_gen\/a55bfef5daf82a78f393f684c67908ca_fit.jpg?ts=1785686356","width":1881,"height":836,"title":"","link":null,"description":"","address":""}],"border":{"border":"5px none #00008c"},"padding":10,"thumbWidth":100,"thumbHeight":100,"thumbAlign":"center","thumbPadding":6,"thumbAnim":"","thumbShadow":"","imageCover":true,"disablePopup":false,"controlsArrow":"chevron","controlsArrowSize":14,"controlsArrowStyle":{"normal":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}},"hover":{"color":"#DDDDDD","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#222222","forText":true,"css":{"text-shadow":"0px 0px 1px #222222"}}},"active":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}}},"slideOpacity":100,"showPictureCaption":"always","captionIncludeDescription":false,"captionPosition":"center bottom","mapTypeId":"roadmap","markerIconTypeId":"thumbs","zoom":2,"mapCenter":{"latLng":{"lat":41.244772343082,"lng":-5.2734375},"text":"41.244772343082076, -5.2734375"},"key":"AIzaSyD-9xtp38UunEmx7XfJ7eBh-K4w8qQ6SEw","theme":"default","color":"#eeeeee","showSatellite":true,"showZoom":true,"showStreetView":true,"showFullscreen":true,"allowDragging":true,"showRoads":true,"showLandmarks":true,"showLabels":true,"locale":"sw_TZ","pauseOnHover":true});
						lib.appendTo(el);
					})(window.wbmodGalleryLib);
				});
			});
		</script></div></div></div></div></div><div id="a19fb429722400fb62f16c17777e0dbd" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19fb42971f400a1d073d65740953b98" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Jisajiri/"><span>Jisajiri Kushiriki</span></a></div><div id="a19fb429722c00d2df553faa4f96bb89" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Bidhaa-za-Tamasha/"><span>Bidhaa</span></a></div><div id="a19fb4297209006e0ba9445e1db2f558" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Waliopendekezwa-kupewa-Tuzo/"><span>Walio pendekezwa Kupata Tuzo</span></a></div></div></div></div></div><div id="a19fb81fa1120059e7c1682d66b9ba06" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"></div></div></div></div></div></div></div></div><div id="wb_main_a188dd9eef53020e3326fc90d8aab24d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc385f200426b5174f4def70" class="wb_element wb-elm-orient-horizontal" data-plugin="Line"><div class="wb-elm-line"></div></div><div id="a188dd9ebc3861eea39af9b8f837bd73" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc3862b43a37cf14d603271c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc3863b7110ee5a5df862bcb" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc38642f410f4dd4a096c5aa" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h1 class="wb-stl-heading1" style="text-align: center;"><span style="color:rgba(12,163,166,1);">Get in Touch with Jukanye Festival</span></h1>

<p style="text-align: center;"> </p>

<p class="wb-stl-normal" style="text-align: center;">Discover more, be part of it. Let's be together to make a memorable event.</p>
</div><div id="a188dd9ebc38650b2d8c80406082d00a" class="wb_element wb-elm-orient-horizontal" data-plugin="Line"><div class="wb-elm-line"></div></div><div id="a188dd9ebc386adeab519ec36f708f28" class="wb_element" data-plugin="Form"><form id="a188dd9ebc386adeab519ec36f708f28_form" class="wb_form wb_mob_form wb_form_ltr wb_form_vertical" method="post" enctype="multipart/form-data" action="__wb_curr_url__"><input type="hidden" name="wb_form_id" value="9197457e"><input type="hidden" name="wb_form_uuid" value="f55fba5c"><input type="hidden" name="secure_token" value="<?php echo session_id() ? ('a188dd9ebc386adeab519ec36f708f28:' . ($_SESSION['wb_form_secure_token_a188dd9ebc386adeab519ec36f708f28'] = md5(microtime()."a188dd9eef53020e3326fc90d8aab24d"))) : ''?>"><textarea name="message" rows="3" cols="20" class="hpc" autocomplete="off"></textarea><table><tr><th>Name<span class="text-danger">&nbsp;*</span></th><td><input type="hidden" name="wb_input_0" value="Name"><div><input class="form-control form-field" type="text" value="" placeholder="" maxlength="255" name="wb_input_0" required="required"></div></td></tr><tr><th>Email<span class="text-danger">&nbsp;*</span></th><td><input type="hidden" name="wb_input_1" value="Email"><div><input class="form-control form-field" type="text" value="" placeholder="" maxlength="255" name="wb_input_1" required="required"></div></td></tr><tr><th>City<span class="text-danger">&nbsp;*</span></th><td><input type="hidden" name="wb_input_2" value="City"><div><input class="form-control form-field" type="text" value="" placeholder="" maxlength="255" name="wb_input_2" required="required"></div></td></tr><tr class="area-row"><th>Message<span class="text-danger">&nbsp;*</span></th><td><input type="hidden" name="wb_input_3" value="Message"><div><textarea class="form-control form-field form-area-field" rows="4" placeholder="" name="wb_input_3" required="required"></textarea></div></td></tr><tr class="form-footer"><td colspan="2" class="text-right"><button type="submit" class="btn btn-default"><span>Submit</span></button></td></tr></table><?php if (isset($wbPopupMode) && $wbPopupMode): ?><input type="hidden" name="wb_popup_mode" value="1" /><?php endif; ?></form><script>window._spDefer.add(function() {
			<?php $wb_form_id = sessionOrGlobalVar("wb_form_id"); if ($wb_form_id == "9197457e") { ?>
				<?php popSessionOrGlobalVar("wb_form_id"); ?>
				var formValues = <?php echo json_encode(popSessionOrGlobalVar("post")); ?>;
				var formErrors = <?php echo json_encode(popSessionOrGlobalVar("formErrors")); ?>;
				wb_form_validateForm("9197457e", formValues, formErrors);
			<?php } ?>
			});</script><script>window._spDefer.add(function() {
	$('#a188dd9ebc386adeab519ec36f708f28 form').on('submit', function (e) {
		if (document.cookieIsAllowed && !document.cookieIsAllowed("_GRECAPTCHA")) {
			e.stopPropagation();
			$(this).find('button[type=submit]').append($('<input>').attr('type', 'hidden').attr('name', 'cookieDontAllow').val('1'));
			return true;
		}		;return true;
	});
});
</script></div></div></div></div></div></div></div></div></div><div id="wb_footer_a188dd9eef53020e3326fc90d8aab24d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fc2149cd000eb3b8848562ec6f176" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc386d7d4d77961b3399b7e7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19fb429723a03cab5671bd0692f5610" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/"><span><svg xmlns="http://www.w3.org/2000/svg" width="1793.982" height="1793.982" viewBox="0 0 1793.982 1793.982" style="display: inline-block; vertical-align: middle; position: relative; top: -1px; height: 1em; width: 1em; overflow: visible; direction: ltr;"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg>&nbsp;Home </span></a></div><div id="a188dd9ebc386e9c761088b65418f7a1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc386f7f651dc7e4d0792624" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="sw/Mwanzo/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#4be6e6"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a188dd9ebc38700f452a2fef2fcabe01" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="sw/Mwanzo/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1024 1024" style="direction: ltr; color:#ffffff"><text x="64" y="960" font-size="1024" fill="currentColor" style='font-family: "builder-ui-icons-plugins"'></text></svg></a></div></div></div><div id="a188dd9ebc3871cfcba1a4cf7091cb6d" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="sw/Mwanzo/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#ffffff"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div></div></div><div id="a19fc20bdb7e00c6080e244c0b41b351" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-custom16" style="text-align: center;">ADDRESS:</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">P,O BOX  DAR- ES - SALAAM, TANZANIA</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">PHONE: +255 746 174403 +255 789  388232 +255 719 083050</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">EMAIL: jukanyefestival@gmail.com</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">info@jukanye.com</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">WEBSITE: www.jukanye.com</h3>
</div><div id="a188dd9ebc38721835f60daecdc81bab" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/3a0fa4358ae2f4fb06a94eaab03b4403_fit.png?ts=1785686356"></div></div></div><div id="a188dd9ebc387353ef2d51652b5ef64e" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-footer">© 2025 <a href="http://jukanye.com">jukanye.com</a> - Kuwaenzi Viongozi wa Afrika Walioongoza Harakati za Ukombozi</p>
</div></div></div></div></div><div id="wb_footer_c" class="wb_element" data-plugin="WB_Footer" style="text-align: center; width: 100%;"><div class="wb_footer"></div><script>window._spDefer.add(function() {
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
	<script src="js/a188dd9eef53020e3326fc90d8aab24d-bundle.js?ts=20260802185857" type="text/javascript" defer></script>{{hr_out}}<script>
    document.addEventListener('DOMContentLoaded', function () {
        window._spDefer.done();
    });
</script>
</body>
</html>


<?php } ?>
