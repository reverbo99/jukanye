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

				document.cookie = '__cookie_law__=' + (2) + '; path=/; expires=Wed, 28 Jul 2027 18:59:07 GMT';

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
	<title><?php echo htmlspecialchars((isset($seoTitle) && $seoTitle !== "") ? $seoTitle : "About Us"); ?></title>
	<base href="{{base_url}}" />
	<?php echo isset($sitemapUrls) ? (generateCanonicalUrl($sitemapUrls)."\n") : ""; ?>	
		<link rel="alternate" hreflang="en" href="{{base_url}}{{lang_en}}" />
		<link rel="alternate" hreflang="x-default" href="{{base_url}}{{lang_en}}" />
			<link rel="alternate" hreflang="sw" href="{{base_url}}{{lang_sw}}" />
		
						<meta name="viewport" content="width=device-width, initial-scale=1" />
					<meta name="description" content="<?php echo htmlspecialchars((isset($seoDescription) && $seoDescription !== "") ? $seoDescription : "About Us"); ?>" />
			<meta name="keywords" content="<?php echo htmlspecialchars((isset($seoKeywords) && $seoKeywords !== "") ? $seoKeywords : "About Us"); ?>" />
			
	<!-- Facebook Open Graph -->
		<meta property="og:title" content="<?php echo htmlspecialchars((isset($seoTitle) && $seoTitle !== "") ? $seoTitle : "About Us"); ?>" />
			<meta property="og:description" content="<?php echo htmlspecialchars((isset($seoDescription) && $seoDescription !== "") ? $seoDescription : "About Us"); ?>" />
			<meta property="og:image" content="<?php echo htmlspecialchars((isset($seoImage) && $seoImage !== "") ? "{{base_url}}".$seoImage : ""); ?>" />
			<meta property="og:type" content="article" />
			<meta property="og:url" content="__wb_curr_url__" />
		<!-- Facebook Open Graph end -->

		<meta name="generator" content="Website Builder" />
			<link href="css/common-bundle.css?ts=20260802185857" rel="stylesheet" type="text/css" />
	<link href="css/a188dd9eef5301da18cbe22b97624cf4-bundle.css?ts=20260802185857" rel="stylesheet" type="text/css" id="wb-page-stylesheet" />
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


<body class="site site-lang-en<?php if (isset($wbPopupMode) && $wbPopupMode) echo ' popup-mode'; ?> " <?php ?>><div id="wb_root" class="root wb-layout-vertical"><div class="wb_sbg"></div><div id="wb_header_a188dd9eef5301da18cbe22b97624cf4" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc3858a7a4bf4599d6087d14" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc38596f36338d0b0d66657b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1986657436700dbe63ba0cbad5bbe2c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fb4223ec700f33f6a6750b25b7549" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fc26ad9c300737c8a0c139e48b498" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc385abbb04767f5aaa74a38" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/63a123b911049cc657f1d0f2a9cc7765_fit.png?ts=1785686347"></div></div></div><div id="a19fb4297212030bdabc97de04dae2a0" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom49">JULIUS KAMBARAGE NYERERE INTERNATIONAL FESTIVAL</h2>
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
			'class' => 'wb_this_page_menu_item active',
			'children' => array()
		),
		(object) array(
			'id' => 10,
			'href' => 'Contacts/',
			'name' => 'Contacts',
			'class' => '',
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
						var lib = new GalleryLib({"id":"a19fb8f3c64000b0747d009eda7d1a44","height":"auto","type":"slideshow","trackResize":true,"interval":5,"speed":1000,"images":[{"thumb":"gallery_gen\/9147f62c31174403cafdbe5847fd40e4_301.5x134_fill.png","src":"gallery_gen\/a0295deaa452d91f264f568d7ace6a7c_fit.png?ts=1785686347","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/f3e0a489b3b22ccf940c58dffbcd2ad4_301.5x134_fill.jpg","src":"gallery_gen\/2a406b85dd90631c40b79158c1877d4f_fit.jpg?ts=1785686347","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/da8e24800b8f72dd8eed800429e1a18b_301.5x134_fill.jpg","src":"gallery_gen\/3c456088697ef08011819b714ae09234_fit.jpg?ts=1785686347","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/d362c813c5330d042dde3a964f0bfed1_301.5x134_fill.jpg","src":"gallery_gen\/30ce731cc7b1cc1edd84ddce750a6366_fit.jpg?ts=1785686347","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/33145b78952db630d35b79ec91eed8d5_301.5x134_fill.jpg","src":"gallery_gen\/47e964e8cdbbdbffac1cc75dec2c4369_fit.jpg?ts=1785686347","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/72018fdb993c6ceb781c0740d2917da8_301.5x134_fill.jpg","src":"gallery_gen\/a55bfef5daf82a78f393f684c67908ca_fit.jpg?ts=1785686347","width":1881,"height":836,"title":"","link":null,"description":"","address":""}],"border":{"border":"5px none #00008c"},"padding":10,"thumbWidth":100,"thumbHeight":100,"thumbAlign":"center","thumbPadding":6,"thumbAnim":"","thumbShadow":"","imageCover":true,"disablePopup":false,"controlsArrow":"chevron","controlsArrowSize":14,"controlsArrowStyle":{"normal":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}},"hover":{"color":"#DDDDDD","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#222222","forText":true,"css":{"text-shadow":"0px 0px 1px #222222"}}},"active":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}}},"slideOpacity":100,"showPictureCaption":"always","captionIncludeDescription":false,"captionPosition":"center bottom","mapTypeId":"roadmap","markerIconTypeId":"thumbs","zoom":2,"mapCenter":{"latLng":{"lat":41.244772343082,"lng":-5.2734375},"text":"41.244772343082076, -5.2734375"},"key":"AIzaSyD-9xtp38UunEmx7XfJ7eBh-K4w8qQ6SEw","theme":"default","color":"#eeeeee","showSatellite":true,"showZoom":true,"showStreetView":true,"showFullscreen":true,"allowDragging":true,"showRoads":true,"showLandmarks":true,"showLabels":true,"locale":"en_US","pauseOnHover":true});
						lib.appendTo(el);
					})(window.wbmodGalleryLib);
				});
			});
		</script></div></div></div></div></div><div id="a19fb429722400fb62f16c17777e0dbd" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19fb42971f400a1d073d65740953b98" class="wb_element" data-plugin="Button"><a class="wb_button" href="Register/"><span>Register to participate</span></a></div><div id="a19fb429722c00d2df553faa4f96bb89" class="wb_element" data-plugin="Button"><a class="wb_button" href="Event-Products/"><span>Products</span></a></div><div id="a19fb429721202bf4c948ac3d6dde212" class="wb_element" data-plugin="Button"><a class="wb_button" href="Award-Nominees/"><span>Award Nominees</span></a></div></div></div></div></div></div></div></div></div></div></div><div id="wb_main_a188dd9eef5301da18cbe22b97624cf4" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc373a2d41d10bc0452ce348" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc373bbf7df0aa48ef1af0b6" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc37419f6795150fd119e6d6" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fc1b857d200a867938503fb9c4c68" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p><span class="wb-stl-custom8">Julius Kambarage Nyerere International Festival (JUKANYE)</span></p>

