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

				document.cookie = '__cookie_law__=' + (2) + '; path=/; expires=Wed, 28 Jul 2027 18:59:17 GMT';

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
	<title><?php echo htmlspecialchars((isset($seoTitle) && $seoTitle !== "") ? $seoTitle : "Unlisted"); ?></title>
	<base href="{{base_url}}" />
	<?php echo isset($sitemapUrls) ? (generateCanonicalUrl($sitemapUrls)."\n") : ""; ?>	
		<link rel="alternate" hreflang="en" href="{{base_url}}{{lang_en}}" />
		<link rel="alternate" hreflang="x-default" href="{{base_url}}{{lang_en}}" />
			<link rel="alternate" hreflang="sw" href="{{base_url}}{{lang_sw}}" />
		
						<meta name="viewport" content="width=device-width, initial-scale=1" />
					<meta name="description" content="<?php echo htmlspecialchars((isset($seoDescription) && $seoDescription !== "") ? $seoDescription : "Unlisted"); ?>" />
			<meta name="keywords" content="<?php echo htmlspecialchars((isset($seoKeywords) && $seoKeywords !== "") ? $seoKeywords : "Unlisted"); ?>" />
			
	<!-- Facebook Open Graph -->
		<meta property="og:title" content="<?php echo htmlspecialchars((isset($seoTitle) && $seoTitle !== "") ? $seoTitle : "Unlisted"); ?>" />
			<meta property="og:description" content="<?php echo htmlspecialchars((isset($seoDescription) && $seoDescription !== "") ? $seoDescription : "Unlisted"); ?>" />
			<meta property="og:image" content="<?php echo htmlspecialchars((isset($seoImage) && $seoImage !== "") ? "{{base_url}}".$seoImage : ""); ?>" />
			<meta property="og:type" content="article" />
			<meta property="og:url" content="__wb_curr_url__" />
		<!-- Facebook Open Graph end -->

		<meta name="generator" content="Website Builder" />
			<link href="css/common-bundle.css?ts=20260802185857" rel="stylesheet" type="text/css" />
	<link href="css/a198900350f300a37ae9158159156524-bundle.css?ts=20260802185857" rel="stylesheet" type="text/css" id="wb-page-stylesheet" />
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


<body class="site site-lang-en<?php if (isset($wbPopupMode) && $wbPopupMode) echo ' popup-mode'; ?> " <?php ?>><div id="wb_root" class="root wb-layout-vertical"><div class="wb_sbg"></div><div id="wb_header_a198900350f300a37ae9158159156524" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc3858a7a4bf4599d6087d14" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc38596f36338d0b0d66657b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1986657436700dbe63ba0cbad5bbe2c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fb4223ec700f33f6a6750b25b7549" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fc26ad9c300737c8a0c139e48b498" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc385abbb04767f5aaa74a38" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/63a123b911049cc657f1d0f2a9cc7765_fit.png?ts=1785686357"></div></div></div><div id="a19fb4297212030bdabc97de04dae2a0" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom49">JULIUS KAMBARAGE NYERERE INTERNATIONAL FESTIVAL</h2>
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
						var lib = new GalleryLib({"id":"a19fb8f3c64000b0747d009eda7d1a44","height":"auto","type":"slideshow","trackResize":true,"interval":5,"speed":1000,"images":[{"thumb":"gallery_gen\/9147f62c31174403cafdbe5847fd40e4_301.5x134_fill.png","src":"gallery_gen\/a0295deaa452d91f264f568d7ace6a7c_fit.png?ts=1785686357","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/f3e0a489b3b22ccf940c58dffbcd2ad4_301.5x134_fill.jpg","src":"gallery_gen\/2a406b85dd90631c40b79158c1877d4f_fit.jpg?ts=1785686357","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/da8e24800b8f72dd8eed800429e1a18b_301.5x134_fill.jpg","src":"gallery_gen\/3c456088697ef08011819b714ae09234_fit.jpg?ts=1785686357","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/d362c813c5330d042dde3a964f0bfed1_301.5x134_fill.jpg","src":"gallery_gen\/30ce731cc7b1cc1edd84ddce750a6366_fit.jpg?ts=1785686357","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/33145b78952db630d35b79ec91eed8d5_301.5x134_fill.jpg","src":"gallery_gen\/47e964e8cdbbdbffac1cc75dec2c4369_fit.jpg?ts=1785686357","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/72018fdb993c6ceb781c0740d2917da8_301.5x134_fill.jpg","src":"gallery_gen\/a55bfef5daf82a78f393f684c67908ca_fit.jpg?ts=1785686357","width":1881,"height":836,"title":"","link":null,"description":"","address":""}],"border":{"border":"5px none #00008c"},"padding":10,"thumbWidth":100,"thumbHeight":100,"thumbAlign":"center","thumbPadding":6,"thumbAnim":"","thumbShadow":"","imageCover":true,"disablePopup":false,"controlsArrow":"chevron","controlsArrowSize":14,"controlsArrowStyle":{"normal":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}},"hover":{"color":"#DDDDDD","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#222222","forText":true,"css":{"text-shadow":"0px 0px 1px #222222"}}},"active":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}}},"slideOpacity":100,"showPictureCaption":"always","captionIncludeDescription":false,"captionPosition":"center bottom","mapTypeId":"roadmap","markerIconTypeId":"thumbs","zoom":2,"mapCenter":{"latLng":{"lat":41.244772343082,"lng":-5.2734375},"text":"41.244772343082076, -5.2734375"},"key":"AIzaSyD-9xtp38UunEmx7XfJ7eBh-K4w8qQ6SEw","theme":"default","color":"#eeeeee","showSatellite":true,"showZoom":true,"showStreetView":true,"showFullscreen":true,"allowDragging":true,"showRoads":true,"showLandmarks":true,"showLabels":true,"locale":"en_US","pauseOnHover":true});
						lib.appendTo(el);
					})(window.wbmodGalleryLib);
				});
			});
		</script></div></div></div></div></div><div id="a19fb429722400fb62f16c17777e0dbd" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19fb42971f400a1d073d65740953b98" class="wb_element" data-plugin="Button"><a class="wb_button" href="Register/"><span>Register to participate</span></a></div><div id="a19fb429722c00d2df553faa4f96bb89" class="wb_element" data-plugin="Button"><a class="wb_button" href="Event-Products/"><span>Products</span></a></div><div id="a19fb429721202bf4c948ac3d6dde212" class="wb_element" data-plugin="Button"><a class="wb_button" href="Award-Nominees/"><span>Award Nominees</span></a></div></div></div></div></div></div></div></div></div></div></div><div id="wb_main_a198900350f300a37ae9158159156524" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035418032f147dbf5bf44fa000" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035418042f314761cebd519549" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035418051fcac84b13e28b6006" class="wb_element wb_element_picture loop wb-anim-entry wb-anim wb-anim-fade-in-none" data-plugin="Picture" data-wb-anim-entry-time="11" data-wb-anim-entry-delay="7" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery/julius-nyerere.jpg?ts=1785686357"></div></div></div><div id="a1989003544100df64ec6a46d5767460" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354180684288d31f5aea574c7" class="wb_element wb-prevent-layout-click" data-plugin="Video"><video controls="" autoplay="true" loop="true" muted="true" playsinline=""><source type="video/mp4" src="gallery/Mambo%20Jambo%20Poa.mp4"></source><a href="gallery/Mambo%20Jambo%20Poa.mp4">Mambo Jambo Poa.mp4</a></video></div></div></div></div></div></div></div><div id="a19890035440072008d1c83053905684" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541700896fd71d77e81f774f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354170149e8dc033a8b34b36a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354180717fc997389280e4083" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541808a1965c15485e2338b1" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2">JULIUS KAMBARAGE NYERERE INTERNATIONAL HISTORY FESTIVAL</h2>
</div><div id="a198900354420754fec8d5800d1c1b92" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3">Africa’s journey to independence is a story of courage, sacrifice, and unwavering vision. From the dusty battlefields to the halls of diplomacy,African liberation leaders stood firm against colonial oppression, igniting theflame of freedom that still burns across the continent today.</h3>
</div><div id="a1989003544f05b749b5edc224a56f58" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354180992e3967d17bbe12ed2" class="wb_element" data-plugin="Button"><a class="wb_button" href="Register/"><span>Register to participate</span></a></div><div id="a198900354420661b91a8bfaa5faf35c" class="wb_element" data-plugin="Button"><a class="wb_button" href="Award-Nominees/"><span>Award Nominees</span></a></div><div id="a19890035454001e668935e1cbdb6ec6" class="wb_element" data-plugin="Button"><a class="wb_button" href="Event-Products/"><span>Products</span></a></div><div id="a198900354590969953004d27bcc8565" class="wb_element" data-plugin="Button"><a class="wb_button" href="Donate/"><span>Donation</span></a></div><div id="a1989003545b0697d990deab813e8923" class="wb_element" data-plugin="Button"><a class="wb_button" href="Sponsors/"><span>Sponsors</span></a></div></div></div></div></div><div id="a198900354490c0608f6971a9ca838a3" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom42"><strong>Who Can Join?</strong></p>

<ul>
<li class="wb-stl-custom42">All African countries interested in taking part</li>
<li class="wb-stl-custom42">Friendly nations with a strong connection to Africa</li>
<li class="wb-stl-custom42">International partners from education, development, and culture</li>
<li class="wb-stl-custom42">Tourists, businesspeople, and professionals</li>
<li class="wb-stl-custom42">Communities from Tanzania and around the world—everyone is welcome!</li>
</ul>
</div></div></div></div></div></div></div><div id="a1989003545102a25dc0357c49f07e57" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541c0b0cc88e94fd0f82686a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541d00e1ffbbf79b698d4868" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035428029abaf5c9a0d8a899a0" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541d0179b1681b43fadbc224" class="wb_element wb-accordion" data-plugin="Accordion"><div class="wb-accordion-type-slider"><div id="a1989003541d0179b1681b43fadbc224-list" class="carousel slide" data-ride="carousel" data-interval="5000"><div class="carousel-indicators"><li class="wb-accordion-item active" data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="0" data-item-id="0"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="1" data-item-id="1"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="2" data-item-id="2"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="3" data-item-id="3"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="4" data-item-id="4"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="5" data-item-id="5"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="6" data-item-id="6"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="7" data-item-id="7"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="8" data-item-id="8"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="9" data-item-id="9"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="10" data-item-id="10"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="11" data-item-id="11"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="12" data-item-id="12"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="13" data-item-id="13"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="14" data-item-id="14"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="15" data-item-id="15"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="16" data-item-id="16"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="17" data-item-id="17"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="18" data-item-id="18"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="19" data-item-id="19"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="20" data-item-id="20"></div><div class="carousel-inner" role="listbox"><div class="item active"><div id="a1989003541d02558cafdb9d77cb9941" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541d039755ddeb39b33cda31" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541d0443d45e913100b4d6f5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541d0525cbd3bbd7f3a29aa6" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Julius_Nyerere" title="Read more" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a86452e680a9d209ec90c05814a9e82d_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354420bf9d3e0811f0b39dcbf" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545103298eb4d997a8ead0b8" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Julius Kambarage Nyerere</p>

<p class="wb-stl-normal" style="text-align: center;">- Tanganyika.</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a19890035443004ef0aca37545878c7e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541d06bec67d4e1813a4ed67" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541d0751f8407e4f38f16eba" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541d082d8afec5c8c6b054cd" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Abeid_Karume" target="_blank"><img loading="lazy" alt="" src="gallery_gen/53628c301827bcdf90467e47fb0295fd_300x300_fit.jpg?ts=1785686357"></a></div></div></div><div id="a19890035443014fe0352171c6958b82" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035451042d7507317558c30932" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Abeid Amani Karume - Zanzibar</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545105853bc1d2886efddd1b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541d09a7168099df0388b4f9" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541d0a3c53ab5db537a1d5f7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541d0b9df1af074612ae2346" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Jomo_Kenyatta" target="_blank"><img loading="lazy" alt="" src="gallery_gen/4240af0cc9e867792a97a4a870e58b18_300x200_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544302ef7e96698d5ac6c338" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545106a676eb3efd3b0da39e" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Mzee Jomo Kenyatta</p>

<p class="wb-stl-custom4" style="text-align: center;">- Kenya</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545a023df9a777aba5750e03" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541e00983901a67cfa563969" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541e01c84e932c92a329816f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541e02a6b6849c18cc57ea8a" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Milton_Obote" target="_blank"><img loading="lazy" alt="" src="gallery_gen/30c009dbc34db243f3bcf4a3732a0618_300x354_fit.jpg?ts=1785686357"></a></div></div></div><div id="a19890035443030cf0420554bb5fc606" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035451077008418322ffd8f763" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Milton Obote</p>

<p class="wb-stl-custom4" style="text-align: center;">- Uganda</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545b081cbf2d74f11410d44a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541e03fe3c10bc264712d36e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541e04f18178773f71bac54d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541e0579227f6c0277d4b705" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Nelson_Mandela" target="_blank"><img loading="lazy" alt="" src="gallery_gen/c1032c63f0fb7d37d10eaa57e255d971_300x300_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544304d12f8259fe17780c0f" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545108a3e7066d7b80552735" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Nelson Mandela</p>

<p class="wb-stl-custom4" style="text-align: center;">- South Africa</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545c02f77f7bfede21744b77" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541e065e5a183ef686cfe2ea" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541e07e7f276cffe9df01a39" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541e08c6af038da5f097b89b" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Kwame_Nkrumah" target="_blank"><img loading="lazy" alt="" src="gallery_gen/314d963a5d161c4b366ba9af914ab6cc_fit.jpg?ts=1785686357"></a></div></div></div><div id="a19890035443057e9fd53fc3892e1f8c" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035451097378bb0d58fedee352" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Kwame Nkrumah</p>

<p class="wb-stl-custom4" style="text-align: center;">- Ghana</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545c0aadd4ecb97ff6eec2ab" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541e094f7b1566f2075d3477" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541e0aa66d304cee163f2878" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541e0bbe9000342427390dc0" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Robert_Mugabe" target="_blank"><img loading="lazy" alt="" src="gallery_gen/78ec9ad1a85de1c5931bafb55601100b_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544306c92461aabd9dcb6564" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354510a267011657f3bbb7573" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Robert Mugabe</p>

<p class="wb-stl-custom4" style="text-align: center;">- Zimbabwe</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545d0203cbe9364f8170a0de" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541e0c78a6f470898f2dfbda" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541f001f05f246fd6c89c88c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541f0119c9ec935ac9add1fb" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmed_Ben_Bella" target="_blank"><img loading="lazy" alt="" src="gallery_gen/cea04620dd38546d6e4d97a8e2c11ab9_fit.jpg?ts=1785686357"></a></div></div></div><div id="a19890035443077d44ee1cd675f2bcd7" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354510bbc78669bf45c145833" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmed Ben Bella</p>

<p class="wb-stl-custom4" style="text-align: center;">- Algeria</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545d09b9576e4542158308bc" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541f02696031f6046d609e42" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541f036b680fbe5affbb263c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541f0482ce4353e6c0e73f7b" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmed_Sékou_Touré" target="_blank"><img loading="lazy" alt="" src="gallery_gen/0924f22b791426459b1fceeea5d83d13_300x376_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354430830c02b35f38e1d0391" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354510c4299b1cf6398d28fad" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmed Sekour Toure</p>

<p class="wb-stl-custom4" style="text-align: center;">-Guinea</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545e00b4fc9bb17abeb67899" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541f058a92c5cf082af2c5e7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541f0665719a9f4bdc4fb26d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541f0734b70d4c707c571b3c" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Agostinho_Neto" target="_blank"><img loading="lazy" alt="" src="gallery_gen/6a3850f6562e671cb5d12dc555d49b51_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544309e1f25e065380abea7e" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354510d15ff2b4da44cbad600" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Agostinho Neto</p>

<p class="wb-stl-custom4" style="text-align: center;">- Angola</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545e08bf17b8003cd6959876" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541f086cdeb2312b626db60c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541f09b7403e26f4a5eb87f3" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541f0a97ea824cd1de0fa8d1" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Sam_Nujoma" target="_blank"><img loading="lazy" alt="" src="gallery_gen/1570396f1726775d1cce5c83b0706c7e_300x210_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354430aa75ee1e390a2fe3101" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354510e641a1dbf0e7f2e2446" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Sam Nujoma</p>

<p class="wb-stl-custom4" style="text-align: center;">- Namibia</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545e0f5b1bcda44d05dd6279" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541f0b9a2af7b603f955a69f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541f0cc7b024f079d9c7b2f6" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541f0d713073ddc010f7a951" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Samora_Machel" title="https://en.wikipedia.org/wiki/Samora_Machel" target="_blank"><img loading="lazy" alt="" src="gallery_gen/5372a2a280dd4375e74cdf264fc32a5b_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354430bdc7a4f6320ffecaa3f" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354510ffb1f6cb6bc31a40349" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Samora Machel</p>

<p class="wb-stl-custom4" style="text-align: center;"> - Mozambique</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545f06b339483fd42420b39f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542000fb4dff27816d08e12c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542001e3e5f5dabe5070ec7b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542002566df74e4cd5b7f5fd" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Kenneth_Kaunda" target="_blank"><img loading="lazy" alt="" src="gallery_gen/550d22763a73fab3b73256541dddbc4b_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354430cd6d15143d16779fc23" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545110637806fb9ed1a6e3f6" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Kenneth Kaunda</p>

<p class="wb-stl-custom4" style="text-align: center;">- Zambia</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545f0dedcd93235756c4ec86" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035420031955440f792e21a073" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542004042c8d791007841f30" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035420051414102781d75aa997" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Patrice_Lumumba" target="_blank"><img loading="lazy" alt="" src="gallery_gen/14574c93b57ee80df1b44a961c8a11f8_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354430dc207e7e317eb99965b" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354520059e361815555aafd24" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Patrice Lumumba</p>

<p class="wb-stl-custom4" style="text-align: center;">- Congo (Zaire /DRC)</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003546003d20c4ada9c9371ad48" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542006dbb96e6abfcf2f950b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354200723f7c326c9002d742e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354200864e4478a517bc52ab3" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Hastings_Banda" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a770b5a86758ef8c981bd2d8e0b76d18_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354430e226bfefb8836aca90d" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354520155a108f128f1c3956b" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Hastings Kamuzu Banda</p>

<p class="wb-stl-custom4" style="text-align: center;">- Malawi</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354600a1a9e3f2cc3d5978126" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542009e862f88d3f51309e77" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354200a53c755f2662fb5d403" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354200b2f62768ff8c222970a" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://sw.wikipedia.org/wiki/Nnamdi_Azikiwe" target="_blank"><img loading="lazy" alt="" src="gallery_gen/933760d0723f699380102b2a69b12759_300x344_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544400dc3c1878b4ee4f22a8" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545202d77f148180732d84a1" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Nnamdi Azikiwe</p>

<p class="wb-stl-custom4" style="text-align: center;">- Nigeria</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003546103d4ab4c1c28f5dbc273" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354200c803ced589b0ad55979" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354200da600e48133f75c6745" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035421003a11c249cb5aae5f99" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Thomas_Sankara" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a6c8cd240f9f4d8d18fc4aa78f178e5f_300x168_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544401bbfc9169baef5183fe" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035452038ae26aea7bd13f1e82" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Thomas Sankara</p>

<p class="wb-stl-custom4" style="text-align: center;">- Burkina Fasor</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a19890035461082630bda2231ddf31c4" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035421013b1d726614c68b2817" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542102dd4582473497fab6a5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542103de0f89edab299120c0" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Muammar_Gaddafi" target="_blank"><img loading="lazy" alt="" src="gallery_gen/1db65078b522d721e9d5c6c5b5ecd98c_300x374_fit.jpg?ts=1785686357"></a></div></div></div><div id="a19890035444027d5b9a9f83c797bed6" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545204b2790cfd56bf07d267" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Muammari Gaddafi</p>

<p class="wb-stl-custom4" style="text-align: center;">- Libya</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354610d44d29896a79d7d9fd8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354210462c507756f3bf7443e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542105c2c738d203b0b9ce0e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542106332c96bf960422b134" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Haile_Selassie" target="_blank"><img loading="lazy" alt="" src="gallery_gen/b6c71636a45fb72d58b79f9d573734ba_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544403123270bf89d4ffae1f" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545205e6450bdf24ab28a204" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Emperor Haile Selassie I</p>

<p class="wb-stl-custom4" style="text-align: center;">- Ethiopia</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354620152a28bd56d8e7bb517" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542107ad5bea0cd9e94a2565" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035421080a850e89d4a1629b4b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035421096a127e7f4e5bb14c5c" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Léopold_Sédar_Senghor" target="_blank"><img loading="lazy" alt="" src="gallery_gen/052732d784d8347a79730116e3fd7d8c_300x170_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544404d321db4db915de9218" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545206538c14acb01e1e6444" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Leopold Sedar Senghor </p>

<p class="wb-stl-custom4" style="text-align: center;">- Senegal</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a19890035462061639c5b71443b1bb50" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354210a1aabb0cdcdb7f34be2" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354210be47640020da7472c77" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354210c0a74564ee4f0b3e6b8" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmadou_Ahidjo" target="_blank"><img loading="lazy" alt="" src="gallery_gen/dbd7c2b0f071ed790fad39a580770b78_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544405ee39cd362fbc13da23" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545207b06bc7ed9280b6b6ae" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmadou Ahidjo</p>

<p class="wb-stl-custom4" style="text-align: center;">- Cameroon</p>
</div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div><div id="a198900354440773d085f9bf3b82b2d8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354210d2be39c67d69728e0c1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542200bf39de59fb8b106c57" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542201d094d8d40e2e43ee8a" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom25">Celebrating African Liberation, Legacy, Unity and Honoring Africa's True Patriots and Heroes</h2>
</div><div id="a198900354490df61ad877981c66c347" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542202630c607a53fd676ae8" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal">These leaders - men and women of immense resolve-did not seek personal gain.They dreamed of dignity for their people, unity for their nations, and a futureled by Africans themselves. From Mwalimu Julius Kambarage Nyerere’s wisdom inuniting Tanzania, to Kwame Nkrumah’s bold call for Pan-Africanism, from PatriceLumumba’s cry for justice to Nelson Mandela’s spirit of reconciliation - theirlegacy lives on.</p>

<p class="wb-stl-normal">At the JUKANYE International HistoryFestival 2026, we honor these heroes. Through storytelling,exhibitions, music, seminars, and cultural celebrations, we remember andcelebrate their sacrifice - reminding the present generation and inspiring thenext with the spirit of patriotism.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>Let their names echo.</strong></p>

<p class="wb-stl-normal"><strong>Let their stories inspire.</strong></p>

<p class="wb-stl-normal" style="text-align: center;"> </p>

<p class="wb-stl-normal" style="text-align: center;"><strong><em>Let their vision live on.</em></strong></p>
</div></div></div></div></div></div></div></div></div></div></div><div id="a1989003545a0aea50174a68d960cd7b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003544b0872a798c128265438eb" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035419016d5cf8104985b89aa2" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541a033ca39c1e79f4ee075f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541a049b9cf5b18b5d2a3ad0" class="wb_element wb-accordion" data-plugin="Accordion" data-save-open-tab="true"><div class="wb-accordion-type-slider"><div id="a1989003541a049b9cf5b18b5d2a3ad0-list" class="carousel slide" data-ride="carousel" data-interval="5000"><div class="carousel-indicators"><li class="wb-accordion-item active" data-target="#a1989003541a049b9cf5b18b5d2a3ad0-list" data-slide-to="0" data-item-id="0"><li class="wb-accordion-item " data-target="#a1989003541a049b9cf5b18b5d2a3ad0-list" data-slide-to="1" data-item-id="1"><li class="wb-accordion-item " data-target="#a1989003541a049b9cf5b18b5d2a3ad0-list" data-slide-to="2" data-item-id="2"><li class="wb-accordion-item " data-target="#a1989003541a049b9cf5b18b5d2a3ad0-list" data-slide-to="3" data-item-id="3"><li class="wb-accordion-item " data-target="#a1989003541a049b9cf5b18b5d2a3ad0-list" data-slide-to="4" data-item-id="4"><li class="wb-accordion-item " data-target="#a1989003541a049b9cf5b18b5d2a3ad0-list" data-slide-to="5" data-item-id="5"><li class="wb-accordion-item " data-target="#a1989003541a049b9cf5b18b5d2a3ad0-list" data-slide-to="6" data-item-id="6"><li class="wb-accordion-item " data-target="#a1989003541a049b9cf5b18b5d2a3ad0-list" data-slide-to="7" data-item-id="7"></div><div class="carousel-inner" role="listbox"><div class="item active"><div id="a1989003541a0588fb0bbfcb4727b0b1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541a066ee8c42546c8acdf32" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541a07add0c82426d233e8a5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541a0865fc0aa66d408f0514" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Maria_Nyerere" target="_blank"><img loading="lazy" alt="" src="gallery_gen/4918c2486b617465c8d909cba95212bc_370x208_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354410bdb4302126f2d1a2810" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545000d71e8b3f964d3ca570" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Mama Maria Nyerere</p>