<p class="wb-stl-custom6"> </p>

<p class="wb-stl-custom6">Jasiri Arts &amp; Culture Institution (JACI), based in Dar es Salaam and registered under the National Arts Council of Tanzania (BASATA), specializes in arts, culture, and the coordination of social and historical events. Together with CATZ Company Limited, also based in Dar es Salaam and registered with the Business Registrations and Licensing Agency (BRELA Registration No. 173701225), which specializes in creative services, writing, and the production of social and cultural content, and in collaboration with the National Museum of Tanzania (NMT)—a public institution under the Ministry of Natural Resources and Tourism responsible for preserving and promoting Tanzania's historical heritage—they are pleased to announce the official preparations for the Julius Kambarage Nyerere International Festival (JUKANYE) 2027, a biennial international festival dedicated to honoring the life, legacy, and enduring ideals of Mwalimu Julius Kambarage Nyerere, the Founding Father of Tanzania.</p>

<p class="wb-stl-custom6"> </p>

<p class="wb-stl-custom6">The festival is being organized in close collaboration with the National Arts Council of Tanzania (BASATA), the African Liberation Heritage Centre (ALHC), and the Small Industries Development Organization (SIDO).</p>

<p class="wb-stl-custom6">The JUKANYE 2027 Festival will take place at the Kisongo Grounds in Arusha, Tanzania, from 17 July to 1 August 2027.</p>

<p class="wb-stl-custom6"> </p>

<p class="wb-stl-custom6"><span class="wb-stl-custom8">The festival will feature a diverse range of activities, including:</span></p>

<p class="wb-stl-custom6">Tourism Promotion</p>

<p class="wb-stl-custom6">International Exhibitions and Trade Fair</p>

<p class="wb-stl-custom6">Cultural and Heritage Exhibitions</p>

<p class="wb-stl-custom6">Conferences and Workshops</p>

<p class="wb-stl-custom6">Sports and Recreational Activities</p>

<p class="wb-stl-custom6">Cultural Performances and Entertainment</p>

<p class="wb-stl-custom6">Community Outreach and Social Services</p>

<p class="wb-stl-custom6">Business and Investment Networking</p>

<p class="wb-stl-custom6">Innovation and Creative Industry Showcases</p>

<p class="wb-stl-custom6">Recognition and Awards Ceremony</p>

<p class="wb-stl-custom6"> </p>

<p class="wb-stl-custom6">JUKANYE serves as an international platform that brings together governments, cultural institutions, development partners, investors, artists, researchers, youth, and communities from across Africa and beyond to celebrate African heritage, strengthen Pan-African unity, promote sustainable tourism, encourage trade and investment, and inspire future generations through the enduring legacy of Mwalimu Julius Kambarage Nyerere.</p>
</div><div id="a188dd9ebc3743d1a94aea5d08799ce9" class="wb_element" data-plugin="Button"><a class="wb_button" href="Contacts/"><span>Join Jukanye Now</span></a></div></div></div></div></div></div></div><div id="a188dd9ebc3706f3996079a5177b4918" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc3707f6fcb112a4c2b4920b" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2" style="text-align: center;">Meet the Jukanye Visionaries</h2>
</div><div id="a188dd9ebc3708162c299814f835440a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc37091108a2646ee2690e91" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc370a4dcc4fdf9699e4be9f" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/1fdcfeee417139d1e0bc4ba308bdb4fb_318x320_fit.jpg?ts=1785686347"></div></div></div><div id="a188dd9ebc370b7d0e546bb51e899180" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;"><b>Ahmad Hussein Mwita - JACI</b></p>
</div><div id="a188dd9ebc370cbbb686d2c5a00ea100" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;"><strong>Founder of the JUKANYE Festival idea</strong></p>

<p class="wb-stl-normal" style="text-align: center;"> </p>

<p class="wb-stl-normal" style="text-align: center;"><em>A patriot, arts enthusiast, and passionate advocate for Africa’s liberation history. I believe today's generation must inherit and uphold the legacy of our heroes.</em></p>
</div><div id="a188dd9ebc370d5873d3b619b5982a9d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc370e92493c90917c2c39bb" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://behance.net"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 2049.02083 1793.982" style="direction: ltr; color:#000000"><text x="1.02083" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a188dd9ebc370ff3553deab6a50808d3" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://youtube.com"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a188dd9ebc3710c2d7b77ea17143984d" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://twitter.com"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div></div></div></div></div><div id="a198850e30be00f10e52e8d36b9d6423" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198850e30c5002a5700f91f086dd84a" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/aeac1929bc577905767ad3338228bc1e_264x320_fit.jpg?ts=1785686347"></div></div></div><div id="a198850e30d100a75f3971209569657d" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;"><strong>Mainda Mkwiro - </strong><b>JACI</b></h3>
</div><div id="a198850e30dd0066bc6fa9feb5517b5d" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;"><em><strong>Co-Founder of Jukanye Festival.</strong></em></p>

<p class="wb-stl-normal" style="text-align: center;"><br>
<em>A voice for heritage, a lens for identity<strong>. </strong>Focusing the soul of a nation into stories that inspire, preserve, and awaken.</em></p>
</div><div id="a198850e30eb003d52a23dbd7bb4042b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198850e30f2006d790efae199b9ddd6" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://behance.net"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 2049.02083 1793.982" style="direction: ltr; color:#000000"><text x="1.02083" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a198850e30fe00ff8091889582325a3f" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://youtube.com"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a198850e3109006ddeda3cd5f5367d89" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://twitter.com"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div></div></div></div></div><div id="a188dd9ebc37193b90bb6685991b2abf" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc371a2216a75ab3296b1fbe" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/397b0d3b2d6b42f350e7f614286c5918_272x320_fit.jpg?ts=1785686347"></div></div></div><div id="a188dd9ebc371b05c73315642ea08b5a" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;"><strong>Mwene Jabril Ikaweba - </strong><b>JACI</b></h3>
</div><div id="a188dd9ebc371c25e6dd452094372cc4" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;"><em><strong>Co-Founder of Jukanye Festival.</strong></em></p>

<p class="wb-stl-normal" style="text-align: center;"> </p>

<p class="wb-stl-normal" style="text-align: center;"><em> JUKANYE is not just a festival – it is a platform for the liberation of African consciousness. This is our generation’s call to defend our history, our language, and the dignity of our continent.”</em></p>
</div><div id="a188dd9ebc371dced8253c8bea7b1cac" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc371e453a22b8c2b74d8870" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://behance.net"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 2049.02083 1793.982" style="direction: ltr; color:#000000"><text x="1.02083" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a188dd9ebc371f1cbe4bf7f5568e3398" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://youtube.com"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a188dd9ebc372016c9fe84b5e873d995" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://twitter.com"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div></div></div></div></div><div id="a188dd9ebc37111ab72c51f79765b2a0" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc3712a1773e9fc7c961ff17" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/a80278560a6e08f34a07c24684a9fd38_fit.jpg?ts=1785686347"></div></div></div><div id="a188dd9ebc371343288cd52c73c936b7" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;"><strong>Silondwa C. Johns - CATZ</strong></p>
</div><div id="a188dd9ebc3714cb6805d4e0867d0461" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;"><strong>Writer Of Jukanye Festival Program</strong></p>

<p class="wb-stl-normal" style="text-align: center;"> </p>

<p class="wb-stl-normal" style="text-align: center;"><em>JUKANYE is more than a festival—it is our ancestral fire reborn.</em></p>

<p class="wb-stl-normal" style="text-align: center;"><em>A promise to awaken Africa’s memory,<br>
To script her pride,<br>
And to stage her glory.</em></p>
</div><div id="a188dd9ebc3715bce9585462a45ed66f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc37163817ae77a751b89904" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://behance.net"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 2049.02083 1793.982" style="direction: ltr; color:#000000"><text x="1.02083" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a188dd9ebc37173cdf0eac98f219778d" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://youtube.com"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a188dd9ebc3718aba20350376d4ec8a1" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://twitter.com"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div></div></div></div></div></div></div><div id="a19884ca34bb00b17a3bcfe697daee72" class="wb_element" data-plugin="Button"><a class="wb_button" href="Contacts/"><span>Join Jukanye Now</span></a></div><div id="a188dd9ebc372106608d45cee4e53395" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19884f99e29002469af83e143041adc" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19884f99e30009ded7aafebcd6c56ec" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2">Board Members:</h2>
</div><div id="a19884f99e4400c8932f1ee1d05cdb74" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;">The main festival committee will include members from JACI, Catz Company Limited, leaders from various government sectors, international organizations, and experts from the Culture, Energy, Tourism, and Education sectors.</h3>
</div><div id="a19884f99e5700b16872d154443cfaf7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19884f99e5e002d0224d46599b3f9c0" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19884f99e7100f21ccb05f502ed1776" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19884f99e7700d0887a72432316c224" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;">Roles</h3>
</div><div id="a19884f99e830045f37fa908d6700221" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><ul>
<li class="wb-stl-normal">
<p class="wb-stl-normal">Strategic Oversight – Provide vision, direction, and long-term goals for the festival.</p>
</li>
<li class="wb-stl-normal">
<p class="wb-stl-normal">Policy Guidance – Approve major plans, policies, and ensure alignment with the festival’s mission.</p>
</li>
<li class="wb-stl-normal">
<p class="wb-stl-normal">Resource Mobilization – Support fundraising efforts, partnerships, and sponsorship outreach.</p>
</li>
<li class="wb-stl-normal">
<p class="wb-stl-normal">Governance &amp; Compliance – Ensure transparency, accountability, and legal compliance.</p>
</li>
<li class="wb-stl-normal">
<p class="wb-stl-normal">Advisory Role – Offer expertise in areas such as history, culture, tourism, and development.</p>
</li>
<li class="wb-stl-normal">
<p class="wb-stl-normal">Representation – Act as ambassadors of the festival to national and international stakeholders.</p>
</li>
<li class="wb-stl-normal">
<p class="wb-stl-normal">Monitoring &amp; Evaluation – Oversee performance, assess progress, and recommend improvements.</p>
</li>
</ul>
</div></div></div><div id="a19fc1c4614b00f334288694872a8528" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19884f99e9300b9f2fe805b0751515f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19884f99e9800d4089f5b301f2910d3" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;">Roles</h3>
</div><div id="a19884f99ea5006968d74c64d3d8cd53" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><ul>
<li class="wb-stl-normal">
<h3 class="wb-stl-heading3"><strong>Status of Board Members:</strong></h3>

<p> </p>
</li>
<li class="wb-stl-normal">
<p><strong>Chairperson of the Board</strong><br>
– A highly respected national figure (e.g., former president, senior statesperson, or prominent academic/cultural leader).<br>
– Symbol of integrity and national unity.</p>
</li>
<li class="wb-stl-normal">
<p> </p>

<p><strong>Vice Chairperson</strong></p>
– An experienced professional in cultural, heritage, or public service sectors.

<p> </p>
</li>
<li class="wb-stl-normal">
<p><strong>Secretary to the Board</strong><br>
– Senior representative from  CATZ COMPANY LTD /JACI (Jasiri Arts &amp; Culture Institution), managing communication and coordination.</p>

<p> </p>
</li>
<li class="wb-stl-normal">
<p class="wb-stl-normal"><strong>Treasurer</strong><br>
– Finance or development expert responsible for financial oversight and resource mobilization.</p>

<p> </p>
</li>
<li class="wb-stl-normal">
<p><strong>Board Member – Government Representative</strong><br>
– Appointee from the Ministry of Culture, Tourism, or Foreign Affairs to ensure government engagement.</p>

<p> </p>
</li>
<li class="wb-stl-normal">
<p><strong>Board Member – Private Sector Leader</strong><br>
– Business executive with experience in sponsorship, media, or branding.</p>

<p> </p>
</li>
<li class="wb-stl-normal">
<p><strong>Board Member – Pan-African Representative</strong><br>
– A recognized individual from another African country to promote continental ownership.</p>

<p> </p>
</li>
<li class="wb-stl-normal">
<p><strong>Board Member – International Partner/Donor Rep</strong><br>
– From a collaborating international organization (e.g., UNESCO, AU, UNDP, etc.)</p>

<p> </p>
</li>
<li class="wb-stl-normal">
<p><strong>Board Member – Youth &amp; Innovation Advocate</strong><br>
– A young changemaker representing youth interests and fresh perspectives.</p>