<p class="wb-stl-normal" style="text-align: center;">- Tanzania</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354410c4800868700f54471a7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541a09a014a1c3c0f4d955df" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541a0aeebefcfdd641762187" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541a0b9468183adcc8a97db1" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Winnie_Mandela" target="_blank"><img loading="lazy" alt="" src="gallery_gen/7d3ea66627f68b8539fa93451e7b2ee6_300x310_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354410d0527fcc4548e8fd681" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354500118a78db4298692c8dd" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Winnie Madikizela-Mandela</p>

<p class="wb-stl-normal" style="text-align: center;">South Africa</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354500240ee195b75b889c7a7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541b00ef5d6a5e5c05ca17a7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541b012cd2ea8cd8383739b8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541b0234f2dd11b652bf32da" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Bibi_Titi_Mohammed" target="_blank"><img loading="lazy" alt="" src="gallery_gen/9cc8778cc2d825fc90ccad3cc815f8b1_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544200f3df71bf4473450ae0" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035450039c3824588ca5b7062a" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Bibi Titi Mohamed</p>

<p class="wb-stl-custom4" style="text-align: center;">- Tanzania</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354590cc65e623a8736456f9d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541b033d89faedc86711842e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541b04e9710e45492369081e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541b051f59b172fbd5771486" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Funmilayo_Ransome-Kuti" target="_blank"><img loading="lazy" alt="" src="gallery_gen/0477196f0ab1dd3d3baec3b8091ced95_fit.jpg?ts=1785686357"></a></div></div></div><div id="a19890035442016b9be99f962364ecd1" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545004785ef0e79f06b67cad" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Funmilayo Ransome-Kuti</p>

<p class="wb-stl-custom4" style="text-align: center;">- Nigeria </p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545b055cbbb45b915c13040c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541b062829dd768c53f9a698" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541b071d72660dbdc5696aaa" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541b08069e73c68c113b71a8" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Josina_Machel" target="_blank"><img loading="lazy" alt="" src="gallery_gen/fc8dc6d71309eb953432a22de4bfde38_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544202245ac430cbf870e203" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545005b385ab592073761c57" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Josina Machel</p>

<p class="wb-stl-custom4" style="text-align: center;">- Mozambique </p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545c0003da3d8816382724d5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541b095b17382f98a4405571" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541b0a4443bbcdba2a135a7e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541b0b1453482ec5e536283a" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Graça_Machel" target="_blank"><img loading="lazy" alt="" src="gallery_gen/85395aad08a64d7f68860c24d2602bec_300x242_fit.png?ts=1785686357"></a></div></div></div><div id="a198900354420324989043497451beac" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545006bfb89a5d26dfd933ed" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Graça Machel</p>

<p class="wb-stl-custom4" style="text-align: center;">- Mozambique </p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545c09986f4e618344b12d02" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541c00372221bcda2bbf725d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541c01021966f83fbbe5e663" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541c0235db7ef644be1c73fa" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ngina_Kenyatta" target="_blank"><img loading="lazy" alt="" src="gallery_gen/5eace340d4b9a9b008fcd928433f4415_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544204d3102bbf0da7193e54" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545007fbb1cbb389a1a35c61" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Mama Ngina Kenyataa</p>

<p class="wb-stl-custom4" style="text-align: center;">- Kenya</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545d01660d26633281d2861f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541c03a02aab45e9768f4ea5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541c04bd2aa7c0492dab4de1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541c053c5ed2072c9b05b076" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Nzinga_of_Ndongo_and_Matamba" target="_blank"><img loading="lazy" alt="" src="gallery_gen/420270513dec75acd1b93455dc019dbb_300x266_fit.jpg?ts=1785686357"></a></div></div></div><div id="a19890035442056b618645a7aa463db7" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035450083e191733b992f09990" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Queen Nzinga (17C) </p>

<p class="wb-stl-custom4" style="text-align: center;">- Angola </p>
</div></div></div></div></div></div></div></div></div></div></div></div></div></div><div id="a1989003544101a9ac759e159cf575df" class="wb_element wb-anim-entry wb-anim wb-anim-fade-in-none wb-layout-element" data-plugin="LayoutElement" data-wb-anim-entry-time="0.6" data-wb-anim-entry-delay="0"><div class="wb_content wb-layout-vertical"><div id="a19890035419027b3fede3a1e180e3a7" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h1 class="wb-stl-heading1">Mama Afrika Award for Liberation and Legacy</h1>
</div><div id="a198900354410252079178e0e1d18f51" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;"><strong>Special Award: MAMA AFRIKA AWARD FOR LIBERATION AND LEGACY<br>
(Tuzo ya Mama Afrika kwa Ukombozi na Urithi)</strong></p>

<p> </p>

<p class="wb-stl-normal">List of Women Freedom Fighters of Africa In recognition of the significant contributions made by women in Africa’s liberation struggles, we plan to present special Honorary Awards during the JUKANYE INTERNATIONAL HISTORY FESTIVAL – 2026. This award will recognize and celebrate the courage, sacrifice, and lasting legacy of women who played vital roles in the political, social, and cultural liberation of Africa.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal" style="text-align: center;"><strong><em>This award serves as a tribute to the historical role of African women in shaping a free, dignified, united, and progressively developed continent.</em></strong></p>
</div></div></div></div></div></div></div></div></div><div id="a1989003545b0e6c51ff213da5d44de7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003544d09043e9ba8905570fdee" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543f04d5396eb6b29ab234e6" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543f0504dfec810a6bda938b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543f066cea410cb63939e698" class="wb_element wb-accordion" data-plugin="Accordion"><div class="wb-accordion-type-slider"><div id="a1989003543f066cea410cb63939e698-list" class="carousel slide" data-ride="carousel" data-interval="5000"><div class="carousel-indicators"><li class="wb-accordion-item " data-target="#a1989003543f066cea410cb63939e698-list" data-slide-to="0" data-item-id="0"><li class="wb-accordion-item " data-target="#a1989003543f066cea410cb63939e698-list" data-slide-to="1" data-item-id="1"><li class="wb-accordion-item active" data-target="#a1989003543f066cea410cb63939e698-list" data-slide-to="2" data-item-id="2"></div><div class="carousel-inner" role="listbox"><div class="item "><div id="a1989003543f07b5da9b93e1619e37f7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543f080a2eb481024b761718" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543f09e60bdd8741e6ece2e5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543f0a9d4841e5a8a17a5bac" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Maria_Nyerere" target="_blank"><img loading="lazy" alt="" src="gallery_gen/dbc6b6de3f3283a86dae0cc34fce724f_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544e0003c6a323c5a51b248f" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545901b4baaa0991bd7a44dc" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Archbishop Desmond Tutu</p>

<p class="wb-stl-normal" style="text-align: center;">- South Africa</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003544e01c72dcb8f9dc0edd443" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543f0b9b933abef3c8b8db8a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543f0c652e7b0b1865d5724d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543f0d825ea1957c1217dfa9" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Winnie_Mandela" target="_blank"><img loading="lazy" alt="" src="gallery_gen/fdb2153969a286286297c755142edc9f_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544e024f7d9cb380d1537066" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545902ea3b231110c6ee6b6f" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Abuna Theophilos</p>

<p class="wb-stl-normal" style="text-align: center;"> - Ethiopia</p>
</div></div></div></div></div></div></div></div><div class="item active"><div id="a1989003545903eb5440e470e33ffddb" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035440007ec1da2c9244d5bcd7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354400123fc46fcd152c1422e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354400274d517dda98410d4a6" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Bibi_Titi_Mohammed" target="_blank"><img loading="lazy" alt="" src="gallery_gen/61b02c9b09b9e7066adafafe18e987e4_300x270_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544e03177562b3f64e4f9a88" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545904e0de4081b4c2f15beb" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Archbishop Abel Muzorewa</p>

<p class="wb-stl-custom4" style="text-align: center;">- Zimbabwe</p>
</div></div></div></div></div></div></div></div></div></div></div></div></div></div><div id="a1989003544e04d9bcd0dfcd6da775cb" class="wb_element wb-anim-entry wb-anim wb-anim-fade-in-none wb-layout-element" data-plugin="LayoutElement" data-wb-anim-entry-time="0.6" data-wb-anim-entry-delay="0"><div class="wb_content wb-layout-vertical"><div id="a1989003544003c6ad869e840f9c6a76" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2">Wisdom Of Liberation Award</h2>
</div><div id="a1989003544e05019f57d0d32e0ccc13" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;"><strong>Wisdom of Liberation Award</strong></p>

<p class="wb-stl-normal" style="text-align: center;"><br>
This award is presented to religious leaders who have made significant contributions to Africa’s liberation movements by using their morals, faith, and influence to inspire the fight against colonialism, oppression, and mental slavery across the continent.</p>
</div></div></div></div></div></div></div></div></div><div id="a1989003545d03a111eb7766139b65ed" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035422038f8cecdbf40d4f1221" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035422043d4032d8a86e6a1627" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003545b09b670602589d69b410c" class="wb_element wb-iframe-video-auto" data-plugin="Youtube"><iframe title="YouTube video player" class="youtube-player" allowfullscreen="" data-defer-load="Youtube" data-src="//www.youtube.com/embed/pyRooTjKDLk?controls=1" frameborder="0"></iframe></div></div></div></div></div></div></div><div id="a1989003545d0bab6fe6f61848041cee" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035428048bfc7c1534fa8d21a2" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542805477f8235812c50337c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542806dc14a462ab57853783" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035428075322785c224f5392ba" class="wb_element wb-accordion" data-plugin="Accordion"><div class="wb-accordion-type-slider"><div id="a19890035428075322785c224f5392ba-list" class="carousel slide" data-ride="carousel" data-interval="5000"><div class="carousel-indicators"><li class="wb-accordion-item active" data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="0" data-item-id="0"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="1" data-item-id="1"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="2" data-item-id="2"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="3" data-item-id="3"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="4" data-item-id="4"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="5" data-item-id="5"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="6" data-item-id="6"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="7" data-item-id="7"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="8" data-item-id="8"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="9" data-item-id="9"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="10" data-item-id="10"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="11" data-item-id="11"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="12" data-item-id="12"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="13" data-item-id="13"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="14" data-item-id="14"></div><div class="carousel-inner" role="listbox"><div class="item active"><div id="a19890035428081fb8b717be95a7a1b9" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542809f5f42930283dee48a4" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354280a3bad875d4d18274010" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542900930620974e7f35a279" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Julius_Nyerere" title="Read more" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a68ce73e635caa688cdf78fa80889a33_300x168_fit.png?ts=1785686357"></a></div></div></div><div id="a1989003544700a2ddc446c41d8cb685" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545401167549c76fe19704d3" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Samia Suluhu Hassan</p>

<p class="wb-stl-normal" style="text-align: center;">- Tanzania.</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003544701c155bcf0cbe47c68c8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542901f351753f1657997a14" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542902fc4dae6bb46397e2f1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542903d4b5d7a53512cfa2fc" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Abeid_Karume" target="_blank"><img loading="lazy" alt="" src="gallery_gen/ad88d2489309cdcd795f110cd2f82277_300x400_fit.jpg?ts=1785686357"></a></div></div></div><div id="a19890035447025a5093d6046de5d988" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Abeid Amani Karume - Zanzibar</p>
</div><div id="a1989003545402e64fc4f5375dd165a1" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545403dd95ebcce1f0bab952" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354290445a3e79b5276920420" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542905c1c853dfaec11468d9" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542906fd95e231234c46c9d2" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Jomo_Kenyatta" target="_blank"><img loading="lazy" alt="" src="gallery_gen/4240af0cc9e867792a97a4a870e58b18_300x200_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544703d7d7270215474e4425" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Mzee Jomo Kenyatta</p>

<p class="wb-stl-custom4" style="text-align: center;">- Kenya</p>
</div><div id="a1989003545404d4d49619d0292681f1" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545a0765deceb836e2c3a49c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354290785b3f63a7b64fb7b34" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354290855891556134885bb9d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542909ced14f59a17dd40ac0" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Milton_Obote" target="_blank"><img loading="lazy" alt="" src="gallery_gen/30c009dbc34db243f3bcf4a3732a0618_300x354_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354470469f1b396df714ed37d" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Milton Obote</p>

<p class="wb-stl-custom4" style="text-align: center;">- Uganda</p>
</div><div id="a19890035454056a388d339aad693852" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545b0b62e0919d0920a56dff" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354290aded159ac17751cf1b0" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354290b339c8b37c376f04f48" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354290c07485573b9e2767425" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Nelson_Mandela" target="_blank"><img loading="lazy" alt="" src="gallery_gen/c1032c63f0fb7d37d10eaa57e255d971_300x300_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544705696277686149020abf" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Nelson Mandela</p>

<p class="wb-stl-custom4" style="text-align: center;">- South Africa</p>
</div><div id="a1989003545406b175214c12fa69b2a0" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545c04e36f7a9bb16352c548" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354290d6de6302cf8d291ef30" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542a0048ca9f4b91b0d0bae0" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542a01421937b2ff638c2dab" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Kwame_Nkrumah" target="_blank"><img loading="lazy" alt="" src="gallery_gen/314d963a5d161c4b366ba9af914ab6cc_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544706e734d689410c624948" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Kwame Nkrumah</p>

<p class="wb-stl-custom4" style="text-align: center;">- Ghana</p>
</div><div id="a19890035454077661df585056d2a5f8" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545c0c8de0f6df2a5c0b567f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542a024069e4a487a8be751c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542a03b11dd995c34577935a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542a04690951c1a1003c4ce2" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Robert_Mugabe" target="_blank"><img loading="lazy" alt="" src="gallery_gen/78ec9ad1a85de1c5931bafb55601100b_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544707adcb5bc7e200c8d196" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Robert Mugabe</p>

<p class="wb-stl-custom4" style="text-align: center;">- Zimbabwe</p>
</div><div id="a1989003545408913b0212a7c65a36f2" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545d05fec0817f76d25db894" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542a05707dc548c459d4e10e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542a06f04e22639ecbc9933a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542a07006405ad6a25cc596f" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmed_Ben_Bella" target="_blank"><img loading="lazy" alt="" src="gallery_gen/cea04620dd38546d6e4d97a8e2c11ab9_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354470805bb3537ba4cb264ff" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmed Ben Bella</p>

<p class="wb-stl-custom4" style="text-align: center;">- Algeria</p>
</div><div id="a1989003545409448b7e934d57e94f43" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545d0c5f37b34d933b96f77f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542a08297c9da6661ca5c66d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542a095edca55f1107c738ca" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542a0ade6d8bf0a9e318bbae" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmed_Sékou_Touré" target="_blank"><img loading="lazy" alt="" src="gallery_gen/0924f22b791426459b1fceeea5d83d13_300x376_fit.jpg?ts=1785686357"></a></div></div></div><div id="a19890035447096fe5de7a9c9b4fb10f" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmed Sekour Toure</p>

<p class="wb-stl-custom4" style="text-align: center;">-Guinea</p>
</div><div id="a198900354540a3fbcafd1ecbb560f86" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545e02eed6def71726e9c76e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542a0b1e2b889faf12218428" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542a0ce9b69f780c53e28d3c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542b003a10cf5030b07e4752" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Agostinho Neto</p>

<p class="wb-stl-custom4" style="text-align: center;">- Angola</p>
</div><div id="a198900354470a6a54aa8ff6be6f5090" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Agostinho_Neto" target="_blank"><img loading="lazy" alt="" src="gallery_gen/6a3850f6562e671cb5d12dc555d49b51_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354540b37ccfd9ff4030120f1" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545e0a93dec98eda28ee67ad" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542b01f6afa993200aa4e505" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542b0278930b139486fe7629" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542b03ecd1a521d5492295c2" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Sam Nujoma</p>

<p class="wb-stl-custom4" style="text-align: center;">- Namibia</p>
</div><div id="a198900354470b032fe990c4459bf3c4" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Sam_Nujoma" target="_blank"><img loading="lazy" alt="" src="gallery_gen/1570396f1726775d1cce5c83b0706c7e_300x210_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354540cd04f106de4a48dd1f3" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545f01abd6549aab79dead68" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542b04fcdbf0025728dbf1df" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542b05cceea49ea27264e446" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542b0617bf462389f21c69d8" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Samora_Machel" title="https://en.wikipedia.org/wiki/Samora_Machel" target="_blank"><img loading="lazy" alt="" src="gallery_gen/5372a2a280dd4375e74cdf264fc32a5b_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354470c977ea7c77d861c83fa" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Samora Machel</p>

<p class="wb-stl-custom4" style="text-align: center;"> - Mozambique</p>
</div><div id="a198900354540d217edd29b5113b094b" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545f08915273ca067225cf79" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542b07ce073f9ed9af39b78c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542b08620e565514b6081c1f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542b09ca71dbba9c002597ab" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Kenneth_Kaunda" target="_blank"><img loading="lazy" alt="" src="gallery_gen/550d22763a73fab3b73256541dddbc4b_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354470da6748cc31109ea24b2" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Kenneth Kaunda</p>

<p class="wb-stl-custom4" style="text-align: center;">- Zambia</p>
</div><div id="a198900354540e9c2b47777d7627b8df" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545f0fc540ae214eb2c474c2" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542b0a2dc42d75e3ac7577fb" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542b0b77c2642fc7d5a8eaaf" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542b0c2b67ce831acc17d70f" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Patrice_Lumumba" target="_blank"><img loading="lazy" alt="" src="gallery_gen/14574c93b57ee80df1b44a961c8a11f8_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354470e83e707a3273c5085a9" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Patrice Lumumba</p>

<p class="wb-stl-custom4" style="text-align: center;">- Congo (Zaire /DRC)</p>
</div><div id="a1989003545500c1ec3259bec5824133" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a19890035460066dea9e4a3726737caa" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542c00d20481eabf2246b708" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542c01cd1b13f4223bce31a4" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542c023f9de1d3aaaac568fe" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Hastings_Banda" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a770b5a86758ef8c981bd2d8e0b76d18_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354470f62599f18b0a07d3901" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Hastings Kamuzu Banda</p>

<p class="wb-stl-custom4" style="text-align: center;">- Malawi</p>
</div><div id="a19890035455013672b49f9f0098c791" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div><div id="a1989003544711a12c6d363daee34771" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542c038fe8d83562ae2f3884" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542c046a4c900b1ccb2e1d74" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542c05d662333536359af996" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom25">JUKANYE Contemporary Presidents Excellence Award</h2>
</div><div id="a19890035448008fbac1b7f71cd6fa58" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal">Honoring visionary leaders who drive development, safeguard national sovereignty, and promote democratic governance; uphold the firm commitment to African liberation, unity, and socio-economic transformation, while inspiring patriotism among current generations. Through courage and vision, they preserve dignity and strengthen the spirit of African unity.</p>

<p> </p>

<p style="text-align: center;"><em><strong>Their exemplary leadership and lasting legacy should be nurtured and honored.</strong></em></p>
</div></div></div></div></div></div></div></div></div><div id="a1989003545e034d1ccb3b5a1cf7fae6" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542c063d8aa542a88bd7cc5b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542c0773cc9bd1dce9692d67" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542c08cc958232c186b77e5d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542c09f519510e17fbfb618e" class="wb_element wb-accordion" data-plugin="Accordion"><div class="wb-accordion-type-slider"><div id="a1989003542c09f519510e17fbfb618e-list" class="carousel slide" data-ride="carousel" data-interval="5000"><div class="carousel-indicators"><li class="wb-accordion-item active" data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="0" data-item-id="0"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="1" data-item-id="1"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="2" data-item-id="2"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="3" data-item-id="3"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="4" data-item-id="4"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="5" data-item-id="5"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="6" data-item-id="6"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="7" data-item-id="7"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="8" data-item-id="8"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="9" data-item-id="9"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="10" data-item-id="10"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="11" data-item-id="11"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="12" data-item-id="12"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="13" data-item-id="13"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="14" data-item-id="14"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="15" data-item-id="15"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="16" data-item-id="16"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="17" data-item-id="17"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="18" data-item-id="18"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="19" data-item-id="19"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="20" data-item-id="20"></div><div class="carousel-inner" role="listbox"><div class="item active"><div id="a1989003542c0a648b05dc1165b1e165" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542c0b0743bc392f69b5b72a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542c0ca7b49d1926ca74a18a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542d001f6af5ebab663fcaf9" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Julius_Nyerere" title="Read more" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a86452e680a9d209ec90c05814a9e82d_fit.jpg?ts=1785686357"></a></div></div></div><div id="a19890035448017dccb1e99d22e28c5c" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035455036f96495d75d0864c76" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Julius Kambarage Nyerere</p>

<p class="wb-stl-normal" style="text-align: center;">- Tanganyika.</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a19890035448026527e3c008808b5a7a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542d01b72655f5134a13cc3f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542d024a1f70b793b7f5bbf6" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542d036bf376d356828fc46a" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Abeid_Karume" target="_blank"><img loading="lazy" alt="" src="gallery_gen/53628c301827bcdf90467e47fb0295fd_300x300_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354480343945b6843750beffb" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035455042862d8eab91523155d" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Abeid Amani Karume - Zanzibar</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a19890035455059fedd514c7532380e5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542d041308f10278436560b8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542d054f564f9e7ba5e443b4" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542d06d482cac9ce59fca2e2" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Jomo_Kenyatta" target="_blank"><img loading="lazy" alt="" src="gallery_gen/4240af0cc9e867792a97a4a870e58b18_300x200_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544804f203fdb9c0706fc7eb" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545506f26e87f3259f1c7da2" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Mzee Jomo Kenyatta</p>

<p class="wb-stl-custom4" style="text-align: center;">- Kenya</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545a09f2193f55bdd6c6c4ac" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542d076b4aeeb8580bdd7db9" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542d081d21b908ef8eb99cb8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542d092fb573f2c3b9e9e0c2" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Milton_Obote" target="_blank"><img loading="lazy" alt="" src="gallery_gen/30c009dbc34db243f3bcf4a3732a0618_300x354_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544805da7a0398da3b1a7b75" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035455079a0abe2aeead8f16fa" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Milton Obote</p>

<p class="wb-stl-custom4" style="text-align: center;">- Uganda</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545b0ce5ef9214f95b4e887f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542d0a3cc26a2bd64f2090a9" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542e00477d58caa84641932d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542e01d2e0536a0c25f6c4af" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Nelson_Mandela" target="_blank"><img loading="lazy" alt="" src="gallery_gen/c1032c63f0fb7d37d10eaa57e255d971_300x300_fit.jpg?ts=1785686357"></a></div></div></div><div id="a19890035448064a179b7545747b7f01" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354550868cc89db810b58b899" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Nelson Mandela</p>

<p class="wb-stl-custom4" style="text-align: center;">- South Africa</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545c0522c6d595e44dbc29ab" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542e021f910378f471251c21" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542e03289ef6daa314a38492" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542e04eab82725b01101db9b" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Kwame_Nkrumah" target="_blank"><img loading="lazy" alt="" src="gallery_gen/314d963a5d161c4b366ba9af914ab6cc_fit.jpg?ts=1785686357"></a></div></div></div><div id="a19890035448073595a8b900707f3f1c" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035455098b66c1178afa6040cf" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Kwame Nkrumah</p>

<p class="wb-stl-custom4" style="text-align: center;">- Ghana</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545c0d1458d91136ca596524" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542e050beac8c80c3fc1e492" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542e067851a91ec0695dfa89" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542f002496891d4a1b12f51d" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Robert_Mugabe" target="_blank"><img loading="lazy" alt="" src="gallery_gen/78ec9ad1a85de1c5931bafb55601100b_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544808ff2785c60be97b099b" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354550afc8e4b1e7165e4cc4d" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Robert Mugabe</p>

<p class="wb-stl-custom4" style="text-align: center;">- Zimbabwe</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545d06dd625444402d15395f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542f01cc2883a7b1c1fef4b7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542f026d36bc7b0c771b61e8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542f03e2108f53acef550d81" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmed_Ben_Bella" target="_blank"><img loading="lazy" alt="" src="gallery_gen/cea04620dd38546d6e4d97a8e2c11ab9_fit.jpg?ts=1785686357"></a></div></div></div><div id="a19890035448099f71514bc148a5795a" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354550bcc1b0cb6a023668d10" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmed Ben Bella</p>

<p class="wb-stl-custom4" style="text-align: center;">- Algeria</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545d0d6bda6a2417c6504bf2" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542f0438665d925fb3c3d681" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542f05e6deac678b28a3d6f7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542f06b88bd4259c5f256156" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmed_Sékou_Touré" target="_blank"><img loading="lazy" alt="" src="gallery_gen/0924f22b791426459b1fceeea5d83d13_300x376_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354480a2eceae40104af47d86" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354550cd4293b7be41dfac6e9" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmed Sekour Toure</p>

<p class="wb-stl-custom4" style="text-align: center;">-Guinea</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545e04f98070989830dce0db" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542f07075c0e08626cf3702d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543000695212359794af7b3d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543001878769bfc092c531a1" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Agostinho_Neto" target="_blank"><img loading="lazy" alt="" src="gallery_gen/6a3850f6562e671cb5d12dc555d49b51_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354480bf9bbfd8e9b20a06cb5" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354550d71db77a8fcf7056669" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Agostinho Neto</p>

<p class="wb-stl-custom4" style="text-align: center;">- Angola</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545e0b8f3443b9f988b27ab8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543002283e28b2925a9a53bb" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354300399227a44211c4f3cdb" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543004d8eae41f8e3b9a6e08" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Sam_Nujoma" target="_blank"><img loading="lazy" alt="" src="gallery_gen/1570396f1726775d1cce5c83b0706c7e_300x210_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354480cb7c46923230325400b" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545600c74cd3db5543ddb4e5" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Sam Nujoma</p>

<p class="wb-stl-custom4" style="text-align: center;">- Namibia</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545f0225a44866afb99c2a4b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035430053647d7420b06d8ab54" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354300680f46a647a806f6bcb" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543007226c66942ccd64dc27" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Samora_Machel" title="https://en.wikipedia.org/wiki/Samora_Machel" target="_blank"><img loading="lazy" alt="" src="gallery_gen/5372a2a280dd4375e74cdf264fc32a5b_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354480d979fbcf38b37989020" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354560159814341b6cf5dbc63" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Samora Machel</p>

<p class="wb-stl-custom4" style="text-align: center;"> - Mozambique</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545f095cbc4e15e03497222d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354310082ef74694c179eccbf" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035431017609429b584cc869d3" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543102c7ea09d1e61bf4016e" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Kenneth_Kaunda" target="_blank"><img loading="lazy" alt="" src="gallery_gen/550d22763a73fab3b73256541dddbc4b_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544900354bc9392de18618fc" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354560271f682df2822b72f28" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Kenneth Kaunda</p>

<p class="wb-stl-custom4" style="text-align: center;">- Zambia</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a19890035460003a95b3fcb8fcc23c5f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543103e7c0159b3f05b2d472" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543104eef6cb13c2dc69ac84" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543105ddf2166af7492dcd58" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Patrice_Lumumba" target="_blank"><img loading="lazy" alt="" src="gallery_gen/14574c93b57ee80df1b44a961c8a11f8_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544901e450e706bddba05b0c" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035456039018d2e1f5306dee1b" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Patrice Lumumba</p>

<p class="wb-stl-custom4" style="text-align: center;">- Congo (Zaire /DRC)</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003546007e9afa2691f1ea7706a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543106e7867bd6231fd58cd1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543107f461d3f8de9fc3c33e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543200e51ee8a8fd1cbf8ddb" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Hastings_Banda" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a770b5a86758ef8c981bd2d8e0b76d18_fit.jpg?ts=1785686357"></a></div></div></div><div id="a19890035449026f450aaeaa6f6151a8" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354560425145aa2e2ebcf81e3" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Hastings Kamuzu Banda</p>

<p class="wb-stl-custom4" style="text-align: center;">- Malawi</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003546100aafabfe651c84c3b0d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543201c1a7e24682f0627295" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035432025f851380afec0f7a93" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035432034a28170d0c90a64a67" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://sw.wikipedia.org/wiki/Nnamdi_Azikiwe" target="_blank"><img loading="lazy" alt="" src="gallery_gen/933760d0723f699380102b2a69b12759_300x344_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544903e5119cdae6faa43036" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545605153ebb9240d7472e6e" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Nnamdi Azikiwe</p>

<p class="wb-stl-custom4" style="text-align: center;">- Nigeria</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354610532ea75802a34b6b923" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035432041f8a295a720d937d3c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543205123ce45358ecb5f1d5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543206a71aa9603e59948fed" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Thomas_Sankara" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a6c8cd240f9f4d8d18fc4aa78f178e5f_300x168_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354490485805233b0fb7598b4" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035456061fa2eb1edb84c1cddf" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Thomas Sankara</p>

<p class="wb-stl-custom4" style="text-align: center;">- Burkina Fasor</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354610a721fb3ffa5840d9d32" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354320747a205bd9041ba4e45" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035432089d30ee971994b6f7d4" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035433001e491ac471cd2ae1b5" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Muammar_Gaddafi" target="_blank"><img loading="lazy" alt="" src="gallery_gen/1db65078b522d721e9d5c6c5b5ecd98c_300x374_fit.jpg?ts=1785686357"></a></div></div></div><div id="a19890035449052a874ef9068ffc9f04" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035456074a6cc7c29fef35fbb1" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Muammari Gaddafi</p>

<p class="wb-stl-custom4" style="text-align: center;">- Libya</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354610f181525ae75e1f24577" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354330166e9d967f2229aaa4a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035433024fa101780cdc98ea6e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035433031d69643a989585205b" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Haile_Selassie" target="_blank"><img loading="lazy" alt="" src="gallery_gen/b6c71636a45fb72d58b79f9d573734ba_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544906693f7b2f0fcf40d944" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545608f3a8b9244b86d5f27a" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Emperor Haile Selassie I</p>

<p class="wb-stl-custom4" style="text-align: center;">- Ethiopia</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a19890035462037b00400eac94a3128d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354330461e344f2258a4b9848" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354330597b8a15338d3003b06" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354330678d0aec080546df053" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Léopold_Sédar_Senghor" target="_blank"><img loading="lazy" alt="" src="gallery_gen/052732d784d8347a79730116e3fd7d8c_300x170_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354490787c6091531c6344eb4" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545609d0eb403fd8b0bd30fb" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Leopold Sedar Senghor </p>

<p class="wb-stl-custom4" style="text-align: center;">- Senegal</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003546208d467f13ca137c7b313" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035433078eb2cef563ff689cde" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035433087e3482e0360111f9ea" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354330931442ed54bcf6a31a3" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmadou_Ahidjo" target="_blank"><img loading="lazy" alt="" src="gallery_gen/dbd7c2b0f071ed790fad39a580770b78_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544908afc2f5b0a41e96e45a" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354560a96999295b7f5c4f54b" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmadou Ahidjo</p>

<p class="wb-stl-custom4" style="text-align: center;">- Cameroon</p>
</div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div><div id="a198900354490ad730959335a2e9c748" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354330a684c78d13e83fa5069" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543400074f8b47bdb80b6536" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543401a0cf9cefb95038b5b1" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom25">The Voice of Liberation Award</h2>
</div><div id="a198900354490bc6de8e7a0cbce6266c" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal">Celebrating the voices that fueled Africa’s freedom and ignite Pan-African pride today.</p>

<p class="wb-stl-normal">This award honors musicians whose art fought for liberation and shaped our continent’s history — inspiring today’s artists to keep the flame alive.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal">At JUKANYE Festival, we preserve our legacy, salute our heroes, and empower future generations through culture and pride.</p>
</div></div></div></div></div></div></div></div></div><div id="a1989003545e072c01b7728be46828ac" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354170233a71d19148f2b11a9" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2" style="margin: 0in 0in 8pt;"><span style="color:rgba(61,61,61,1);">Objectives &amp; Activities</span></h2>
</div><div id="a1989003544008085dbb74bb32542880" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035417037f8806ea7f2dc0b0aa" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541704943452c743ca5dc66a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035417051ca08aef9c1e16c2c9" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="45" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#0ca3a6"><text x="1.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354400c7117c3b2c59cac1752" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;">Objectives of the JUKANYE International History Festival</h3>
</div><div id="a1989003544e09b64c9cda4840db659d" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><ul>
<li>
<p class="wb-stl-normal">Preserve Africa’s Liberation History</p>
</li>
<li>
<p class="wb-stl-normal">To educate future generations about the historical struggle for Africa’s independence.</p>
</li>
<li>
<p class="wb-stl-normal">Promote Economic Development</p>
</li>
<li>
<p class="wb-stl-normal">To encourage youth participation in key economic and technological sectors.</p>
</li>
<li>
<p class="wb-stl-normal">Strengthen Peace and Justice</p>
</li>
<li>
<p class="wb-stl-normal">To cultivate a spirit of peace as the foundation for sustainable development and human rights.</p>
</li>
<li>
<p class="wb-stl-normal">Promote Kiswahili and African Culture</p>
</li>
<li>
<p class="wb-stl-normal">To champion Kiswahili as a unifying African language and cultural heritage.</p>
</li>
<li>
<p class="wb-stl-normal">Enhance Intersectoral Collaboration</p>
</li>
<li>
<p class="wb-stl-normal">To build partnerships across tourism, arts, energy, heritage, and education.</p>
</li>
<li>
<p class="wb-stl-normal">Inspire Patriotism and Hard Work</p>
</li>
<li>
<p class="wb-stl-normal">To nurture responsible youth committed to national service and progress.</p>
</li>
<li>
<p class="wb-stl-normal">Protect Natural Resources and the Environment</p>
</li>
<li>
<p class="wb-stl-normal">To promote clean energy use and raise environmental conservation awareness.</p>
</li>
<li>
<p class="wb-stl-normal">Empower Youth and Innovation</p>
</li>
<li>
<p class="wb-stl-normal">To establish a fund that supports youth creativity and connects them to opportunities and financing.</p>
</li>
<li>
<p class="wb-stl-normal">Digitize and Promote National Heritage Sites</p>
</li>
<li>
<p class="wb-stl-normal">To modernize and preserve museums and historical monuments through digitization.</p>
</li>
<li>
<p class="wb-stl-normal">Generate Income and Jobs</p>
</li>
<li>
<p class="wb-stl-normal">To expand local and international markets while creating jobs, especially in tourism, transport, entertainment, and hospitality sectors.</p>
</li>
<li>
<p class="wb-stl-normal">Advance the Vision of Mwalimu Nyerere</p>
</li>
<li>
<p class="wb-stl-normal">To fulfill the vision of unity, development, and African cooperation across all sectors.</p>
</li>
</ul>
</div></div></div><div id="a1989003544009e415d26b40cbf59f1f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354180016c65db17e4a4526aa" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="45" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#0ca3a6"><text x="1.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354400d09b68733b2ac66662e" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;">Key Events and Activities of the JUKANYE Festival 2026</h3>
</div><div id="a1989003544e0a78a34ac664f3f0a4a7" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><ul>
<li>
<p class="wb-stl-normal">Historical Museum Exhibitions of African Liberation Leaders</p>
</li>
<li>
<p class="wb-stl-normal">Showcasing the lives, philosophies, and contributions of key African liberation leaders such as Mwalimu Julius Kambarage Nyerere, Nelson Mandela, Kwame Nkrumah, and others.</p>
</li>
<li>
<p class="wb-stl-normal">Traditional and Contemporary Music Performances from Across Africa</p>
</li>
<li>
<p class="wb-stl-normal">Live performances by local and international music groups delivering messages of unity, patriotism, and African pride through traditional and modern sounds.</p>
</li>
<li>
<p class="wb-stl-normal">Historical-Themed Theatrical Performances</p>
</li>
<li>
<p class="wb-stl-normal">Engaging dramatizations of Africa’s liberation struggles that bring history to life through artistic expression and storytelling.</p>
</li>
<li>
<p class="wb-stl-normal">Cultural Dress and Showcase of Participating Nations' Heritage</p>
</li>
<li>
<p class="wb-stl-normal">A vibrant display of traditional clothing and customs, celebrating the cultural diversity and unity of Africa.</p>
</li>
<li>
<p class="wb-stl-normal">Youth Forums and Historical Stakeholders’ Dialogues</p>
</li>
<li>
<p class="wb-stl-normal">Interactive platforms for young people and experts to explore political and economic liberation, Pan-African development, and historical consciousness.</p>
</li>
<li>
<p class="wb-stl-normal">Community Health Clinics</p>
</li>
<li>
<p class="wb-stl-normal">Free health services will be provided to local communities as part of the festival’s commitment to social well-being and sustainable development.</p>
</li>
<li>
<p class="wb-stl-normal">Historical Tours: Butiama, Tabora, Dar es Salaam, and Southern Regions</p>
</li>
<li>
<p class="wb-stl-normal">Guided visits to significant historical sites that shaped Tanzania’s and Africa’s liberation movements.</p>
</li>
<li>
<p class="wb-stl-normal">Tourism Excursions</p>
</li>
<li>
<p class="wb-stl-normal">Exploration of major tourist attractions including Mount Kilimanjaro, Zanzibar Islands, National Parks, game reserves, forests, and museums.</p>
</li>
<li>
<p class="wb-stl-normal">Promotion of Clean and Safe Household Cooking Energy</p>
</li>
<li>
<p class="wb-stl-normal">Public awareness campaigns encouraging the use of eco-friendly and safe cooking energy solutions in homes and small businesses.</p>
</li>
<li>
<p class="wb-stl-normal">Special Documentary Launch on Mwalimu Julius Nyerere</p>
</li>
<li>
<p class="wb-stl-normal">Premiere of a dedicated documentary film celebrating the legacy and development vision of Tanzania’s founding father.</p>
</li>
<li>
<p class="wb-stl-normal">“Let’s Speak Swahili” Language Classes for International Guests</p>
</li>
<li>
<p class="wb-stl-normal">Opportunities for non-Swahili speakers to learn this important African language and promote cross-cultural communication across the continent.</p>
</li>
<li>
<p class="wb-stl-normal">Presentation of Honorary Awards</p>
</li>
<li>
<p class="wb-stl-normal">Honoring past and present leaders, musicians, and contributors whose work has advanced African unity, patriotism, peace, and environmental conservation through art, leadership, and activism.</p>
</li>
</ul>
</div></div></div></div></div></div></div></div></div><div id="a1989003545e0e0301d32ab87ffd3120" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354180167cf91c11f20323f5d" class="wb_element wb-elm-orient-horizontal" data-plugin="Line"><div class="wb-elm-line"></div></div><div id="a198900354400ae2647867fce27f337e" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2" style="text-align: center;"><span style="color:rgba(242,230,2,1);">Call for participation and Support</span></h2>
</div><div id="a1989003544f00fc287c11efb2065599" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h1 class="wb-stl-custom7">We invite collaboration and support from all sectors and partners to make this historic festival a success. Your involvement can make a difference in preserving Africa’s liberation history and inspiring the next generation. We welcome:</h1>

<h1 class="wb-stl-custom7"> </h1>

<ul class="wb-stl-list3">
<li class="wb-stl-normal">
<h1 class="wb-stl-custom7">Strong collaboration between governments and the private sector</h1>
</li>
<li class="wb-stl-normal">
<h1 class="wb-stl-custom7">Support and partnership from international organizations</h1>
</li>
<li class="wb-stl-normal">
<h1 class="wb-stl-custom7">Financial and material sponsorship from institutions and individuals</h1>
</li>
<li class="wb-stl-normal">
<h1 class="wb-stl-custom7">Engagement from media and communication stakeholders to amplify our message</h1>
</li>
<li class="wb-stl-normal">
<h1 class="wb-stl-custom7">Active participation from historians, artists, scholars, and young people</h1>
</li>
<li class="wb-stl-normal">
<h1 class="wb-stl-custom7">Involvement of communities from across Africa and the world</h1>
</li>
</ul>
</div></div></div><div id="a1989003545f053b4811e7f8a987878f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354180251bcaa9a5020c478ef" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2">Our Team:</h2>
</div><div id="a198900354400b052cb3bef8a85a498f" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;">The main festival committee will include members from JACI, Catz Company Limited, leaders from various government sectors, international organizations, and experts from the Culture, Energy, Tourism, and Education sectors.</h3>
</div><div id="a1989003544f08c9dbd620520463c3fc" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541907bbbcbda1749d8b5155" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541908fb1b91dc40ab5b25c4" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://www.nmt.go.tz" target="_blank"><img loading="lazy" alt="" src="gallery_gen/3fdd3cc7f1099a420d73a7c21affc660_fit.jpg?ts=1785686357"></a></div></div></div><div id="a1989003544106b8a2fb29b39f4464b0" class="wb_element wb-elm-orient-horizontal" data-plugin="Line"><div class="wb-elm-line"></div></div><div id="a1989003544f0347e3ad37222004f86b" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;"> Invitation and Protocol</h3>
</div><div id="a1989003545908ec689b7f102de713b9" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><ul>
<li>
<p class="wb-stl-normal">Coordinating Official Invitations:</p>
</li>
<li>
<p class="wb-stl-normal">Prepare and dispatch formal invitations to high-profile guests including national and international leaders, ambassadors, liberation heroes, artists, and key stakeholders of the festival.</p>
</li>
<li>
<p class="wb-stl-normal">Ensuring Protocol Compliance:</p>
</li>
<li>
<p class="wb-stl-normal">Organize official receptions for dignitaries, adhering to proper national and international protocol procedures.</p>
</li>
<li>
<p class="wb-stl-normal">Maintain proper decorum and respect according to the rank and role of each guest.</p>
</li>
<li>
<p class="wb-stl-normal">Facilitating Special Guest Logistics:</p>
</li>
<li>
<p class="wb-stl-normal">Arrange schedules, transportation, accommodation, and security for VIP participants.</p>
</li>
<li>
<p class="wb-stl-normal">Provide guests with essential information about the festival agenda and their individual engagements.</p>
</li>
<li>
<p class="wb-stl-normal">Managing Ceremonial Flow and Official Speeches:</p>
</li>
<li>
<p class="wb-stl-normal">Oversee the coordination of keynote speeches, award presentations, and high-level ceremonial events.</p>
</li>
<li>
<p class="wb-stl-normal">Provide trained hosts or emcees familiar with protocol, language, and cultural etiquette.</p>
</li>
<li>
<p class="wb-stl-normal">Supporting International Participation:</p>
</li>
<li>
<p class="wb-stl-normal">Facilitate travel, visa processing, safety, and hospitality for international guests.</p>
</li>
<li>
<p class="wb-stl-normal">Serve as a bridge between the festival team and diplomatic missions or international organizations.</p>
</li>
<li>
<p class="wb-stl-normal">Supervising Seating and Guest Arrangements:</p>
</li>
<li>
<p class="wb-stl-normal">Manage seating protocols for official events, ensuring all dignitaries are positioned according to their status and title.</p>
</li>
</ul>
</div><div id="a1989003545b01c3b3a932886f3aeb62" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom15"><strong>CONTACT:</strong></p>
</div></div></div><div id="a1989003544107750aa7ea8f57d68939" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541a00af2b8b4e16f67ea782" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://www.nishati.go.tz" target="_blank"><img loading="lazy" alt="" src="gallery_gen/f83f0258de31faf7e886a5bc4dcd3d93_fit.jpg?ts=1785686357"></a></div></div></div><div id="a198900354410800d18f850e6db2c864" class="wb_element wb-elm-orient-horizontal" data-plugin="Line"><div class="wb-elm-line"></div></div><div id="a1989003544f010cceabb8b3dd7d70f1" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;">Sustainable Energy and Clean Cooking Awareness</h3>
</div><div id="a198900354590a4c4de67ecc9e6a42fa" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal"> </p>

<ul>
<li>
<p class="wb-stl-normal">Promoting the use of clean household cooking energy</p>
</li>
<li>
<p class="wb-stl-normal">Educating the public on the health and environmental impacts of using charcoal and firewood.</p>
</li>
<li>
<p class="wb-stl-normal">Raising awareness of the benefits of improved cookstoves, clean LPG gas, electricity, and renewable energy sources like solar.</p>
</li>
<li>
<p class="wb-stl-normal">Participating in exhibitions showcasing clean energy technologies</p>
</li>
<li>
<p class="wb-stl-normal">Displaying products and innovations that contribute to safe and eco-friendly energy use.</p>
</li>
<li>
<p class="wb-stl-normal">Hosting seminars and workshops for youth and citizens on economic opportunities in the clean energy sector</p>
</li>
<li>
<p class="wb-stl-normal">Sharing information about government-supported loans, training, and programs that promote the adoption of sustainable energy.</p>
</li>
<li>
<p class="wb-stl-normal">Providing policy guidance and consultation to festival participants and development stakeholders</p>
</li>
<li>
<p class="wb-stl-normal">Clarifying government efforts towards achieving Sustainable Development Goals (SDG), especially SDG 7: Affordable and Clean Energy for All.</p>
</li>
<li>
<p class="wb-stl-normal">Collaborating with international partners and the private sector to advance Africa’s sustainable energy agenda</p>
</li>
<li>
<p class="wb-stl-normal">Through expert forums and panels at the festival, while encouraging investment and joint ventures.</p>
</li>
</ul>
</div><div id="a1989003545b029986148f250a68a417" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom15"><strong>CONTACT:</strong></p>
</div></div></div><div id="a1989003544f09cad53640fe57a3c6b4" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541a0170e5f9f498af5edfd6" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/2b29980d44a736136b86c0374c00bcde_300x278_fit.jpg?ts=1785686357"></div></div></div><div id="a198900354410964e63c00a8bbd91209" class="wb_element wb-elm-orient-horizontal" data-plugin="Line"><div class="wb-elm-line"></div></div><div id="a1989003544f0aa1a40a589e220236f4" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;">Content &amp; Entertainment</h3>
</div><div id="a19890035459060cb20baaeaecac5dd0" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><ul>
<li>
<p class="wb-stl-normal">1. Coordination of Festival Content</p>
</li>
<li>
<p class="wb-stl-normal">Develop and oversee the overall content plan for the festival (stories, lectures, panel discussions, videos, exhibitions, etc.).</p>
</li>
<li>
<p class="wb-stl-normal">Supervise the selection of themes, content writers, speakers, and presenters.</p>
</li>
<li>
<p class="wb-stl-normal">Ensure that all content aligns with the historical, educational, and cultural objectives of JUKANYE.</p>
</li>
<li>
<p class="wb-stl-normal">2. Entertainment and Performing Arts</p>
</li>
<li>
<p class="wb-stl-normal">Design the entertainment program, including traditional and modern music, cultural dances, drama, poetry, and other performing arts.</p>
</li>
<li>
<p class="wb-stl-normal">Coordinate the participation of local and international artists whose work aligns with the themes of liberation, patriotism, peace, and African unity.</p>
</li>
<li>
<p class="wb-stl-normal">Ensure high artistic standards and meaningful messaging through performances.</p>
</li>
<li>
<p class="wb-stl-normal">3. Management of Performers and Artists</p>
</li>
<li>
<p class="wb-stl-normal">Communicate with performance groups, individual artists, musicians, and drama teams regarding their technical and artistic needs.</p>
</li>
<li>
<p class="wb-stl-normal">Ensure all artists' logistics are well-handled (stage setup, sound, rehearsals, schedules, hospitality).</p>
</li>
<li>
<p class="wb-stl-normal">Uphold professionalism and ethical standards in all performances in line with the dignity of the festival.</p>
</li>
<li>
<p class="wb-stl-normal">4. Creation of the Festival Program Line-Up</p>
</li>
<li>
<p class="wb-stl-normal">Develop a clear and engaging day-by-day festival schedule.</p>
</li>
<li>
<p class="wb-stl-normal">Collaborate with the protocol team, historians, and seminar organizers to integrate all sessions seamlessly.</p>
</li>
<li>
<p class="wb-stl-normal">5. Digital Content and Festival Documentation</p>
</li>
<li>
<p class="wb-stl-normal">Coordinate the production of digital content such as videos, documentaries, podcasts, or TV segments about the event and historical figures.</p>
</li>
<li>
<p class="wb-stl-normal">Ensure proper archiving of all festival content for future reference and educational purposes.</p>
</li>
<li>
<p class="wb-stl-normal">6. Creative Experience Design for Participants</p>
</li>
<li>
<p class="wb-stl-normal">Curate interactive and impactful events that offer an inspiring and educational experience.</p>
</li>
<li>
<p class="wb-stl-normal">Ensure participants leave the festival with memorable and enriching encounters.</p>
</li>
</ul>
</div><div id="a1989003545b038f400230d75177a147" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom15"><strong>CONTACT:</strong></p>
</div></div></div><div id="a198900354590bffb9960afb23fb71a7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541a02d45a1c97144f5747cc" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/8eb61f70aae3a1d3c32780df72e4693a_392x502_fit.png?ts=1785686357"></div></div></div><div id="a198900354410a0e1fe9b2fd7fa8b874" class="wb_element wb-elm-orient-horizontal" data-plugin="Line"><div class="wb-elm-line"></div></div><div id="a1989003544f0245030bc2e067a633c9" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;">Brand Manager</h3>
</div><div id="a19890035459070b1189ea8eac4755dc" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><ul>
<li>
<p class="wb-stl-normal">Brand Management:</p>
</li>
<li>
<p class="wb-stl-normal">Develop and manage the festival’s official brand identity, including the logo, colors, slogan, and modern visual presentation.</p>
</li>
<li>
<p class="wb-stl-normal">Ensure that the brand reflects the values, history, and goals of the festival.</p>
</li>
<li>
<p class="wb-stl-normal">Maintain brand consistency across all communication materials, exhibitions, advertisements, and online platforms.</p>
</li>
<li>
<p class="wb-stl-normal">Marketing Strategy:</p>
</li>
<li>
<p class="wb-stl-normal">Design and implement effective marketing strategies to raise awareness and encourage participation in the festival.</p>
</li>
<li>
<p class="wb-stl-normal">Target local, African, and international audiences using diverse communication channels.</p>
</li>
<li>
<p class="wb-stl-normal">Conduct market research to understand audience needs and determine the best approaches for engagement.</p>
</li>
<li>
<p class="wb-stl-normal">Advertising and Promotion:</p>
</li>
<li>
<p class="wb-stl-normal">Plan advertising campaigns via TV, radio, newspapers, digital platforms, social media, and street billboards.</p>
</li>
<li>
<p class="wb-stl-normal">Collaborate with journalists, broadcasters, influencers, and media houses to promote the festival widely.</p>
</li>
<li>
<p class="wb-stl-normal">Sponsorship and Partnerships:</p>
</li>
<li>
<p class="wb-stl-normal">Prepare commercial documents and sponsorship proposals.</p>
</li>
<li>
<p class="wb-stl-normal">Seek sponsors and build strategic partnerships by offering visibility opportunities within the festival.</p>
</li>
<li>
<p class="wb-stl-normal">Ensure sponsors are acknowledged and given proper visibility in promotional materials and at events.</p>
</li>
<li>
<p class="wb-stl-normal">Media and Social Media Management:</p>
</li>
<li>
<p class="wb-stl-normal">Manage a content calendar for social media platforms, ensuring messaging aligns with the festival’s objectives.</p>
</li>
<li>
<p class="wb-stl-normal">Prepare press releases, promotional videos, banners, brochures, and digital assets.</p>
</li>
<li>
<p class="wb-stl-normal">Track engagement and impact of campaigns using analytics and reports.</p>
</li>
<li>
<p class="wb-stl-normal">Monitoring and Evaluation:</p>
</li>
<li>
<p class="wb-stl-normal">Assess the success of marketing campaigns and adjust strategies where necessary.</p>
</li>
<li>
<p class="wb-stl-normal">Collect feedback from participants and stakeholders to understand the perception and reach of the festival.</p>
</li>
</ul>
</div><div id="a1989003545b048b02a77a471df771aa" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom15"><strong>CONTACT:</strong></p>