<p> </p>
</li>
<li class="wb-stl-normal">
<p><strong>Board Member – Arts &amp; Culture Expert</strong><br>
– A renowned artist, filmmaker, or historian contributing to the festival’s creative direction.</p>
</li>
</ul>
</div></div></div></div></div></div></div></div></div></div></div></div></div></div></div><div id="a198991e9e94005588ecb458970c24dd" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198991e9e99007ce929e176918bb49f" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2" style="text-align: center;">Meet the Jukanye Festival 2026 Commitee</h2>
</div><div id="a198991e9f940072db23cdd5de3fbeae" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198991ea036006fcf6e133ef0850899" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198991ea04500d3a39dfc9317abd90e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198991ea048008546e347463d2006db" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198991ea067001bd233bfcfe06a4d78" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198991ea070003792c081cf8c8da8e9" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h1 class="wb-stl-heading1" style="text-align: center;">COMMITTEES</h1>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>A) MAIN ORGANIZING COMMITTEE (STEERING COMMITTEE)</strong></p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>Chairperson of the Main Committee</strong><br>
The chief overseer of the Festival, responsible for leading and directing the overall vision and execution of all tasks.<br>
🔹 Coordinates meetings and ensures every committee operates efficiently.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>Vice Chairperson</strong><br>
Assists the Chairperson, acts in their place when absent, and supervises the implementation of decisions.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>General Secretary</strong><br>
Oversees the preparation of meetings, document production, and official communications.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>Treasurer</strong><br>
Manages the festival’s income and expenditure, ensures the budget is adhered to, and provides financial reports.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>Head of Protocol &amp; Official Invitations</strong><br>
Coordinates invitations for national and international leaders, ambassadors, and guests of honor.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>Chief Advisor of the Festival</strong><br>
Provides strategic and protocol advice, especially on African history, leadership, and Nyerere’s vision.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>B) SUB-COMMITTEES FOR IMPLEMENTATION</strong></p>

<p class="wb-stl-normal"> </p>

<ol>
<li class="wb-stl-normal">Programme and Content Committee</li>
<li class="wb-stl-normal">Finance and Resources Committee</li>
<li class="wb-stl-normal">Communications, Media, and Public Relations Committee</li>
<li class="wb-stl-normal">Protocol, Invitations, and Special Guests Committee</li>
<li class="wb-stl-normal">Museum and Historical Exhibitions Committee</li>
<li class="wb-stl-normal">Entertainment and Arts Committee</li>
<li class="wb-stl-normal">Tourism and Historical Tours Committee</li>
<li class="wb-stl-normal">Health Clinic and Community Services Committee</li>
<li class="wb-stl-normal">Kiswahili Training Committee (Let’s Speak Kiswahili)</li>
<li class="wb-stl-normal">Security and Logistics Committee</li>
<li class="wb-stl-normal">Awards Committee</li>
<li class="wb-stl-normal">Health, Environment, and Sanitation Committee</li>
</ol>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>C) INSTITUTIONS TO BE INVOLVED IN THE COMMITTEE - </strong>(As stakeholders and professional advisors)</p>

<p class="wb-stl-normal">“The organizing committee will bring together key national and international stakeholders, including leading cultural, academic, tourism, conservation, and heritage institutions such as government ministries, regulatory authorities, museums, foundations, councils, and organizations that play a central role in promoting arts, culture, Kiswahili, tourism, conservation, and international cooperation.”</p>

<p class="wb-stl-normal"> </p>
</div></div></div></div></div></div></div></div></div></div></div></div></div></div></div><div id="wb_footer_a188dd9eef5301da18cbe22b97624cf4" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fc2149cd000eb3b8848562ec6f176" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc386d7d4d77961b3399b7e7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19fb429723a03cab5671bd0692f5610" class="wb_element" data-plugin="Button"><a class="wb_button" href="{{base_url}}"><span><svg xmlns="http://www.w3.org/2000/svg" width="1793.982" height="1793.982" viewBox="0 0 1793.982 1793.982" style="display: inline-block; vertical-align: middle; position: relative; top: -1px; height: 1em; width: 1em; overflow: visible; direction: ltr;"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg>&nbsp;Home Page</span></a></div><div id="a188dd9ebc386e9c761088b65418f7a1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc386f7f651dc7e4d0792624" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="Homeb/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#4be6e6"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a188dd9ebc38700f452a2fef2fcabe01" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="Homeb/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1024 1024" style="direction: ltr; color:#ffffff"><text x="64" y="960" font-size="1024" fill="currentColor" style='font-family: "builder-ui-icons-plugins"'></text></svg></a></div></div></div><div id="a188dd9ebc3871cfcba1a4cf7091cb6d" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="Homeb/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#ffffff"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div></div></div><div id="a19fc20bdb7e00c6080e244c0b41b351" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-custom16" style="text-align: center;">ADDRESS:</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">P,O BOX  DAR- ES - SALAAM, TANZANIA</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">PHONE: +255 746 174403 +255 789  388232 +255 719 083050</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">EMAIL: jukanyefestival@gmail.com</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">info@jukanye.com</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">WEBSITE: www.jukanye.com</h3>
</div><div id="a188dd9ebc38721835f60daecdc81bab" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/3a0fa4358ae2f4fb06a94eaab03b4403_fit.png?ts=1785686347"></div></div></div><div id="a19fc20a045f00e06f9422396398c49c" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-footer">© 2025 <a href="http://jukanye.com">jukanye.com</a> - Honoring Africa’s True Patriots and Heroes.</p>
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
	<script src="js/a188dd9eef5301da18cbe22b97624cf4-bundle.js?ts=20260802185857" type="text/javascript" defer></script>{{hr_out}}<script>
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

				document.cookie = '__cookie_law__=' + (2) + '; path=/; expires=Wed, 28 Jul 2027 18:59:07 GMT';

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
	<title><?php echo htmlspecialchars((isset($seoTitle) && $seoTitle !== "") ? $seoTitle : "Shughuli Zetu"); ?></title>
	<base href="{{base_url}}" />
	<?php echo isset($sitemapUrls) ? (generateCanonicalUrl($sitemapUrls)."\n") : ""; ?>	
		<link rel="alternate" hreflang="en" href="{{base_url}}{{lang_en}}" />
		<link rel="alternate" hreflang="x-default" href="{{base_url}}{{lang_en}}" />
			<link rel="alternate" hreflang="sw" href="{{base_url}}{{lang_sw}}" />
		
						<meta name="viewport" content="width=device-width, initial-scale=1" />
					<meta name="description" content="<?php echo htmlspecialchars((isset($seoDescription) && $seoDescription !== "") ? $seoDescription : "Shughuli Zetu"); ?>" />
			<meta name="keywords" content="<?php echo htmlspecialchars((isset($seoKeywords) && $seoKeywords !== "") ? $seoKeywords : "Shughuli Zetu"); ?>" />
			
	<!-- Facebook Open Graph -->
		<meta property="og:title" content="<?php echo htmlspecialchars((isset($seoTitle) && $seoTitle !== "") ? $seoTitle : "Shughuli Zetu"); ?>" />
			<meta property="og:description" content="<?php echo htmlspecialchars((isset($seoDescription) && $seoDescription !== "") ? $seoDescription : "Shughuli Zetu"); ?>" />
			<meta property="og:image" content="<?php echo htmlspecialchars((isset($seoImage) && $seoImage !== "") ? "{{base_url}}".$seoImage : ""); ?>" />
			<meta property="og:type" content="article" />
			<meta property="og:url" content="__wb_curr_url__" />
		<!-- Facebook Open Graph end -->

		<meta name="generator" content="Website Builder" />
			<link href="css/common-bundle.css?ts=20260802185857" rel="stylesheet" type="text/css" />
	<link href="css/a188dd9eef5301da18cbe22b97624cf4-bundle.css?ts=20260802185857" rel="stylesheet" type="text/css" id="wb-page-stylesheet" />
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