<p class="wb-stl-custom15"><strong>P.O Box 38012</strong></p>

<p class="wb-stl-custom15"><b>Dar Es Salaam, Tanzania</b></p>

<p class="wb-stl-custom15"><b>Mob. +255 (0) 673 023 547</b></p>

<p class="wb-stl-custom15"><b>Mob. +255 (0) 789 388 232</b></p>
</div></div></div></div></div></div></div><div id="a1989003545f0c4344f5e362818c2aec" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541903e5479582ea0f7a7da8" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2">Board Members:</h2>
</div><div id="a19890035441037178cbc0265f4aac14" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;">The main festival committee will include members from JACI, Catz Company Limited, leaders from various government sectors, international organizations, and experts from the Culture, Energy, Tourism, and Education sectors.</h3>
</div><div id="a1989003544f064f9ca9b18bff32a96a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354190440d0cee829cdf56138" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035419053b4eb5fd9efc7507d9" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354280304af8a2e747f931fc3" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery/kitenge%20mat.jpg?ts=1785686357"></div></div></div></div></div><div id="a1989003544104d8af41a91408be49a1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035419069d8923cac40857b3fb" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;">Roles</h3>
</div><div id="a198900354410579937b59c4948612cb" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><ul>
<li class="wb-stl-normal">Strategic Oversight – Provide vision, direction, and long-term goals for the festival.</li>
<li class="wb-stl-normal">Policy Guidance – Approve major plans, policies, and ensure alignment with the festival’s mission.</li>
<li class="wb-stl-normal">Resource Mobilization – Support fundraising efforts, partnerships, and sponsorship outreach.</li>
<li class="wb-stl-normal">Governance &amp; Compliance – Ensure transparency, accountability, and legal compliance.</li>
<li class="wb-stl-normal">Advisory Role – Offer expertise in areas such as history, culture, tourism, and development.</li>
<li class="wb-stl-normal">Representation – Act as ambassadors of the festival to national and international stakeholders.</li>
<li class="wb-stl-normal">Monitoring &amp; Evaluation – Oversee performance, assess progress, and recommend improvements.</li>
</ul>
</div></div></div><div id="a1989003544f07dca8051b85ff969415" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"></div></div></div></div></div></div><div id="a1989003545a089807d0745a6b5bc663" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><ul>
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
</div></div></div><div id="a1989003546005f89639c8da85bc5c2b" class="wb_element" data-plugin="Button"><a class="wb_button" href="Homeb/"><span><svg xmlns="http://www.w3.org/2000/svg" width="1793.982" height="1793.982" viewBox="0 0 1793.982 1793.982" style="display: inline-block; vertical-align: middle; position: relative; top: -1px; height: 1em; width: 1em; overflow: visible; direction: ltr;"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg>&nbsp;Home Page</span></a></div></div></div><div id="wb_footer_a198900350f300a37ae9158159156524" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fc2149cd000eb3b8848562ec6f176" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc386d7d4d77961b3399b7e7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19fb429723a03cab5671bd0692f5610" class="wb_element" data-plugin="Button"><a class="wb_button" href="{{base_url}}"><span><svg xmlns="http://www.w3.org/2000/svg" width="1793.982" height="1793.982" viewBox="0 0 1793.982 1793.982" style="display: inline-block; vertical-align: middle; position: relative; top: -1px; height: 1em; width: 1em; overflow: visible; direction: ltr;"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg>&nbsp;Home Page</span></a></div><div id="a188dd9ebc386e9c761088b65418f7a1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc386f7f651dc7e4d0792624" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="Homeb/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#4be6e6"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a188dd9ebc38700f452a2fef2fcabe01" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="Homeb/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1024 1024" style="direction: ltr; color:#ffffff"><text x="64" y="960" font-size="1024" fill="currentColor" style='font-family: "builder-ui-icons-plugins"'></text></svg></a></div></div></div><div id="a188dd9ebc3871cfcba1a4cf7091cb6d" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="Homeb/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#ffffff"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div></div></div><div id="a19fc20bdb7e00c6080e244c0b41b351" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-custom16" style="text-align: center;">ADDRESS:</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">P,O BOX  DAR- ES - SALAAM, TANZANIA</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">PHONE: +255 746 174403 +255 789  388232 +255 719 083050</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">EMAIL: jukanyefestival@gmail.com</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">info@jukanye.com</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">WEBSITE: www.jukanye.com</h3>
</div><div id="a188dd9ebc38721835f60daecdc81bab" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/3a0fa4358ae2f4fb06a94eaab03b4403_fit.png?ts=1785686357"></div></div></div><div id="a19fc20a045f00e06f9422396398c49c" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-footer">© 2025 <a href="http://jukanye.com">jukanye.com</a> - Honoring Africa’s True Patriots and Heroes.</p>
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
	<script src="js/a198900350f300a37ae9158159156524-bundle.js?ts=20260802185857" type="text/javascript" defer></script>{{hr_out}}<script>
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

				document.cookie = '__cookie_law__=' + (2) + '; path=/; expires=Wed, 28 Jul 2027 18:59:19 GMT';

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
	<title><?php echo htmlspecialchars((isset($seoTitle) && $seoTitle !== "") ? $seoTitle : "Unlisted"); ?></title>
	<base href="{{base_url}}" />
	<?php echo isset($sitemapUrls) ? (generateCanonicalUrl($sitemapUrls)."\n") : ""; ?>	
		<link rel="alternate" hreflang="en" href="{{base_url}}{{lang_en}}" />
		<link rel="alternate" hreflang="x-default" href="{{base_url}}{{lang_en}}" />
			<link rel="alternate" hreflang="sw" href="{{base_url}}{{lang_sw}}" />
		
						<meta name="viewport" content="width=device-width, initial-scale=1" />
					<meta name="description" content="<?php echo htmlspecialchars((isset($seoDescription) && $seoDescription !== "") ? $seoDescription : "Unlisted"); ?>" />
			<meta name="keywords" content="<?php echo htmlspecialchars((isset($seoKeywords) && $seoKeywords !== "") ? $seoKeywords : "Unlisted"); ?>" />
			
	<!-- Facebook Open Graph -->
		<meta property="og:title" content="<?php echo htmlspecialchars((isset($seoTitle) && $seoTitle !== "") ? $seoTitle : "Unlisted"); ?>" />
			<meta property="og:description" content="<?php echo htmlspecialchars((isset($seoDescription) && $seoDescription !== "") ? $seoDescription : "Unlisted"); ?>" />
			<meta property="og:image" content="<?php echo htmlspecialchars((isset($seoImage) && $seoImage !== "") ? "{{base_url}}".$seoImage : ""); ?>" />
			<meta property="og:type" content="article" />
			<meta property="og:url" content="__wb_curr_url__" />
		<!-- Facebook Open Graph end -->

		<meta name="generator" content="Website Builder" />
			<link href="css/common-bundle.css?ts=20260802185857" rel="stylesheet" type="text/css" />
	<link href="css/a198900350f300a37ae9158159156524-bundle.css?ts=20260802185857" rel="stylesheet" type="text/css" id="wb-page-stylesheet" />
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


<body class="site site-lang-sw<?php if (isset($wbPopupMode) && $wbPopupMode) echo ' popup-mode'; ?> " <?php ?>><div id="wb_root" class="root wb-layout-vertical"><div class="wb_sbg"></div><div id="wb_header_a198900350f300a37ae9158159156524" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc3858a7a4bf4599d6087d14" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc38596f36338d0b0d66657b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1986657436700dbe63ba0cbad5bbe2c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fb4223ec700f33f6a6750b25b7549" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fc26ad9c300737c8a0c139e48b498" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc385abbb04767f5aaa74a38" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/63a123b911049cc657f1d0f2a9cc7765_fit.png?ts=1785686358"></div></div></div><div id="a19fb4297212030bdabc97de04dae2a0" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom49">TAMASHA LA KIMATAIFA LA JULIUS KAMBARAGE NYERERE</h2>
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
						var lib = new GalleryLib({"id":"a19fb8f3c64000b0747d009eda7d1a44","height":"auto","type":"slideshow","trackResize":true,"interval":5,"speed":1000,"images":[{"thumb":"gallery_gen\/9147f62c31174403cafdbe5847fd40e4_301.5x134_fill.png","src":"gallery_gen\/a0295deaa452d91f264f568d7ace6a7c_fit.png?ts=1785686358","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/f3e0a489b3b22ccf940c58dffbcd2ad4_301.5x134_fill.jpg","src":"gallery_gen\/2a406b85dd90631c40b79158c1877d4f_fit.jpg?ts=1785686358","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/da8e24800b8f72dd8eed800429e1a18b_301.5x134_fill.jpg","src":"gallery_gen\/3c456088697ef08011819b714ae09234_fit.jpg?ts=1785686358","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/d362c813c5330d042dde3a964f0bfed1_301.5x134_fill.jpg","src":"gallery_gen\/30ce731cc7b1cc1edd84ddce750a6366_fit.jpg?ts=1785686358","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/33145b78952db630d35b79ec91eed8d5_301.5x134_fill.jpg","src":"gallery_gen\/47e964e8cdbbdbffac1cc75dec2c4369_fit.jpg?ts=1785686358","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/72018fdb993c6ceb781c0740d2917da8_301.5x134_fill.jpg","src":"gallery_gen\/a55bfef5daf82a78f393f684c67908ca_fit.jpg?ts=1785686358","width":1881,"height":836,"title":"","link":null,"description":"","address":""}],"border":{"border":"5px none #00008c"},"padding":10,"thumbWidth":100,"thumbHeight":100,"thumbAlign":"center","thumbPadding":6,"thumbAnim":"","thumbShadow":"","imageCover":true,"disablePopup":false,"controlsArrow":"chevron","controlsArrowSize":14,"controlsArrowStyle":{"normal":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}},"hover":{"color":"#DDDDDD","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#222222","forText":true,"css":{"text-shadow":"0px 0px 1px #222222"}}},"active":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}}},"slideOpacity":100,"showPictureCaption":"always","captionIncludeDescription":false,"captionPosition":"center bottom","mapTypeId":"roadmap","markerIconTypeId":"thumbs","zoom":2,"mapCenter":{"latLng":{"lat":41.244772343082,"lng":-5.2734375},"text":"41.244772343082076, -5.2734375"},"key":"AIzaSyD-9xtp38UunEmx7XfJ7eBh-K4w8qQ6SEw","theme":"default","color":"#eeeeee","showSatellite":true,"showZoom":true,"showStreetView":true,"showFullscreen":true,"allowDragging":true,"showRoads":true,"showLandmarks":true,"showLabels":true,"locale":"sw_TZ","pauseOnHover":true});
						lib.appendTo(el);
					})(window.wbmodGalleryLib);
				});
			});
		</script></div></div></div></div></div><div id="a19fb429722400fb62f16c17777e0dbd" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19fb42971f400a1d073d65740953b98" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Jisajiri/"><span>Jisajiri Kushiriki</span></a></div><div id="a19fb429722c00d2df553faa4f96bb89" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Bidhaa-za-Tamasha/"><span>Bidhaa</span></a></div><div id="a19fb4297209006e0ba9445e1db2f558" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Waliopendekezwa-kupewa-Tuzo/"><span>Walio pendekezwa Kupata Tuzo</span></a></div></div></div></div></div><div id="a19fb81fa1120059e7c1682d66b9ba06" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"></div></div></div></div></div></div></div></div><div id="wb_main_a198900350f300a37ae9158159156524" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035418032f147dbf5bf44fa000" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035418042f314761cebd519549" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035418051fcac84b13e28b6006" class="wb_element wb_element_picture loop wb-anim-entry wb-anim wb-anim-fade-in-none" data-plugin="Picture" data-wb-anim-entry-time="11" data-wb-anim-entry-delay="7" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery/julius-nyerere.jpg?ts=1785686358"></div></div></div><div id="a1989003544100df64ec6a46d5767460" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354180684288d31f5aea574c7" class="wb_element wb-prevent-layout-click" data-plugin="Video"><video controls="" autoplay="true" loop="true" muted="true" playsinline=""><source type="video/mp4" src="gallery/Mambo%20Jambo%20Poa.mp4"></source><a href="gallery/Mambo%20Jambo%20Poa.mp4">Mambo Jambo Poa.mp4</a></video></div></div></div></div></div></div></div><div id="a19890035440072008d1c83053905684" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541700896fd71d77e81f774f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354170149e8dc033a8b34b36a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354180717fc997389280e4083" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541808a1965c15485e2338b1" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2"> </h2>

<h2 class="wb-stl-heading2">TAMASHA LA KIMATAIFA LA HISTORIA LA JULIUS KAMABARAGE NYERERE</h2>
</div><div id="a198900354420754fec8d5800d1c1b92" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3">Safari ya Afrika kuelekea uhuru ni hadithi ya ujasiri, kujitolea, na maono yasiyotetereka. Kuanzia kwenye viwanja vya vita vya vumbi hadi kwenye kumbi za diplomasia, viongozi wa ukombozi wa Afrika walisimama imara dhidi ya ukandamizaji wa kikoloni, wakawasha mwanga wa uhuru unaoendelea kung'aa kote barani hadi leo.</h3>
</div><div id="a1989003544f05b749b5edc224a56f58" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354180992e3967d17bbe12ed2" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Jisajiri/"><span>Jisajiri Kushiriki</span></a></div><div id="a198900354420661b91a8bfaa5faf35c" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Waliopendekezwa-kupewa-Tuzo/"><span>Wadhamini</span></a></div><div id="a19890035454001e668935e1cbdb6ec6" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Bidhaa-za-Tamasha/"><span>Bidhaa</span></a></div><div id="a198900354590969953004d27bcc8565" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Changia/"><span>Changia</span></a></div><div id="a1989003545b0697d990deab813e8923" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Wadhamini/"><span>Wadhamini</span></a></div></div></div><div id="a1989003545a06d1f24b270080f89ebe" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035428019ba03999fbfbf13d60" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Waliopendekezwa-kupewa-Tuzo/"><span>Walio pendekezwa Kupata Tuzo</span></a></div></div></div></div></div><div id="a198900354490c0608f6971a9ca838a3" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom42"><strong>Nani Anaweza Kushiriki?</strong></p>

<ul>
<li class="wb-stl-custom42">Nchi zote za Afrika zinazotaka kushiriki</li>
<li class="wb-stl-custom42">Nchi rafiki zenye uhusiano mzuri na Afrika</li>
<li class="wb-stl-custom42">Washirika wa kimataifa kutoka sekta za elimu, maendeleo, na utamaduni</li>
<li class="wb-stl-custom42">Watalii, wafanyabiashara, na wataalamu</li>
<li class="wb-stl-custom42">Jamii kutoka Tanzania na duniani kote—kila mtu yupo mkaribisho!<strong>  </strong></li>
</ul>
</div></div></div></div></div></div></div><div id="a1989003545102a25dc0357c49f07e57" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541c0b0cc88e94fd0f82686a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541d00e1ffbbf79b698d4868" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035428029abaf5c9a0d8a899a0" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541d0179b1681b43fadbc224" class="wb_element wb-accordion" data-plugin="Accordion"><div class="wb-accordion-type-slider"><div id="a1989003541d0179b1681b43fadbc224-list" class="carousel slide" data-ride="carousel" data-interval="5000"><div class="carousel-indicators"><li class="wb-accordion-item active" data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="0" data-item-id="0"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="1" data-item-id="1"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="2" data-item-id="2"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="3" data-item-id="3"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="4" data-item-id="4"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="5" data-item-id="5"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="6" data-item-id="6"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="7" data-item-id="7"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="8" data-item-id="8"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="9" data-item-id="9"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="10" data-item-id="10"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="11" data-item-id="11"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="12" data-item-id="12"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="13" data-item-id="13"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="14" data-item-id="14"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="15" data-item-id="15"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="16" data-item-id="16"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="17" data-item-id="17"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="18" data-item-id="18"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="19" data-item-id="19"><li class="wb-accordion-item " data-target="#a1989003541d0179b1681b43fadbc224-list" data-slide-to="20" data-item-id="20"></div><div class="carousel-inner" role="listbox"><div class="item active"><div id="a1989003541d02558cafdb9d77cb9941" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541d039755ddeb39b33cda31" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541d0443d45e913100b4d6f5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541d0525cbd3bbd7f3a29aa6" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Julius_Nyerere" title="Read more" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a86452e680a9d209ec90c05814a9e82d_fit.jpg?ts=1785686358"></a></div></div></div><div id="a198900354420bf9d3e0811f0b39dcbf" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545103298eb4d997a8ead0b8" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Julius Kambarage Nyerere</p>

<p class="wb-stl-normal" style="text-align: center;">- Tanganyika.</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a19890035443004ef0aca37545878c7e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541d06bec67d4e1813a4ed67" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541d0751f8407e4f38f16eba" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541d082d8afec5c8c6b054cd" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Abeid_Karume" target="_blank"><img loading="lazy" alt="" src="gallery_gen/53628c301827bcdf90467e47fb0295fd_300x300_fit.jpg?ts=1785686358"></a></div></div></div><div id="a19890035443014fe0352171c6958b82" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035451042d7507317558c30932" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Abeid Amani Karume - Zanzibar</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545105853bc1d2886efddd1b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541d09a7168099df0388b4f9" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541d0a3c53ab5db537a1d5f7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541d0b9df1af074612ae2346" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Jomo_Kenyatta" target="_blank"><img loading="lazy" alt="" src="gallery_gen/4240af0cc9e867792a97a4a870e58b18_300x200_fit.jpg?ts=1785686358"></a></div></div></div><div id="a1989003544302ef7e96698d5ac6c338" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545106a676eb3efd3b0da39e" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Mzee Jomo Kenyatta</p>

<p class="wb-stl-custom4" style="text-align: center;">- Kenya</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545a023df9a777aba5750e03" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541e00983901a67cfa563969" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541e01c84e932c92a329816f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541e02a6b6849c18cc57ea8a" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Milton_Obote" target="_blank"><img loading="lazy" alt="" src="gallery_gen/30c009dbc34db243f3bcf4a3732a0618_300x354_fit.jpg?ts=1785686358"></a></div></div></div><div id="a19890035443030cf0420554bb5fc606" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035451077008418322ffd8f763" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Milton Obote</p>

<p class="wb-stl-custom4" style="text-align: center;">- Uganda</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545b081cbf2d74f11410d44a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541e03fe3c10bc264712d36e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541e04f18178773f71bac54d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541e0579227f6c0277d4b705" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Nelson_Mandela" target="_blank"><img loading="lazy" alt="" src="gallery_gen/c1032c63f0fb7d37d10eaa57e255d971_300x300_fit.jpg?ts=1785686358"></a></div></div></div><div id="a1989003544304d12f8259fe17780c0f" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545108a3e7066d7b80552735" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Nelson Mandela</p>

<p class="wb-stl-custom4" style="text-align: center;">- South Africa</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545c02f77f7bfede21744b77" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541e065e5a183ef686cfe2ea" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541e07e7f276cffe9df01a39" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541e08c6af038da5f097b89b" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Kwame_Nkrumah" target="_blank"><img loading="lazy" alt="" src="gallery_gen/314d963a5d161c4b366ba9af914ab6cc_fit.jpg?ts=1785686358"></a></div></div></div><div id="a19890035443057e9fd53fc3892e1f8c" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035451097378bb0d58fedee352" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Kwame Nkrumah</p>

<p class="wb-stl-custom4" style="text-align: center;">- Ghana</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545c0aadd4ecb97ff6eec2ab" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541e094f7b1566f2075d3477" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541e0aa66d304cee163f2878" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541e0bbe9000342427390dc0" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Robert_Mugabe" target="_blank"><img loading="lazy" alt="" src="gallery_gen/78ec9ad1a85de1c5931bafb55601100b_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544306c92461aabd9dcb6564" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354510a267011657f3bbb7573" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Robert Mugabe</p>

<p class="wb-stl-custom4" style="text-align: center;">- Zimbabwe</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545d0203cbe9364f8170a0de" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541e0c78a6f470898f2dfbda" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541f001f05f246fd6c89c88c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541f0119c9ec935ac9add1fb" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmed_Ben_Bella" target="_blank"><img loading="lazy" alt="" src="gallery_gen/cea04620dd38546d6e4d97a8e2c11ab9_fit.jpg?ts=1785686359"></a></div></div></div><div id="a19890035443077d44ee1cd675f2bcd7" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354510bbc78669bf45c145833" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmed Ben Bella</p>

<p class="wb-stl-custom4" style="text-align: center;">- Algeria</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545d09b9576e4542158308bc" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541f02696031f6046d609e42" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541f036b680fbe5affbb263c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541f0482ce4353e6c0e73f7b" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmed_Sékou_Touré" target="_blank"><img loading="lazy" alt="" src="gallery_gen/0924f22b791426459b1fceeea5d83d13_300x376_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354430830c02b35f38e1d0391" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354510c4299b1cf6398d28fad" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmed Sekour Toure</p>

<p class="wb-stl-custom4" style="text-align: center;">-Guinea</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545e00b4fc9bb17abeb67899" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541f058a92c5cf082af2c5e7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541f0665719a9f4bdc4fb26d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541f0734b70d4c707c571b3c" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Agostinho_Neto" target="_blank"><img loading="lazy" alt="" src="gallery_gen/6a3850f6562e671cb5d12dc555d49b51_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544309e1f25e065380abea7e" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354510d15ff2b4da44cbad600" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Agostinho Neto</p>

<p class="wb-stl-custom4" style="text-align: center;">- Angola</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545e08bf17b8003cd6959876" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541f086cdeb2312b626db60c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541f09b7403e26f4a5eb87f3" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541f0a97ea824cd1de0fa8d1" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Sam_Nujoma" target="_blank"><img loading="lazy" alt="" src="gallery_gen/1570396f1726775d1cce5c83b0706c7e_300x210_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354430aa75ee1e390a2fe3101" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354510e641a1dbf0e7f2e2446" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Sam Nujoma</p>

<p class="wb-stl-custom4" style="text-align: center;">- Namibia</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545e0f5b1bcda44d05dd6279" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541f0b9a2af7b603f955a69f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541f0cc7b024f079d9c7b2f6" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541f0d713073ddc010f7a951" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Samora_Machel" title="https://en.wikipedia.org/wiki/Samora_Machel" target="_blank"><img loading="lazy" alt="" src="gallery_gen/5372a2a280dd4375e74cdf264fc32a5b_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354430bdc7a4f6320ffecaa3f" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354510ffb1f6cb6bc31a40349" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Samora Machel</p>

<p class="wb-stl-custom4" style="text-align: center;"> - Msumbiji</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545f06b339483fd42420b39f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542000fb4dff27816d08e12c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542001e3e5f5dabe5070ec7b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542002566df74e4cd5b7f5fd" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Kenneth_Kaunda" target="_blank"><img loading="lazy" alt="" src="gallery_gen/550d22763a73fab3b73256541dddbc4b_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354430cd6d15143d16779fc23" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545110637806fb9ed1a6e3f6" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Kenneth Kaunda</p>

<p class="wb-stl-custom4" style="text-align: center;">- Zambia</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545f0dedcd93235756c4ec86" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035420031955440f792e21a073" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542004042c8d791007841f30" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035420051414102781d75aa997" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Patrice_Lumumba" target="_blank"><img loading="lazy" alt="" src="gallery_gen/14574c93b57ee80df1b44a961c8a11f8_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354430dc207e7e317eb99965b" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354520059e361815555aafd24" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Patrice Lumumba</p>

<p class="wb-stl-custom4" style="text-align: center;">- Congo (Zaire /DRC)</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003546003d20c4ada9c9371ad48" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542006dbb96e6abfcf2f950b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354200723f7c326c9002d742e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354200864e4478a517bc52ab3" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Hastings_Banda" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a770b5a86758ef8c981bd2d8e0b76d18_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354430e226bfefb8836aca90d" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354520155a108f128f1c3956b" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Hastings Kamuzu Banda</p>

<p class="wb-stl-custom4" style="text-align: center;">- Malawi</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354600a1a9e3f2cc3d5978126" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542009e862f88d3f51309e77" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354200a53c755f2662fb5d403" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354200b2f62768ff8c222970a" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://sw.wikipedia.org/wiki/Nnamdi_Azikiwe" target="_blank"><img loading="lazy" alt="" src="gallery_gen/933760d0723f699380102b2a69b12759_300x344_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544400dc3c1878b4ee4f22a8" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545202d77f148180732d84a1" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Nnamdi Azikiwe</p>

<p class="wb-stl-custom4" style="text-align: center;">- Nigeria</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003546103d4ab4c1c28f5dbc273" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354200c803ced589b0ad55979" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354200da600e48133f75c6745" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035421003a11c249cb5aae5f99" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Thomas_Sankara" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a6c8cd240f9f4d8d18fc4aa78f178e5f_300x168_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544401bbfc9169baef5183fe" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035452038ae26aea7bd13f1e82" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Thomas Sankara</p>

<p class="wb-stl-custom4" style="text-align: center;">- Burkina Fasor</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a19890035461082630bda2231ddf31c4" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035421013b1d726614c68b2817" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542102dd4582473497fab6a5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542103de0f89edab299120c0" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Muammar_Gaddafi" target="_blank"><img loading="lazy" alt="" src="gallery_gen/1db65078b522d721e9d5c6c5b5ecd98c_300x374_fit.jpg?ts=1785686359"></a></div></div></div><div id="a19890035444027d5b9a9f83c797bed6" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545204b2790cfd56bf07d267" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Muammari Gaddafi</p>

<p class="wb-stl-custom4" style="text-align: center;">- Libya</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354610d44d29896a79d7d9fd8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354210462c507756f3bf7443e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542105c2c738d203b0b9ce0e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542106332c96bf960422b134" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Haile_Selassie" target="_blank"><img loading="lazy" alt="" src="gallery_gen/b6c71636a45fb72d58b79f9d573734ba_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544403123270bf89d4ffae1f" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545205e6450bdf24ab28a204" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Emperor Haile Selassie I</p>

<p class="wb-stl-custom4" style="text-align: center;">- Ethiopia</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354620152a28bd56d8e7bb517" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542107ad5bea0cd9e94a2565" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035421080a850e89d4a1629b4b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035421096a127e7f4e5bb14c5c" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Léopold_Sédar_Senghor" target="_blank"><img loading="lazy" alt="" src="gallery_gen/052732d784d8347a79730116e3fd7d8c_300x170_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544404d321db4db915de9218" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545206538c14acb01e1e6444" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Leopold Sedar Senghor </p>

<p class="wb-stl-custom4" style="text-align: center;">- Senegal</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a19890035462061639c5b71443b1bb50" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354210a1aabb0cdcdb7f34be2" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354210be47640020da7472c77" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354210c0a74564ee4f0b3e6b8" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmadou_Ahidjo" target="_blank"><img loading="lazy" alt="" src="gallery_gen/dbd7c2b0f071ed790fad39a580770b78_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544405ee39cd362fbc13da23" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545207b06bc7ed9280b6b6ae" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmadou Ahidjo</p>

<p class="wb-stl-custom4" style="text-align: center;">- Cameroon</p>
</div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div><div id="a198900354440773d085f9bf3b82b2d8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354210d2be39c67d69728e0c1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542200bf39de59fb8b106c57" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542201d094d8d40e2e43ee8a" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom25">Kusherehekea Ukombozi wa Afrika, Urithi, Umoja na Kawaenzi Mashujaa wa Kweli na Wazalendo wa Afrika.</h2>

<h2 class="wb-stl-custom25"> </h2>
</div><div id="a198900354490df61ad877981c66c347" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542202630c607a53fd676ae8" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal">Hawa viongozi – wanaume na wanawake wenye msimamo thabiti – hawakutafuta faida binafsi. Waliota juu ya heshima kwa watu wao, umoja kwa mataifa yao, na mustakabali unaoongozwa na Waafrika wenyewe. Kuanzia hekima ya Mwalimu Julius Kambarage Nyerere katika kuunganisha Tanzania, hadi wito wa ujasiri wa Kwame Nkrumah wa Uafrika Moja, kutoka kilio cha haki cha Patrice Lumumba hadi roho ya maridhiano ya Nelson Mandela – urithi wao unaendelea kuishi.</p>

<p class="wb-stl-normal">Katika Tamasha la Kimataifa la Historia la JUKANYE 2026, tunawaenzi mashujaa hawa. Kupitia usimulizi wa historia, maonesho, muziki, semina, na sherehe za kitamaduni, tunakumbuka na kusherehekea kujitoa kwao – tukikumbusha kizazi cha sasa na kuhamasisha kizazi kijacho kwa roho ya uzalendo.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>Majina yao yasikike.</strong></p>

<p class="wb-stl-normal"><strong>Hadithi zao ziinspire</strong>.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal" style="text-align: center;"><strong><em>Maono yao yaendelee kuishi.</em></strong></p>
</div></div></div></div></div></div></div></div></div></div></div><div id="a1989003545a0aea50174a68d960cd7b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543402f5ef7c798c9fc1743e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035434033a0dafaa5eb326fa7f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543404d17c65672786c2d5b5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543405ddc7c0bbe674d18f6f" class="wb_element wb-accordion" data-plugin="Accordion"><div class="wb-accordion-type-slider"><div id="a1989003543405ddc7c0bbe674d18f6f-list" class="carousel slide" data-ride="carousel" data-interval="5000"><div class="carousel-indicators"><li class="wb-accordion-item active" data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="0" data-item-id="0"><li class="wb-accordion-item " data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="1" data-item-id="1"><li class="wb-accordion-item " data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="2" data-item-id="2"><li class="wb-accordion-item " data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="3" data-item-id="3"><li class="wb-accordion-item " data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="4" data-item-id="4"><li class="wb-accordion-item " data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="5" data-item-id="5"><li class="wb-accordion-item " data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="6" data-item-id="6"><li class="wb-accordion-item " data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="7" data-item-id="7"><li class="wb-accordion-item " data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="8" data-item-id="8"><li class="wb-accordion-item " data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="9" data-item-id="9"><li class="wb-accordion-item " data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="10" data-item-id="10"><li class="wb-accordion-item " data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="11" data-item-id="11"><li class="wb-accordion-item " data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="12" data-item-id="12"><li class="wb-accordion-item " data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="13" data-item-id="13"><li class="wb-accordion-item " data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="14" data-item-id="14"><li class="wb-accordion-item " data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="15" data-item-id="15"><li class="wb-accordion-item " data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="16" data-item-id="16"><li class="wb-accordion-item " data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="17" data-item-id="17"><li class="wb-accordion-item " data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="18" data-item-id="18"><li class="wb-accordion-item " data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="19" data-item-id="19"><li class="wb-accordion-item " data-target="#a1989003543405ddc7c0bbe674d18f6f-list" data-slide-to="20" data-item-id="20"></div><div class="carousel-inner" role="listbox"><div class="item active"><div id="a19890035434068eecb50ed0f780c504" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354340771a4e80fb85890c6bb" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035434084bd4c23cd9d83b43bc" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543409745b15c9789ed34156" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Julius_Nyerere" title="Read more" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a86452e680a9d209ec90c05814a9e82d_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354490ee954679a10e47367df" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354560cbe241aa0129019e326" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Julius Kambarage Nyerere</p>

<p class="wb-stl-normal" style="text-align: center;">- Tanganyika.</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003544a005139693fa341172821" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354340ad837b0141e717e2e60" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354340bc44fd8573ab2dfa9f0" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035435008776e7de1c9f9aaa2c" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Abeid_Karume" target="_blank"><img loading="lazy" alt="" src="gallery_gen/53628c301827bcdf90467e47fb0295fd_300x300_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544a012673f0790c73b1b6ed" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354560d79512cc1793d721c03" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Abeid Amani Karume - Zanzibar</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354560e3e970e8e3742305217" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543501ed7dd198a81d174d66" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543502422ec6f9aacda917cb" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035435031b8a349d06251eecaf" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Jomo_Kenyatta" target="_blank"><img loading="lazy" alt="" src="gallery_gen/4240af0cc9e867792a97a4a870e58b18_300x200_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544a02839f7fbfa3c3e0ccbc" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354560fc73207cc7736b72b80" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Mzee Jomo Kenyatta</p>

<p class="wb-stl-custom4" style="text-align: center;">- Kenya</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545a0b13a96616af8b68f182" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354350482b0dbc05068ca2108" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354350576208d8289cf7ce356" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354350633d5d3ecaba3c57da7" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Milton_Obote" target="_blank"><img loading="lazy" alt="" src="gallery_gen/30c009dbc34db243f3bcf4a3732a0618_300x354_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544a03c8511bdee25fe905cb" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035456104119a2c67cad422cce" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Milton Obote</p>

<p class="wb-stl-custom4" style="text-align: center;">- Uganda</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545b0db13c34bd6748fd338c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543507b503d8a85ef28f771f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035435083a51bb76a97763b43c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035435096cdab1ead27429c16c" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Nelson_Mandela" target="_blank"><img loading="lazy" alt="" src="gallery_gen/c1032c63f0fb7d37d10eaa57e255d971_300x300_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544a04acabed9673036b45a3" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035456115e75ead4b992d4d392" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Nelson Mandela</p>

<p class="wb-stl-custom4" style="text-align: center;">- South Africa</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545c06bc7d7e67f69101f2c5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354350a726b67fe7c6c8f8a41" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354350b1b152178cdf6fae8b8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543600c93b669d27959e1ddd" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Kwame_Nkrumah" target="_blank"><img loading="lazy" alt="" src="gallery_gen/314d963a5d161c4b366ba9af914ab6cc_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544a0535bdd4b0d436997bbc" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035456124efe614a226bc437d1" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Kwame Nkrumah</p>

<p class="wb-stl-custom4" style="text-align: center;">- Ghana</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545c0e43753253bd8639d9f5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543601776f74827b71d30b4d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543602dd5c0c85f9cfb07ba1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354360356e6b3e0ad8e6d6259" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Robert_Mugabe" target="_blank"><img loading="lazy" alt="" src="gallery_gen/78ec9ad1a85de1c5931bafb55601100b_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544a06cf45c7eb8770ee015b" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545613c188f618753ecd77cb" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Robert Mugabe</p>

<p class="wb-stl-custom4" style="text-align: center;">- Zimbabwe</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545d079a8ce64a2e00bf1307" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543604b576d21f123b3abe60" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543605065e6853ba52781045" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543606c2bc6f824986bfb8f9" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmed_Ben_Bella" target="_blank"><img loading="lazy" alt="" src="gallery_gen/cea04620dd38546d6e4d97a8e2c11ab9_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544a070392f9aab1183fbb6a" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545700b24e9871c872fe89f0" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmed Ben Bella</p>

<p class="wb-stl-custom4" style="text-align: center;">- Algeria</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545d0ef23c78086cc307b303" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354360756671560f229635618" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354360820906e1ebcbccd4da0" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035436099aa1fa653e99502e63" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmed_Sékou_Touré" target="_blank"><img loading="lazy" alt="" src="gallery_gen/0924f22b791426459b1fceeea5d83d13_300x376_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544a08e22bfe924ed6088559" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035457010d307cbc3374777d26" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmed Sekour Toure</p>

<p class="wb-stl-custom4" style="text-align: center;">-Guinea</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545e05490421f090be859f6a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354360aa7ea71d6c19d8b57ae" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354360ba917d96d9b16c84519" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035437001f4ba1d184275931d4" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Agostinho_Neto" target="_blank"><img loading="lazy" alt="" src="gallery_gen/6a3850f6562e671cb5d12dc555d49b51_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544a09be25e462edbc37ab6b" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545702401c5294c658f0f5c7" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Agostinho Neto</p>

<p class="wb-stl-custom4" style="text-align: center;">- Angola</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545e0cc86e1370b90bd46969" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543701f0d8453d2bf74c8290" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543702ce7157f049c4dce4d0" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543703a328d8364d2959ac97" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Sam_Nujoma" target="_blank"><img loading="lazy" alt="" src="gallery_gen/1570396f1726775d1cce5c83b0706c7e_300x210_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544a0a88554bb6a4125a0d19" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545703c0fbbca7d8b864a5c2" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Sam Nujoma</p>

<p class="wb-stl-custom4" style="text-align: center;">- Namibia</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545f03689c1029952e88d9a9" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543704972f0f28ffb5cc1484" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543705ee81574840c69b496b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035437064988de68f8a9fc93af" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Samora_Machel" title="https://en.wikipedia.org/wiki/Samora_Machel" target="_blank"><img loading="lazy" alt="" src="gallery_gen/5372a2a280dd4375e74cdf264fc32a5b_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544a0b0b05cc655f1a550924" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035457042d3ff1f22ccb66aa99" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Samora Machel</p>

<p class="wb-stl-custom4" style="text-align: center;"> - Msumbiji</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545f0ace26b3cf3b70128507" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543707b0a88e8b7ccc9d66c9" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543708736ba9f8ff469f5a3f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035437092e299f3cda5c89bf4d" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Kenneth_Kaunda" target="_blank"><img loading="lazy" alt="" src="gallery_gen/550d22763a73fab3b73256541dddbc4b_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544a0cee32e280ad52d5894e" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354570580f637a37aa4c56cb5" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Kenneth Kaunda</p>

<p class="wb-stl-custom4" style="text-align: center;">- Zambia</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354600133fdea2274350454e3" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354370a7975d61237429417dc" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354370b0caa1fa11e0603d09a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354380085b0f2f99fa3f11527" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Patrice_Lumumba" target="_blank"><img loading="lazy" alt="" src="gallery_gen/14574c93b57ee80df1b44a961c8a11f8_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544a0dbfdbd07d57203beffe" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354570653e74e3e7e99f5338a" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Patrice Lumumba</p>

<p class="wb-stl-custom4" style="text-align: center;">- Congo (Zaire /DRC)</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354600842cb12b28907fd4cbd" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354380137eacb4f5444bc1bcc" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035438021eafd8e453f1c475d1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035438036a6a7be1d23fec2222" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Hastings_Banda" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a770b5a86758ef8c981bd2d8e0b76d18_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544b00df0f4fd8c827f86dae" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354570776e918cb52ffdfb112" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Hastings Kamuzu Banda</p>

<p class="wb-stl-custom4" style="text-align: center;">- Malawi</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a19890035461016c6f3a95b925b4565c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543804707d86a3c7e901d8b1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543805d01aff6b15f5ea19ad" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543806a414bb9bea90d98735" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://sw.wikipedia.org/wiki/Nnamdi_Azikiwe" target="_blank"><img loading="lazy" alt="" src="gallery_gen/933760d0723f699380102b2a69b12759_300x344_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544b0194950b03a8729a8ffb" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035457080bd0dce417e9b543ab" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Nnamdi Azikiwe</p>

<p class="wb-stl-custom4" style="text-align: center;">- Nigeria</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354610670f937c488668e9f61" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543807f88d15ae58a921dd94" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035438082c0aa28b5c53fdad2b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035438091561f63391cc24cf82" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Thomas_Sankara" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a6c8cd240f9f4d8d18fc4aa78f178e5f_300x168_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544b027296a12347026dc829" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545709a9070289bab39ca00f" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Thomas Sankara</p>

<p class="wb-stl-custom4" style="text-align: center;">- Burkina Fasor</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354610bf1c0e61b8efe1f94a8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354380a286cc14f1098f9264f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035439004265030d03857906a7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543901e5208b13526e33e230" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Muammar_Gaddafi" target="_blank"><img loading="lazy" alt="" src="gallery_gen/1db65078b522d721e9d5c6c5b5ecd98c_300x374_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544b03ad695d9d23f9bf5116" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354570a59505e9073734d90c6" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Muammari Gaddafi</p>

<p class="wb-stl-custom4" style="text-align: center;">- Libya</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a19890035461103aab1b018571cb69ce" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035439024ea8755856c0beef16" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035439030f17834f92c2a84e02" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543904bc1faaa0e792f24dd2" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Haile_Selassie" target="_blank"><img loading="lazy" alt="" src="gallery_gen/b6c71636a45fb72d58b79f9d573734ba_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544b04184d88635b173a8795" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354570bae51a869d20eccf176" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Emperor Haile Selassie I</p>

<p class="wb-stl-custom4" style="text-align: center;">- Ethiopia</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003546204c42fe957ad3e529a5d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543905bb590804d1e219676d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354390681952441eaa6b0959f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543907dc1e99190389e19ba5" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Léopold_Sédar_Senghor" target="_blank"><img loading="lazy" alt="" src="gallery_gen/052732d784d8347a79730116e3fd7d8c_300x170_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544b05c4fc8a90770db9d713" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354570c465e786c5c6d49523f" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Leopold Sedar Senghor </p>

<p class="wb-stl-custom4" style="text-align: center;">- Senegal</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a19890035462091c3ed65a34fb7853a1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354390847f7d27dd77ec03d64" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035439093031d7cf07f325a392" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354390a4ccd5a5fc42d6f0cd4" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmadou_Ahidjo" target="_blank"><img loading="lazy" alt="" src="gallery_gen/dbd7c2b0f071ed790fad39a580770b78_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544b06f911bcdf23bb61d18c" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354570d8a37cfa018eb6d96fe" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmadou Ahidjo</p>

<p class="wb-stl-custom4" style="text-align: center;">- Cameroon</p>
</div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div><div id="a1989003544b0872a798c128265438eb" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035419016d5cf8104985b89aa2" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541a033ca39c1e79f4ee075f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541a049b9cf5b18b5d2a3ad0" class="wb_element wb-accordion" data-plugin="Accordion" data-save-open-tab="true"><div class="wb-accordion-type-slider"><div id="a1989003541a049b9cf5b18b5d2a3ad0-list" class="carousel slide" data-ride="carousel" data-interval="5000"><div class="carousel-indicators"><li class="wb-accordion-item active" data-target="#a1989003541a049b9cf5b18b5d2a3ad0-list" data-slide-to="0" data-item-id="0"><li class="wb-accordion-item " data-target="#a1989003541a049b9cf5b18b5d2a3ad0-list" data-slide-to="1" data-item-id="1"><li class="wb-accordion-item " data-target="#a1989003541a049b9cf5b18b5d2a3ad0-list" data-slide-to="2" data-item-id="2"><li class="wb-accordion-item " data-target="#a1989003541a049b9cf5b18b5d2a3ad0-list" data-slide-to="3" data-item-id="3"><li class="wb-accordion-item " data-target="#a1989003541a049b9cf5b18b5d2a3ad0-list" data-slide-to="4" data-item-id="4"><li class="wb-accordion-item " data-target="#a1989003541a049b9cf5b18b5d2a3ad0-list" data-slide-to="5" data-item-id="5"><li class="wb-accordion-item " data-target="#a1989003541a049b9cf5b18b5d2a3ad0-list" data-slide-to="6" data-item-id="6"><li class="wb-accordion-item " data-target="#a1989003541a049b9cf5b18b5d2a3ad0-list" data-slide-to="7" data-item-id="7"></div><div class="carousel-inner" role="listbox"><div class="item active"><div id="a1989003541a0588fb0bbfcb4727b0b1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541a066ee8c42546c8acdf32" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541a07add0c82426d233e8a5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541a0865fc0aa66d408f0514" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Maria_Nyerere" target="_blank"><img loading="lazy" alt="" src="gallery_gen/4918c2486b617465c8d909cba95212bc_370x208_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354410bdb4302126f2d1a2810" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545000d71e8b3f964d3ca570" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Mama Maria Nyerere</p>

<p class="wb-stl-normal" style="text-align: center;">- Tanzania</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354410c4800868700f54471a7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541a09a014a1c3c0f4d955df" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541a0aeebefcfdd641762187" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541a0b9468183adcc8a97db1" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Winnie_Mandela" target="_blank"><img loading="lazy" alt="" src="gallery_gen/7d3ea66627f68b8539fa93451e7b2ee6_300x310_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354410d0527fcc4548e8fd681" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354500118a78db4298692c8dd" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Winnie Madikizela-Mandela</p>

<p class="wb-stl-normal" style="text-align: center;">South Africa</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354500240ee195b75b889c7a7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541b00ef5d6a5e5c05ca17a7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541b012cd2ea8cd8383739b8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541b0234f2dd11b652bf32da" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Bibi_Titi_Mohammed" target="_blank"><img loading="lazy" alt="" src="gallery_gen/9cc8778cc2d825fc90ccad3cc815f8b1_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544200f3df71bf4473450ae0" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035450039c3824588ca5b7062a" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Bibi Titi Mohamed</p>

<p class="wb-stl-custom4" style="text-align: center;">- Tanzania</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354590cc65e623a8736456f9d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541b033d89faedc86711842e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541b04e9710e45492369081e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541b051f59b172fbd5771486" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Funmilayo_Ransome-Kuti" target="_blank"><img loading="lazy" alt="" src="gallery_gen/0477196f0ab1dd3d3baec3b8091ced95_fit.jpg?ts=1785686359"></a></div></div></div><div id="a19890035442016b9be99f962364ecd1" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545004785ef0e79f06b67cad" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Funmilayo Ransome-Kuti</p>

<p class="wb-stl-custom4" style="text-align: center;">- Nigeria </p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545b055cbbb45b915c13040c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541b062829dd768c53f9a698" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541b071d72660dbdc5696aaa" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541b08069e73c68c113b71a8" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Josina_Machel" target="_blank"><img loading="lazy" alt="" src="gallery_gen/fc8dc6d71309eb953432a22de4bfde38_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544202245ac430cbf870e203" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545005b385ab592073761c57" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Josina Machel</p>

<p class="wb-stl-custom4" style="text-align: center;">- Mozambique </p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545c0003da3d8816382724d5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541b095b17382f98a4405571" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541b0a4443bbcdba2a135a7e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541b0b1453482ec5e536283a" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Graça_Machel" target="_blank"><img loading="lazy" alt="" src="gallery_gen/85395aad08a64d7f68860c24d2602bec_300x242_fit.png?ts=1785686359"></a></div></div></div><div id="a198900354420324989043497451beac" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545006bfb89a5d26dfd933ed" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Graça Machel</p>

<p class="wb-stl-custom4" style="text-align: center;">- Mozambique </p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545c09986f4e618344b12d02" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541c00372221bcda2bbf725d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541c01021966f83fbbe5e663" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541c0235db7ef644be1c73fa" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ngina_Kenyatta" target="_blank"><img loading="lazy" alt="" src="gallery_gen/5eace340d4b9a9b008fcd928433f4415_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544204d3102bbf0da7193e54" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545007fbb1cbb389a1a35c61" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Mama Ngina Kenyataa</p>

<p class="wb-stl-custom4" style="text-align: center;">- Kenya</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545d01660d26633281d2861f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541c03a02aab45e9768f4ea5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541c04bd2aa7c0492dab4de1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541c053c5ed2072c9b05b076" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Nzinga_of_Ndongo_and_Matamba" target="_blank"><img loading="lazy" alt="" src="gallery_gen/420270513dec75acd1b93455dc019dbb_300x266_fit.jpg?ts=1785686359"></a></div></div></div><div id="a19890035442056b618645a7aa463db7" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035450083e191733b992f09990" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Queen Nzinga (17C) </p>

<p class="wb-stl-custom4" style="text-align: center;">- Angola </p>
</div></div></div></div></div></div></div></div></div></div></div></div></div></div><div id="a1989003544101a9ac759e159cf575df" class="wb_element wb-anim-entry wb-anim wb-anim-fade-in-none wb-layout-element" data-plugin="LayoutElement" data-wb-anim-entry-time="0.6" data-wb-anim-entry-delay="0"><div class="wb_content wb-layout-vertical"><div id="a19890035419027b3fede3a1e180e3a7" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2">TUZO YA MAMA AFRIKA KWA UKOMBOZI NA URITHI</h2>
</div><div id="a198900354410252079178e0e1d18f51" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal"><strong>Orodha ya Wanawake Mashujaa wa Ukombozi wa Afrika</strong></p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal">Katika kuenzi mchango mkubwa wa wanawake katika harakati za ukombozi wa Bara la Afrika, tunatarajia kutoa Tuzo za Heshima maalum wakati wa Tamasha la JUKANYE INTERNATIONAL HISTORY FESTIVAL – 2026.</p>