<body class="site site-lang-sw<?php if (isset($wbPopupMode) && $wbPopupMode) echo ' popup-mode'; ?> " <?php ?>><div id="wb_root" class="root wb-layout-vertical"><div class="wb_sbg"></div><div id="wb_header_a188dd9eef5301da18cbe22b97624cf4" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc3858a7a4bf4599d6087d14" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc38596f36338d0b0d66657b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1986657436700dbe63ba0cbad5bbe2c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fb4223ec700f33f6a6750b25b7549" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fc26ad9c300737c8a0c139e48b498" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc385abbb04767f5aaa74a38" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/63a123b911049cc657f1d0f2a9cc7765_fit.png?ts=1785686347"></div></div></div><div id="a19fb4297212030bdabc97de04dae2a0" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom49">TAMASHA LA KIMATAIFA LA JULIUS KAMBARAGE NYERERE</h2>
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
			'class' => 'wb_this_page_menu_item active',
			'children' => array()
		),
		(object) array(
			'id' => 10,
			'href' => 'sw/Mawasiliano/',
			'name' => 'Mawasiliano',
			'class' => '',
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
						var lib = new GalleryLib({"id":"a19fb8f3c64000b0747d009eda7d1a44","height":"auto","type":"slideshow","trackResize":true,"interval":5,"speed":1000,"images":[{"thumb":"gallery_gen\/9147f62c31174403cafdbe5847fd40e4_301.5x134_fill.png","src":"gallery_gen\/a0295deaa452d91f264f568d7ace6a7c_fit.png?ts=1785686347","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/f3e0a489b3b22ccf940c58dffbcd2ad4_301.5x134_fill.jpg","src":"gallery_gen\/2a406b85dd90631c40b79158c1877d4f_fit.jpg?ts=1785686347","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/da8e24800b8f72dd8eed800429e1a18b_301.5x134_fill.jpg","src":"gallery_gen\/3c456088697ef08011819b714ae09234_fit.jpg?ts=1785686347","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/d362c813c5330d042dde3a964f0bfed1_301.5x134_fill.jpg","src":"gallery_gen\/30ce731cc7b1cc1edd84ddce750a6366_fit.jpg?ts=1785686347","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/33145b78952db630d35b79ec91eed8d5_301.5x134_fill.jpg","src":"gallery_gen\/47e964e8cdbbdbffac1cc75dec2c4369_fit.jpg?ts=1785686347","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/72018fdb993c6ceb781c0740d2917da8_301.5x134_fill.jpg","src":"gallery_gen\/a55bfef5daf82a78f393f684c67908ca_fit.jpg?ts=1785686347","width":1881,"height":836,"title":"","link":null,"description":"","address":""}],"border":{"border":"5px none #00008c"},"padding":10,"thumbWidth":100,"thumbHeight":100,"thumbAlign":"center","thumbPadding":6,"thumbAnim":"","thumbShadow":"","imageCover":true,"disablePopup":false,"controlsArrow":"chevron","controlsArrowSize":14,"controlsArrowStyle":{"normal":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}},"hover":{"color":"#DDDDDD","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#222222","forText":true,"css":{"text-shadow":"0px 0px 1px #222222"}}},"active":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}}},"slideOpacity":100,"showPictureCaption":"always","captionIncludeDescription":false,"captionPosition":"center bottom","mapTypeId":"roadmap","markerIconTypeId":"thumbs","zoom":2,"mapCenter":{"latLng":{"lat":41.244772343082,"lng":-5.2734375},"text":"41.244772343082076, -5.2734375"},"key":"AIzaSyD-9xtp38UunEmx7XfJ7eBh-K4w8qQ6SEw","theme":"default","color":"#eeeeee","showSatellite":true,"showZoom":true,"showStreetView":true,"showFullscreen":true,"allowDragging":true,"showRoads":true,"showLandmarks":true,"showLabels":true,"locale":"sw_TZ","pauseOnHover":true});
						lib.appendTo(el);
					})(window.wbmodGalleryLib);
				});
			});
		</script></div></div></div></div></div><div id="a19fb429722400fb62f16c17777e0dbd" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19fb42971f400a1d073d65740953b98" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Jisajiri/"><span>Jisajiri Kushiriki</span></a></div><div id="a19fb429722c00d2df553faa4f96bb89" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Bidhaa-za-Tamasha/"><span>Bidhaa</span></a></div><div id="a19fb4297209006e0ba9445e1db2f558" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Waliopendekezwa-kupewa-Tuzo/"><span>Walio pendekezwa Kupata Tuzo</span></a></div></div></div></div></div><div id="a19fb81fa1120059e7c1682d66b9ba06" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"></div></div></div></div></div></div></div></div><div id="wb_main_a188dd9eef5301da18cbe22b97624cf4" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc373a2d41d10bc0452ce348" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc373bbf7df0aa48ef1af0b6" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc37419f6795150fd119e6d6" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fc1b857d200a867938503fb9c4c68" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom6"> </p>

<p class="wb-stl-custom6" style="text-align: center;"><span style="color:rgba(250,250,250,1);">K</span></p>

<p class="wb-stl-custom6">Tamasha la Kimataifa la Julius Kambarage Nyerere (JUKANYE)</p>