<p class="wb-stl-normal">Tuzo hii itatambua na kuthamini juhudi, ujasiri, na urithi wa wanawake waliopigania uhuru na haki za watu wa Afrika katika nyanja mbalimbali za kisiasa, kijamii na kitamaduni.</p>

<p class="wb-stl-normal">Tuzo Maalum: TUZO YA MAMA AFRIKA KWA UKOMBOZI NA URITHI<br>
(Mama Afrika Award for Liberation and Legacy)</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal">Tuzo hii ni ishara ya heshima kwa wanawake waliobeba jukumu la kihistoria katika kujenga Afrika huru, yenye utu, mshikamano, na maendeleo endelevu.</p>
</div></div></div></div></div><div id="a1989003544b0913afcca0c3f77e1f03" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354390bb29f2a44de46ff2c63" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543a0033c2bea5cb0142e774" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom25">Kusherehekea Ukombozi wa Afrika, Urithi, Umoja na Kawaenzi Mashujaa wa Kweli na Wazalendo wa Afrika.</h2>

<h2 class="wb-stl-custom25"> </h2>
</div><div id="a1989003544b0a532bbade4ccda5f4f3" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal">Hawa viongozi – wanaume na wanawake wenye msimamo thabiti – hawakutafuta faida binafsi. Waliota juu ya heshima kwa watu wao, umoja kwa mataifa yao, na mustakabali unaoongozwa na Waafrika wenyewe. Kuanzia hekima ya Mwalimu Julius Kambarage Nyerere katika kuunganisha Tanzania, hadi wito wa ujasiri wa Kwame Nkrumah wa Uafrika Moja, kutoka kilio cha haki cha Patrice Lumumba hadi roho ya maridhiano ya Nelson Mandela – urithi wao unaendelea kuishi.</p>

<p class="wb-stl-normal">Katika Tamasha la Kimataifa la Historia la JUKANYE 2026, tunawaenzi mashujaa hawa. Kupitia usimulizi wa historia, maonesho, muziki, semina, na sherehe za kitamaduni, tunakumbuka na kusherehekea kujitoa kwao – tukikumbusha kizazi cha sasa na kuhamasisha kizazi kijacho kwa roho ya uzalendo.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>Majina yao yasikike.</strong></p>

<p class="wb-stl-normal"><strong>Hadithi zao ziinspire</strong>.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal" style="text-align: center;"><strong><em>Maono yao yaendelee kuishi.</em></strong></p>
</div></div></div></div></div></div></div></div></div><div id="a1989003545b0e6c51ff213da5d44de7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543a01bca9aec6ff540e9a37" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543a02c7cc01abd05e2ed193" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543a035122f19dea75a3e487" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543a048235975e8c4339a522" class="wb_element wb-accordion" data-plugin="Accordion"><div class="wb-accordion-type-slider"><div id="a1989003543a048235975e8c4339a522-list" class="carousel slide" data-ride="carousel" data-interval="5000"><div class="carousel-indicators"><li class="wb-accordion-item active" data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="0" data-item-id="0"><li class="wb-accordion-item " data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="1" data-item-id="1"><li class="wb-accordion-item " data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="2" data-item-id="2"><li class="wb-accordion-item " data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="3" data-item-id="3"><li class="wb-accordion-item " data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="4" data-item-id="4"><li class="wb-accordion-item " data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="5" data-item-id="5"><li class="wb-accordion-item " data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="6" data-item-id="6"><li class="wb-accordion-item " data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="7" data-item-id="7"><li class="wb-accordion-item " data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="8" data-item-id="8"><li class="wb-accordion-item " data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="9" data-item-id="9"><li class="wb-accordion-item " data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="10" data-item-id="10"><li class="wb-accordion-item " data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="11" data-item-id="11"><li class="wb-accordion-item " data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="12" data-item-id="12"><li class="wb-accordion-item " data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="13" data-item-id="13"><li class="wb-accordion-item " data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="14" data-item-id="14"><li class="wb-accordion-item " data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="15" data-item-id="15"><li class="wb-accordion-item " data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="16" data-item-id="16"><li class="wb-accordion-item " data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="17" data-item-id="17"><li class="wb-accordion-item " data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="18" data-item-id="18"><li class="wb-accordion-item " data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="19" data-item-id="19"><li class="wb-accordion-item " data-target="#a1989003543a048235975e8c4339a522-list" data-slide-to="20" data-item-id="20"></div><div class="carousel-inner" role="listbox"><div class="item active"><div id="a1989003543a05767f68c5b71634fb12" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543a06f77f683b473205bfcf" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543a0758cf7d2e993240a90f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543a08fe4b1e10ce5c806302" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Julius_Nyerere" title="Read more" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a86452e680a9d209ec90c05814a9e82d_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544b0b2c7f04764946e3b6e4" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354570f19a683265885a835e9" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Julius Kambarage Nyerere</p>

<p class="wb-stl-normal" style="text-align: center;">- Tanganyika.</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003544b0cb51731057a723ed6ea" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543a099a363afbfa97d43a13" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543a0a44f6ea1f495b0cb9fe" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543a0b8ef0e92f1380bba933" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Abeid_Karume" target="_blank"><img loading="lazy" alt="" src="gallery_gen/53628c301827bcdf90467e47fb0295fd_300x300_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544b0d3f1e6fd4346eeb6c5d" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035457106aaf043a40be7dc690" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Abeid Amani Karume - Zanzibar</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545711d3333f414708393c88" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543a0cc55fb34c7da5ee2062" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543a0de6fa2375d2c25847d5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543b00d3eac406b45c1ffb41" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Jomo_Kenyatta" target="_blank"><img loading="lazy" alt="" src="gallery_gen/4240af0cc9e867792a97a4a870e58b18_300x200_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544c00bb3ad3a3383ad765fd" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354580007fcd55811131a0c58" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Mzee Jomo Kenyatta</p>

<p class="wb-stl-custom4" style="text-align: center;">- Kenya</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545b0069804a3f49abdfec90" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543b018e24bc8d48b2e0e91b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543b0211d2886e6e42887c6b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543b0302c6a39eda00ee4c3f" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Milton_Obote" target="_blank"><img loading="lazy" alt="" src="gallery_gen/30c009dbc34db243f3bcf4a3732a0618_300x354_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544c01793cc211d8f3f5d167" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545801e8947128bff0185567" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Milton Obote</p>

<p class="wb-stl-custom4" style="text-align: center;">- Uganda</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545b0f0aa820c7b2adfbb4a5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543b04a883e5d1a441a45545" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543b05f8f58251b0a3d45491" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543b06aa3e3c294fcb53b09b" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Nelson_Mandela" target="_blank"><img loading="lazy" alt="" src="gallery_gen/c1032c63f0fb7d37d10eaa57e255d971_300x300_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544c021336b0940b31bde585" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545802d9a3d51a0f3f09bb0f" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Nelson Mandela</p>

<p class="wb-stl-custom4" style="text-align: center;">- South Africa</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545c0749f033c74e9297080e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543b07e11708eaedf5946ba6" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543b0850803e6db357f0f7ae" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543b09b39f586f9c468deb32" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Kwame_Nkrumah" target="_blank"><img loading="lazy" alt="" src="gallery_gen/314d963a5d161c4b366ba9af914ab6cc_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544c03b994ed2c0b5d9f86be" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035458034330b767f50059fb15" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Kwame Nkrumah</p>

<p class="wb-stl-custom4" style="text-align: center;">- Ghana</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545d007b033524d7af8c4c51" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543b0ace5e98714b288def60" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543b0bb3144131e34ec7cb6b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543c003dfab11ffb911c0dde" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Robert_Mugabe" target="_blank"><img loading="lazy" alt="" src="gallery_gen/78ec9ad1a85de1c5931bafb55601100b_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544c0479b2f9a0df8d36837f" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545804c91efb736c886ceb8c" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Robert Mugabe</p>

<p class="wb-stl-custom4" style="text-align: center;">- Zimbabwe</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545d087cc340aa27a9b56ad3" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543c012887ceb3c8a880c3cb" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543c026b6d446604744d7db0" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543c03f3bda9fa44f5d20b3e" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmed_Ben_Bella" target="_blank"><img loading="lazy" alt="" src="gallery_gen/cea04620dd38546d6e4d97a8e2c11ab9_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544c05fb2f8b9ccaf65feb10" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545805348395ee452aaba521" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmed Ben Bella</p>

<p class="wb-stl-custom4" style="text-align: center;">- Algeria</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545d0f86cb5be766f0a74f73" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543c049a4da777c9f22c5cf6" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543c05792ee2b53e504290d9" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543c06addd8311f7529ab729" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmed_Sékou_Touré" target="_blank"><img loading="lazy" alt="" src="gallery_gen/0924f22b791426459b1fceeea5d83d13_300x376_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544c065a30517d8a17e7c068" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354580673448fad7602996020" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmed Sekour Toure</p>

<p class="wb-stl-custom4" style="text-align: center;">-Guinea</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545e0626a02cd738dbc18a12" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543c078f3e6478aea04ed15e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543c08c2e81502ae400062e2" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543c094e74d289d436f914d2" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Agostinho_Neto" target="_blank"><img loading="lazy" alt="" src="gallery_gen/6a3850f6562e671cb5d12dc555d49b51_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544c0713a5c633141797e38d" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354580730a20c025b6ce1095d" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Agostinho Neto</p>

<p class="wb-stl-custom4" style="text-align: center;">- Angola</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545e0dc0d0e3fe0238390f26" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543c0aa80adc9fe243a3443e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543c0beab3155ebcb4847ed6" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543c0ce0cb60c6b19d2f62f2" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Sam_Nujoma" target="_blank"><img loading="lazy" alt="" src="gallery_gen/1570396f1726775d1cce5c83b0706c7e_300x210_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544c088ca487fc1bce70c7af" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545808104dd9fd06f09935d8" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Sam Nujoma</p>

<p class="wb-stl-custom4" style="text-align: center;">- Namibia</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545f04e1c1b960b857b82cea" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543d00dadf108ecfb7f89f05" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543d0121e14cbb04ee13a9c7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543d02d0e222468f152b6c38" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Samora_Machel" title="https://en.wikipedia.org/wiki/Samora_Machel" target="_blank"><img loading="lazy" alt="" src="gallery_gen/5372a2a280dd4375e74cdf264fc32a5b_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544c0935215b12bbcae2fcbb" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545809f130c3a981215f8dce" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Samora Machel</p>

<p class="wb-stl-custom4" style="text-align: center;"> - Msumbiji</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545f0b20054ee3b28833df1d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543d03d5d1df1535b7c9aaba" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543d04401b6653955cdc0836" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543d0500459f9becd44923ce" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Kenneth_Kaunda" target="_blank"><img loading="lazy" alt="" src="gallery_gen/550d22763a73fab3b73256541dddbc4b_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544c0a80d22efe4aea776eb6" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354580ad9321c37ec4800f373" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Kenneth Kaunda</p>

<p class="wb-stl-custom4" style="text-align: center;">- Zambia</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003546002c83a6689263decfc15" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543d067f182f940e4f4e7797" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543d07846fb90f6e6050f0e4" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543d08e152930348715f3105" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Patrice_Lumumba" target="_blank"><img loading="lazy" alt="" src="gallery_gen/14574c93b57ee80df1b44a961c8a11f8_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544d007ad33856d64aac279e" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354580b9bfe836ad1858912de" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Patrice Lumumba</p>

<p class="wb-stl-custom4" style="text-align: center;">- Congo (Zaire /DRC)</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003546009800e674623a6a8dbae" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543d09ad9f1e0843f70d4b79" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543d0aeebac2ff668a68c93d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543d0b48a75cf37f9546e4b5" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Hastings_Banda" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a770b5a86758ef8c981bd2d8e0b76d18_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544d01078e1a71bb44495cfd" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354580ca891fc37052287520f" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Hastings Kamuzu Banda</p>

<p class="wb-stl-custom4" style="text-align: center;">- Malawi</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003546102e97b0108732cb78323" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543d0c903b9e8b21ae9379b6" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543e00f550948e26760fafa5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543e01d28b69fb5753e3b5f0" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://sw.wikipedia.org/wiki/Nnamdi_Azikiwe" target="_blank"><img loading="lazy" alt="" src="gallery_gen/933760d0723f699380102b2a69b12759_300x344_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544d02d0f9082b6514ffecd8" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354580d747dbe72e8460e3052" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Nnamdi Azikiwe</p>

<p class="wb-stl-custom4" style="text-align: center;">- Nigeria</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003546107cbafc8b0be6b496574" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543e0202aed77101f34887e4" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543e038f41969dfe6163de1b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543e04e91d5949f0be56f439" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Thomas_Sankara" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a6c8cd240f9f4d8d18fc4aa78f178e5f_300x168_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544d038a1c3852222aee59e1" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354580e8109e6b5d1a8eaf67a" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Thomas Sankara</p>

<p class="wb-stl-custom4" style="text-align: center;">- Burkina Fasor</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354610c95433e3945e7acd0c2" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543e050b5c166fc6a2733d66" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543e06e1f503e611398f6725" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543e07f7e84c51e576b1b2b2" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Muammar_Gaddafi" target="_blank"><img loading="lazy" alt="" src="gallery_gen/1db65078b522d721e9d5c6c5b5ecd98c_300x374_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544d04ebb6750176b60142fb" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354580ffb781fc779612701f9" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Muammari Gaddafi</p>

<p class="wb-stl-custom4" style="text-align: center;">- Libya</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a19890035462009eedbd647079ba091a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543e080d17046d9f902f69b3" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543e0936bf67e1e4709c88df" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543e0a8e45a740545f0406e2" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Haile_Selassie" target="_blank"><img loading="lazy" alt="" src="gallery_gen/b6c71636a45fb72d58b79f9d573734ba_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544d05a5f7b7fae2dd06bdb4" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545810e457274aecdb3753bb" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Emperor Haile Selassie I</p>

<p class="wb-stl-custom4" style="text-align: center;">- Ethiopia</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003546205ba9c3b012b77d92cc1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543e0b7df26bfe797b27cd23" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543e0cc72a2249ba92de8243" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543f00cd85117f4fbb1b26be" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Léopold_Sédar_Senghor" target="_blank"><img loading="lazy" alt="" src="gallery_gen/052732d784d8347a79730116e3fd7d8c_300x170_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544d0638436ea708b7caed08" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545811d0b6456bd44f21bc0c" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Leopold Sedar Senghor </p>

<p class="wb-stl-custom4" style="text-align: center;">- Senegal</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354620ad89236b0f9521b9fa9" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543f0171b35e44ee1046f2ae" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543f025f2a178e0814309b63" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543f038e4c1ac28b1f511272" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmadou_Ahidjo" target="_blank"><img loading="lazy" alt="" src="gallery_gen/dbd7c2b0f071ed790fad39a580770b78_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544d07db3d34d05d67fe460f" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354581280268ee333a6c8b1b5" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmadou Ahidjo</p>

<p class="wb-stl-custom4" style="text-align: center;">- Cameroon</p>
</div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div><div id="a1989003544d09043e9ba8905570fdee" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543f04d5396eb6b29ab234e6" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543f0504dfec810a6bda938b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543f066cea410cb63939e698" class="wb_element wb-accordion" data-plugin="Accordion"><div class="wb-accordion-type-slider"><div id="a1989003543f066cea410cb63939e698-list" class="carousel slide" data-ride="carousel" data-interval="5000"><div class="carousel-indicators"><li class="wb-accordion-item " data-target="#a1989003543f066cea410cb63939e698-list" data-slide-to="0" data-item-id="0"><li class="wb-accordion-item " data-target="#a1989003543f066cea410cb63939e698-list" data-slide-to="1" data-item-id="1"><li class="wb-accordion-item active" data-target="#a1989003543f066cea410cb63939e698-list" data-slide-to="2" data-item-id="2"></div><div class="carousel-inner" role="listbox"><div class="item "><div id="a1989003543f07b5da9b93e1619e37f7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543f080a2eb481024b761718" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543f09e60bdd8741e6ece2e5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543f0a9d4841e5a8a17a5bac" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Maria_Nyerere" target="_blank"><img loading="lazy" alt="" src="gallery_gen/dbc6b6de3f3283a86dae0cc34fce724f_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544e0003c6a323c5a51b248f" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545901b4baaa0991bd7a44dc" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Askofu Desmond Tutu</p>

<p class="wb-stl-normal" style="text-align: center;">- Africa Kusini</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003544e01c72dcb8f9dc0edd443" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543f0b9b933abef3c8b8db8a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543f0c652e7b0b1865d5724d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543f0d825ea1957c1217dfa9" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Winnie_Mandela" target="_blank"><img loading="lazy" alt="" src="gallery_gen/fdb2153969a286286297c755142edc9f_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544e024f7d9cb380d1537066" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545902ea3b231110c6ee6b6f" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Abuna Theophilos</p>

<p class="wb-stl-normal" style="text-align: center;"> - Ethiopia</p>
</div></div></div></div></div></div></div></div><div class="item active"><div id="a1989003545903eb5440e470e33ffddb" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035440007ec1da2c9244d5bcd7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354400123fc46fcd152c1422e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354400274d517dda98410d4a6" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Bibi_Titi_Mohammed" target="_blank"><img loading="lazy" alt="" src="gallery_gen/61b02c9b09b9e7066adafafe18e987e4_300x270_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544e03177562b3f64e4f9a88" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545904e0de4081b4c2f15beb" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Bishop Abel Muzorewa</p>

<p class="wb-stl-custom4" style="text-align: center;">- Zimbabwe</p>
</div></div></div></div></div></div></div></div></div></div></div></div></div></div><div id="a1989003544e04d9bcd0dfcd6da775cb" class="wb_element wb-anim-entry wb-anim wb-anim-fade-in-none wb-layout-element" data-plugin="LayoutElement" data-wb-anim-entry-time="0.6" data-wb-anim-entry-delay="0"><div class="wb_content wb-layout-vertical"><div id="a1989003544003c6ad869e840f9c6a76" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2">Tuzo Ya Hekima Ya Ukombozi</h2>
</div><div id="a1989003544e05019f57d0d32e0ccc13" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;"><strong>Tuzo ya Hekima ya Ukombozi</strong></p>

<p class="wb-stl-normal" style="text-align: center;"><br>
Tuzo hii hutolewa kwa viongozi wa kidini waliochangia kwa kiasi kikubwa katika harakati za ukombozi wa Afrika, kwa kutumia maadili, imani, na ushawishi wao kuhamasisha mapambano dhidi ya ukoloni, unyanyasaji, na utumwa wa kiakili barani Afrika.</p>
</div></div></div></div></div><div id="a1989003544e06b5c52251f3bc8348b8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003544004ad4627b4d51ab07734" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035440050f89b85b0cdc7a2454" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom25">Kusherehekea Ukombozi wa Afrika, Urithi, Umoja na Kawaenzi Mashujaa wa Kweli na Wazalendo wa Afrika.</h2>

<h2 class="wb-stl-custom25"> </h2>
</div><div id="a1989003544e0717fa22574d54fd5776" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal">Hawa viongozi – wanaume na wanawake wenye msimamo thabiti – hawakutafuta faida binafsi. Waliota juu ya heshima kwa watu wao, umoja kwa mataifa yao, na mustakabali unaoongozwa na Waafrika wenyewe. Kuanzia hekima ya Mwalimu Julius Kambarage Nyerere katika kuunganisha Tanzania, hadi wito wa ujasiri wa Kwame Nkrumah wa Uafrika Moja, kutoka kilio cha haki cha Patrice Lumumba hadi roho ya maridhiano ya Nelson Mandela – urithi wao unaendelea kuishi.</p>

<p class="wb-stl-normal">Katika Tamasha la Kimataifa la Historia la JUKANYE 2026, tunawaenzi mashujaa hawa. Kupitia usimulizi wa historia, maonesho, muziki, semina, na sherehe za kitamaduni, tunakumbuka na kusherehekea kujitoa kwao – tukikumbusha kizazi cha sasa na kuhamasisha kizazi kijacho kwa roho ya uzalendo.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>Majina yao yasikike.</strong></p>

<p class="wb-stl-normal"><strong>Hadithi zao ziinspire</strong>.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal" style="text-align: center;"><strong><em>Maono yao yaendelee kuishi.</em></strong></p>
</div></div></div></div></div></div></div></div></div><div id="a1989003545c01af962ee7398004d1dd" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"></div></div><div id="a1989003545c089f81d8677f2ca057b3" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035419004f08d2178f6cd5d143" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"></div></div></div></div><div id="a1989003545d03a111eb7766139b65ed" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035422038f8cecdbf40d4f1221" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035422043d4032d8a86e6a1627" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003545b09b670602589d69b410c" class="wb_element wb-iframe-video-auto" data-plugin="Youtube"><iframe title="YouTube video player" class="youtube-player" allowfullscreen="" data-defer-load="Youtube" data-src="//www.youtube.com/embed/pyRooTjKDLk?controls=1" frameborder="0"></iframe></div></div></div></div></div><div id="a1989003544500978f7a2335ac332688" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542208b0504003eed9a0a1f8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"></div></div></div></div></div></div><div id="a1989003545d0bab6fe6f61848041cee" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035428048bfc7c1534fa8d21a2" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542805477f8235812c50337c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542806dc14a462ab57853783" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035428075322785c224f5392ba" class="wb_element wb-accordion" data-plugin="Accordion"><div class="wb-accordion-type-slider"><div id="a19890035428075322785c224f5392ba-list" class="carousel slide" data-ride="carousel" data-interval="5000"><div class="carousel-indicators"><li class="wb-accordion-item active" data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="0" data-item-id="0"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="1" data-item-id="1"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="2" data-item-id="2"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="3" data-item-id="3"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="4" data-item-id="4"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="5" data-item-id="5"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="6" data-item-id="6"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="7" data-item-id="7"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="8" data-item-id="8"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="9" data-item-id="9"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="10" data-item-id="10"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="11" data-item-id="11"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="12" data-item-id="12"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="13" data-item-id="13"><li class="wb-accordion-item " data-target="#a19890035428075322785c224f5392ba-list" data-slide-to="14" data-item-id="14"></div><div class="carousel-inner" role="listbox"><div class="item active"><div id="a19890035428081fb8b717be95a7a1b9" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542809f5f42930283dee48a4" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354280a3bad875d4d18274010" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542900930620974e7f35a279" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Julius_Nyerere" title="Read more" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a68ce73e635caa688cdf78fa80889a33_300x168_fit.png?ts=1785686359"></a></div></div></div><div id="a1989003544700a2ddc446c41d8cb685" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545401167549c76fe19704d3" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Samia Suluhu Hassan</p>

<p class="wb-stl-normal" style="text-align: center;">- Tanzania.</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003544701c155bcf0cbe47c68c8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542901f351753f1657997a14" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542902fc4dae6bb46397e2f1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542903d4b5d7a53512cfa2fc" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Abeid_Karume" target="_blank"><img loading="lazy" alt="" src="gallery_gen/ad88d2489309cdcd795f110cd2f82277_300x400_fit.jpg?ts=1785686359"></a></div></div></div><div id="a19890035447025a5093d6046de5d988" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Abeid Amani Karume - Zanzibar</p>
</div><div id="a1989003545402e64fc4f5375dd165a1" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545403dd95ebcce1f0bab952" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354290445a3e79b5276920420" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542905c1c853dfaec11468d9" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542906fd95e231234c46c9d2" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Jomo_Kenyatta" target="_blank"><img loading="lazy" alt="" src="gallery_gen/4240af0cc9e867792a97a4a870e58b18_300x200_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544703d7d7270215474e4425" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Mzee Jomo Kenyatta</p>

<p class="wb-stl-custom4" style="text-align: center;">- Kenya</p>
</div><div id="a1989003545404d4d49619d0292681f1" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545a0765deceb836e2c3a49c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354290785b3f63a7b64fb7b34" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354290855891556134885bb9d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542909ced14f59a17dd40ac0" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Milton_Obote" target="_blank"><img loading="lazy" alt="" src="gallery_gen/30c009dbc34db243f3bcf4a3732a0618_300x354_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354470469f1b396df714ed37d" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Milton Obote</p>

<p class="wb-stl-custom4" style="text-align: center;">- Uganda</p>
</div><div id="a19890035454056a388d339aad693852" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545b0b62e0919d0920a56dff" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354290aded159ac17751cf1b0" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354290b339c8b37c376f04f48" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354290c07485573b9e2767425" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Nelson_Mandela" target="_blank"><img loading="lazy" alt="" src="gallery_gen/c1032c63f0fb7d37d10eaa57e255d971_300x300_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544705696277686149020abf" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Nelson Mandela</p>

<p class="wb-stl-custom4" style="text-align: center;">- South Africa</p>
</div><div id="a1989003545406b175214c12fa69b2a0" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545c04e36f7a9bb16352c548" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354290d6de6302cf8d291ef30" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542a0048ca9f4b91b0d0bae0" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542a01421937b2ff638c2dab" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Kwame_Nkrumah" target="_blank"><img loading="lazy" alt="" src="gallery_gen/314d963a5d161c4b366ba9af914ab6cc_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544706e734d689410c624948" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Kwame Nkrumah</p>

<p class="wb-stl-custom4" style="text-align: center;">- Ghana</p>
</div><div id="a19890035454077661df585056d2a5f8" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545c0c8de0f6df2a5c0b567f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542a024069e4a487a8be751c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542a03b11dd995c34577935a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542a04690951c1a1003c4ce2" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Robert_Mugabe" target="_blank"><img loading="lazy" alt="" src="gallery_gen/78ec9ad1a85de1c5931bafb55601100b_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544707adcb5bc7e200c8d196" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Robert Mugabe</p>

<p class="wb-stl-custom4" style="text-align: center;">- Zimbabwe</p>
</div><div id="a1989003545408913b0212a7c65a36f2" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545d05fec0817f76d25db894" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542a05707dc548c459d4e10e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542a06f04e22639ecbc9933a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542a07006405ad6a25cc596f" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmed_Ben_Bella" target="_blank"><img loading="lazy" alt="" src="gallery_gen/cea04620dd38546d6e4d97a8e2c11ab9_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354470805bb3537ba4cb264ff" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmed Ben Bella</p>

<p class="wb-stl-custom4" style="text-align: center;">- Algeria</p>
</div><div id="a1989003545409448b7e934d57e94f43" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545d0c5f37b34d933b96f77f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542a08297c9da6661ca5c66d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542a095edca55f1107c738ca" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542a0ade6d8bf0a9e318bbae" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmed_Sékou_Touré" target="_blank"><img loading="lazy" alt="" src="gallery_gen/0924f22b791426459b1fceeea5d83d13_300x376_fit.jpg?ts=1785686359"></a></div></div></div><div id="a19890035447096fe5de7a9c9b4fb10f" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmed Sekour Toure</p>

<p class="wb-stl-custom4" style="text-align: center;">-Guinea</p>
</div><div id="a198900354540a3fbcafd1ecbb560f86" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545e02eed6def71726e9c76e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542a0b1e2b889faf12218428" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542a0ce9b69f780c53e28d3c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542b003a10cf5030b07e4752" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Agostinho Neto</p>

<p class="wb-stl-custom4" style="text-align: center;">- Angola</p>
</div><div id="a198900354470a6a54aa8ff6be6f5090" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Agostinho_Neto" target="_blank"><img loading="lazy" alt="" src="gallery_gen/6a3850f6562e671cb5d12dc555d49b51_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354540b37ccfd9ff4030120f1" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545e0a93dec98eda28ee67ad" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542b01f6afa993200aa4e505" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542b0278930b139486fe7629" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542b03ecd1a521d5492295c2" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Sam Nujoma</p>

<p class="wb-stl-custom4" style="text-align: center;">- Namibia</p>
</div><div id="a198900354470b032fe990c4459bf3c4" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Sam_Nujoma" target="_blank"><img loading="lazy" alt="" src="gallery_gen/1570396f1726775d1cce5c83b0706c7e_300x210_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354540cd04f106de4a48dd1f3" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545f01abd6549aab79dead68" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542b04fcdbf0025728dbf1df" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542b05cceea49ea27264e446" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542b0617bf462389f21c69d8" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Samora_Machel" title="https://en.wikipedia.org/wiki/Samora_Machel" target="_blank"><img loading="lazy" alt="" src="gallery_gen/5372a2a280dd4375e74cdf264fc32a5b_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354470c977ea7c77d861c83fa" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Samora Machel</p>

<p class="wb-stl-custom4" style="text-align: center;"> - Msumbiji</p>
</div><div id="a198900354540d217edd29b5113b094b" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545f08915273ca067225cf79" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542b07ce073f9ed9af39b78c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542b08620e565514b6081c1f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542b09ca71dbba9c002597ab" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Kenneth_Kaunda" target="_blank"><img loading="lazy" alt="" src="gallery_gen/550d22763a73fab3b73256541dddbc4b_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354470da6748cc31109ea24b2" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Kenneth Kaunda</p>

<p class="wb-stl-custom4" style="text-align: center;">- Zambia</p>
</div><div id="a198900354540e9c2b47777d7627b8df" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545f0fc540ae214eb2c474c2" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542b0a2dc42d75e3ac7577fb" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542b0b77c2642fc7d5a8eaaf" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542b0c2b67ce831acc17d70f" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Patrice_Lumumba" target="_blank"><img loading="lazy" alt="" src="gallery_gen/14574c93b57ee80df1b44a961c8a11f8_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354470e83e707a3273c5085a9" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Patrice Lumumba</p>

<p class="wb-stl-custom4" style="text-align: center;">- Congo (Zaire /DRC)</p>
</div><div id="a1989003545500c1ec3259bec5824133" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div><div class="item "><div id="a19890035460066dea9e4a3726737caa" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542c00d20481eabf2246b708" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542c01cd1b13f4223bce31a4" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542c023f9de1d3aaaac568fe" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Hastings_Banda" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a770b5a86758ef8c981bd2d8e0b76d18_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354470f62599f18b0a07d3901" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Hastings Kamuzu Banda</p>

<p class="wb-stl-custom4" style="text-align: center;">- Malawi</p>
</div><div id="a19890035455013672b49f9f0098c791" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div><div id="a1989003544711a12c6d363daee34771" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542c038fe8d83562ae2f3884" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542c046a4c900b1ccb2e1d74" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542c05d662333536359af996" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom25"> </h2>

<h2 class="wb-stl-custom25">Tuzo ya Heshima ya Jukanye kwa Marais wa Sasa </h2>
</div><div id="a19890035448008fbac1b7f71cd6fa58" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal">"Kuwatunuku viongozi wenye maono yanayochochea maendeleo, kulinda uhuru wa taifa, na kuendeleza utawala wa kidemokrasia; kusimamia dhamira thabiti ya ukombozi wa Afrika, umoja, na mabadiliko ya kijamii na kiuchumi, pamoja na kuhamasisha uzalendo kwa vizazi vya sasa. Kupitia ujasiri na maono, wanatunza heshima na kuimarisha ari ya Umoja wa Afrika.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal" style="text-align: center;"><em><strong>Uongozi wao bora na urithi wao wa kudumu viendelezwe na kuenziwe."</strong></em></p>
</div></div></div></div></div></div></div></div></div><div id="a1989003545e034d1ccb3b5a1cf7fae6" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542c063d8aa542a88bd7cc5b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542c0773cc9bd1dce9692d67" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542c08cc958232c186b77e5d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542c09f519510e17fbfb618e" class="wb_element wb-accordion" data-plugin="Accordion"><div class="wb-accordion-type-slider"><div id="a1989003542c09f519510e17fbfb618e-list" class="carousel slide" data-ride="carousel" data-interval="5000"><div class="carousel-indicators"><li class="wb-accordion-item active" data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="0" data-item-id="0"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="1" data-item-id="1"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="2" data-item-id="2"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="3" data-item-id="3"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="4" data-item-id="4"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="5" data-item-id="5"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="6" data-item-id="6"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="7" data-item-id="7"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="8" data-item-id="8"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="9" data-item-id="9"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="10" data-item-id="10"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="11" data-item-id="11"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="12" data-item-id="12"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="13" data-item-id="13"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="14" data-item-id="14"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="15" data-item-id="15"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="16" data-item-id="16"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="17" data-item-id="17"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="18" data-item-id="18"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="19" data-item-id="19"><li class="wb-accordion-item " data-target="#a1989003542c09f519510e17fbfb618e-list" data-slide-to="20" data-item-id="20"></div><div class="carousel-inner" role="listbox"><div class="item active"><div id="a1989003542c0a648b05dc1165b1e165" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542c0b0743bc392f69b5b72a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542c0ca7b49d1926ca74a18a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542d001f6af5ebab663fcaf9" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Julius_Nyerere" title="Read more" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a86452e680a9d209ec90c05814a9e82d_fit.jpg?ts=1785686359"></a></div></div></div><div id="a19890035448017dccb1e99d22e28c5c" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035455036f96495d75d0864c76" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Julius Kambarage Nyerere</p>

<p class="wb-stl-normal" style="text-align: center;">- Tanganyika.</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a19890035448026527e3c008808b5a7a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542d01b72655f5134a13cc3f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542d024a1f70b793b7f5bbf6" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542d036bf376d356828fc46a" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Abeid_Karume" target="_blank"><img loading="lazy" alt="" src="gallery_gen/53628c301827bcdf90467e47fb0295fd_300x300_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354480343945b6843750beffb" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035455042862d8eab91523155d" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;">Abeid Amani Karume - Zanzibar</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a19890035455059fedd514c7532380e5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542d041308f10278436560b8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542d054f564f9e7ba5e443b4" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542d06d482cac9ce59fca2e2" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Jomo_Kenyatta" target="_blank"><img loading="lazy" alt="" src="gallery_gen/4240af0cc9e867792a97a4a870e58b18_300x200_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544804f203fdb9c0706fc7eb" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545506f26e87f3259f1c7da2" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Mzee Jomo Kenyatta</p>

<p class="wb-stl-custom4" style="text-align: center;">- Kenya</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545a09f2193f55bdd6c6c4ac" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542d076b4aeeb8580bdd7db9" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542d081d21b908ef8eb99cb8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542d092fb573f2c3b9e9e0c2" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Milton_Obote" target="_blank"><img loading="lazy" alt="" src="gallery_gen/30c009dbc34db243f3bcf4a3732a0618_300x354_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544805da7a0398da3b1a7b75" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035455079a0abe2aeead8f16fa" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Milton Obote</p>

<p class="wb-stl-custom4" style="text-align: center;">- Uganda</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545b0ce5ef9214f95b4e887f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542d0a3cc26a2bd64f2090a9" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542e00477d58caa84641932d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542e01d2e0536a0c25f6c4af" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Nelson_Mandela" target="_blank"><img loading="lazy" alt="" src="gallery_gen/c1032c63f0fb7d37d10eaa57e255d971_300x300_fit.jpg?ts=1785686359"></a></div></div></div><div id="a19890035448064a179b7545747b7f01" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354550868cc89db810b58b899" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Nelson Mandela</p>

<p class="wb-stl-custom4" style="text-align: center;">- South Africa</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545c0522c6d595e44dbc29ab" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542e021f910378f471251c21" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542e03289ef6daa314a38492" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542e04eab82725b01101db9b" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Kwame_Nkrumah" target="_blank"><img loading="lazy" alt="" src="gallery_gen/314d963a5d161c4b366ba9af914ab6cc_fit.jpg?ts=1785686359"></a></div></div></div><div id="a19890035448073595a8b900707f3f1c" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035455098b66c1178afa6040cf" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Kwame Nkrumah</p>

<p class="wb-stl-custom4" style="text-align: center;">- Ghana</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545c0d1458d91136ca596524" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542e050beac8c80c3fc1e492" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542e067851a91ec0695dfa89" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542f002496891d4a1b12f51d" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Robert_Mugabe" target="_blank"><img loading="lazy" alt="" src="gallery_gen/78ec9ad1a85de1c5931bafb55601100b_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544808ff2785c60be97b099b" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354550afc8e4b1e7165e4cc4d" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Robert Mugabe</p>

<p class="wb-stl-custom4" style="text-align: center;">- Zimbabwe</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545d06dd625444402d15395f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542f01cc2883a7b1c1fef4b7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542f026d36bc7b0c771b61e8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542f03e2108f53acef550d81" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmed_Ben_Bella" target="_blank"><img loading="lazy" alt="" src="gallery_gen/cea04620dd38546d6e4d97a8e2c11ab9_fit.jpg?ts=1785686359"></a></div></div></div><div id="a19890035448099f71514bc148a5795a" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354550bcc1b0cb6a023668d10" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmed Ben Bella</p>

<p class="wb-stl-custom4" style="text-align: center;">- Algeria</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545d0d6bda6a2417c6504bf2" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542f0438665d925fb3c3d681" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003542f05e6deac678b28a3d6f7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542f06b88bd4259c5f256156" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmed_Sékou_Touré" target="_blank"><img loading="lazy" alt="" src="gallery_gen/0924f22b791426459b1fceeea5d83d13_300x376_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354480a2eceae40104af47d86" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354550cd4293b7be41dfac6e9" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmed Sekour Toure</p>

<p class="wb-stl-custom4" style="text-align: center;">-Guinea</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545e04f98070989830dce0db" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003542f07075c0e08626cf3702d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543000695212359794af7b3d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543001878769bfc092c531a1" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Agostinho_Neto" target="_blank"><img loading="lazy" alt="" src="gallery_gen/6a3850f6562e671cb5d12dc555d49b51_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354480bf9bbfd8e9b20a06cb5" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354550d71db77a8fcf7056669" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Agostinho Neto</p>

<p class="wb-stl-custom4" style="text-align: center;">- Angola</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545e0b8f3443b9f988b27ab8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543002283e28b2925a9a53bb" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354300399227a44211c4f3cdb" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543004d8eae41f8e3b9a6e08" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Sam_Nujoma" target="_blank"><img loading="lazy" alt="" src="gallery_gen/1570396f1726775d1cce5c83b0706c7e_300x210_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354480cb7c46923230325400b" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545600c74cd3db5543ddb4e5" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Sam Nujoma</p>

<p class="wb-stl-custom4" style="text-align: center;">- Namibia</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545f0225a44866afb99c2a4b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035430053647d7420b06d8ab54" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354300680f46a647a806f6bcb" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543007226c66942ccd64dc27" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Samora_Machel" title="https://en.wikipedia.org/wiki/Samora_Machel" target="_blank"><img loading="lazy" alt="" src="gallery_gen/5372a2a280dd4375e74cdf264fc32a5b_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354480d979fbcf38b37989020" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354560159814341b6cf5dbc63" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Samora Machel</p>

<p class="wb-stl-custom4" style="text-align: center;"> - Msumbiji</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003545f095cbc4e15e03497222d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354310082ef74694c179eccbf" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035431017609429b584cc869d3" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543102c7ea09d1e61bf4016e" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Kenneth_Kaunda" target="_blank"><img loading="lazy" alt="" src="gallery_gen/550d22763a73fab3b73256541dddbc4b_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544900354bc9392de18618fc" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354560271f682df2822b72f28" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Kenneth Kaunda</p>

<p class="wb-stl-custom4" style="text-align: center;">- Zambia</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a19890035460003a95b3fcb8fcc23c5f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543103e7c0159b3f05b2d472" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543104eef6cb13c2dc69ac84" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543105ddf2166af7492dcd58" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Patrice_Lumumba" target="_blank"><img loading="lazy" alt="" src="gallery_gen/14574c93b57ee80df1b44a961c8a11f8_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544901e450e706bddba05b0c" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035456039018d2e1f5306dee1b" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Patrice Lumumba</p>

<p class="wb-stl-custom4" style="text-align: center;">- Congo (Zaire /DRC)</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003546007e9afa2691f1ea7706a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543106e7867bd6231fd58cd1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543107f461d3f8de9fc3c33e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543200e51ee8a8fd1cbf8ddb" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Hastings_Banda" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a770b5a86758ef8c981bd2d8e0b76d18_fit.jpg?ts=1785686359"></a></div></div></div><div id="a19890035449026f450aaeaa6f6151a8" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354560425145aa2e2ebcf81e3" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Hastings Kamuzu Banda</p>

<p class="wb-stl-custom4" style="text-align: center;">- Malawi</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003546100aafabfe651c84c3b0d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543201c1a7e24682f0627295" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035432025f851380afec0f7a93" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035432034a28170d0c90a64a67" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://sw.wikipedia.org/wiki/Nnamdi_Azikiwe" target="_blank"><img loading="lazy" alt="" src="gallery_gen/933760d0723f699380102b2a69b12759_300x344_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544903e5119cdae6faa43036" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545605153ebb9240d7472e6e" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Nnamdi Azikiwe</p>

<p class="wb-stl-custom4" style="text-align: center;">- Nigeria</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354610532ea75802a34b6b923" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035432041f8a295a720d937d3c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543205123ce45358ecb5f1d5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543206a71aa9603e59948fed" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Thomas_Sankara" target="_blank"><img loading="lazy" alt="" src="gallery_gen/a6c8cd240f9f4d8d18fc4aa78f178e5f_300x168_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354490485805233b0fb7598b4" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035456061fa2eb1edb84c1cddf" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Thomas Sankara</p>

<p class="wb-stl-custom4" style="text-align: center;">- Burkina Fasor</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354610a721fb3ffa5840d9d32" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354320747a205bd9041ba4e45" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035432089d30ee971994b6f7d4" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035433001e491ac471cd2ae1b5" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Muammar_Gaddafi" target="_blank"><img loading="lazy" alt="" src="gallery_gen/1db65078b522d721e9d5c6c5b5ecd98c_300x374_fit.jpg?ts=1785686359"></a></div></div></div><div id="a19890035449052a874ef9068ffc9f04" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19890035456074a6cc7c29fef35fbb1" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Muammari Gaddafi</p>

<p class="wb-stl-custom4" style="text-align: center;">- Libya</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a198900354610f181525ae75e1f24577" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354330166e9d967f2229aaa4a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035433024fa101780cdc98ea6e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035433031d69643a989585205b" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Haile_Selassie" target="_blank"><img loading="lazy" alt="" src="gallery_gen/b6c71636a45fb72d58b79f9d573734ba_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544906693f7b2f0fcf40d944" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545608f3a8b9244b86d5f27a" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Emperor Haile Selassie I</p>

<p class="wb-stl-custom4" style="text-align: center;">- Ethiopia</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a19890035462037b00400eac94a3128d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354330461e344f2258a4b9848" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354330597b8a15338d3003b06" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354330678d0aec080546df053" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Léopold_Sédar_Senghor" target="_blank"><img loading="lazy" alt="" src="gallery_gen/052732d784d8347a79730116e3fd7d8c_300x170_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354490787c6091531c6344eb4" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a1989003545609d0eb403fd8b0bd30fb" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Leopold Sedar Senghor </p>

<p class="wb-stl-custom4" style="text-align: center;">- Senegal</p>
</div></div></div></div></div></div></div></div><div class="item "><div id="a1989003546208d467f13ca137c7b313" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035433078eb2cef563ff689cde" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19890035433087e3482e0360111f9ea" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354330931442ed54bcf6a31a3" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://en.wikipedia.org/wiki/Ahmadou_Ahidjo" target="_blank"><img loading="lazy" alt="" src="gallery_gen/dbd7c2b0f071ed790fad39a580770b78_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544908afc2f5b0a41e96e45a" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#000000"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354560a96999295b7f5c4f54b" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom4" style="text-align: center;">Ahmadou Ahidjo</p>

<p class="wb-stl-custom4" style="text-align: center;">- Cameroon</p>
</div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div></div><div id="a198900354490ad730959335a2e9c748" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354330a684c78d13e83fa5069" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003543400074f8b47bdb80b6536" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003543401a0cf9cefb95038b5b1" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom25"> Tuzo ya Sauti Ya Ukombozi</h2>
</div><div id="a198900354490bc6de8e7a0cbce6266c" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>Kutunuku sauti zilizoichochea Afrika kujiweka huru na kuendeleza fahari ya Umoja wa Afrika leo.</strong></p>

<p class="wb-stl-normal">Tuzo hii inawathamini wasanii waliotumia sanaa zao kupigania ukombozi na kuunda historia ya bara letu — ikiwahamasisha wasanii wa sasa kuendeleza mshikamano huo.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal" style="text-align: center;"><em><strong>Katika Tamasha la JUKANYE, tunahifadhi urithi wetu, kuenzi mashujaa wetu, na kuimarisha vizazi vijavyo kupitia utamaduni na fahari.</strong></em></p>
</div></div></div></div></div></div></div></div></div><div id="a1989003545e072c01b7728be46828ac" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354170233a71d19148f2b11a9" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2"><span style="color:rgba(61,61,61,1);">Malengo na Matukio</span></h2>
</div><div id="a1989003544008085dbb74bb32542880" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035417037f8806ea7f2dc0b0aa" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541704943452c743ca5dc66a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035417051ca08aef9c1e16c2c9" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="45" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#0ca3a6"><text x="1.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354400c7117c3b2c59cac1752" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2">Malengo ya JUKANYE International History Festival</h2>
</div><div id="a1989003544e09b64c9cda4840db659d" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><ul>
<li>
<p class="wb-stl-normal">Kuhifadhi Historia ya Ukombozi wa Afrika</p>
</li>
<li>
<p class="wb-stl-normal">Kukuza uelewa wa vizazi vijavyo kuhusu historia ya ukombozi wa bara letu.</p>
</li>
<li>
<p class="wb-stl-normal">Kuchochea Maendeleo ya Kiuchumi</p>
</li>
<li>
<p class="wb-stl-normal">Kuhamasisha vijana kushiriki katika sekta za uchumi na teknolojia.</p>
</li>
<li>
<p class="wb-stl-normal">Kuimarisha Amani na Haki</p>
</li>
<li>
<p class="wb-stl-normal">Kujenga jamii yenye mshikamano, haki na mazingira bora ya maendeleo.</p>
</li>
<li>
<p class="wb-stl-normal">Kukuza Kiswahili na Utamaduni wa Afrika</p>
</li>
<li>
<p class="wb-stl-normal">Kukitangaza Kiswahili kama lugha ya umoja na urithi wa bara letu.</p>
</li>
<li>
<p class="wb-stl-normal">Kujenga Ushirikiano wa Kisekta</p>
</li>
<li>
<p class="wb-stl-normal">Kuunganisha sekta za utalii, sanaa, elimu, makumbusho na nishati.</p>
</li>
<li>
<p class="wb-stl-normal">Kuhamasisha Uzalendo na Bidii</p>
</li>
<li>
<p class="wb-stl-normal">Kuwalea vijana wenye moyo wa kujituma na mapenzi kwa nchi zao.</p>
</li>
<li>
<p class="wb-stl-normal">Kulinda Mali Asili na Mazingira</p>
</li>
<li>
<p class="wb-stl-normal">Kukuza matumizi ya nishati salama na kuhimiza uhifadhi wa mazingira.</p>
</li>
<li>
<p class="wb-stl-normal">Kuwezesha Vijana na Ubunifu</p>
</li>
<li>
<p class="wb-stl-normal">Kuanzisha mfuko wa kusaidia vijana wabunifu na kuwaunganisha na fursa.</p>
</li>
<li>
<p class="wb-stl-normal">Kuimarisha Sekta ya Makumbusho</p>
</li>
<li>
<p class="wb-stl-normal">Kuyaboresha makumbusho na maeneo ya kihistoria kwa njia za kidijitali.</p>
</li>
<li>
<p class="wb-stl-normal">Kuwa Chanzo cha Mapato na Ajira</p>
</li>
<li>
<p class="wb-stl-normal">Kupanua masoko, kuongeza ajira, na kuimarisha ushirikiano wa kibiashara.</p>
</li>
<li>
<p class="wb-stl-normal">Kuendeleza Maono ya Mwalimu Nyerere</p>
</li>
<li>
<p class="wb-stl-normal">Kutekeleza maono ya umoja, maendeleo, na mshikamano wa Afrika.</p>
</li>
</ul>
</div></div></div><div id="a1989003544009e415d26b40cbf59f1f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354180016c65db17e4a4526aa" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="45" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#0ca3a6"><text x="1.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a198900354400d09b68733b2ac66662e" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2">Matukio na Shughuli Kuu za Tamasha la JUKANYE 2026</h2>
</div><div id="a1989003544e0a78a34ac664f3f0a4a7" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><ul>
<li>
<p class="wb-stl-normal">Matukio na Shughuli Kuu za Tamasha la JUKANYE 2026</p>
</li>
<li>
<p class="wb-stl-normal">Maonesho ya Makumbusho ya Historia ya Viongozi wa Afrika</p>
</li>
<li>
<p class="wb-stl-normal">Yatahusu maisha, fikra na mchango wa viongozi mashuhuri kama Mwalimu Julius Kambarage Nyerere, Nelson Mandela, Kwame Nkrumah na wengine waliopigania uhuru wa Afrika.</p>
</li>
<li>
<p class="wb-stl-normal">Burdani za Muziki wa Asili na Kisasa kutoka Mataifa Mbalimbali</p>
</li>
<li>
<p class="wb-stl-normal">Vikundi mbalimbali vya muziki kutoka ndani na nje ya Afrika vitatumbuiza kwa nyimbo za asili na kisasa zenye ujumbe wa umoja, uzalendo na maendeleo.</p>
</li>
<li>
<p class="wb-stl-normal">Michezo ya Kuigiza Maudhui ya Historia</p>
</li>
<li>
<p class="wb-stl-normal">Tamthilia zenye kuelezea harakati za ukombozi wa Afrika na changamoto zilizokuwepo, zitakazosaidia kuibua uelewa wa kihistoria kwa njia ya sanaa.</p>
</li>
<li>
<p class="wb-stl-normal">Maonyesho ya Mavazi ya Kitamaduni na Tamaduni za Nchi Shiriki</p>
</li>
<li>
<p class="wb-stl-normal">Kila nchi shiriki itaonesha utajiri wa mavazi na mila zao, kudhihirisha utofauti na mshikamano wa bara la Afrika.</p>
</li>
<li>
<p class="wb-stl-normal">Makongamano kwa Vijana na Wadau wa Historia</p>
</li>
<li>
<p class="wb-stl-normal">Majadiliano ya kitaalamu yatatoa fursa kwa vijana na wataalamu kuchambua masuala ya maendeleo, ukombozi wa kisiasa na kiuchumi barani Afrika.</p>
</li>
<li>
<p class="wb-stl-normal">Kliniki ya Afya kwa Jamii</p>
</li>
<li>
<p class="wb-stl-normal">Huduma za afya bure kwa jamii zitapatikana, ikiwa ni sehemu ya kukuza maendeleo ya watu kupitia huduma bora za afya.</p>
</li>
<li>
<p class="wb-stl-normal">Ziara za Historia: Butiama, Tabora, Dar es Salaam na Mikoa ya Kusini</p>
</li>
<li>
<p class="wb-stl-normal">Ziara maalum kwenye maeneo yenye historia ya harakati za ukombozi kwa ajili ya kujifunza kwa vitendo.</p>
</li>
<li>
<p class="wb-stl-normal">Ziara za Kitalii</p>
</li>
<li>
<p class="wb-stl-normal">Utembeleaji wa vivutio vya utalii kama Mlima Kilimanjaro, visiwa vya Zanzibar, hifadhi za Taifa, mapori ya akiba, misitu na makumbusho.</p>
</li>
<li>
<p class="wb-stl-normal">Kampeni ya Matumizi ya Nishati Salama ya Kupikia</p>
</li>
<li>
<p class="wb-stl-normal">Elimu kwa jamii juu ya umuhimu wa kutumia nishati salama na rafiki kwa mazingira majumbani na katika biashara.</p>
</li>
<li>
<p class="wb-stl-normal">Uzinduzi wa Makala Maalum ya Mwalimu Julius Nyerere</p>
</li>
<li>
<p class="wb-stl-normal">Filamu au makala mahsusi kuhusu maisha, falsafa na mchango wa Baba wa Taifa kwa maendeleo ya Afrika.</p>
</li>
<li>
<p class="wb-stl-normal">Madarasa ya “Tuseme Kiswahili” kwa Wageni kutoka Mataifa Mbalimbali</p>
</li>
<li>
<p class="wb-stl-normal">Fursa kwa wageni kujifunza Kiswahili, ili kuongeza matumizi ya lugha hii kama chombo cha mawasiliano barani Afrika.</p>
</li>
<li>
<p class="wb-stl-normal">Utoaji wa Tuzo za Heshima</p>
</li>
<li>
<p class="wb-stl-normal">Kuwatambua na kuwapa heshima viongozi wa zamani na wa sasa, wanamuziki, na watu waliotoa mchango mkubwa katika kuhamasisha uzalendo, amani, na maendeleo kupitia kazi zao.</p>
</li>
</ul>
</div></div></div></div></div></div></div></div></div><div id="a1989003545e0e0301d32ab87ffd3120" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354180167cf91c11f20323f5d" class="wb_element wb-elm-orient-horizontal" data-plugin="Line"><div class="wb-elm-line"></div></div><div id="a198900354400ae2647867fce27f337e" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2" style="text-align: center;"><span style="color:rgba(242,230,2,1);">Wito wa Ushiriki na ufadhili</span></h2>
</div><div id="a1989003544f00fc287c11efb2065599" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h1 class="wb-stl-custom7"> </h1>