<p class="wb-stl-custom6">Jasiri Arts &amp; Culture Institution (JACI), yenye makao yake makuu jijini Dar es Salaam na iliyosajiliwa chini ya Baraza la Sanaa la Taifa (BASATA), ni taasisi inayojihusisha na maendeleo ya sanaa, utamaduni na uratibu wa matukio ya kijamii na kihistoria. Kwa kushirikiana na CATZ Company Limited, yenye makao yake jijini Dar es Salaam na iliyosajiliwa na Wakala wa Usajili wa Biashara na Leseni (BRELA), Namba ya Usajili 173701225, kampuni inayojishughulisha na ubunifu, uandishi na uzalishaji wa maudhui ya kijamii na kiutamaduni, pamoja na Makumbusho ya Taifa la Tanzania (NMT)—taasisi ya umma iliyo chini ya Wizara ya Maliasili na Utalii, yenye jukumu la kuhifadhi, kulinda na kuendeleza urithi wa kihistoria wa Tanzania—wanafuraha kutangaza kuanza rasmi kwa maandalizi ya Tamasha la Kimataifa la Julius Kambarage Nyerere (JUKANYE) 2027, tamasha la kimataifa linalofanyika kila baada ya miaka miwili kwa lengo la kuenzi maisha, urithi, falsafa na maadili ya kudumu ya Mwalimu Julius Kambarage Nyerere, Baba wa Taifa la Tanzania.</p>

<p class="wb-stl-custom6">Tamasha hili linaandaliwa kwa ushirikiano wa karibu na Baraza la Sanaa la Taifa (BASATA), Kituo cha Urithi wa Ukombozi wa Afrika (African Liberation Heritage Centre – ALHC) pamoja na Shirika la Kuhudumia Viwanda Vidogo (SIDO).</p>

<p class="wb-stl-custom6"> </p>

<p class="wb-stl-custom6">JUKANYE 2027 itafanyika katika Viwanja vya Kisongo, Mkoa wa Arusha, Tanzania, kuanzia tarehe 17 Julai hadi 1 Agosti 2027.</p>

<p class="wb-stl-custom6"> </p>

<p class="wb-stl-custom6">Tamasha litajumuisha shughuli mbalimbali zikiwemo:</p>

<p class="wb-stl-custom6"> </p>

<p class="wb-stl-custom6">Kukuza na Kutangaza Utalii</p>

<p class="wb-stl-custom6">Maonesho ya Kimataifa na Maonesho ya Biashara</p>

<p class="wb-stl-custom6">Maonesho ya Utamaduni na Urithi wa Taifa</p>

<p class="wb-stl-custom6">Mikutano, Makongamano na Warsha</p>

<p class="wb-stl-custom6">Michezo na Shughuli za Burudani</p>

<p class="wb-stl-custom6">Maonesho ya Sanaa, Utamaduni na Burudani</p>

<p class="wb-stl-custom6">Huduma za Kijamii na Uhamasishaji wa Jamii</p>

<p class="wb-stl-custom6">Mitandao ya Biashara na Uwekezaji</p>

<p class="wb-stl-custom6">Maonesho ya Ubunifu na Sekta za Uchumi Bunifu</p>

<p class="wb-stl-custom6">Hafla ya Utambuzi na Utoaji wa Tuzo</p>

<p class="wb-stl-custom6"> </p>

<p class="wb-stl-custom6">JUKANYE ni jukwaa la kimataifa linalokusanya pamoja serikali, taasisi za utamaduni, washirika wa maendeleo, wawekezaji, wafanyabiashara, wasanii, watafiti, vijana na jamii kutoka Tanzania, Afrika na sehemu mbalimbali duniani ili kusherehekea urithi wa Afrika, kuimarisha umoja wa Waafrika, kukuza utalii endelevu, kuhamasisha biashara na uwekezaji, pamoja na kuhamasisha kizazi cha sasa na kijacho kupitia urithi na maono ya kudumu ya Mwalimu Julius Kambarage Nyerere. Tamasha hili pia linalenga kuitangaza Tanzania kama kitovu cha utalii wa urithi, utamaduni, diplomasia ya utamaduni, ubunifu na maendeleo endelevu barani Afrika.</p>

<p class="wb-stl-custom6" style="text-align: center;"><span style="color:rgba(250,250,250,1);">uhusu Tamasha la Jukanye 2026</span></p>
</div><div id="a188dd9ebc37426cfa6cd5cdd17f923a" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal">JUKANYE International History Festival ni tamasha la kipekee linaloratibiwa na Jasiri Arts &amp; Culture Institution (JACI) kwa kushirikiana na CATZ Company Ltd, likiwa sehemu ya mpango wa miaka 10 wa kumuenzi Baba wa Taifa, Mwalimu Julius Kambarage Nyerere.</p>

<p class="wb-stl-normal">Lengo lake kuu ni kuenzi urithi wa ukombozi wa Afrika, kukuza uzalendo, kuunga mkono juhudi za utalii na utamaduni wa Tanzania, sambamba na kusisitiza mshikamano wa Waafrika katika kuujenga uchumi wa bara letu.</p>

<p class="wb-stl-normal">Tamasha hufanyika kila baada ya miaka miwili, na toleo la kwanza litafanyika Arusha, Tanzania kuanzia 01–13 Septemba 2026, likihusisha Utalii maonyesho, warsha, mijadala, burudani, na utoaji tuzo.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong><em>Kauli mbiu: “Uchumi wa Afrika utaimarishwa na Waafrika wenyewe kwa kushikamana.”</em></strong></p>

<p class="wb-stl-normal"> </p>
</div><div id="a188dd9ebc3743d1a94aea5d08799ce9" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Mawasiliano/"><span>Join Jukanye Now</span></a></div></div></div></div></div></div></div><div id="a188dd9ebc3706f3996079a5177b4918" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc3707f6fcb112a4c2b4920b" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2">Wabeba maono ya Jukanye</h2>
</div><div id="a188dd9ebc3708162c299814f835440a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc37091108a2646ee2690e91" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc370a4dcc4fdf9699e4be9f" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/1fdcfeee417139d1e0bc4ba308bdb4fb_318x320_fit.jpg?ts=1785686347"></div></div></div><div id="a188dd9ebc370b7d0e546bb51e899180" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;"><b>Ahmad Hussein Mwita - JACI</b></p>
</div><div id="a188dd9ebc370cbbb686d2c5a00ea100" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;"><strong>Mwanzilishi wa wazo la JUKANYE Festiva</strong>l</p>

<p class="wb-stl-normal" style="text-align: center;"> </p>