<h1 class="wb-stl-custom7"> </h1>

<h1 class="wb-stl-custom7">Tunakaribisha ushirikiano na msaada kutoka kwa sekta zote na wadau mbalimbali ili kufanikisha tamasha hili la kihistoria. Ushiriki wako utachangia kuhifadhi historia ya ukombozi wa Afrika na kuhamasisha kizazi kijacho. Tunakaribisha:</h1>

<h1 class="wb-stl-custom7"> </h1>

<ul>
<li class="wb-stl-normal">
<h1 class="wb-stl-custom7">Ushirikiano imara kati ya serikali na sekta binafsi</h1>
</li>
<li class="wb-stl-normal">
<h1 class="wb-stl-custom7">Msaada na ushirikiano kutoka kwa mashirika ya kimataifa</h1>
</li>
<li class="wb-stl-normal">
<h1 class="wb-stl-custom7">Ufadhili wa kifedha na vifaa kutoka kwa taasisi na watu binafsi</h1>
</li>
<li class="wb-stl-normal">
<h1 class="wb-stl-custom7">Ushiriki wa wadau wa vyombo vya habari na mawasiliano ili kufikisha ujumbe wetu kwa jamii pana</h1>
</li>
<li class="wb-stl-normal">
<h1 class="wb-stl-custom7">Ushiriki wa wanahistoria, wasanii, wanataaluma na vijana wenye shauku ya maendeleo ya Afrika</h1>
</li>
<li class="wb-stl-normal">
<h1 class="wb-stl-custom7">Ushirikiano kutoka kwa jamii za ndani na washiriki kutoka pembe zote za dunia</h1>
</li>
</ul>
</div></div></div><div id="a1989003545f053b4811e7f8a987878f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354180251bcaa9a5020c478ef" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2">Timu Yetu:</h2>
</div><div id="a198900354400b052cb3bef8a85a498f" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;"> Kamati kuu ya tamasha itajumuisha wajumbe kutoka JACI, Catz Company Limited, viongozi wa sekta mbalimbali za serikali, mashirika ya kimataifa, na wataalamu kutoka sekta ya Utamaduni, Nishati, Utalii na Elimu. </p>
</div><div id="a1989003544f08c9dbd620520463c3fc" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989003541907bbbcbda1749d8b5155" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541908fb1b91dc40ab5b25c4" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://www.nmt.go.tz" target="_blank"><img loading="lazy" alt="" src="gallery_gen/3fdd3cc7f1099a420d73a7c21affc660_fit.jpg?ts=1785686359"></a></div></div></div><div id="a1989003544106b8a2fb29b39f4464b0" class="wb_element wb-elm-orient-horizontal" data-plugin="Line"><div class="wb-elm-line"></div></div><div id="a1989003544f0347e3ad37222004f86b" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;">Mialiko na Itifaki</h3>
</div><div id="a1989003545908ec689b7f102de713b9" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal"> </p>

<ul>
<li>Kuratibu Mialiko Rasmi:</li>
<li>Kuandaa na kutuma mialiko kwa wageni wa heshima wakiwemo viongozi wa kitaifa na kimataifa, mabalozi, mashujaa wa ukombozi, wasanii, na wadau wakuu wa tamasha.</li>
<li>Kuhakikisha Itifaki Inazingatiwa:</li>
<li>Kuratibu mapokezi ya wageni maalum kwa kufuata taratibu za heshima na hadhi zao.</li>
<li>Kuhakikisha taratibu za kitaifa na kimataifa za itifaki zinafuatwa ipasavyo kwenye hafla rasmi.</li>
<li>Kurahisisha Uratibu wa Wageni Maalum:</li>
<li>Kupanga ratiba, usafiri, malazi, na ulinzi wa wageni mashuhuri wanaoshiriki tamasha.</li>
<li>Kuwapa taarifa muhimu wageni kuhusu programu ya tamasha na mahitaji yao binafsi.</li>
<li>Kuhakikisha Uwasilishaji Bora wa Hotuba na Heshima:</li>
<li>Kusimamia mpangilio wa hotuba, utoaji wa tuzo, na matukio mengine ya hadhi ya juu.</li>
<li>Kuandaa watangazaji au wahudumu wa hafla wenye uelewa wa lugha na mila za itifaki.</li>
<li>Kuwezesha Ushirikiano wa Kimataifa:</li>
<li>Kufanikisha ujio na ushiriki wa wageni kutoka nje ya nchi, ikiwa ni pamoja na kusaidia utoaji wa viza na taarifa za kiusalama na mahitaji maalum.</li>
</ul>
</div><div id="a1989003545b01c3b3a932886f3aeb62" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom15"><strong>CONTACT:</strong></p>
</div></div></div><div id="a1989003544107750aa7ea8f57d68939" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541a00af2b8b4e16f67ea782" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><a href="https://www.nishati.go.tz" target="_blank"><img loading="lazy" alt="" src="gallery_gen/f83f0258de31faf7e886a5bc4dcd3d93_fit.jpg?ts=1785686359"></a></div></div></div><div id="a198900354410800d18f850e6db2c864" class="wb_element wb-elm-orient-horizontal" data-plugin="Line"><div class="wb-elm-line"></div></div><div id="a1989003544f010cceabb8b3dd7d70f1" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3">Nishati Endelevu na Uhamasishaji wa Matumizi Salama ya Nishati</h3>
</div><div id="a198900354590a4c4de67ecc9e6a42fa" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><ul>
<li>Kuhamasisha matumizi ya nishati safi ya kupikia majumbani</li>
<li>Kutoa elimu kuhusu athari za matumizi ya kuni na mkaa kwa mazingira na afya.</li>
<li>Kuelimisha jamii juu ya faida za kutumia majiko banifu, gesi safi (LPG), umeme, na nishati jadidifu kama vile sola.</li>
<li>Kushiriki katika maonyesho ya teknolojia za nishati safi</li>
<li>Kuonyesha bidhaa na teknolojia zinazochangia matumizi salama ya nishati, zikiwemo nishati jadidifu na rafiki kwa mazingira.</li>
<li>Kutoa semina na warsha kwa vijana na wananchi kuhusu fursa za kiuchumi katika sekta ya nishati safi</li>
<li>Kutoa taarifa kuhusu mikopo, mafunzo, na miradi ya serikali inayolenga kukuza matumizi ya nishati endelevu.</li>
<li>Kutoa ushauri na miongozo kwa washiriki wa tamasha na wadau wa maendeleo kuhusu sera za nishati</li>
<li>Kufafanua juhudi za serikali katika kufikia malengo ya maendeleo endelevu (SDGs), hasa SDG 7: Nishati safi na nafuu kwa wote.</li>
<li>Kushirikiana na wadau wa kimataifa na sekta binafsi kuendeleza ajenda ya nishati endelevu barani Afrika</li>
<li class="wb-stl-custom15">Kupitia makongamano na majadiliano ya wataalamu ndani ya tamasha, pamoja na kuwezesha uwekezaji wa pamoja.</li>
</ul>
</div><div id="a1989003545b029986148f250a68a417" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom15"><strong>CONTACT:</strong></p>
</div></div></div><div id="a1989003544f09cad53640fe57a3c6b4" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541a0170e5f9f498af5edfd6" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/2b29980d44a736136b86c0374c00bcde_300x278_fit.jpg?ts=1785686359"></div></div></div><div id="a198900354410964e63c00a8bbd91209" class="wb_element wb-elm-orient-horizontal" data-plugin="Line"><div class="wb-elm-line"></div></div><div id="a1989003544f0aa1a40a589e220236f4" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3"> </h3>

<h3 class="wb-stl-heading3"> </h3>

<h3 class="wb-stl-heading3" style="text-align: center;">Maudhui na Burudani</h3>
</div><div id="a19890035459060cb20baaeaecac5dd0" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal"> </p>

<ul>
<li>Uratibu wa Maudhui ya Tamasha, Kuweka mpango wa jumla wa maudhui yote yatakayowasilishwa katika tamasha (simulizi, mihadhara, mijadala, video, maonyesho, n.k).</li>
<li><strong>Kusimamia uteuzi wa mada;</strong> waandishi wa maudhui, watangazaji na watoa mihadhara. Kuhakikisha kuwa maudhui yanalingana na malengo ya kihistoria, kielimu na kiutamaduni ya JUKANYE.</li>
<li><strong>Burdani na Sanaa za Jukwaani; </strong>Kuandaa ratiba ya burudani ikijumuisha muziki wa asili, wa kisasa, ngoma za jadi, maigizo, mashairi na sanaa nyingine.</li>
<li>Kuratibu ushiriki wa wasanii wa ndani na wa kimataifa wanaohusiana na maudhui ya ukombozi, uzalendo, amani na mshikamano wa Afrika.</li>
<li>Kuweka viwango vya ubora na ujumbe wa kisanaa unaotolewa na wasanii.</li>
<li><strong>Usimamizi wa Watumbuizaji na Wasanii</strong>Kuwasiliana na vikundi vya burudani, wasanii binafsi, wanamuziki, na vikundi vya maigizo kuhusu mahitaji yao ya kiufundi na kisanii.</li>
<li>Kuhakikisha mahitaji ya wasanii yamezingatiwa (stage setup, sound, rehearsals, time slots, hospitality).</li>
<li>Kusimamia maadili na nidhamu ya burudani ili kuendana na heshima ya tukio.</li>
<li>
<p><strong>Uundaji wa Ratiba ya Tamasha (Program Line-up); </strong>Kutengeneza ratiba yenye mtiririko mzuri wa matukio kwa kila siku ya tamasha.</p>
</li>
<li>Kushirikiana na timu ya itifaki, walimu wa historia, na waandaaji wa semina na makongamano.</li>
<li><strong>Maudhui ya Kidijitali na Rekodi za Tamasha' </strong>Kuratibu uundaji wa maudhui ya video, nyaraka, podcast au vipindi vya TV kuhusu tukio na viongozi wa kihistoria.</li>
<li>Kuhakikisha maudhui yote yanahifadhiwa kwa matumizi ya baadaye na marejeo ya kihistoria.</li>
<li><strong>Ubunifu wa Tukio na Uzoefu wa Washiriki</strong></li>
<li>
<p>Kuweka mpangilio wa matukio ya kuvutia na yenye kusisimua kwa hadhira.</p>
</li>
<li>Kuhakikisha wageni wa tamasha wanapata uzoefu wa kipekee, wa kielimu na burudani.</li>
</ul>
</div><div id="a1989003545b038f400230d75177a147" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom15"><strong>CONTACT:</strong></p>
</div></div></div><div id="a198900354590bffb9960afb23fb71a7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541a02d45a1c97144f5747cc" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/8eb61f70aae3a1d3c32780df72e4693a_392x502_fit.png?ts=1785686359"></div></div></div><div id="a198900354410a0e1fe9b2fd7fa8b874" class="wb_element wb-elm-orient-horizontal" data-plugin="Line"><div class="wb-elm-line"></div></div><div id="a1989003544f0245030bc2e067a633c9" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3"> </h3>

<h3 class="wb-stl-heading3"> </h3>

<h3 class="wb-stl-heading3" style="text-align: center;">Brand and Marketing </h3>
</div><div id="a19890035459070b1189ea8eac4755dc" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><ul>
<li>1. Usimamizi wa Chapa ya Tamasha (Brand Management):</li>
<li>Kuunda na kusimamia utambulisho rasmi wa tamasha (brand identity), ikiwemo nembo, rangi, kaulimbiu na mwonekano wa kisasa wa JUKANYE.</li>
<li>Kuhakikisha kuwa chapa ya tamasha inaendana na maadili, historia na malengo ya tamasha.</li>
<li>Kusimamia ulinganifu wa chapa kwenye vifaa vyote vya mawasiliano, maonyesho, matangazo na mitandaoni.</li>
<li>2. Mkakati wa Masoko (Marketing Strategy):</li>
<li>Kubuni na kutekeleza mikakati madhubuti ya masoko ili kuongeza uelewa na ushiriki wa watu katika tamasha.</li>
<li>Kulenga hadhira ya ndani ya nchi, bara la Afrika na kimataifa kwa kutumia njia mbalimbali za mawasiliano.</li>
<li>Kufanya tafiti za soko ili kuelewa mahitaji ya wadau na kujua namna bora ya kuwafikia.</li>
<li>3. Matangazo na Uhamasishaji (Advertising &amp; Promotion):</li>
<li>Kupanga kampeni za matangazo kwenye TV, redio, magazeti, majukwaa ya kidigitali, mitandao ya kijamii na mabango ya mitaani.</li>
<li>Kushirikiana na waandishi wa habari, watangazaji, influencers na media houses kwa ajili ya kuhamasisha tamasha.</li>
<li>4. Ushirikiano na Wadhamini (Sponsorship &amp; Partnerships):</li>
<li>Kuandaa nyaraka za kibiashara na pendekezo la wadhamini (sponsorship proposal).</li>
<li>Kusaka wadhamini na kushirikiana nao kwa kutumia nafasi ya tamasha kukuza chapa zao.</li>
<li>Kuhakikisha kuwa wadhamini wanaonekana na kuthaminiwa ipasavyo kwenye matangazo na matukio ya tamasha.</li>
<li>5. Usimamizi wa Vyombo vya Habari na Mitandao ya Kijamii (Media &amp; Social Media Management):</li>
<li>Kusimamia kalenda ya maudhui (content calendar) kwa mitandao ya kijamii na kuhakikisha mawasiliano yanaendana na ujumbe wa tamasha.</li>
<li>Kuandaa taarifa kwa vyombo vya habari, matangazo ya video, mabango, vipeperushi na nyenzo za kidigitali.</li>
<li>Kufuatilia usikivu na ushawishi wa kampeni kupitia ripoti na takwimu.</li>
<li>6. Utafiti na Tathmini (Monitoring &amp; Evaluation):</li>
<li>Kupima mafanikio ya kampeni za masoko na kuboresha pale inapobidi.</li>
<li>Kukusanya mrejesho kutoka kwa washiriki na wadau kuhusu taswira ya tamasha na kiwango cha uhamasishaji.</li>
</ul>
</div><div id="a1989003545b048b02a77a471df771aa" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom15"><strong>MAWASILIANO:</strong></p>

<p class="wb-stl-custom15"><strong>S.L.P 38012</strong></p>

<p class="wb-stl-custom15"><b>Dar Es Salaam, Tanzania</b></p>

<p class="wb-stl-custom15"><b>Mob. +255 (0) 673 023 547</b></p>

<p class="wb-stl-custom15"><b>Mob. +255 (0) 789 388 232</b></p>
</div></div></div></div></div></div></div><div id="a1989003545f0c4344f5e362818c2aec" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989003541903e5479582ea0f7a7da8" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2">Wajumbe wa Bodi:</h2>
</div><div id="a19890035441037178cbc0265f4aac14" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;"> Kamati kuu ya tamasha itajumuisha wajumbe kutoka JACI, Catz Company Limited, viongozi wa sekta mbalimbali za serikali, mashirika ya kimataifa, na wataalamu kutoka sekta ya Utamaduni, Nishati, Utalii na Elimu. </p>
</div><div id="a1989003544f064f9ca9b18bff32a96a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a198900354190440d0cee829cdf56138" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035419053b4eb5fd9efc7507d9" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a198900354280304af8a2e747f931fc3" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery/kitenge%20mat.jpg?ts=1785686359"></div></div></div></div></div><div id="a1989003544104d8af41a91408be49a1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19890035419069d8923cac40857b3fb" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;">Majukumu</h3>
</div><div id="a198900354410579937b59c4948612cb" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal"> </p>

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
</div></div></div><div id="a1989003544f07dca8051b85ff969415" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"></div></div></div></div></div></div><div id="a1989003545a089807d0745a6b5bc663" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal">Sifa za  Wajumbe wa Bodi:</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>Mwenyekiti wa Bodi:</strong><br>
– Mtu mashuhuri na mwenye heshima kubwa kitaifa (mfano, kiongozi mstaafu wa ngazi ya juu, mtaalam maarufu, au kiongozi mwenye ushawishi mkubwa katika taaluma au utamaduni).<br>
– Alama ya uadilifu, mshikamano, na umoja wa kitaifa.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>Makamu Mwenyekiti:</strong><br>
– Mtaalamu mwenye uzoefu mkubwa katika sekta za utamaduni, urithi, au huduma za umma.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>Katibu wa Bodi:</strong><br>
– Mwakilishi mkongwe kutoka CATZ COMPANY LTD / JACI (Taasisi ya Sanaa na Utamaduni Jasiri), anayehusika na usimamizi wa mawasiliano na uratibu wa shughuli za Bodi.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>Mhazini:</strong><br>
– Mtaalamu wa fedha au maendeleo mwenye jukumu la usimamizi wa rasilimali fedha na ukusanyaji wa michango.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>Mjumbe wa Bodi</strong> – Mwakilishi wa Serikali<br>
– Aliyeateuliwa na Wizara ya Utamaduni, Utalii, au Mambo ya Nje ili kuhakikisha ushiriki wa serikali katika shughuli za Bodi.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>Mjumbe wa Bodi</strong> – Kiongozi wa Sekta Binafsi<br>
– Msimamizi wa sekta binafsi mwenye uzoefu katika maeneo ya udhamini, vyombo vya habari, au uendelezaji wa chapa.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>Mjumbe wa Bodi</strong> – Mwakilishi wa Afrika Mashariki / Pan-Afrika<br>
– Mtu mwenye hadhi na ushawishi kutoka nchi nyingine ya Afrika kwa lengo la kukuza umiliki wa bara na mshikamano wa kikanda.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>Mjumbe wa Bod</strong>i – Mshirika/Muwakilishi wa Wafadhili wa Kimataifa<br>
– Mwakilishi kutoka taasisi za kimataifa zinazoshirikiana (mfano, UNESCO, AU, UNDP, nk).</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>Mjumbe wa Bodi </strong>– Mwakilishi wa Vijana na Ubunifu<br>
– Mtu mchanga mwenye ushawishi wa mabadiliko anayeleta sauti na mawazo mapya ya vijana.</p>

<p class="wb-stl-normal"> </p>

<p class="wb-stl-normal"><strong>Mjumbe wa Bodi</strong> – Mtaalamu wa Sanaa na Utamaduni<br>
– Msanii, mtayarishaji wa filamu, au mtaalam wa historia mwenye mchango mkubwa katika mwelekeo wa ubunifu wa tamasha.</p>
</div></div></div><div id="a1989003546005f89639c8da85bc5c2b" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Mwanzo/"><span><svg xmlns="http://www.w3.org/2000/svg" width="1793.982" height="1793.982" viewBox="0 0 1793.982 1793.982" style="display: inline-block; vertical-align: middle; position: relative; top: -1px; height: 1em; width: 1em; overflow: visible; direction: ltr;"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg>&nbsp;Mwanzo</span></a></div></div></div><div id="wb_footer_a198900350f300a37ae9158159156524" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fc2149cd000eb3b8848562ec6f176" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc386d7d4d77961b3399b7e7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19fb429723a03cab5671bd0692f5610" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/"><span><svg xmlns="http://www.w3.org/2000/svg" width="1793.982" height="1793.982" viewBox="0 0 1793.982 1793.982" style="display: inline-block; vertical-align: middle; position: relative; top: -1px; height: 1em; width: 1em; overflow: visible; direction: ltr;"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg>&nbsp;Home </span></a></div><div id="a188dd9ebc386e9c761088b65418f7a1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc386f7f651dc7e4d0792624" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="sw/Mwanzo/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#4be6e6"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a188dd9ebc38700f452a2fef2fcabe01" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="sw/Mwanzo/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1024 1024" style="direction: ltr; color:#ffffff"><text x="64" y="960" font-size="1024" fill="currentColor" style='font-family: "builder-ui-icons-plugins"'></text></svg></a></div></div></div><div id="a188dd9ebc3871cfcba1a4cf7091cb6d" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="sw/Mwanzo/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#ffffff"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div></div></div><div id="a19fc20bdb7e00c6080e244c0b41b351" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-custom16" style="text-align: center;">ADDRESS:</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">P,O BOX  DAR- ES - SALAAM, TANZANIA</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">PHONE: +255 746 174403 +255 789  388232 +255 719 083050</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">EMAIL: jukanyefestival@gmail.com</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">info@jukanye.com</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">WEBSITE: www.jukanye.com</h3>
</div><div id="a188dd9ebc38721835f60daecdc81bab" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/3a0fa4358ae2f4fb06a94eaab03b4403_fit.png?ts=1785686359"></div></div></div><div id="a188dd9ebc387353ef2d51652b5ef64e" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-footer">© 2025 <a href="http://jukanye.com">jukanye.com</a> - Kuwaenzi Viongozi wa Afrika Walioongoza Harakati za Ukombozi</p>
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
	<script src="js/a198900350f300a37ae9158159156524-bundle.js?ts=20260802185857" type="text/javascript" defer></script>{{hr_out}}<script>
    document.addEventListener('DOMContentLoaded', function () {
        window._spDefer.done();
    });
</script>
</body>
</html>


<?php } ?>