<p class="wb-stl-normal" style="text-align: center;"><em>Mzalendo, mweledi wa sanaa, na mtetezi wa historia ya ukombozi wa Afrika. naamini kuwa kizazi cha sasa kinapaswa kurithi na kuenzi urithi wa mashujaa wetu.</em></p>
</div><div id="a188dd9ebc370d5873d3b619b5982a9d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc370e92493c90917c2c39bb" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://behance.net"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 2049.02083 1793.982" style="direction: ltr; color:#000000"><text x="1.02083" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a188dd9ebc370ff3553deab6a50808d3" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://youtube.com"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a188dd9ebc3710c2d7b77ea17143984d" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://twitter.com"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div></div></div></div></div><div id="a198850e30be00f10e52e8d36b9d6423" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198850e30c5002a5700f91f086dd84a" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/aeac1929bc577905767ad3338228bc1e_264x320_fit.jpg?ts=1785686347"></div></div></div><div id="a198850e30d100a75f3971209569657d" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;"><strong>Mainda Mkwiro - </strong><b>JACI</b></h3>
</div><div id="a198850e30dd0066bc6fa9feb5517b5d" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;"><strong>Mwanzilishi Mwenza wa Tamasha la Jukanye.</strong></p>

<p class="wb-stl-normal" style="text-align: center;"><br>
Sauti ya urithi, lenzi ya utambulisho.<br>
Akiangazia roho ya taifa katika hadithi zinazohamasisha, kuhifadhi, na kuamsha.</p>
</div><div id="a198850e30eb003d52a23dbd7bb4042b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198850e30f2006d790efae199b9ddd6" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://behance.net"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 2049.02083 1793.982" style="direction: ltr; color:#000000"><text x="1.02083" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a198850e30fe00ff8091889582325a3f" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://youtube.com"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a198850e3109006ddeda3cd5f5367d89" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://twitter.com"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div></div></div></div></div><div id="a188dd9ebc37193b90bb6685991b2abf" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc371a2216a75ab3296b1fbe" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/397b0d3b2d6b42f350e7f614286c5918_272x320_fit.jpg?ts=1785686347"></div></div></div><div id="a188dd9ebc371b05c73315642ea08b5a" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;"><strong>Mwene Jabril Ikaweba - </strong><b>JACI</b></h3>
</div><div id="a188dd9ebc371c25e6dd452094372cc4" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;"><strong>Mwanzilishi Mwenza wa Tamasha la Jukanye.</strong></p>

<p class="wb-stl-normal">JUKANYE si tamasha tu – ni jukwaa la ukombozi wa fikra za Kiafrika. Ni mwito wa kizazi chetu kutetea historia yetu, lugha yetu, na hadhi ya bara letu.</p>
</div><div id="a188dd9ebc371dced8253c8bea7b1cac" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc371e453a22b8c2b74d8870" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://behance.net"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 2049.02083 1793.982" style="direction: ltr; color:#000000"><text x="1.02083" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a188dd9ebc371f1cbe4bf7f5568e3398" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://youtube.com"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a188dd9ebc372016c9fe84b5e873d995" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://twitter.com"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div></div></div></div></div><div id="a188dd9ebc37111ab72c51f79765b2a0" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc3712a1773e9fc7c961ff17" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/a80278560a6e08f34a07c24684a9fd38_fit.jpg?ts=1785686347"></div></div></div><div id="a188dd9ebc371343288cd52c73c936b7" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;"><strong>Silondwa C. Johns - CATZ</strong></p>
</div><div id="a188dd9ebc3714cb6805d4e0867d0461" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal"> <strong>Mwandishi wa Program ya Jukanye</strong></p>

<p> </p>

<p><em>JUKANYE si tamasha tu - ni moto wa mababu zetu uliorejea. kuamsha kumbukumbu ya Afrika,<br>
Kuandika fahari yake, na kupandisha utukufu wa africa jukwaani.</em></p>
</div><div id="a188dd9ebc3715bce9585462a45ed66f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc37163817ae77a751b89904" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://behance.net"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 2049.02083 1793.982" style="direction: ltr; color:#000000"><text x="1.02083" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a188dd9ebc37173cdf0eac98f219778d" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://youtube.com"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a188dd9ebc3718aba20350376d4ec8a1" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="http://twitter.com"><svg xmlns="http://www.w3.org/2000/svg" width="100%" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div></div></div></div></div></div></div><div id="a19884ca34bb00b17a3bcfe697daee72" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Mawasiliano/"><span>Join Jukanye Now</span></a></div><div id="a188dd9ebc372106608d45cee4e53395" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19884f99e29002469af83e143041adc" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19884f99e30009ded7aafebcd6c56ec" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2">Wajumbe wa Bodi:</h2>
</div><div id="a19884f99e5700b16872d154443cfaf7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19884f99e5e002d0224d46599b3f9c0" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19884f99e7100f21ccb05f502ed1776" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19884f99e7700d0887a72432316c224" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;">Majukumu</h3>
</div><div id="a19884f99e830045f37fa908d6700221" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal"> </p>

<ul>
<li>
<p class="wb-stl-normal">Kuratibu Mialiko Rasmi:</p>
</li>
<li>
<p class="wb-stl-normal">Kuandaa na kutuma mialiko kwa wageni wa heshima wakiwemo viongozi wa kitaifa na kimataifa, mabalozi, mashujaa wa ukombozi, wasanii, na wadau wakuu wa tamasha.</p>
</li>
<li>
<p class="wb-stl-normal">Kuhakikisha Itifaki Inazingatiwa:</p>
</li>
<li>
<p class="wb-stl-normal">Kuratibu mapokezi ya wageni maalum kwa kufuata taratibu za heshima na hadhi zao.</p>
</li>
<li>
<p class="wb-stl-normal">Kuhakikisha taratibu za kitaifa na kimataifa za itifaki zinafuatwa ipasavyo kwenye hafla rasmi.</p>
</li>
<li>
<p class="wb-stl-normal">Kurahisisha Uratibu wa Wageni Maalum:</p>
</li>
<li>
<p class="wb-stl-normal">Kupanga ratiba, usafiri, malazi, na ulinzi wa wageni mashuhuri wanaoshiriki tamasha.</p>
</li>
<li>
<p class="wb-stl-normal">Kuwapa taarifa muhimu wageni kuhusu programu ya tamasha na mahitaji yao binafsi.</p>
</li>
<li>
<p class="wb-stl-normal">Kuhakikisha Uwasilishaji Bora wa Hotuba na Heshima:</p>
</li>
<li>
<p class="wb-stl-normal">Kusimamia mpangilio wa hotuba, utoaji wa tuzo, na matukio mengine ya hadhi ya juu.</p>
</li>
<li>
<p class="wb-stl-normal">Kuandaa watangazaji au wahudumu wa hafla wenye uelewa wa lugha na mila za itifaki.</p>
</li>
<li>
<p class="wb-stl-normal">Kuwezesha Ushirikiano wa Kimataifa:</p>
</li>
<li>
<p class="wb-stl-normal">Kufanikisha ujio na ushiriki wa wageni kutoka nje ya nchi, ikiwa ni pamoja na kusaidia utoaji wa viza na taarifa za kiusalama na mahitaji maalum.</p>
</li>
</ul>
</div></div></div></div></div></div></div></div></div></div></div></div></div><div id="a198991e9e94005588ecb458970c24dd" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198991e9f8d0035006e5e3feada8b67" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Mawasiliano/"><span>Join Jukanye Now</span></a></div><div id="a198991e9f940072db23cdd5de3fbeae" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198991ea036006fcf6e133ef0850899" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198991ea04500d3a39dfc9317abd90e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198991ea048008546e347463d2006db" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198991ea067001bd233bfcfe06a4d78" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198991ea070003792c081cf8c8da8e9" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h1 class="wb-stl-heading1" style="text-align: center;">KAMATI</h1>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>A). KAMATI KUU YA MAANDALIZI (STEERING COMMITTEE)</strong></p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>1. Mwenyekiti  </strong><br>
Msimamizi Mkuu wa Tamasha, atasimamia na kuongoza, mwelekeo na utekelezaji wa majukumu yote.<br>
🔹Ataratibu vikao na kuhakikisha kila kamati inafanya kazi kwa ufanisi.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>2. Makamu Mwenyekiti </strong><br>
Msaidizi wa Mwenyekiti, anachukua nafasi wakati Mwenyekiti hayupo na kusimamia utekelezaji wa maamuzi.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>3. Katibu wa Kamati Kuu </strong><br>
Anasimamia maandalizi ya mikutano, uchapaji wa nyaraka, na mawasiliano rasmi.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>4. Mhazini </strong><br>
Anasimamia mapato na matumizi ya fedha za tamasha, anahakikisha bajeti inazingatiwa na kutoa ripoti za kifedha.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>5. Msimamizi wa Itifaki &amp; Mialiko ya Viongozi </strong><br>
Anaratibu mialiko ya viongozi wa kitaifa, kimataifa, mabalozi, na wageni wa heshima.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>6. Mshauri Mkuu wa Tamasha </strong><br>
Anatoa ushauri wa kimkakati na kiitifaki, hususan kuhusu historia ya Afrika, viongozi, na maono ya Nyerere.</p>

<div>
<hr size="2"></div>

<p class="wb-stl-normal"><strong>B). KAMATI NDOGO NDOGO ZA UTEKELEZAJI</strong></p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal">1. Kamati ya Programu na Maudhui</p>

<p class="wb-stl-normal">2. Kamati ya Fedha na Rasilimali</p>

<p class="wb-stl-normal">3. Kamati ya Mawasiliano, Habari na Mahusiano ya Umma</p>

<p class="wb-stl-normal">4. Kamati ya Itifaki, Mialiko na Wageni Maalum</p>

<p class="wb-stl-normal">5. Kamati ya Makumbusho na Maonyesho ya Historia</p>

<p class="wb-stl-normal">6. Kamati ya Burudani na Sanaa </p>

<p class="wb-stl-normal">7. Kamati ya utalii na Ziara za Kihistoria</p>

<p class="wb-stl-normal">8. Kamati ya Kliniki ya Afya na Huduma za Jamii                                                       </p>

<p class="wb-stl-normal">8. Kamati ya mafunzo ya Kiswahili (Tuseme Kiswahili)</p>

<p class="wb-stl-normal">9. Kamati ya Usalama na Logistics    </p>

<p class="wb-stl-normal">10. Kamati ya Tuzo </p>

<p class="wb-stl-normal">11. Kamati ya Afya, Mazingira na Usafi </p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong> C). TAASISI ZITAKAZO SHIRIKISHWA KWENYE KAMATI (Kama wadau na washauri wa mambo ya kitaaluma)</strong></p>

<div> </div>

<p class="wb-stl-normal"> </p>

<ul>
<li>
<p class="wb-stl-normal">“Kamati ya maandalizi itajumuisha wadau na washauri wa kitaalamu kutoka taasisi kuu za kitaifa na kimataifa zinazohusika na uratibu wa utamaduni, sanaa, lugha ya Kiswahili, urithi wa taifa, elimu ya juu, utalii, uhifadhi wa mazingira pamoja na ushirikiano wa kimataifa.”</p>
</li>
</ul>
</div></div></div></div></div></div></div></div></div></div></div></div></div></div></div><div id="wb_footer_a188dd9eef5301da18cbe22b97624cf4" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fc2149cd000eb3b8848562ec6f176" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc386d7d4d77961b3399b7e7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19fb429723a03cab5671bd0692f5610" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/"><span><svg xmlns="http://www.w3.org/2000/svg" width="1793.982" height="1793.982" viewBox="0 0 1793.982 1793.982" style="display: inline-block; vertical-align: middle; position: relative; top: -1px; height: 1em; width: 1em; overflow: visible; direction: ltr;"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg>&nbsp;Home </span></a></div><div id="a188dd9ebc386e9c761088b65418f7a1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc386f7f651dc7e4d0792624" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="sw/Mwanzo/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#4be6e6"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a188dd9ebc38700f452a2fef2fcabe01" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="sw/Mwanzo/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1024 1024" style="direction: ltr; color:#ffffff"><text x="64" y="960" font-size="1024" fill="currentColor" style='font-family: "builder-ui-icons-plugins"'></text></svg></a></div></div></div><div id="a188dd9ebc3871cfcba1a4cf7091cb6d" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="sw/Mwanzo/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#ffffff"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div></div></div><div id="a19fc20bdb7e00c6080e244c0b41b351" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-custom16" style="text-align: center;">ADDRESS:</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">P,O BOX  DAR- ES - SALAAM, TANZANIA</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">PHONE: +255 746 174403 +255 789  388232 +255 719 083050</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">EMAIL: jukanyefestival@gmail.com</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">info@jukanye.com</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">WEBSITE: www.jukanye.com</h3>
</div><div id="a188dd9ebc38721835f60daecdc81bab" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/3a0fa4358ae2f4fb06a94eaab03b4403_fit.png?ts=1785686347"></div></div></div><div id="a188dd9ebc387353ef2d51652b5ef64e" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-footer">© 2025 <a href="http://jukanye.com">jukanye.com</a> - Kuwaenzi Viongozi wa Afrika Walioongoza Harakati za Ukombozi</p>
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
	<script src="js/a188dd9eef5301da18cbe22b97624cf4-bundle.js?ts=20260802185857" type="text/javascript" defer></script>{{hr_out}}<script>
    document.addEventListener('DOMContentLoaded', function () {
        window._spDefer.done();
    });
</script>
</body>
</html>


<?php } ?>
