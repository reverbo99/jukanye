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

				document.cookie = '__cookie_law__=' + (2) + '; path=/; expires=Wed, 28 Jul 2027 18:59:04 GMT';

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
	<title><?php echo htmlspecialchars((isset($seoTitle) && $seoTitle !== "") ? $seoTitle : "Homeb"); ?></title>
	<base href="{{base_url}}" />
	<?php echo isset($sitemapUrls) ? (generateCanonicalUrl($sitemapUrls)."\n") : ""; ?>	
		<link rel="alternate" hreflang="en" href="{{base_url}}{{lang_en}}" />
		<link rel="alternate" hreflang="x-default" href="{{base_url}}{{lang_en}}" />
			<link rel="alternate" hreflang="sw" href="{{base_url}}{{lang_sw}}" />
		
						<meta name="viewport" content="width=device-width, initial-scale=1" />
					<meta name="description" content="<?php echo htmlspecialchars((isset($seoDescription) && $seoDescription !== "") ? $seoDescription : "JUKANYE International  Festival \nCelebrating African Liberation, Legacy, and Unity"); ?>" />
			<meta name="keywords" content="<?php echo htmlspecialchars((isset($seoKeywords) && $seoKeywords !== "") ? $seoKeywords : "JUKANYE Festival,African liberation history,Tanzania cultural events,Julius Nyerere International Festival,African leaders tribute,African independence celebration,Pan-Africanism events,African heritage exhibitions,African music performances,historical tours Tanzania,Mama Afrika Award,African women freedom fighters,African cultural festivals,Tanzania tourism events,African history seminars,youth empowerment Africa,sustainable energy Tanzania,clean cooking energy awareness,African cultural dress showcase,African heritage digitization,African unity initiatives,African arts and culture,historical sites Tanzania,African peace and reconciliation,African storytelling festivals,promoting Kiswahili language,African tourism promotion,African history documentaries,African patriotism events,African development conferences,international African festivals,African community health programs,African cultural dialogue,African leadership awards,African youth forums,African historical documentaries,African environmental conservation"); ?>" />
			
	<!-- Facebook Open Graph -->
		<meta property="og:title" content="<?php echo htmlspecialchars((isset($seoTitle) && $seoTitle !== "") ? $seoTitle : "Homeb"); ?>" />
			<meta property="og:description" content="<?php echo htmlspecialchars((isset($seoDescription) && $seoDescription !== "") ? $seoDescription : "JUKANYE International  Festival \nCelebrating African Liberation, Legacy, and Unity"); ?>" />
			<meta property="og:image" content="<?php echo htmlspecialchars((isset($seoImage) && $seoImage !== "") ? "{{base_url}}".$seoImage : ""); ?>" />
			<meta property="og:type" content="article" />
			<meta property="og:url" content="__wb_curr_url__" />
		<!-- Facebook Open Graph end -->

		<meta name="generator" content="Website Builder" />
			<link href="css/common-bundle.css?ts=20260802185857" rel="stylesheet" type="text/css" />
	<link href="css/a188dd9eef5300bb9a9e9122025694a7-bundle.css?ts=20260802185857" rel="stylesheet" type="text/css" id="wb-page-stylesheet" />
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


<body class="site site-lang-en<?php if (isset($wbPopupMode) && $wbPopupMode) echo ' popup-mode'; ?> " <?php ?>><div id="wb_root" class="root wb-layout-vertical"><div class="wb_sbg"></div><div id="wb_header_a188dd9eef5300bb9a9e9122025694a7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc3858a7a4bf4599d6087d14" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc38596f36338d0b0d66657b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1986657436700dbe63ba0cbad5bbe2c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fb4223ec700f33f6a6750b25b7549" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fc26ad9c300737c8a0c139e48b498" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc385abbb04767f5aaa74a38" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/63a123b911049cc657f1d0f2a9cc7765_fit.png?ts=1785686343"></div></div></div><div id="a19fb4297212030bdabc97de04dae2a0" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom49">JULIUS KAMBARAGE NYERERE INTERNATIONAL FESTIVAL</h2>
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
						var lib = new GalleryLib({"id":"a19fb8f3c64000b0747d009eda7d1a44","height":"auto","type":"slideshow","trackResize":true,"interval":5,"speed":1000,"images":[{"thumb":"gallery_gen\/9147f62c31174403cafdbe5847fd40e4_301.5x134_fill.png","src":"gallery_gen\/a0295deaa452d91f264f568d7ace6a7c_fit.png?ts=1785686343","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/f3e0a489b3b22ccf940c58dffbcd2ad4_301.5x134_fill.jpg","src":"gallery_gen\/2a406b85dd90631c40b79158c1877d4f_fit.jpg?ts=1785686343","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/da8e24800b8f72dd8eed800429e1a18b_301.5x134_fill.jpg","src":"gallery_gen\/3c456088697ef08011819b714ae09234_fit.jpg?ts=1785686343","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/d362c813c5330d042dde3a964f0bfed1_301.5x134_fill.jpg","src":"gallery_gen\/30ce731cc7b1cc1edd84ddce750a6366_fit.jpg?ts=1785686343","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/33145b78952db630d35b79ec91eed8d5_301.5x134_fill.jpg","src":"gallery_gen\/47e964e8cdbbdbffac1cc75dec2c4369_fit.jpg?ts=1785686343","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/72018fdb993c6ceb781c0740d2917da8_301.5x134_fill.jpg","src":"gallery_gen\/a55bfef5daf82a78f393f684c67908ca_fit.jpg?ts=1785686343","width":1881,"height":836,"title":"","link":null,"description":"","address":""}],"border":{"border":"5px none #00008c"},"padding":10,"thumbWidth":100,"thumbHeight":100,"thumbAlign":"center","thumbPadding":6,"thumbAnim":"","thumbShadow":"","imageCover":true,"disablePopup":false,"controlsArrow":"chevron","controlsArrowSize":14,"controlsArrowStyle":{"normal":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}},"hover":{"color":"#DDDDDD","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#222222","forText":true,"css":{"text-shadow":"0px 0px 1px #222222"}}},"active":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}}},"slideOpacity":100,"showPictureCaption":"always","captionIncludeDescription":false,"captionPosition":"center bottom","mapTypeId":"roadmap","markerIconTypeId":"thumbs","zoom":2,"mapCenter":{"latLng":{"lat":41.244772343082,"lng":-5.2734375},"text":"41.244772343082076, -5.2734375"},"key":"AIzaSyD-9xtp38UunEmx7XfJ7eBh-K4w8qQ6SEw","theme":"default","color":"#eeeeee","showSatellite":true,"showZoom":true,"showStreetView":true,"showFullscreen":true,"allowDragging":true,"showRoads":true,"showLandmarks":true,"showLabels":true,"locale":"en_US","pauseOnHover":true});
						lib.appendTo(el);
					})(window.wbmodGalleryLib);
				});
			});
		</script></div></div></div></div></div><div id="a19fb429722400fb62f16c17777e0dbd" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19fb42971f400a1d073d65740953b98" class="wb_element" data-plugin="Button"><a class="wb_button" href="Register/"><span>Register to participate</span></a></div><div id="a19fb429722c00d2df553faa4f96bb89" class="wb_element" data-plugin="Button"><a class="wb_button" href="Event-Products/"><span>Products</span></a></div><div id="a19fb429721202bf4c948ac3d6dde212" class="wb_element" data-plugin="Button"><a class="wb_button" href="Award-Nominees/"><span>Award Nominees</span></a></div></div></div></div></div></div></div></div></div></div></div><div id="wb_main_a188dd9eef5300bb9a9e9122025694a7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19864bf86c8001fc9d9bd361fe4ff6f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"></div></div><div id="a188dd9ebc381ac1f603a855675c2f4a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc381bf31c5e5985a7b3ea71" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc381c9681aec4944a3acde8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19864bf86d6000d2cbb532a4358dd95" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19864bf86d900f3fc2fd1e613794f3d" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2">JULIUS KAMBARAGE NYERERE INTERNATIONAL  FESTIVAL</h2>
</div><div id="a19880bfdac60043a43e6b84ff9e83d2" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3">Africa’s journey to independence is a story of courage, sacrifice, and unwavering vision. From the dusty battlefields to the halls of diplomacy,African liberation leaders stood firm against colonial oppression, igniting theflame of freedom that still burns across the continent today.</h3>
</div><div id="a19864bf86e5006f98f97ae33f2d18ae" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19864bf86ed00d4ecd9b1babc6ce461" class="wb_element" data-plugin="Button"><a class="wb_button" href="Register/"><span>Register to participate</span></a></div><div id="a1987be71a3b00a24b144c63e45517d8" class="wb_element" data-plugin="Button"><a class="wb_button" href="Award-Nominees/"><span>Award Nominees</span></a></div><div id="a1988e3c7cfa000ebac3c5a7f0873487" class="wb_element" data-plugin="Button"><a class="wb_button" href="Event-Products/"><span>Products</span></a></div><div id="a19864bf86e800d839909ed539924710" class="wb_element" data-plugin="Button"><a class="wb_button" href="Donate/"><span>Donation</span></a></div><div id="a1987b9cc12d0019fb2c5efaa51087ac" class="wb_element" data-plugin="Button"><a class="wb_button" href="Sponsors/"><span>Sponsors</span></a></div></div></div></div></div><div id="a1988f8816e90091a2b81ae89247d3f1" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom42"><strong>Who Can Join?</strong></p>

<ul>
<li class="wb-stl-custom42">All African countries interested in taking part</li>
<li class="wb-stl-custom42">Friendly nations with a strong connection to Africa</li>
<li class="wb-stl-custom42">International partners from education, development, and culture</li>
<li class="wb-stl-custom42">Tourists, businesspeople, and professionals</li>
<li class="wb-stl-custom42">Communities from Tanzania and around the world—everyone is welcome!</li>
</ul>
</div></div></div></div></div></div></div><div id="a19890cfce1400152a2343bebeae6fbb" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"></div></div><div id="a19880ceb5a500fcab77be9a85849818" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19880ceb5ad00afa9b5a9e5ba94f4a5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"></div></div></div></div><div id="a1988f2fc1ea00d86023f977e444d64c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1988f2fca95002f37610e639febf7bc" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"></div></div></div></div><div id="a188dd9ebc38280da666ccec53c75f15" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc38295c0a1675fff24cd57a" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2" style="margin: 0in 0in 8pt;"><span style="color:rgba(61,61,61,1);">Objectives &amp; Activities</span></h2>
</div><div id="a188dd9ebc382a15752ecdeaf40ca074" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc382b76f0d937168722cd71" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc382c062c53c6e14f567251" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc382de07bca2228c8a71e17" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="45" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#0ca3a6"><text x="1.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a192ffce42210037686ddb1988374720" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;">Objectives of the JUKANYE International  Festival</h3>
</div><div id="a188dd9ebc382e9f6516a28d8a67bcf5" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><ul>
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
</div></div></div><div id="a188dd9ebc382fd05789a2acf475fedc" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc3830d4194caaed462aebad" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="45" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#0ca3a6"><text x="1.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a192ffcee9f900874a3fb1d4ee028a55" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3" style="text-align: center;">Key Events and Activities of the JUKANYE Festival 2026</h3>
</div><div id="a188dd9ebc383105ab183265deebb135" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><ul>
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
</div></div></div></div></div></div></div><div id="a19899ef940400439f7995107dfaccd0" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"></div></div></div></div><div id="a19899eefb490014fe2890d96aefc7d7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19899eefb930018cec02d52ecbc6825" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19899eefba700ca249e0ddea577ed52" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19899eefbbb00ff23ac141b8911fc7d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"></div></div><div id="a188dd9ebc38356d2bb45a52182269f8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"></div></div></div></div></div></div></div></div><div id="a198941c143300bf579a21498b5220cb" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;"> </p>

<p class="wb-stl-normal" style="text-align: center;"><strong>🔥 Own the Spirit. Fund the Festival.</strong></p>

<p class="wb-stl-normal" style="text-align: center;"> </p>

<p class="wb-stl-normal" style="text-align: center;">By wearing JUKANYE merchandise, you: </p>

<p class="wb-stl-normal" style="text-align: center;">Support the JUKANYE International History Festival and its powerful mission to inspire, educate, and unite</p>

<p class="wb-stl-normal" style="text-align: center;">Help fund programs in youth empowerment, cultural preservation, African unity, and historical education</p>

<p class="wb-stl-normal" style="text-align: center;"> </p>

<p class="wb-stl-normal" style="text-align: center;">Every T-shirt, Cap, and Kitenge you purchase is more than just apparel — It’s a statement of patriotism, a tribute to our heroes, and a contribution to the future of African heritage.</p>

<p class="wb-stl-normal" style="text-align: center;"> </p>

<p class="wb-stl-normal" style="text-align: center;"><strong>Let your fashion speak freedom.</strong></p>
</div><div id="a19893d716370034ae28d3760275c429" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19893d71637014e5c652ce70f3687a0" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19893e1bc00007f4980ed8081c49c76" class="wb_element wb-prevent-layout-click wb_gallery" data-plugin="Gallery"><script type="text/javascript">
			window._spDefer.add(function() {
				$(function() {
					(function(GalleryLib) {
						var el = document.getElementById("a19893e1bc00007f4980ed8081c49c76");
						var lib = new GalleryLib({"id":"a19893e1bc00007f4980ed8081c49c76","height":"auto","type":"list","trackResize":true,"interval":3,"speed":400,"images":[{"thumb":"gallery_gen\/7d39828787d2a197f4707adea081e6ab_164x246_fill.jpg","src":"gallery_gen\/eced5c2186f5c78eeebf3b2f2b70176c_fit.jpg?ts=1785686343","width":1024,"height":1536,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/fd76f03bf3c69627ef3332ffe0cf4788_164x246_fill.jpg","src":"gallery_gen\/83ee15477d529ff1690e72b763e300dc_fit.jpg?ts=1785686343","width":1024,"height":1536,"title":"","link":null,"description":"","address":""}],"border":{"border":"5px none #00008c"},"padding":10,"thumbWidth":100,"thumbHeight":100,"thumbAlign":"center","thumbPadding":6,"thumbAnim":"","thumbShadow":"","imageCover":true,"disablePopup":false,"controlsArrow":"chevron","controlsArrowSize":14,"controlsArrowStyle":{"normal":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}},"hover":{"color":"#DDDDDD","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#222222","forText":true,"css":{"text-shadow":"0px 0px 1px #222222"}}},"active":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}}},"slideOpacity":100,"showPictureCaption":"always","captionIncludeDescription":false,"captionPosition":"center bottom","mapTypeId":"roadmap","markerIconTypeId":"thumbs","zoom":2,"mapCenter":{"latLng":{"lat":41.244772343082,"lng":-5.2734375},"text":"41.244772343082076, -5.2734375"},"key":"AIzaSyD-9xtp38UunEmx7XfJ7eBh-K4w8qQ6SEw","theme":"default","color":"#eeeeee","showSatellite":true,"showZoom":true,"showStreetView":true,"showFullscreen":true,"allowDragging":true,"showRoads":true,"showLandmarks":true,"showLabels":true,"locale":"en_US","pauseOnHover":false});
						lib.appendTo(el);
					})(window.wbmodGalleryLib);
				});
			});
		</script></div><div id="a19893d7163703a2d07121683357a3c6" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom46"><span class="text-nocut">$ 20</span></h2>
</div><div id="a19893d71637046e7c4d5159d797524a" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h1 class="wb-stl-custom45"><span class="text-nocut">Caps</span></h1>
</div><div id="a19893d716370583d2e3a69b9d16e40c" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-custom40">Cap It with Pride – Support the Festival."</h3>
</div><div id="a19893d85a9d00d3400c0f9d1fc564a3" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19893d7163706b3011974b125caa740" class="wb_element wb-prevent-layout-click" data-plugin="BuyNow">


	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" data-gateway-id="Paypal" target="_blank" style="width: 100%; height: 100%;">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="">
		<input type="hidden" name="amount" value="1">
		<input type="hidden" name="currency_code" value="USD">
		<input type="hidden" name="button_subtype" value="services">
		<input type="hidden" name="no_note" value="0">
		<input type="hidden" name="shipping" value="">
		<input type="hidden" name="bn" value="JSCProfis_SP">
		<?php global $pluginData; $pluginData = json_decode('{"business":"","itemName":"","amount":"1","currencyCode":"USD","shipping":"","test":false,"html":"<form action=\\"https:\\/\\/www.paypal.com\\/cgi-bin\\/webscr\\" method=\\"post\\" target=\\"_blank\\" class=\\"paypal\\"><input type=\\"hidden\\" name=\\"cmd\\" value=\\"_xclick\\"><input type=\\"hidden\\" name=\\"business\\" value=\\"\\"><input type=\\"hidden\\" name=\\"lc\\" value=\\"US\\"><input type=\\"hidden\\" name=\\"item_name\\" value=\\"\\"><input type=\\"hidden\\" name=\\"amount\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"currency_code\\" value=\\"USD\\"><input type=\\"hidden\\" name=\\"button_subtype\\" value=\\"services\\"><input type=\\"hidden\\" name=\\"no_note\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"shipping\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"bn\\" value=\\"JSCProfis_SP\\"><input type=\\"image\\" src=\\"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/btn\\/btn_buynow_LG.gif\\" border=\\"0\\" name=\\"submit\\" alt=\\"PayPal - The safer, easier way to pay online!\\"><img alt=\\"\\" border=\\"0\\" src=\\"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/scr\\/pixel.gif\\" width=\\"1\\" height=\\"1\\"><\\/form>","__globalVars":["demo"],"logo":"paypal_color.svg","_locale":"en_US","button_label":"","button_color":"transparent","font_family":"Arial,Helvetica,sans-serif","font_size":14,"label_color":"#333333","button_border":{"differ":false,"color":["#eeeeee","#eeeeee","#eeeeee","#eeeeee"],"style":["none","none","none","none"],"weight":[1,1,1,1],"radius":null,"css":{"border":"1px none #eeeeee"},"cssRaw":"border: 1px none #eeeeee;"},"demo":false,"showlogo":true,"logo_width":107,"button_padding":0,"buttonBorderCss":"border-bottom: 1px none #eeeeee;border-left: 1px none #eeeeee;border-right: 1px none #eeeeee;border-top: 1px none #eeeeee;border-radius: 0px;-webkit-border-radius: 0px;-moz-border-radius: 0px;","remoteLogo":"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/btn\\/btn_buynow_LG.gif","logo_src":"gallery_gen\\/BuyNow\\/paypal_color.svg","paymentGatewayButton":"<button type=\\"submit\\" id=\\"a19893d7163706b3011974b125caa740_payment_gateway_button\\" class=\\"btn btn-default btn-sm\\" style=\\"width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 0px;background-color: transparent;border: 1px none #eeeeee;border-radius: 0px 0px 0px 0px; -moz-border-radius: 0px 0px 0px 0px; -webkit-border-radius: 0px 0px 0px 0px;\\"><img src=\\"gallery_gen\\/BuyNow\\/paypal_color.svg\\" alt=\\"BuyNow\\" style=\\"width: 107px; max-width: 100%;\\" \\/><\\/button>","border_radius":{"lt":0,"rt":0,"rb":0,"lb":0,"differ":false,"css":{"border-radius":"0px 0px 0px 0px","-moz-border-radius":"0px 0px 0px 0px","-webkit-border-radius":"0px 0px 0px 0px"},"cssRaw":"border-radius: 0px 0px 0px 0px; -moz-border-radius: 0px 0px 0px 0px; -webkit-border-radius: 0px 0px 0px 0px;"},"clientId":"","clientSecret":"","button_icon":null}'); $pluginData->_extReferenceId = ''; $pluginData->elemId = 'a19893d7163706b3011974b125caa740'; $pluginData->currLang = 'en'; $pluginData->currLangLocale = 'en_US'; $pluginData->isPreview = ''; if (!function_exists('_spDefer_wrap_scripts')) { function _spDefer_wrap_scripts($output) { return preg_replace_callback('%<script\\b((?:(?!src=)[^>])*)>(.*?)</script>%is', function($m) { if (strpos($m[2], '_spDefer.done(') !== false) return $m[0]; if (strpos($m[2], 'window._spDefer') !== false) return $m[0]; return '<script>window._spDefer.add(function() {' . $m[2] . '});</script>'; }, $output); } } ob_start(); require dirname(__FILE__).'/main_BuyNow.php'; echo _spDefer_wrap_scripts(ob_get_clean()); ?>
	</form>

</div><div id="a19893d7ef6d0094a3386d92b460c81e" class="wb_element wb-prevent-layout-click" data-plugin="mpesa"><button type="submit" id="a19893d7ef6d0094a3386d92b460c81e_payment_gateway_button" class="btn btn-default btn-sm" style="width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 4px;background-color: #ffffff;border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;"><strong style="display: block; color: #949494;font-family: Arial;font-size: 12px;">Pay with</strong><img src="gallery_gen/mpesa/mpesa.svg" alt="mpesa" style="width: 110px; max-width: 100%;"></button>

<div class="modal fade" id="mpesaModal_a19893d7ef6d0094a3386d92b460c81e" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">Mpesa</h4>
            </div>
            <div class="modal-body">
                <form id="mpesaForm_a19893d7ef6d0094a3386d92b460c81e" method="post" action="" data-gateway-id="Mpesa">
                    <div class="form-group">
                        <label>Phone number: <span style="color: #c00;">*</span></label>
                        <input type="text" class="form-control" name="phone" id="phone_a19893d7ef6d0094a3386d92b460c81e" value="">
                    </div>
                    <div class="form-group">
                        <label>Country: <span style="color: #c00;">*</span></label>
                        <select class="form-control" id="country_a19893d7ef6d0094a3386d92b460c81e" name="country" required="required">
                            <option value="GHA">Ghana</option>
                            <option value="TZN">Tanzania</option>
                            <option value="LES">Lesotho</option>
                            <option value="DRC">DR Congo</option>
                        </select>
                    </div>
                </form>
                <div id="mpesaCheckoutRender_a19893d7ef6d0094a3386d92b460c81e"></div>
                <div id="mpesaError_a19893d7ef6d0094a3386d92b460c81e" class="alert alert-danger"></div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button id="mpesaBackBtn_a19893d7ef6d0094a3386d92b460c81e" type="button" class="btn btn-primary" onclick="showModal_a19893d7ef6d0094a3386d92b460c81e()">Back</button>
                <button id="mpesaNextBtn_a19893d7ef6d0094a3386d92b460c81e" type="button" class="btn btn-primary" onclick="startPaymentFlow_a19893d7ef6d0094a3386d92b460c81e()">Pay</button>

            </div>
        </div>
    </div>
</div>
<?php global $pluginData; $pluginData = json_decode('{"__globalVars":["apiKey","isTest"],"apiKey":"","price":"","isTest":true,"providerCode":"","button_label":"Pay with","button_color":"#ffffff","font_family":"Arial","font_size":12,"label_color":"#949494","button_border":{"differ":false,"differRadius":false,"color":["#cccccc","#cccccc","#cccccc","#cccccc"],"style":["solid","solid","solid","solid"],"weight":[1,1,1,1],"css":{"border":"1px solid #cccccc","-moz-border-radius":"0px","-webkit-border-radius":"0px","border-radius":"0px"},"cssRaw":"border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;"},"logo":"mpesa.svg","showlogo":true,"logo_width":110,"border_radius":{"lt":0,"rt":0,"rb":0,"lb":0,"differ":false,"css":{"border-radius":"0px","-moz-border-radius":"0px","-webkit-border-radius":"0px"},"cssRaw":"border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;"},"button_padding":4,"logo_src":"gallery_gen\\/mpesa\\/mpesa.svg","paymentGatewayButton":"<button type=\\"submit\\" id=\\"a19893d7ef6d0094a3386d92b460c81e_payment_gateway_button\\" class=\\"btn btn-default btn-sm\\" style=\\"width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 4px;background-color: #ffffff;border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;\\"><strong style=\\"display: block; color: #949494;font-family: Arial;font-size: 12px;\\">Pay with<\\/strong><img src=\\"gallery_gen\\/mpesa\\/mpesa.svg\\" alt=\\"mpesa\\" style=\\"width: 110px; max-width: 100%;\\" \\/><\\/button>","button_icon":null}'); $pluginData->_extReferenceId = ''; $pluginData->elemId = 'a19893d7ef6d0094a3386d92b460c81e'; $pluginData->currLang = 'en'; $pluginData->currLangLocale = 'en_US'; $pluginData->isPreview = ''; if (!function_exists('_spDefer_wrap_scripts')) { function _spDefer_wrap_scripts($output) { return preg_replace_callback('%<script\\b((?:(?!src=)[^>])*)>(.*?)</script>%is', function($m) { if (strpos($m[2], '_spDefer.done(') !== false) return $m[0]; if (strpos($m[2], 'window._spDefer') !== false) return $m[0]; return '<script>window._spDefer.add(function() {' . $m[2] . '});</script>'; }, $output); } } ob_start(); require dirname(__FILE__).'/main_mpesa.php'; echo _spDefer_wrap_scripts(ob_get_clean()); ?>
</div></div></div></div></div><div id="a19893e523630054d8651dab325610ff" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19893e5238c001df7f4b7e57e5624f6" class="wb_element wb-prevent-layout-click wb_gallery" data-plugin="Gallery"><script type="text/javascript">
			window._spDefer.add(function() {
				$(function() {
					(function(GalleryLib) {
						var el = document.getElementById("a19893e5238c001df7f4b7e57e5624f6");
						var lib = new GalleryLib({"id":"a19893e5238c001df7f4b7e57e5624f6","height":"auto","type":"list","trackResize":true,"interval":3,"speed":400,"images":[{"thumb":"gallery_gen\/304f547d8240773f1800797c8c981179_164x246_fill.jpg","src":"gallery_gen\/a8399cf429a9c98caa3cc480449585b2_fit.jpg?ts=1785686344","width":1024,"height":1536,"title":"","link":null,"description":"","address":""}],"border":{"border":"5px none #00008c"},"padding":10,"thumbWidth":100,"thumbHeight":100,"thumbAlign":"center","thumbPadding":6,"thumbAnim":"","thumbShadow":"","imageCover":true,"disablePopup":false,"controlsArrow":"chevron","controlsArrowSize":14,"controlsArrowStyle":{"normal":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}},"hover":{"color":"#DDDDDD","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#222222","forText":true,"css":{"text-shadow":"0px 0px 1px #222222"}}},"active":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}}},"slideOpacity":100,"showPictureCaption":"always","captionIncludeDescription":false,"captionPosition":"center bottom","mapTypeId":"roadmap","markerIconTypeId":"thumbs","zoom":2,"mapCenter":{"latLng":{"lat":41.244772343082,"lng":-5.2734375},"text":"41.244772343082076, -5.2734375"},"key":"AIzaSyD-9xtp38UunEmx7XfJ7eBh-K4w8qQ6SEw","theme":"default","color":"#eeeeee","showSatellite":true,"showZoom":true,"showStreetView":true,"showFullscreen":true,"allowDragging":true,"showRoads":true,"showLandmarks":true,"showLabels":true,"locale":"en_US","pauseOnHover":false});
						lib.appendTo(el);
					})(window.wbmodGalleryLib);
				});
			});
		</script></div><div id="a19893e523a400c4b38077c76e70331a" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom46"><span class="text-nocut">$ 20</span></h2>
</div><div id="a19893e523bc00cd1bc5904c373b20e4" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h1 class="wb-stl-custom45"><span class="text-nocut">Polo T-Shirts executive</span></h1>
</div><div id="a19893e523d4008d5974bf873994a947" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom43"><strong>"Wear the Spirit. Power the Festival." </strong></p>
</div><div id="a19893e523eb00dba9963044ef43172f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19893e523f8001e1b4f0e6fd7c5e030" class="wb_element wb-prevent-layout-click" data-plugin="BuyNow">


	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" data-gateway-id="Paypal" target="_blank" style="width: 100%; height: 100%;">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="">
		<input type="hidden" name="amount" value="1">
		<input type="hidden" name="currency_code" value="USD">
		<input type="hidden" name="button_subtype" value="services">
		<input type="hidden" name="no_note" value="0">
		<input type="hidden" name="shipping" value="">
		<input type="hidden" name="bn" value="JSCProfis_SP">
		<?php global $pluginData; $pluginData = json_decode('{"business":"","itemName":"","amount":"1","currencyCode":"USD","shipping":"","test":false,"html":"<form action=\\"https:\\/\\/www.paypal.com\\/cgi-bin\\/webscr\\" method=\\"post\\" target=\\"_blank\\" class=\\"paypal\\"><input type=\\"hidden\\" name=\\"cmd\\" value=\\"_xclick\\"><input type=\\"hidden\\" name=\\"business\\" value=\\"\\"><input type=\\"hidden\\" name=\\"lc\\" value=\\"US\\"><input type=\\"hidden\\" name=\\"item_name\\" value=\\"\\"><input type=\\"hidden\\" name=\\"amount\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"currency_code\\" value=\\"USD\\"><input type=\\"hidden\\" name=\\"button_subtype\\" value=\\"services\\"><input type=\\"hidden\\" name=\\"no_note\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"shipping\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"bn\\" value=\\"JSCProfis_SP\\"><input type=\\"image\\" src=\\"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/btn\\/btn_buynow_LG.gif\\" border=\\"0\\" name=\\"submit\\" alt=\\"PayPal - The safer, easier way to pay online!\\"><img alt=\\"\\" border=\\"0\\" src=\\"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/scr\\/pixel.gif\\" width=\\"1\\" height=\\"1\\"><\\/form>","__globalVars":["demo"],"logo":"paypal_color.svg","_locale":"en_US","button_label":"","button_color":"transparent","font_family":"Arial,Helvetica,sans-serif","font_size":14,"label_color":"#333333","button_border":{"differ":false,"color":["#eeeeee","#eeeeee","#eeeeee","#eeeeee"],"style":["none","none","none","none"],"weight":[1,1,1,1],"radius":null,"css":{"border":"1px none #eeeeee"},"cssRaw":"border: 1px none #eeeeee;"},"demo":false,"showlogo":true,"logo_width":107,"button_padding":0,"buttonBorderCss":"border-bottom: 1px none #eeeeee;border-left: 1px none #eeeeee;border-right: 1px none #eeeeee;border-top: 1px none #eeeeee;border-radius: 0px;-webkit-border-radius: 0px;-moz-border-radius: 0px;","remoteLogo":"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/btn\\/btn_buynow_LG.gif","logo_src":"gallery_gen\\/BuyNow\\/paypal_color.svg","paymentGatewayButton":"<button type=\\"submit\\" id=\\"a19893e523f8001e1b4f0e6fd7c5e030_payment_gateway_button\\" class=\\"btn btn-default btn-sm\\" style=\\"width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 0px;background-color: transparent;border: 1px none #eeeeee;border-radius: 0px 0px 0px 0px; -moz-border-radius: 0px 0px 0px 0px; -webkit-border-radius: 0px 0px 0px 0px;\\"><img src=\\"gallery_gen\\/BuyNow\\/paypal_color.svg\\" alt=\\"BuyNow\\" style=\\"width: 107px; max-width: 100%;\\" \\/><\\/button>","border_radius":{"lt":0,"rt":0,"rb":0,"lb":0,"differ":false,"css":{"border-radius":"0px 0px 0px 0px","-moz-border-radius":"0px 0px 0px 0px","-webkit-border-radius":"0px 0px 0px 0px"},"cssRaw":"border-radius: 0px 0px 0px 0px; -moz-border-radius: 0px 0px 0px 0px; -webkit-border-radius: 0px 0px 0px 0px;"},"clientId":"","clientSecret":"","button_icon":null}'); $pluginData->_extReferenceId = ''; $pluginData->elemId = 'a19893e523f8001e1b4f0e6fd7c5e030'; $pluginData->currLang = 'en'; $pluginData->currLangLocale = 'en_US'; $pluginData->isPreview = ''; if (!function_exists('_spDefer_wrap_scripts')) { function _spDefer_wrap_scripts($output) { return preg_replace_callback('%<script\\b((?:(?!src=)[^>])*)>(.*?)</script>%is', function($m) { if (strpos($m[2], '_spDefer.done(') !== false) return $m[0]; if (strpos($m[2], 'window._spDefer') !== false) return $m[0]; return '<script>window._spDefer.add(function() {' . $m[2] . '});</script>'; }, $output); } } ob_start(); require dirname(__FILE__).'/main_BuyNow.php'; echo _spDefer_wrap_scripts(ob_get_clean()); ?>
	</form>

</div><div id="a19893e5240f00f9a717f1047767a554" class="wb_element wb-prevent-layout-click" data-plugin="mpesa"><button type="submit" id="a19893e5240f00f9a717f1047767a554_payment_gateway_button" class="btn btn-default btn-sm" style="width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 4px;background-color: #ffffff;border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;"><strong style="display: block; color: #949494;font-family: Arial;font-size: 12px;">Pay with</strong><img src="gallery_gen/mpesa/mpesa.svg" alt="mpesa" style="width: 110px; max-width: 100%;"></button>

<div class="modal fade" id="mpesaModal_a19893e5240f00f9a717f1047767a554" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">Mpesa</h4>
            </div>
            <div class="modal-body">
                <form id="mpesaForm_a19893e5240f00f9a717f1047767a554" method="post" action="" data-gateway-id="Mpesa">
                    <div class="form-group">
                        <label>Phone number: <span style="color: #c00;">*</span></label>
                        <input type="text" class="form-control" name="phone" id="phone_a19893e5240f00f9a717f1047767a554" value="">
                    </div>
                    <div class="form-group">
                        <label>Country: <span style="color: #c00;">*</span></label>
                        <select class="form-control" id="country_a19893e5240f00f9a717f1047767a554" name="country" required="required">
                            <option value="GHA">Ghana</option>
                            <option value="TZN">Tanzania</option>
                            <option value="LES">Lesotho</option>
                            <option value="DRC">DR Congo</option>
                        </select>
                    </div>
                </form>
                <div id="mpesaCheckoutRender_a19893e5240f00f9a717f1047767a554"></div>
                <div id="mpesaError_a19893e5240f00f9a717f1047767a554" class="alert alert-danger"></div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button id="mpesaBackBtn_a19893e5240f00f9a717f1047767a554" type="button" class="btn btn-primary" onclick="showModal_a19893e5240f00f9a717f1047767a554()">Back</button>
                <button id="mpesaNextBtn_a19893e5240f00f9a717f1047767a554" type="button" class="btn btn-primary" onclick="startPaymentFlow_a19893e5240f00f9a717f1047767a554()">Pay</button>

            </div>
        </div>
    </div>
</div>
<?php global $pluginData; $pluginData = json_decode('{"__globalVars":["apiKey","isTest"],"apiKey":"","price":"","isTest":true,"providerCode":"","button_label":"Pay with","button_color":"#ffffff","font_family":"Arial","font_size":12,"label_color":"#949494","button_border":{"differ":false,"differRadius":false,"color":["#cccccc","#cccccc","#cccccc","#cccccc"],"style":["solid","solid","solid","solid"],"weight":[1,1,1,1],"css":{"border":"1px solid #cccccc","-moz-border-radius":"0px","-webkit-border-radius":"0px","border-radius":"0px"},"cssRaw":"border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;"},"logo":"mpesa.svg","showlogo":true,"logo_width":110,"border_radius":{"lt":0,"rt":0,"rb":0,"lb":0,"differ":false,"css":{"border-radius":"0px","-moz-border-radius":"0px","-webkit-border-radius":"0px"},"cssRaw":"border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;"},"button_padding":4,"logo_src":"gallery_gen\\/mpesa\\/mpesa.svg","paymentGatewayButton":"<button type=\\"submit\\" id=\\"a19893e5240f00f9a717f1047767a554_payment_gateway_button\\" class=\\"btn btn-default btn-sm\\" style=\\"width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 4px;background-color: #ffffff;border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;\\"><strong style=\\"display: block; color: #949494;font-family: Arial;font-size: 12px;\\">Pay with<\\/strong><img src=\\"gallery_gen\\/mpesa\\/mpesa.svg\\" alt=\\"mpesa\\" style=\\"width: 110px; max-width: 100%;\\" \\/><\\/button>","button_icon":null}'); $pluginData->_extReferenceId = ''; $pluginData->elemId = 'a19893e5240f00f9a717f1047767a554'; $pluginData->currLang = 'en'; $pluginData->currLangLocale = 'en_US'; $pluginData->isPreview = ''; if (!function_exists('_spDefer_wrap_scripts')) { function _spDefer_wrap_scripts($output) { return preg_replace_callback('%<script\\b((?:(?!src=)[^>])*)>(.*?)</script>%is', function($m) { if (strpos($m[2], '_spDefer.done(') !== false) return $m[0]; if (strpos($m[2], 'window._spDefer') !== false) return $m[0]; return '<script>window._spDefer.add(function() {' . $m[2] . '});</script>'; }, $output); } } ob_start(); require dirname(__FILE__).'/main_mpesa.php'; echo _spDefer_wrap_scripts(ob_get_clean()); ?>
</div></div></div></div></div><div id="a1989411fa20005c5a352433e805825c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989411fa4d003e58ed2c1b4021b228" class="wb_element wb-prevent-layout-click wb_gallery" data-plugin="Gallery"><script type="text/javascript">
			window._spDefer.add(function() {
				$(function() {
					(function(GalleryLib) {
						var el = document.getElementById("a1989411fa4d003e58ed2c1b4021b228");
						var lib = new GalleryLib({"id":"a1989411fa4d003e58ed2c1b4021b228","height":"auto","type":"list","trackResize":true,"interval":3,"speed":400,"images":[{"thumb":"gallery_gen\/0f99d60c585e1789557bfb6b9602db38_164x246_fill.jpg","src":"gallery_gen\/7f3b0a326986861505b573f5e9d5d284_fit.jpg?ts=1785686344","width":1024,"height":1536,"title":"","link":null,"description":"","address":""}],"border":{"border":"5px none #00008c"},"padding":10,"thumbWidth":100,"thumbHeight":100,"thumbAlign":"center","thumbPadding":6,"thumbAnim":"","thumbShadow":"","imageCover":true,"disablePopup":false,"controlsArrow":"chevron","controlsArrowSize":14,"controlsArrowStyle":{"normal":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}},"hover":{"color":"#DDDDDD","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#222222","forText":true,"css":{"text-shadow":"0px 0px 1px #222222"}}},"active":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}}},"slideOpacity":100,"showPictureCaption":"always","captionIncludeDescription":false,"captionPosition":"center bottom","mapTypeId":"roadmap","markerIconTypeId":"thumbs","zoom":2,"mapCenter":{"latLng":{"lat":41.244772343082,"lng":-5.2734375},"text":"41.244772343082076, -5.2734375"},"key":"AIzaSyD-9xtp38UunEmx7XfJ7eBh-K4w8qQ6SEw","theme":"default","color":"#eeeeee","showSatellite":true,"showZoom":true,"showStreetView":true,"showFullscreen":true,"allowDragging":true,"showRoads":true,"showLandmarks":true,"showLabels":true,"locale":"en_US","pauseOnHover":false});
						lib.appendTo(el);
					})(window.wbmodGalleryLib);
				});
			});
		</script></div><div id="a1989411fa650018d3a2ce5297ec8953" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom46"><span class="text-nocut">$ 10</span></h2>
</div><div id="a1989411fa7d002ca41ac6cec222be29" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h1 class="wb-stl-custom45"><span class="text-nocut">Polo T-Shirts</span></h1>
</div><div id="a19894342075009199636ec2d7109ab9" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom43"><strong>"Wear the Spirit. Power the Festival." </strong></p>
</div><div id="a1989411faad00350960f118e0f19e5d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989411faba000d2bb52e38b6a7e91e" class="wb_element wb-prevent-layout-click" data-plugin="BuyNow">


	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" data-gateway-id="Paypal" target="_blank" style="width: 100%; height: 100%;">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="">
		<input type="hidden" name="amount" value="1">
		<input type="hidden" name="currency_code" value="USD">
		<input type="hidden" name="button_subtype" value="services">
		<input type="hidden" name="no_note" value="0">
		<input type="hidden" name="shipping" value="">
		<input type="hidden" name="bn" value="JSCProfis_SP">
		<?php global $pluginData; $pluginData = json_decode('{"business":"","itemName":"","amount":"1","currencyCode":"USD","shipping":"","test":false,"html":"<form action=\\"https:\\/\\/www.paypal.com\\/cgi-bin\\/webscr\\" method=\\"post\\" target=\\"_blank\\" class=\\"paypal\\"><input type=\\"hidden\\" name=\\"cmd\\" value=\\"_xclick\\"><input type=\\"hidden\\" name=\\"business\\" value=\\"\\"><input type=\\"hidden\\" name=\\"lc\\" value=\\"US\\"><input type=\\"hidden\\" name=\\"item_name\\" value=\\"\\"><input type=\\"hidden\\" name=\\"amount\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"currency_code\\" value=\\"USD\\"><input type=\\"hidden\\" name=\\"button_subtype\\" value=\\"services\\"><input type=\\"hidden\\" name=\\"no_note\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"shipping\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"bn\\" value=\\"JSCProfis_SP\\"><input type=\\"image\\" src=\\"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/btn\\/btn_buynow_LG.gif\\" border=\\"0\\" name=\\"submit\\" alt=\\"PayPal - The safer, easier way to pay online!\\"><img alt=\\"\\" border=\\"0\\" src=\\"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/scr\\/pixel.gif\\" width=\\"1\\" height=\\"1\\"><\\/form>","__globalVars":["demo"],"logo":"paypal_color.svg","_locale":"en_US","button_label":"","button_color":"transparent","font_family":"Arial,Helvetica,sans-serif","font_size":14,"label_color":"#333333","button_border":{"differ":false,"color":["#eeeeee","#eeeeee","#eeeeee","#eeeeee"],"style":["none","none","none","none"],"weight":[1,1,1,1],"radius":null,"css":{"border":"1px none #eeeeee"},"cssRaw":"border: 1px none #eeeeee;"},"demo":false,"showlogo":true,"logo_width":107,"button_padding":0,"buttonBorderCss":"border-bottom: 1px none #eeeeee;border-left: 1px none #eeeeee;border-right: 1px none #eeeeee;border-top: 1px none #eeeeee;border-radius: 0px;-webkit-border-radius: 0px;-moz-border-radius: 0px;","remoteLogo":"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/btn\\/btn_buynow_LG.gif","logo_src":"gallery_gen\\/BuyNow\\/paypal_color.svg","paymentGatewayButton":"<button type=\\"submit\\" id=\\"a1989411faba000d2bb52e38b6a7e91e_payment_gateway_button\\" class=\\"btn btn-default btn-sm\\" style=\\"width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 0px;background-color: transparent;border: 1px none #eeeeee;border-radius: 0px 0px 0px 0px; -moz-border-radius: 0px 0px 0px 0px; -webkit-border-radius: 0px 0px 0px 0px;\\"><img src=\\"gallery_gen\\/BuyNow\\/paypal_color.svg\\" alt=\\"BuyNow\\" style=\\"width: 107px; max-width: 100%;\\" \\/><\\/button>","border_radius":{"lt":0,"rt":0,"rb":0,"lb":0,"differ":false,"css":{"border-radius":"0px 0px 0px 0px","-moz-border-radius":"0px 0px 0px 0px","-webkit-border-radius":"0px 0px 0px 0px"},"cssRaw":"border-radius: 0px 0px 0px 0px; -moz-border-radius: 0px 0px 0px 0px; -webkit-border-radius: 0px 0px 0px 0px;"},"clientId":"","clientSecret":"","button_icon":null}'); $pluginData->_extReferenceId = ''; $pluginData->elemId = 'a1989411faba000d2bb52e38b6a7e91e'; $pluginData->currLang = 'en'; $pluginData->currLangLocale = 'en_US'; $pluginData->isPreview = ''; if (!function_exists('_spDefer_wrap_scripts')) { function _spDefer_wrap_scripts($output) { return preg_replace_callback('%<script\\b((?:(?!src=)[^>])*)>(.*?)</script>%is', function($m) { if (strpos($m[2], '_spDefer.done(') !== false) return $m[0]; if (strpos($m[2], 'window._spDefer') !== false) return $m[0]; return '<script>window._spDefer.add(function() {' . $m[2] . '});</script>'; }, $output); } } ob_start(); require dirname(__FILE__).'/main_BuyNow.php'; echo _spDefer_wrap_scripts(ob_get_clean()); ?>
	</form>

</div><div id="a1989411fad200988bf0596e965afbb6" class="wb_element wb-prevent-layout-click" data-plugin="mpesa"><button type="submit" id="a1989411fad200988bf0596e965afbb6_payment_gateway_button" class="btn btn-default btn-sm" style="width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 4px;background-color: #ffffff;border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;"><strong style="display: block; color: #949494;font-family: Arial;font-size: 12px;">Pay with</strong><img src="gallery_gen/mpesa/mpesa.svg" alt="mpesa" style="width: 110px; max-width: 100%;"></button>

<div class="modal fade" id="mpesaModal_a1989411fad200988bf0596e965afbb6" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">Mpesa</h4>
            </div>
            <div class="modal-body">
                <form id="mpesaForm_a1989411fad200988bf0596e965afbb6" method="post" action="" data-gateway-id="Mpesa">
                    <div class="form-group">
                        <label>Phone number: <span style="color: #c00;">*</span></label>
                        <input type="text" class="form-control" name="phone" id="phone_a1989411fad200988bf0596e965afbb6" value="">
                    </div>
                    <div class="form-group">
                        <label>Country: <span style="color: #c00;">*</span></label>
                        <select class="form-control" id="country_a1989411fad200988bf0596e965afbb6" name="country" required="required">
                            <option value="GHA">Ghana</option>
                            <option value="TZN">Tanzania</option>
                            <option value="LES">Lesotho</option>
                            <option value="DRC">DR Congo</option>
                        </select>
                    </div>
                </form>
                <div id="mpesaCheckoutRender_a1989411fad200988bf0596e965afbb6"></div>
                <div id="mpesaError_a1989411fad200988bf0596e965afbb6" class="alert alert-danger"></div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button id="mpesaBackBtn_a1989411fad200988bf0596e965afbb6" type="button" class="btn btn-primary" onclick="showModal_a1989411fad200988bf0596e965afbb6()">Back</button>
                <button id="mpesaNextBtn_a1989411fad200988bf0596e965afbb6" type="button" class="btn btn-primary" onclick="startPaymentFlow_a1989411fad200988bf0596e965afbb6()">Pay</button>

            </div>
        </div>
    </div>
</div>
<?php global $pluginData; $pluginData = json_decode('{"__globalVars":["apiKey","isTest"],"apiKey":"Kitenge","price":"25","isTest":true,"providerCode":"Vodacom","button_label":"Pay with","button_color":"#ffffff","font_family":"Arial","font_size":12,"label_color":"#949494","button_border":{"differ":false,"differRadius":false,"color":["#cccccc","#cccccc","#cccccc","#cccccc"],"style":["solid","solid","solid","solid"],"weight":[1,1,1,1],"css":{"border":"1px solid #cccccc","-moz-border-radius":"0px","-webkit-border-radius":"0px","border-radius":"0px"},"cssRaw":"border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;"},"logo":"mpesa.svg","showlogo":true,"logo_width":110,"border_radius":{"lt":0,"rt":0,"rb":0,"lb":0,"differ":false,"css":{"border-radius":"0px","-moz-border-radius":"0px","-webkit-border-radius":"0px"},"cssRaw":"border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;"},"button_padding":4,"logo_src":"gallery_gen\\/mpesa\\/mpesa.svg","paymentGatewayButton":"<button type=\\"submit\\" id=\\"a1989411fad200988bf0596e965afbb6_payment_gateway_button\\" class=\\"btn btn-default btn-sm\\" style=\\"width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 4px;background-color: #ffffff;border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;\\"><strong style=\\"display: block; color: #949494;font-family: Arial;font-size: 12px;\\">Pay with<\\/strong><img src=\\"gallery_gen\\/mpesa\\/mpesa.svg\\" alt=\\"mpesa\\" style=\\"width: 110px; max-width: 100%;\\" \\/><\\/button>","itemName":"Kitenge","button_icon":null}'); $pluginData->_extReferenceId = ''; $pluginData->elemId = 'a1989411fad200988bf0596e965afbb6'; $pluginData->currLang = 'en'; $pluginData->currLangLocale = 'en_US'; $pluginData->isPreview = ''; if (!function_exists('_spDefer_wrap_scripts')) { function _spDefer_wrap_scripts($output) { return preg_replace_callback('%<script\\b((?:(?!src=)[^>])*)>(.*?)</script>%is', function($m) { if (strpos($m[2], '_spDefer.done(') !== false) return $m[0]; if (strpos($m[2], 'window._spDefer') !== false) return $m[0]; return '<script>window._spDefer.add(function() {' . $m[2] . '});</script>'; }, $output); } } ob_start(); require dirname(__FILE__).'/main_mpesa.php'; echo _spDefer_wrap_scripts(ob_get_clean()); ?>
</div></div></div></div></div><div id="a19893e4fce900f9f214a57e83d36fa0" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19893e4fd1f003156843115a88547a3" class="wb_element wb-prevent-layout-click wb_gallery" data-plugin="Gallery"><script type="text/javascript">
			window._spDefer.add(function() {
				$(function() {
					(function(GalleryLib) {
						var el = document.getElementById("a19893e4fd1f003156843115a88547a3");
						var lib = new GalleryLib({"id":"a19893e4fd1f003156843115a88547a3","height":"auto","type":"list","trackResize":true,"interval":3,"speed":400,"images":[{"thumb":"gallery_gen\/aa6ef1dd23e54854a49557c3e51d8e80_250.37239583333x158_fill.jpg","src":"gallery_gen\/5bb0074c87228ab415c88e389bd7b3af_fit.jpg?ts=1785686344","width":1217,"height":768,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/8c344bdf47ed64e6cb0451804f3b2a14_246x164_fill.jpg","src":"gallery_gen\/bd6fe3b750a72979eab769c0f74699e6_fit.jpg?ts=1785686344","width":1200,"height":800,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/2e13d4621d07ce3270573517955700f6_246x164_fill.jpg","src":"gallery_gen\/420151faf73ee01df91144d65d3280d7_fit.jpg?ts=1785686344","width":1200,"height":800,"title":"","link":null,"description":"","address":""}],"border":{"border":"5px none #00008c"},"padding":10,"thumbWidth":100,"thumbHeight":100,"thumbAlign":"center","thumbPadding":6,"thumbAnim":"","thumbShadow":"","imageCover":true,"disablePopup":false,"controlsArrow":"chevron","controlsArrowSize":14,"controlsArrowStyle":{"normal":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}},"hover":{"color":"#DDDDDD","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#222222","forText":true,"css":{"text-shadow":"0px 0px 1px #222222"}}},"active":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}}},"slideOpacity":100,"showPictureCaption":"always","captionIncludeDescription":false,"captionPosition":"center bottom","mapTypeId":"roadmap","markerIconTypeId":"thumbs","zoom":2,"mapCenter":{"latLng":{"lat":41.244772343082,"lng":-5.2734375},"text":"41.244772343082076, -5.2734375"},"key":"AIzaSyD-9xtp38UunEmx7XfJ7eBh-K4w8qQ6SEw","theme":"default","color":"#eeeeee","showSatellite":true,"showZoom":true,"showStreetView":true,"showFullscreen":true,"allowDragging":true,"showRoads":true,"showLandmarks":true,"showLabels":true,"locale":"en_US","pauseOnHover":false});
						lib.appendTo(el);
					})(window.wbmodGalleryLib);
				});
			});
		</script></div><div id="a19893e4fd3d00e53bd0f88cb9d7cdcd" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom46"><span class="text-nocut">$ 25</span></h2>
</div><div id="a19893e4fd5900498e6d75013054703a" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h1 class="wb-stl-custom45"><span class="text-nocut">Kitenge (cotton Wax)</span></h1>
</div><div id="a19893e4fd71003efc4758b62db22c2d" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom43"><strong>Wrap in Heritage. Celebrate the Festival</strong></p>
</div><div id="a19893e4fd87008cdad00bcd282dfafc" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19893e4fd950049efe4a6a0e880006f" class="wb_element wb-prevent-layout-click" data-plugin="BuyNow">


	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" data-gateway-id="Paypal" target="_blank" style="width: 100%; height: 100%;">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="">
		<input type="hidden" name="amount" value="1">
		<input type="hidden" name="currency_code" value="USD">
		<input type="hidden" name="button_subtype" value="services">
		<input type="hidden" name="no_note" value="0">
		<input type="hidden" name="shipping" value="">
		<input type="hidden" name="bn" value="JSCProfis_SP">
		<?php global $pluginData; $pluginData = json_decode('{"business":"","itemName":"","amount":"1","currencyCode":"USD","shipping":"","test":false,"html":"<form action=\\"https:\\/\\/www.paypal.com\\/cgi-bin\\/webscr\\" method=\\"post\\" target=\\"_blank\\" class=\\"paypal\\"><input type=\\"hidden\\" name=\\"cmd\\" value=\\"_xclick\\"><input type=\\"hidden\\" name=\\"business\\" value=\\"\\"><input type=\\"hidden\\" name=\\"lc\\" value=\\"US\\"><input type=\\"hidden\\" name=\\"item_name\\" value=\\"\\"><input type=\\"hidden\\" name=\\"amount\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"currency_code\\" value=\\"USD\\"><input type=\\"hidden\\" name=\\"button_subtype\\" value=\\"services\\"><input type=\\"hidden\\" name=\\"no_note\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"shipping\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"bn\\" value=\\"JSCProfis_SP\\"><input type=\\"image\\" src=\\"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/btn\\/btn_buynow_LG.gif\\" border=\\"0\\" name=\\"submit\\" alt=\\"PayPal - The safer, easier way to pay online!\\"><img alt=\\"\\" border=\\"0\\" src=\\"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/scr\\/pixel.gif\\" width=\\"1\\" height=\\"1\\"><\\/form>","__globalVars":["demo"],"logo":"paypal_color.svg","_locale":"en_US","button_label":"","button_color":"transparent","font_family":"Arial,Helvetica,sans-serif","font_size":14,"label_color":"#333333","button_border":{"differ":false,"color":["#eeeeee","#eeeeee","#eeeeee","#eeeeee"],"style":["none","none","none","none"],"weight":[1,1,1,1],"radius":null,"css":{"border":"1px none #eeeeee"},"cssRaw":"border: 1px none #eeeeee;"},"demo":false,"showlogo":true,"logo_width":107,"button_padding":0,"buttonBorderCss":"border-bottom: 1px none #eeeeee;border-left: 1px none #eeeeee;border-right: 1px none #eeeeee;border-top: 1px none #eeeeee;border-radius: 0px;-webkit-border-radius: 0px;-moz-border-radius: 0px;","remoteLogo":"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/btn\\/btn_buynow_LG.gif","logo_src":"gallery_gen\\/BuyNow\\/paypal_color.svg","paymentGatewayButton":"<button type=\\"submit\\" id=\\"a19893e4fd950049efe4a6a0e880006f_payment_gateway_button\\" class=\\"btn btn-default btn-sm\\" style=\\"width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 0px;background-color: transparent;border: 1px none #eeeeee;border-radius: 0px 0px 0px 0px; -moz-border-radius: 0px 0px 0px 0px; -webkit-border-radius: 0px 0px 0px 0px;\\"><img src=\\"gallery_gen\\/BuyNow\\/paypal_color.svg\\" alt=\\"BuyNow\\" style=\\"width: 107px; max-width: 100%;\\" \\/><\\/button>","border_radius":{"lt":0,"rt":0,"rb":0,"lb":0,"differ":false,"css":{"border-radius":"0px 0px 0px 0px","-moz-border-radius":"0px 0px 0px 0px","-webkit-border-radius":"0px 0px 0px 0px"},"cssRaw":"border-radius: 0px 0px 0px 0px; -moz-border-radius: 0px 0px 0px 0px; -webkit-border-radius: 0px 0px 0px 0px;"},"clientId":"","clientSecret":"","button_icon":null}'); $pluginData->_extReferenceId = ''; $pluginData->elemId = 'a19893e4fd950049efe4a6a0e880006f'; $pluginData->currLang = 'en'; $pluginData->currLangLocale = 'en_US'; $pluginData->isPreview = ''; if (!function_exists('_spDefer_wrap_scripts')) { function _spDefer_wrap_scripts($output) { return preg_replace_callback('%<script\\b((?:(?!src=)[^>])*)>(.*?)</script>%is', function($m) { if (strpos($m[2], '_spDefer.done(') !== false) return $m[0]; if (strpos($m[2], 'window._spDefer') !== false) return $m[0]; return '<script>window._spDefer.add(function() {' . $m[2] . '});</script>'; }, $output); } } ob_start(); require dirname(__FILE__).'/main_BuyNow.php'; echo _spDefer_wrap_scripts(ob_get_clean()); ?>
	</form>

</div><div id="a19893e4fdad000e03fe9ad5a7218876" class="wb_element wb-prevent-layout-click" data-plugin="mpesa"><button type="submit" id="a19893e4fdad000e03fe9ad5a7218876_payment_gateway_button" class="btn btn-default btn-sm" style="width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 4px;background-color: #ffffff;border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;"><strong style="display: block; color: #949494;font-family: Arial;font-size: 12px;">Pay with</strong><img src="gallery_gen/mpesa/mpesa.svg" alt="mpesa" style="width: 110px; max-width: 100%;"></button>

<div class="modal fade" id="mpesaModal_a19893e4fdad000e03fe9ad5a7218876" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">Mpesa</h4>
            </div>
            <div class="modal-body">
                <form id="mpesaForm_a19893e4fdad000e03fe9ad5a7218876" method="post" action="" data-gateway-id="Mpesa">
                    <div class="form-group">
                        <label>Phone number: <span style="color: #c00;">*</span></label>
                        <input type="text" class="form-control" name="phone" id="phone_a19893e4fdad000e03fe9ad5a7218876" value="">
                    </div>
                    <div class="form-group">
                        <label>Country: <span style="color: #c00;">*</span></label>
                        <select class="form-control" id="country_a19893e4fdad000e03fe9ad5a7218876" name="country" required="required">
                            <option value="GHA">Ghana</option>
                            <option value="TZN">Tanzania</option>
                            <option value="LES">Lesotho</option>
                            <option value="DRC">DR Congo</option>
                        </select>
                    </div>
                </form>
                <div id="mpesaCheckoutRender_a19893e4fdad000e03fe9ad5a7218876"></div>
                <div id="mpesaError_a19893e4fdad000e03fe9ad5a7218876" class="alert alert-danger"></div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button id="mpesaBackBtn_a19893e4fdad000e03fe9ad5a7218876" type="button" class="btn btn-primary" onclick="showModal_a19893e4fdad000e03fe9ad5a7218876()">Back</button>
                <button id="mpesaNextBtn_a19893e4fdad000e03fe9ad5a7218876" type="button" class="btn btn-primary" onclick="startPaymentFlow_a19893e4fdad000e03fe9ad5a7218876()">Pay</button>

            </div>
        </div>
    </div>
</div>
<?php global $pluginData; $pluginData = json_decode('{"__globalVars":["apiKey","isTest"],"apiKey":"","price":"","isTest":true,"providerCode":"","button_label":"Pay with","button_color":"#ffffff","font_family":"Arial","font_size":12,"label_color":"#949494","button_border":{"differ":false,"differRadius":false,"color":["#cccccc","#cccccc","#cccccc","#cccccc"],"style":["solid","solid","solid","solid"],"weight":[1,1,1,1],"css":{"border":"1px solid #cccccc","-moz-border-radius":"0px","-webkit-border-radius":"0px","border-radius":"0px"},"cssRaw":"border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;"},"logo":"mpesa.svg","showlogo":true,"logo_width":110,"border_radius":{"lt":0,"rt":0,"rb":0,"lb":0,"differ":false,"css":{"border-radius":"0px","-moz-border-radius":"0px","-webkit-border-radius":"0px"},"cssRaw":"border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;"},"button_padding":4,"logo_src":"gallery_gen\\/mpesa\\/mpesa.svg","paymentGatewayButton":"<button type=\\"submit\\" id=\\"a19893e4fdad000e03fe9ad5a7218876_payment_gateway_button\\" class=\\"btn btn-default btn-sm\\" style=\\"width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 4px;background-color: #ffffff;border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;\\"><strong style=\\"display: block; color: #949494;font-family: Arial;font-size: 12px;\\">Pay with<\\/strong><img src=\\"gallery_gen\\/mpesa\\/mpesa.svg\\" alt=\\"mpesa\\" style=\\"width: 110px; max-width: 100%;\\" \\/><\\/button>","button_icon":null}'); $pluginData->_extReferenceId = ''; $pluginData->elemId = 'a19893e4fdad000e03fe9ad5a7218876'; $pluginData->currLang = 'en'; $pluginData->currLangLocale = 'en_US'; $pluginData->isPreview = ''; if (!function_exists('_spDefer_wrap_scripts')) { function _spDefer_wrap_scripts($output) { return preg_replace_callback('%<script\\b((?:(?!src=)[^>])*)>(.*?)</script>%is', function($m) { if (strpos($m[2], '_spDefer.done(') !== false) return $m[0]; if (strpos($m[2], 'window._spDefer') !== false) return $m[0]; return '<script>window._spDefer.add(function() {' . $m[2] . '});</script>'; }, $output); } } ob_start(); require dirname(__FILE__).'/main_mpesa.php'; echo _spDefer_wrap_scripts(ob_get_clean()); ?>
</div></div></div></div></div></div></div><div id="a198d33bd6bd009dd5086abaef87b394" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"></div></div><div id="a1988e3c7ceb008a7fcae0435be2809d" class="wb_element" data-plugin="Button"><a class="wb_button" href="Homeb/"><span><svg xmlns="http://www.w3.org/2000/svg" width="1793.982" height="1793.982" viewBox="0 0 1793.982 1793.982" style="display: inline-block; vertical-align: middle; position: relative; top: -1px; height: 1em; width: 1em; overflow: visible; direction: ltr;"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg>&nbsp;Home Page</span></a></div></div></div><div id="wb_footer_a188dd9eef5300bb9a9e9122025694a7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fc2149cd000eb3b8848562ec6f176" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc386d7d4d77961b3399b7e7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19fb429723a03cab5671bd0692f5610" class="wb_element" data-plugin="Button"><a class="wb_button" href="{{base_url}}"><span><svg xmlns="http://www.w3.org/2000/svg" width="1793.982" height="1793.982" viewBox="0 0 1793.982 1793.982" style="display: inline-block; vertical-align: middle; position: relative; top: -1px; height: 1em; width: 1em; overflow: visible; direction: ltr;"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg>&nbsp;Home Page</span></a></div><div id="a188dd9ebc386e9c761088b65418f7a1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc386f7f651dc7e4d0792624" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="Homeb/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#4be6e6"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a188dd9ebc38700f452a2fef2fcabe01" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="Homeb/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1024 1024" style="direction: ltr; color:#ffffff"><text x="64" y="960" font-size="1024" fill="currentColor" style='font-family: "builder-ui-icons-plugins"'></text></svg></a></div></div></div><div id="a188dd9ebc3871cfcba1a4cf7091cb6d" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="Homeb/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#ffffff"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div></div></div><div id="a19fc20bdb7e00c6080e244c0b41b351" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-custom16" style="text-align: center;">ADDRESS:</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">P,O BOX  DAR- ES - SALAAM, TANZANIA</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">PHONE: +255 746 174403 +255 789  388232 +255 719 083050</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">EMAIL: jukanyefestival@gmail.com</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">info@jukanye.com</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">WEBSITE: www.jukanye.com</h3>
</div><div id="a188dd9ebc38721835f60daecdc81bab" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/3a0fa4358ae2f4fb06a94eaab03b4403_fit.png?ts=1785686344"></div></div></div><div id="a19fc20a045f00e06f9422396398c49c" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-footer">© 2025 <a href="http://jukanye.com">jukanye.com</a> - Honoring Africa’s True Patriots and Heroes.</p>
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
	<script src="js/a188dd9eef5300bb9a9e9122025694a7-bundle.js?ts=20260802185857" type="text/javascript" defer></script>{{hr_out}}<script>
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
	<title><?php echo htmlspecialchars((isset($seoTitle) && $seoTitle !== "") ? $seoTitle : "Mwanzo"); ?></title>
	<base href="{{base_url}}" />
	<?php echo isset($sitemapUrls) ? (generateCanonicalUrl($sitemapUrls)."\n") : ""; ?>	
		<link rel="alternate" hreflang="en" href="{{base_url}}{{lang_en}}" />
		<link rel="alternate" hreflang="x-default" href="{{base_url}}{{lang_en}}" />
			<link rel="alternate" hreflang="sw" href="{{base_url}}{{lang_sw}}" />
		
						<meta name="viewport" content="width=device-width, initial-scale=1" />
					<meta name="description" content="<?php echo htmlspecialchars((isset($seoDescription) && $seoDescription !== "") ? $seoDescription : "JUKANYE International  Festival \nCelebrating African Liberation, Legacy, and Unity"); ?>" />
			<meta name="keywords" content="<?php echo htmlspecialchars((isset($seoKeywords) && $seoKeywords !== "") ? $seoKeywords : "JUKANYE Festival,African liberation history,Tanzania cultural events,Julius Nyerere International Festival,African leaders tribute,African independence celebration,Pan-Africanism events,African heritage exhibitions,African music performances,historical tours Tanzania,Mama Afrika Award,African women freedom fighters,African cultural festivals,Tanzania tourism events,African history seminars,youth empowerment Africa,sustainable energy Tanzania,clean cooking energy awareness,African cultural dress showcase,African heritage digitization,African unity initiatives,African arts and culture,historical sites Tanzania,African peace and reconciliation,African storytelling festivals,promoting Kiswahili language,African tourism promotion,African history documentaries,African patriotism events,African development conferences,international African festivals,African community health programs,African cultural dialogue,African leadership awards,African youth forums,African historical documentaries,African environmental conservation"); ?>" />
			
	<!-- Facebook Open Graph -->
		<meta property="og:title" content="<?php echo htmlspecialchars((isset($seoTitle) && $seoTitle !== "") ? $seoTitle : "Mwanzo"); ?>" />
			<meta property="og:description" content="<?php echo htmlspecialchars((isset($seoDescription) && $seoDescription !== "") ? $seoDescription : "JUKANYE International  Festival \nCelebrating African Liberation, Legacy, and Unity"); ?>" />
			<meta property="og:image" content="<?php echo htmlspecialchars((isset($seoImage) && $seoImage !== "") ? "{{base_url}}".$seoImage : ""); ?>" />
			<meta property="og:type" content="article" />
			<meta property="og:url" content="__wb_curr_url__" />
		<!-- Facebook Open Graph end -->

		<meta name="generator" content="Website Builder" />
			<link href="css/common-bundle.css?ts=20260802185857" rel="stylesheet" type="text/css" />
	<link href="css/a188dd9eef5300bb9a9e9122025694a7-bundle.css?ts=20260802185857" rel="stylesheet" type="text/css" id="wb-page-stylesheet" />
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


<body class="site site-lang-sw<?php if (isset($wbPopupMode) && $wbPopupMode) echo ' popup-mode'; ?> " <?php ?>><div id="wb_root" class="root wb-layout-vertical"><div class="wb_sbg"></div><div id="wb_header_a188dd9eef5300bb9a9e9122025694a7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc3858a7a4bf4599d6087d14" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc38596f36338d0b0d66657b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1986657436700dbe63ba0cbad5bbe2c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fb4223ec700f33f6a6750b25b7549" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fc26ad9c300737c8a0c139e48b498" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc385abbb04767f5aaa74a38" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/63a123b911049cc657f1d0f2a9cc7765_fit.png?ts=1785686345"></div></div></div><div id="a19fb4297212030bdabc97de04dae2a0" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom49">TAMASHA LA KIMATAIFA LA JULIUS KAMBARAGE NYERERE</h2>
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
						var lib = new GalleryLib({"id":"a19fb8f3c64000b0747d009eda7d1a44","height":"auto","type":"slideshow","trackResize":true,"interval":5,"speed":1000,"images":[{"thumb":"gallery_gen\/9147f62c31174403cafdbe5847fd40e4_301.5x134_fill.png","src":"gallery_gen\/a0295deaa452d91f264f568d7ace6a7c_fit.png?ts=1785686345","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/f3e0a489b3b22ccf940c58dffbcd2ad4_301.5x134_fill.jpg","src":"gallery_gen\/2a406b85dd90631c40b79158c1877d4f_fit.jpg?ts=1785686345","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/da8e24800b8f72dd8eed800429e1a18b_301.5x134_fill.jpg","src":"gallery_gen\/3c456088697ef08011819b714ae09234_fit.jpg?ts=1785686345","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/d362c813c5330d042dde3a964f0bfed1_301.5x134_fill.jpg","src":"gallery_gen\/30ce731cc7b1cc1edd84ddce750a6366_fit.jpg?ts=1785686345","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/33145b78952db630d35b79ec91eed8d5_301.5x134_fill.jpg","src":"gallery_gen\/47e964e8cdbbdbffac1cc75dec2c4369_fit.jpg?ts=1785686345","width":1881,"height":836,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/72018fdb993c6ceb781c0740d2917da8_301.5x134_fill.jpg","src":"gallery_gen\/a55bfef5daf82a78f393f684c67908ca_fit.jpg?ts=1785686345","width":1881,"height":836,"title":"","link":null,"description":"","address":""}],"border":{"border":"5px none #00008c"},"padding":10,"thumbWidth":100,"thumbHeight":100,"thumbAlign":"center","thumbPadding":6,"thumbAnim":"","thumbShadow":"","imageCover":true,"disablePopup":false,"controlsArrow":"chevron","controlsArrowSize":14,"controlsArrowStyle":{"normal":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}},"hover":{"color":"#DDDDDD","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#222222","forText":true,"css":{"text-shadow":"0px 0px 1px #222222"}}},"active":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}}},"slideOpacity":100,"showPictureCaption":"always","captionIncludeDescription":false,"captionPosition":"center bottom","mapTypeId":"roadmap","markerIconTypeId":"thumbs","zoom":2,"mapCenter":{"latLng":{"lat":41.244772343082,"lng":-5.2734375},"text":"41.244772343082076, -5.2734375"},"key":"AIzaSyD-9xtp38UunEmx7XfJ7eBh-K4w8qQ6SEw","theme":"default","color":"#eeeeee","showSatellite":true,"showZoom":true,"showStreetView":true,"showFullscreen":true,"allowDragging":true,"showRoads":true,"showLandmarks":true,"showLabels":true,"locale":"sw_TZ","pauseOnHover":true});
						lib.appendTo(el);
					})(window.wbmodGalleryLib);
				});
			});
		</script></div></div></div></div></div><div id="a19fb429722400fb62f16c17777e0dbd" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19fb42971f400a1d073d65740953b98" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Jisajiri/"><span>Jisajiri Kushiriki</span></a></div><div id="a19fb429722c00d2df553faa4f96bb89" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Bidhaa-za-Tamasha/"><span>Bidhaa</span></a></div><div id="a19fb4297209006e0ba9445e1db2f558" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Waliopendekezwa-kupewa-Tuzo/"><span>Walio pendekezwa Kupata Tuzo</span></a></div></div></div></div></div><div id="a19fb81fa1120059e7c1682d66b9ba06" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"></div></div></div></div></div></div></div></div><div id="wb_main_a188dd9eef5300bb9a9e9122025694a7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc381ac1f603a855675c2f4a" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc381bf31c5e5985a7b3ea71" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19def0680eb0ea66f427251975247fd" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"></div></div><div id="a188dd9ebc381c9681aec4944a3acde8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19864bf86d6000d2cbb532a4358dd95" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19864bf86d900f3fc2fd1e613794f3d" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2"> </h2>

<h2 class="wb-stl-heading2"> </h2>

<h2 class="wb-stl-heading2">TAMASHA LA KIMATAIFA LA  JULIUS KAMABARAGE NYERERE</h2>
</div><div id="a19880bfdac60043a43e6b84ff9e83d2" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-heading3">Safari ya Afrika kuelekea uhuru ni hadithi ya ujasiri, kujitolea, na maono yasiyotetereka. Kuanzia kwenye viwanja vya vita vya vumbi hadi kwenye kumbi za diplomasia, viongozi wa ukombozi wa Afrika walisimama imara dhidi ya ukandamizaji wa kikoloni, wakawasha mwanga wa uhuru unaoendelea kung'aa kote barani hadi leo.</h3>
</div><div id="a19864bf86e5006f98f97ae33f2d18ae" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19864bf86ed00d4ecd9b1babc6ce461" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Jisajiri/"><span>Jisajiri Kushiriki</span></a></div><div id="a1987be71a3b00a24b144c63e45517d8" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Waliopendekezwa-kupewa-Tuzo/"><span>Wadhamini</span></a></div><div id="a1988e3c7cfa000ebac3c5a7f0873487" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Bidhaa-za-Tamasha/"><span>Bidhaa</span></a></div><div id="a19864bf86e800d839909ed539924710" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Changia/"><span>Changia</span></a></div><div id="a1987b9cc12d0019fb2c5efaa51087ac" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Wadhamini/"><span>Wadhamini</span></a></div></div></div><div id="a1988e3c7ce100ec6efa30c7f85a3cc1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1988e3c7d1200a0e06e953ec26bc304" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Waliopendekezwa-kupewa-Tuzo/"><span>Walio pendekezwa Kupata Tuzo</span></a></div></div></div></div></div><div id="a1988f8816e90091a2b81ae89247d3f1" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom42"><strong>Nani Anaweza Kushiriki?</strong></p>

<ul>
<li class="wb-stl-custom42">Nchi zote za Afrika zinazotaka kushiriki</li>
<li class="wb-stl-custom42">Nchi rafiki zenye uhusiano mzuri na Afrika</li>
<li class="wb-stl-custom42">Washirika wa kimataifa kutoka sekta za elimu, maendeleo, na utamaduni</li>
<li class="wb-stl-custom42">Watalii, wafanyabiashara, na wataalamu</li>
<li class="wb-stl-custom42">Jamii kutoka Tanzania na duniani kote—kila mtu yupo mkaribisho!<strong>  </strong></li>
</ul>
</div></div></div></div></div></div></div><div id="a19880d1d3fc004c529c246a8eee574e" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"></div></div><div id="a19880ceb5a500fcab77be9a85849818" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19880ceb5ad00afa9b5a9e5ba94f4a5" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19880ceb5b200a7acc30b6a2aebfa56" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"></div></div></div></div><div id="a19880ceb69600a4fa1921699e6e6727" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19880ceb69e0057af8a5328e2a211ee" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"></div></div></div></div></div></div><div id="a1988f2fc1ea00d86023f977e444d64c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"></div></div><div id="a19899eefb490014fe2890d96aefc7d7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19899eefb930018cec02d52ecbc6825" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19899eefba700ca249e0ddea577ed52" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19899eefc7a007e5119f7cde009c03b" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19899eefc9000b75aeb3acd3262bb5a" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><svg xmlns="http://www.w3.org/2000/svg" width="45" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#0ca3a6"><text x="1.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></div></div></div><div id="a19899eefcb700c85f684497b88a2e6d" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2">Matukio na Shughuli Kuu za Tamasha la JUKANYE 2026</h2>
</div><div id="a19899eefcdf00c36a7892953d5407a3" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><ul>
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
</div></div></div><div id="a188dd9ebc38356d2bb45a52182269f8" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a188dd9ebc38371e4e69b7fdd6bcbcd4" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-heading2" style="text-align: center;"><span style="color:rgba(242,230,2,1);">Wito wa Ushiriki na ufadhili</span></h2>
</div><div id="a188dd9ebc383815bd4447212fd63ee8" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h1 class="wb-stl-custom7"> </h1>

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
</div></div></div></div></div></div></div></div></div><div id="a198941c143300bf579a21498b5220cb" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-normal" style="text-align: center;"><strong> 🔥 Miliki Ushujaa. Dhamini Tamasha.</strong></p>

<p class="wb-stl-normal" style="text-align: center;"> </p>

<p class="wb-stl-normal" style="text-align: center;"><strong>Kwa kuvaa bidhaa za JUKANYE, Utakuwa:</strong></p>

<p class="wb-stl-normal" style="text-align: center;">Unaunga mkono Tamasha la Kimataifa la Historia la JUKANYE na dhamira yake yenye nguvu ya kuhamasisha, kuelimisha, na kuunganisha.</p>

<p class="wb-stl-normal" style="text-align: center;">Unasaidia kufadhili programu za uwezeshaji wa vijana, uhifadhi wa urithi wa utamaduni, umoja wa Afrika, na elimu ya historia.</p>

<p class="wb-stl-normal" style="text-align: center;">Kila T-shirt, Kofia, na Kitenge unachonunua si vazi pekee — ni tamko la uzalendo, heshima kwa mashujaa wetu, na mchango kwa mustakabali wa urithi wa Afrika.</p>

<p class="wb-stl-normal" style="text-align: center;"> </p>

<p class="wb-stl-normal" style="text-align: center;"><strong>Vaa Mitindo inayohamasisha Uhuru.</strong></p>
</div><div id="a19893d716370034ae28d3760275c429" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19893d71637014e5c652ce70f3687a0" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19893e1bc00007f4980ed8081c49c76" class="wb_element wb-prevent-layout-click wb_gallery" data-plugin="Gallery"><script type="text/javascript">
			window._spDefer.add(function() {
				$(function() {
					(function(GalleryLib) {
						var el = document.getElementById("a19893e1bc00007f4980ed8081c49c76");
						var lib = new GalleryLib({"id":"a19893e1bc00007f4980ed8081c49c76","height":"auto","type":"list","trackResize":true,"interval":3,"speed":400,"images":[{"thumb":"gallery_gen\/7d39828787d2a197f4707adea081e6ab_164x246_fill.jpg","src":"gallery_gen\/eced5c2186f5c78eeebf3b2f2b70176c_fit.jpg?ts=1785686345","width":1024,"height":1536,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/fd76f03bf3c69627ef3332ffe0cf4788_164x246_fill.jpg","src":"gallery_gen\/83ee15477d529ff1690e72b763e300dc_fit.jpg?ts=1785686345","width":1024,"height":1536,"title":"","link":null,"description":"","address":""}],"border":{"border":"5px none #00008c"},"padding":10,"thumbWidth":100,"thumbHeight":100,"thumbAlign":"center","thumbPadding":6,"thumbAnim":"","thumbShadow":"","imageCover":true,"disablePopup":false,"controlsArrow":"chevron","controlsArrowSize":14,"controlsArrowStyle":{"normal":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}},"hover":{"color":"#DDDDDD","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#222222","forText":true,"css":{"text-shadow":"0px 0px 1px #222222"}}},"active":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}}},"slideOpacity":100,"showPictureCaption":"always","captionIncludeDescription":false,"captionPosition":"center bottom","mapTypeId":"roadmap","markerIconTypeId":"thumbs","zoom":2,"mapCenter":{"latLng":{"lat":41.244772343082,"lng":-5.2734375},"text":"41.244772343082076, -5.2734375"},"key":"AIzaSyD-9xtp38UunEmx7XfJ7eBh-K4w8qQ6SEw","theme":"default","color":"#eeeeee","showSatellite":true,"showZoom":true,"showStreetView":true,"showFullscreen":true,"allowDragging":true,"showRoads":true,"showLandmarks":true,"showLabels":true,"locale":"sw_TZ","pauseOnHover":false});
						lib.appendTo(el);
					})(window.wbmodGalleryLib);
				});
			});
		</script></div><div id="a19893d7163703a2d07121683357a3c6" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom46"><span class="text-nocut">$ 20</span></h2>
</div><div id="a19893d71637046e7c4d5159d797524a" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h1 class="wb-stl-custom45"><span class="text-nocut">Kofia</span></h1>
</div><div id="a19893d716370583d2e3a69b9d16e40c" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-custom40">Pamba Kichwa kwa Fahari. Saidia Tamasha.</h3>
</div><div id="a19893d85a9d00d3400c0f9d1fc564a3" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19893d7163706b3011974b125caa740" class="wb_element wb-prevent-layout-click" data-plugin="BuyNow">


	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" data-gateway-id="Paypal" target="_blank" style="width: 100%; height: 100%;">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="">
		<input type="hidden" name="amount" value="1">
		<input type="hidden" name="currency_code" value="USD">
		<input type="hidden" name="button_subtype" value="services">
		<input type="hidden" name="no_note" value="0">
		<input type="hidden" name="shipping" value="">
		<input type="hidden" name="bn" value="JSCProfis_SP">
		<?php global $pluginData; $pluginData = json_decode('{"business":"","itemName":"","amount":"1","currencyCode":"USD","shipping":"","test":false,"html":"<form action=\\"https:\\/\\/www.paypal.com\\/cgi-bin\\/webscr\\" method=\\"post\\" target=\\"_blank\\" class=\\"paypal\\"><input type=\\"hidden\\" name=\\"cmd\\" value=\\"_xclick\\"><input type=\\"hidden\\" name=\\"business\\" value=\\"\\"><input type=\\"hidden\\" name=\\"lc\\" value=\\"US\\"><input type=\\"hidden\\" name=\\"item_name\\" value=\\"\\"><input type=\\"hidden\\" name=\\"amount\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"currency_code\\" value=\\"USD\\"><input type=\\"hidden\\" name=\\"button_subtype\\" value=\\"services\\"><input type=\\"hidden\\" name=\\"no_note\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"shipping\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"bn\\" value=\\"JSCProfis_SP\\"><input type=\\"image\\" src=\\"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/btn\\/btn_buynow_LG.gif\\" border=\\"0\\" name=\\"submit\\" alt=\\"PayPal - The safer, easier way to pay online!\\"><img alt=\\"\\" border=\\"0\\" src=\\"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/scr\\/pixel.gif\\" width=\\"1\\" height=\\"1\\"><\\/form>","__globalVars":["demo"],"logo":"paypal_color.svg","_locale":"en_US","button_label":"","button_color":"transparent","font_family":"Arial,Helvetica,sans-serif","font_size":14,"label_color":"#333333","button_border":{"differ":false,"color":["#eeeeee","#eeeeee","#eeeeee","#eeeeee"],"style":["none","none","none","none"],"weight":[1,1,1,1],"radius":null,"css":{"border":"1px none #eeeeee"},"cssRaw":"border: 1px none #eeeeee;"},"demo":false,"showlogo":true,"logo_width":107,"button_padding":0,"buttonBorderCss":"border-bottom: 1px none #eeeeee;border-left: 1px none #eeeeee;border-right: 1px none #eeeeee;border-top: 1px none #eeeeee;border-radius: 0px;-webkit-border-radius: 0px;-moz-border-radius: 0px;","remoteLogo":"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/btn\\/btn_buynow_LG.gif","logo_src":"gallery_gen\\/BuyNow\\/paypal_color.svg","paymentGatewayButton":"<button type=\\"submit\\" id=\\"a19893d7163706b3011974b125caa740_payment_gateway_button\\" class=\\"btn btn-default btn-sm\\" style=\\"width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 0px;background-color: transparent;border: 1px none #eeeeee;border-radius: 0px 0px 0px 0px; -moz-border-radius: 0px 0px 0px 0px; -webkit-border-radius: 0px 0px 0px 0px;\\"><img src=\\"gallery_gen\\/BuyNow\\/paypal_color.svg\\" alt=\\"BuyNow\\" style=\\"width: 107px; max-width: 100%;\\" \\/><\\/button>","border_radius":{"lt":0,"rt":0,"rb":0,"lb":0,"differ":false,"css":{"border-radius":"0px 0px 0px 0px","-moz-border-radius":"0px 0px 0px 0px","-webkit-border-radius":"0px 0px 0px 0px"},"cssRaw":"border-radius: 0px 0px 0px 0px; -moz-border-radius: 0px 0px 0px 0px; -webkit-border-radius: 0px 0px 0px 0px;"},"clientId":"","clientSecret":"","button_icon":null}'); $pluginData->_extReferenceId = ''; $pluginData->elemId = 'a19893d7163706b3011974b125caa740'; $pluginData->currLang = 'sw'; $pluginData->currLangLocale = 'sw_TZ'; $pluginData->isPreview = ''; if (!function_exists('_spDefer_wrap_scripts')) { function _spDefer_wrap_scripts($output) { return preg_replace_callback('%<script\\b((?:(?!src=)[^>])*)>(.*?)</script>%is', function($m) { if (strpos($m[2], '_spDefer.done(') !== false) return $m[0]; if (strpos($m[2], 'window._spDefer') !== false) return $m[0]; return '<script>window._spDefer.add(function() {' . $m[2] . '});</script>'; }, $output); } } ob_start(); require dirname(__FILE__).'/main_BuyNow.php'; echo _spDefer_wrap_scripts(ob_get_clean()); ?>
	</form>

</div><div id="a19893d7ef6d0094a3386d92b460c81e" class="wb_element wb-prevent-layout-click" data-plugin="mpesa"><button type="submit" id="a19893d7ef6d0094a3386d92b460c81e_payment_gateway_button" class="btn btn-default btn-sm" style="width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 4px;background-color: #ffffff;border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;"><strong style="display: block; color: #949494;font-family: Arial;font-size: 12px;">Pay with</strong><img src="gallery_gen/mpesa/mpesa.svg" alt="mpesa" style="width: 110px; max-width: 100%;"></button>

<div class="modal fade" id="mpesaModal_a19893d7ef6d0094a3386d92b460c81e" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">Mpesa</h4>
            </div>
            <div class="modal-body">
                <form id="mpesaForm_a19893d7ef6d0094a3386d92b460c81e" method="post" action="" data-gateway-id="Mpesa">
                    <div class="form-group">
                        <label>Nambari ya simu: <span style="color: #c00;">*</span></label>
                        <input type="text" class="form-control" name="phone" id="phone_a19893d7ef6d0094a3386d92b460c81e" value="">
                    </div>
                    <div class="form-group">
                        <label>Nchi: <span style="color: #c00;">*</span></label>
                        <select class="form-control" id="country_a19893d7ef6d0094a3386d92b460c81e" name="country" required="required">
                            <option value="GHA">Ghana</option>
                            <option value="TZN">Tanzania</option>
                            <option value="LES">Lesotho</option>
                            <option value="DRC">DR Congo</option>
                        </select>
                    </div>
                </form>
                <div id="mpesaCheckoutRender_a19893d7ef6d0094a3386d92b460c81e"></div>
                <div id="mpesaError_a19893d7ef6d0094a3386d92b460c81e" class="alert alert-danger"></div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Funga</button>
                <button id="mpesaBackBtn_a19893d7ef6d0094a3386d92b460c81e" type="button" class="btn btn-primary" onclick="showModal_a19893d7ef6d0094a3386d92b460c81e()">Nyuma</button>
                <button id="mpesaNextBtn_a19893d7ef6d0094a3386d92b460c81e" type="button" class="btn btn-primary" onclick="startPaymentFlow_a19893d7ef6d0094a3386d92b460c81e()">Lipa</button>

            </div>
        </div>
    </div>
</div>
<?php global $pluginData; $pluginData = json_decode('{"__globalVars":["apiKey","isTest"],"apiKey":"","price":"","isTest":true,"providerCode":"","button_label":"Pay with","button_color":"#ffffff","font_family":"Arial","font_size":12,"label_color":"#949494","button_border":{"differ":false,"differRadius":false,"color":["#cccccc","#cccccc","#cccccc","#cccccc"],"style":["solid","solid","solid","solid"],"weight":[1,1,1,1],"css":{"border":"1px solid #cccccc","-moz-border-radius":"0px","-webkit-border-radius":"0px","border-radius":"0px"},"cssRaw":"border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;"},"logo":"mpesa.svg","showlogo":true,"logo_width":110,"border_radius":{"lt":0,"rt":0,"rb":0,"lb":0,"differ":false,"css":{"border-radius":"0px","-moz-border-radius":"0px","-webkit-border-radius":"0px"},"cssRaw":"border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;"},"button_padding":4,"logo_src":"gallery_gen\\/mpesa\\/mpesa.svg","paymentGatewayButton":"<button type=\\"submit\\" id=\\"a19893d7ef6d0094a3386d92b460c81e_payment_gateway_button\\" class=\\"btn btn-default btn-sm\\" style=\\"width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 4px;background-color: #ffffff;border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;\\"><strong style=\\"display: block; color: #949494;font-family: Arial;font-size: 12px;\\">Pay with<\\/strong><img src=\\"gallery_gen\\/mpesa\\/mpesa.svg\\" alt=\\"mpesa\\" style=\\"width: 110px; max-width: 100%;\\" \\/><\\/button>","button_icon":null}'); $pluginData->_extReferenceId = ''; $pluginData->elemId = 'a19893d7ef6d0094a3386d92b460c81e'; $pluginData->currLang = 'sw'; $pluginData->currLangLocale = 'sw_TZ'; $pluginData->isPreview = ''; if (!function_exists('_spDefer_wrap_scripts')) { function _spDefer_wrap_scripts($output) { return preg_replace_callback('%<script\\b((?:(?!src=)[^>])*)>(.*?)</script>%is', function($m) { if (strpos($m[2], '_spDefer.done(') !== false) return $m[0]; if (strpos($m[2], 'window._spDefer') !== false) return $m[0]; return '<script>window._spDefer.add(function() {' . $m[2] . '});</script>'; }, $output); } } ob_start(); require dirname(__FILE__).'/main_mpesa.php'; echo _spDefer_wrap_scripts(ob_get_clean()); ?>
</div></div></div></div></div><div id="a19893e523630054d8651dab325610ff" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19893e5238c001df7f4b7e57e5624f6" class="wb_element wb-prevent-layout-click wb_gallery" data-plugin="Gallery"><script type="text/javascript">
			window._spDefer.add(function() {
				$(function() {
					(function(GalleryLib) {
						var el = document.getElementById("a19893e5238c001df7f4b7e57e5624f6");
						var lib = new GalleryLib({"id":"a19893e5238c001df7f4b7e57e5624f6","height":"auto","type":"list","trackResize":true,"interval":3,"speed":400,"images":[{"thumb":"gallery_gen\/304f547d8240773f1800797c8c981179_164x246_fill.jpg","src":"gallery_gen\/a8399cf429a9c98caa3cc480449585b2_fit.jpg?ts=1785686345","width":1024,"height":1536,"title":"","link":null,"description":"","address":""}],"border":{"border":"5px none #00008c"},"padding":10,"thumbWidth":100,"thumbHeight":100,"thumbAlign":"center","thumbPadding":6,"thumbAnim":"","thumbShadow":"","imageCover":true,"disablePopup":false,"controlsArrow":"chevron","controlsArrowSize":14,"controlsArrowStyle":{"normal":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}},"hover":{"color":"#DDDDDD","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#222222","forText":true,"css":{"text-shadow":"0px 0px 1px #222222"}}},"active":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}}},"slideOpacity":100,"showPictureCaption":"always","captionIncludeDescription":false,"captionPosition":"center bottom","mapTypeId":"roadmap","markerIconTypeId":"thumbs","zoom":2,"mapCenter":{"latLng":{"lat":41.244772343082,"lng":-5.2734375},"text":"41.244772343082076, -5.2734375"},"key":"AIzaSyD-9xtp38UunEmx7XfJ7eBh-K4w8qQ6SEw","theme":"default","color":"#eeeeee","showSatellite":true,"showZoom":true,"showStreetView":true,"showFullscreen":true,"allowDragging":true,"showRoads":true,"showLandmarks":true,"showLabels":true,"locale":"sw_TZ","pauseOnHover":false});
						lib.appendTo(el);
					})(window.wbmodGalleryLib);
				});
			});
		</script></div><div id="a19893e523a400c4b38077c76e70331a" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom46"><span class="text-nocut">$ 20</span></h2>
</div><div id="a19893e523bc00cd1bc5904c373b20e4" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h1 class="wb-stl-custom45"><span class="text-nocut">Polo T-Shirts executive</span></h1>
</div><div id="a19893e523d4008d5974bf873994a947" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom43"><strong>Vaa Roho ya Uzalendo. Eneza Nguvu za Tamasha." </strong></p>
</div><div id="a19893e523eb00dba9963044ef43172f" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19893e523f8001e1b4f0e6fd7c5e030" class="wb_element wb-prevent-layout-click" data-plugin="BuyNow">


	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" data-gateway-id="Paypal" target="_blank" style="width: 100%; height: 100%;">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="">
		<input type="hidden" name="amount" value="1">
		<input type="hidden" name="currency_code" value="USD">
		<input type="hidden" name="button_subtype" value="services">
		<input type="hidden" name="no_note" value="0">
		<input type="hidden" name="shipping" value="">
		<input type="hidden" name="bn" value="JSCProfis_SP">
		<?php global $pluginData; $pluginData = json_decode('{"business":"","itemName":"","amount":"1","currencyCode":"USD","shipping":"","test":false,"html":"<form action=\\"https:\\/\\/www.paypal.com\\/cgi-bin\\/webscr\\" method=\\"post\\" target=\\"_blank\\" class=\\"paypal\\"><input type=\\"hidden\\" name=\\"cmd\\" value=\\"_xclick\\"><input type=\\"hidden\\" name=\\"business\\" value=\\"\\"><input type=\\"hidden\\" name=\\"lc\\" value=\\"US\\"><input type=\\"hidden\\" name=\\"item_name\\" value=\\"\\"><input type=\\"hidden\\" name=\\"amount\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"currency_code\\" value=\\"USD\\"><input type=\\"hidden\\" name=\\"button_subtype\\" value=\\"services\\"><input type=\\"hidden\\" name=\\"no_note\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"shipping\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"bn\\" value=\\"JSCProfis_SP\\"><input type=\\"image\\" src=\\"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/btn\\/btn_buynow_LG.gif\\" border=\\"0\\" name=\\"submit\\" alt=\\"PayPal - The safer, easier way to pay online!\\"><img alt=\\"\\" border=\\"0\\" src=\\"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/scr\\/pixel.gif\\" width=\\"1\\" height=\\"1\\"><\\/form>","__globalVars":["demo"],"logo":"paypal_color.svg","_locale":"en_US","button_label":"","button_color":"transparent","font_family":"Arial,Helvetica,sans-serif","font_size":14,"label_color":"#333333","button_border":{"differ":false,"color":["#eeeeee","#eeeeee","#eeeeee","#eeeeee"],"style":["none","none","none","none"],"weight":[1,1,1,1],"radius":null,"css":{"border":"1px none #eeeeee"},"cssRaw":"border: 1px none #eeeeee;"},"demo":false,"showlogo":true,"logo_width":107,"button_padding":0,"buttonBorderCss":"border-bottom: 1px none #eeeeee;border-left: 1px none #eeeeee;border-right: 1px none #eeeeee;border-top: 1px none #eeeeee;border-radius: 0px;-webkit-border-radius: 0px;-moz-border-radius: 0px;","remoteLogo":"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/btn\\/btn_buynow_LG.gif","logo_src":"gallery_gen\\/BuyNow\\/paypal_color.svg","paymentGatewayButton":"<button type=\\"submit\\" id=\\"a19893e523f8001e1b4f0e6fd7c5e030_payment_gateway_button\\" class=\\"btn btn-default btn-sm\\" style=\\"width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 0px;background-color: transparent;border: 1px none #eeeeee;border-radius: 0px 0px 0px 0px; -moz-border-radius: 0px 0px 0px 0px; -webkit-border-radius: 0px 0px 0px 0px;\\"><img src=\\"gallery_gen\\/BuyNow\\/paypal_color.svg\\" alt=\\"BuyNow\\" style=\\"width: 107px; max-width: 100%;\\" \\/><\\/button>","border_radius":{"lt":0,"rt":0,"rb":0,"lb":0,"differ":false,"css":{"border-radius":"0px 0px 0px 0px","-moz-border-radius":"0px 0px 0px 0px","-webkit-border-radius":"0px 0px 0px 0px"},"cssRaw":"border-radius: 0px 0px 0px 0px; -moz-border-radius: 0px 0px 0px 0px; -webkit-border-radius: 0px 0px 0px 0px;"},"clientId":"","clientSecret":"","button_icon":null}'); $pluginData->_extReferenceId = ''; $pluginData->elemId = 'a19893e523f8001e1b4f0e6fd7c5e030'; $pluginData->currLang = 'sw'; $pluginData->currLangLocale = 'sw_TZ'; $pluginData->isPreview = ''; if (!function_exists('_spDefer_wrap_scripts')) { function _spDefer_wrap_scripts($output) { return preg_replace_callback('%<script\\b((?:(?!src=)[^>])*)>(.*?)</script>%is', function($m) { if (strpos($m[2], '_spDefer.done(') !== false) return $m[0]; if (strpos($m[2], 'window._spDefer') !== false) return $m[0]; return '<script>window._spDefer.add(function() {' . $m[2] . '});</script>'; }, $output); } } ob_start(); require dirname(__FILE__).'/main_BuyNow.php'; echo _spDefer_wrap_scripts(ob_get_clean()); ?>
	</form>

</div><div id="a19893e5240f00f9a717f1047767a554" class="wb_element wb-prevent-layout-click" data-plugin="mpesa"><button type="submit" id="a19893e5240f00f9a717f1047767a554_payment_gateway_button" class="btn btn-default btn-sm" style="width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 4px;background-color: #ffffff;border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;"><strong style="display: block; color: #949494;font-family: Arial;font-size: 12px;">Pay with</strong><img src="gallery_gen/mpesa/mpesa.svg" alt="mpesa" style="width: 110px; max-width: 100%;"></button>

<div class="modal fade" id="mpesaModal_a19893e5240f00f9a717f1047767a554" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">Mpesa</h4>
            </div>
            <div class="modal-body">
                <form id="mpesaForm_a19893e5240f00f9a717f1047767a554" method="post" action="" data-gateway-id="Mpesa">
                    <div class="form-group">
                        <label>Nambari ya simu: <span style="color: #c00;">*</span></label>
                        <input type="text" class="form-control" name="phone" id="phone_a19893e5240f00f9a717f1047767a554" value="">
                    </div>
                    <div class="form-group">
                        <label>Nchi: <span style="color: #c00;">*</span></label>
                        <select class="form-control" id="country_a19893e5240f00f9a717f1047767a554" name="country" required="required">
                            <option value="GHA">Ghana</option>
                            <option value="TZN">Tanzania</option>
                            <option value="LES">Lesotho</option>
                            <option value="DRC">DR Congo</option>
                        </select>
                    </div>
                </form>
                <div id="mpesaCheckoutRender_a19893e5240f00f9a717f1047767a554"></div>
                <div id="mpesaError_a19893e5240f00f9a717f1047767a554" class="alert alert-danger"></div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Funga</button>
                <button id="mpesaBackBtn_a19893e5240f00f9a717f1047767a554" type="button" class="btn btn-primary" onclick="showModal_a19893e5240f00f9a717f1047767a554()">Nyuma</button>
                <button id="mpesaNextBtn_a19893e5240f00f9a717f1047767a554" type="button" class="btn btn-primary" onclick="startPaymentFlow_a19893e5240f00f9a717f1047767a554()">Lipa</button>

            </div>
        </div>
    </div>
</div>
<?php global $pluginData; $pluginData = json_decode('{"__globalVars":["apiKey","isTest"],"apiKey":"","price":"","isTest":true,"providerCode":"","button_label":"Pay with","button_color":"#ffffff","font_family":"Arial","font_size":12,"label_color":"#949494","button_border":{"differ":false,"differRadius":false,"color":["#cccccc","#cccccc","#cccccc","#cccccc"],"style":["solid","solid","solid","solid"],"weight":[1,1,1,1],"css":{"border":"1px solid #cccccc","-moz-border-radius":"0px","-webkit-border-radius":"0px","border-radius":"0px"},"cssRaw":"border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;"},"logo":"mpesa.svg","showlogo":true,"logo_width":110,"border_radius":{"lt":0,"rt":0,"rb":0,"lb":0,"differ":false,"css":{"border-radius":"0px","-moz-border-radius":"0px","-webkit-border-radius":"0px"},"cssRaw":"border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;"},"button_padding":4,"logo_src":"gallery_gen\\/mpesa\\/mpesa.svg","paymentGatewayButton":"<button type=\\"submit\\" id=\\"a19893e5240f00f9a717f1047767a554_payment_gateway_button\\" class=\\"btn btn-default btn-sm\\" style=\\"width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 4px;background-color: #ffffff;border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;\\"><strong style=\\"display: block; color: #949494;font-family: Arial;font-size: 12px;\\">Pay with<\\/strong><img src=\\"gallery_gen\\/mpesa\\/mpesa.svg\\" alt=\\"mpesa\\" style=\\"width: 110px; max-width: 100%;\\" \\/><\\/button>","button_icon":null}'); $pluginData->_extReferenceId = ''; $pluginData->elemId = 'a19893e5240f00f9a717f1047767a554'; $pluginData->currLang = 'sw'; $pluginData->currLangLocale = 'sw_TZ'; $pluginData->isPreview = ''; if (!function_exists('_spDefer_wrap_scripts')) { function _spDefer_wrap_scripts($output) { return preg_replace_callback('%<script\\b((?:(?!src=)[^>])*)>(.*?)</script>%is', function($m) { if (strpos($m[2], '_spDefer.done(') !== false) return $m[0]; if (strpos($m[2], 'window._spDefer') !== false) return $m[0]; return '<script>window._spDefer.add(function() {' . $m[2] . '});</script>'; }, $output); } } ob_start(); require dirname(__FILE__).'/main_mpesa.php'; echo _spDefer_wrap_scripts(ob_get_clean()); ?>
</div></div></div></div></div><div id="a1989411fa20005c5a352433e805825c" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a1989411fa4d003e58ed2c1b4021b228" class="wb_element wb-prevent-layout-click wb_gallery" data-plugin="Gallery"><script type="text/javascript">
			window._spDefer.add(function() {
				$(function() {
					(function(GalleryLib) {
						var el = document.getElementById("a1989411fa4d003e58ed2c1b4021b228");
						var lib = new GalleryLib({"id":"a1989411fa4d003e58ed2c1b4021b228","height":"auto","type":"list","trackResize":true,"interval":3,"speed":400,"images":[{"thumb":"gallery_gen\/0f99d60c585e1789557bfb6b9602db38_164x246_fill.jpg","src":"gallery_gen\/7f3b0a326986861505b573f5e9d5d284_fit.jpg?ts=1785686345","width":1024,"height":1536,"title":"","link":null,"description":"","address":""}],"border":{"border":"5px none #00008c"},"padding":10,"thumbWidth":100,"thumbHeight":100,"thumbAlign":"center","thumbPadding":6,"thumbAnim":"","thumbShadow":"","imageCover":true,"disablePopup":false,"controlsArrow":"chevron","controlsArrowSize":14,"controlsArrowStyle":{"normal":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}},"hover":{"color":"#DDDDDD","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#222222","forText":true,"css":{"text-shadow":"0px 0px 1px #222222"}}},"active":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}}},"slideOpacity":100,"showPictureCaption":"always","captionIncludeDescription":false,"captionPosition":"center bottom","mapTypeId":"roadmap","markerIconTypeId":"thumbs","zoom":2,"mapCenter":{"latLng":{"lat":41.244772343082,"lng":-5.2734375},"text":"41.244772343082076, -5.2734375"},"key":"AIzaSyD-9xtp38UunEmx7XfJ7eBh-K4w8qQ6SEw","theme":"default","color":"#eeeeee","showSatellite":true,"showZoom":true,"showStreetView":true,"showFullscreen":true,"allowDragging":true,"showRoads":true,"showLandmarks":true,"showLabels":true,"locale":"sw_TZ","pauseOnHover":false});
						lib.appendTo(el);
					})(window.wbmodGalleryLib);
				});
			});
		</script></div><div id="a1989411fa650018d3a2ce5297ec8953" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom46"><span class="text-nocut">$ 10</span></h2>
</div><div id="a1989411fa7d002ca41ac6cec222be29" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h1 class="wb-stl-custom45">Polo T-shirts</h1>
</div><div id="a19894342075009199636ec2d7109ab9" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom43"><strong>Vaa Roho ya Uzalendo. Eneza Nguvu za Tamasha." </strong></p>
</div><div id="a1989411fa9500bfa0225eabdd7c6f2e" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom44">You will find the latest information about us on this page. Our company is constantly evolving and growing. We provide wide range of services. Our mission is to provide best solution that helps everyone. If you want to contact us, please fill the contact form on our website. We wish you a good day!</p>
</div><div id="a1989411faad00350960f118e0f19e5d" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a1989411faba000d2bb52e38b6a7e91e" class="wb_element wb-prevent-layout-click" data-plugin="BuyNow">


	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" data-gateway-id="Paypal" target="_blank" style="width: 100%; height: 100%;">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="">
		<input type="hidden" name="amount" value="1">
		<input type="hidden" name="currency_code" value="USD">
		<input type="hidden" name="button_subtype" value="services">
		<input type="hidden" name="no_note" value="0">
		<input type="hidden" name="shipping" value="">
		<input type="hidden" name="bn" value="JSCProfis_SP">
		<?php global $pluginData; $pluginData = json_decode('{"business":"","itemName":"","amount":"1","currencyCode":"USD","shipping":"","test":false,"html":"<form action=\\"https:\\/\\/www.paypal.com\\/cgi-bin\\/webscr\\" method=\\"post\\" target=\\"_blank\\" class=\\"paypal\\"><input type=\\"hidden\\" name=\\"cmd\\" value=\\"_xclick\\"><input type=\\"hidden\\" name=\\"business\\" value=\\"\\"><input type=\\"hidden\\" name=\\"lc\\" value=\\"US\\"><input type=\\"hidden\\" name=\\"item_name\\" value=\\"\\"><input type=\\"hidden\\" name=\\"amount\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"currency_code\\" value=\\"USD\\"><input type=\\"hidden\\" name=\\"button_subtype\\" value=\\"services\\"><input type=\\"hidden\\" name=\\"no_note\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"shipping\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"bn\\" value=\\"JSCProfis_SP\\"><input type=\\"image\\" src=\\"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/btn\\/btn_buynow_LG.gif\\" border=\\"0\\" name=\\"submit\\" alt=\\"PayPal - The safer, easier way to pay online!\\"><img alt=\\"\\" border=\\"0\\" src=\\"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/scr\\/pixel.gif\\" width=\\"1\\" height=\\"1\\"><\\/form>","__globalVars":["demo"],"logo":"paypal_color.svg","_locale":"en_US","button_label":"","button_color":"transparent","font_family":"Arial,Helvetica,sans-serif","font_size":14,"label_color":"#333333","button_border":{"differ":false,"color":["#eeeeee","#eeeeee","#eeeeee","#eeeeee"],"style":["none","none","none","none"],"weight":[1,1,1,1],"radius":null,"css":{"border":"1px none #eeeeee"},"cssRaw":"border: 1px none #eeeeee;"},"demo":false,"showlogo":true,"logo_width":107,"button_padding":0,"buttonBorderCss":"border-bottom: 1px none #eeeeee;border-left: 1px none #eeeeee;border-right: 1px none #eeeeee;border-top: 1px none #eeeeee;border-radius: 0px;-webkit-border-radius: 0px;-moz-border-radius: 0px;","remoteLogo":"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/btn\\/btn_buynow_LG.gif","logo_src":"gallery_gen\\/BuyNow\\/paypal_color.svg","paymentGatewayButton":"<button type=\\"submit\\" id=\\"a1989411faba000d2bb52e38b6a7e91e_payment_gateway_button\\" class=\\"btn btn-default btn-sm\\" style=\\"width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 0px;background-color: transparent;border: 1px none #eeeeee;border-radius: 0px 0px 0px 0px; -moz-border-radius: 0px 0px 0px 0px; -webkit-border-radius: 0px 0px 0px 0px;\\"><img src=\\"gallery_gen\\/BuyNow\\/paypal_color.svg\\" alt=\\"BuyNow\\" style=\\"width: 107px; max-width: 100%;\\" \\/><\\/button>","border_radius":{"lt":0,"rt":0,"rb":0,"lb":0,"differ":false,"css":{"border-radius":"0px 0px 0px 0px","-moz-border-radius":"0px 0px 0px 0px","-webkit-border-radius":"0px 0px 0px 0px"},"cssRaw":"border-radius: 0px 0px 0px 0px; -moz-border-radius: 0px 0px 0px 0px; -webkit-border-radius: 0px 0px 0px 0px;"},"clientId":"","clientSecret":"","button_icon":null}'); $pluginData->_extReferenceId = ''; $pluginData->elemId = 'a1989411faba000d2bb52e38b6a7e91e'; $pluginData->currLang = 'sw'; $pluginData->currLangLocale = 'sw_TZ'; $pluginData->isPreview = ''; if (!function_exists('_spDefer_wrap_scripts')) { function _spDefer_wrap_scripts($output) { return preg_replace_callback('%<script\\b((?:(?!src=)[^>])*)>(.*?)</script>%is', function($m) { if (strpos($m[2], '_spDefer.done(') !== false) return $m[0]; if (strpos($m[2], 'window._spDefer') !== false) return $m[0]; return '<script>window._spDefer.add(function() {' . $m[2] . '});</script>'; }, $output); } } ob_start(); require dirname(__FILE__).'/main_BuyNow.php'; echo _spDefer_wrap_scripts(ob_get_clean()); ?>
	</form>

</div><div id="a1989411fad200988bf0596e965afbb6" class="wb_element wb-prevent-layout-click" data-plugin="mpesa"><button type="submit" id="a1989411fad200988bf0596e965afbb6_payment_gateway_button" class="btn btn-default btn-sm" style="width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 4px;background-color: #ffffff;border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;"><strong style="display: block; color: #949494;font-family: Arial;font-size: 12px;">Pay with</strong><img src="gallery_gen/mpesa/mpesa.svg" alt="mpesa" style="width: 110px; max-width: 100%;"></button>

<div class="modal fade" id="mpesaModal_a1989411fad200988bf0596e965afbb6" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">Mpesa</h4>
            </div>
            <div class="modal-body">
                <form id="mpesaForm_a1989411fad200988bf0596e965afbb6" method="post" action="" data-gateway-id="Mpesa">
                    <div class="form-group">
                        <label>Nambari ya simu: <span style="color: #c00;">*</span></label>
                        <input type="text" class="form-control" name="phone" id="phone_a1989411fad200988bf0596e965afbb6" value="">
                    </div>
                    <div class="form-group">
                        <label>Nchi: <span style="color: #c00;">*</span></label>
                        <select class="form-control" id="country_a1989411fad200988bf0596e965afbb6" name="country" required="required">
                            <option value="GHA">Ghana</option>
                            <option value="TZN">Tanzania</option>
                            <option value="LES">Lesotho</option>
                            <option value="DRC">DR Congo</option>
                        </select>
                    </div>
                </form>
                <div id="mpesaCheckoutRender_a1989411fad200988bf0596e965afbb6"></div>
                <div id="mpesaError_a1989411fad200988bf0596e965afbb6" class="alert alert-danger"></div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Funga</button>
                <button id="mpesaBackBtn_a1989411fad200988bf0596e965afbb6" type="button" class="btn btn-primary" onclick="showModal_a1989411fad200988bf0596e965afbb6()">Nyuma</button>
                <button id="mpesaNextBtn_a1989411fad200988bf0596e965afbb6" type="button" class="btn btn-primary" onclick="startPaymentFlow_a1989411fad200988bf0596e965afbb6()">Lipa</button>

            </div>
        </div>
    </div>
</div>
<?php global $pluginData; $pluginData = json_decode('{"__globalVars":["apiKey","isTest"],"apiKey":"Kitenge","price":"25","isTest":true,"providerCode":"Vodacom","button_label":"Pay with","button_color":"#ffffff","font_family":"Arial","font_size":12,"label_color":"#949494","button_border":{"differ":false,"differRadius":false,"color":["#cccccc","#cccccc","#cccccc","#cccccc"],"style":["solid","solid","solid","solid"],"weight":[1,1,1,1],"css":{"border":"1px solid #cccccc","-moz-border-radius":"0px","-webkit-border-radius":"0px","border-radius":"0px"},"cssRaw":"border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;"},"logo":"mpesa.svg","showlogo":true,"logo_width":110,"border_radius":{"lt":0,"rt":0,"rb":0,"lb":0,"differ":false,"css":{"border-radius":"0px","-moz-border-radius":"0px","-webkit-border-radius":"0px"},"cssRaw":"border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;"},"button_padding":4,"logo_src":"gallery_gen\\/mpesa\\/mpesa.svg","paymentGatewayButton":"<button type=\\"submit\\" id=\\"a1989411fad200988bf0596e965afbb6_payment_gateway_button\\" class=\\"btn btn-default btn-sm\\" style=\\"width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 4px;background-color: #ffffff;border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;\\"><strong style=\\"display: block; color: #949494;font-family: Arial;font-size: 12px;\\">Pay with<\\/strong><img src=\\"gallery_gen\\/mpesa\\/mpesa.svg\\" alt=\\"mpesa\\" style=\\"width: 110px; max-width: 100%;\\" \\/><\\/button>","itemName":"Kitenge","button_icon":null}'); $pluginData->_extReferenceId = ''; $pluginData->elemId = 'a1989411fad200988bf0596e965afbb6'; $pluginData->currLang = 'sw'; $pluginData->currLangLocale = 'sw_TZ'; $pluginData->isPreview = ''; if (!function_exists('_spDefer_wrap_scripts')) { function _spDefer_wrap_scripts($output) { return preg_replace_callback('%<script\\b((?:(?!src=)[^>])*)>(.*?)</script>%is', function($m) { if (strpos($m[2], '_spDefer.done(') !== false) return $m[0]; if (strpos($m[2], 'window._spDefer') !== false) return $m[0]; return '<script>window._spDefer.add(function() {' . $m[2] . '});</script>'; }, $output); } } ob_start(); require dirname(__FILE__).'/main_mpesa.php'; echo _spDefer_wrap_scripts(ob_get_clean()); ?>
</div></div></div></div></div><div id="a19893e4fce900f9f214a57e83d36fa0" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19893e4fd1f003156843115a88547a3" class="wb_element wb-prevent-layout-click wb_gallery" data-plugin="Gallery"><script type="text/javascript">
			window._spDefer.add(function() {
				$(function() {
					(function(GalleryLib) {
						var el = document.getElementById("a19893e4fd1f003156843115a88547a3");
						var lib = new GalleryLib({"id":"a19893e4fd1f003156843115a88547a3","height":"auto","type":"list","trackResize":true,"interval":3,"speed":400,"images":[{"thumb":"gallery_gen\/aa6ef1dd23e54854a49557c3e51d8e80_250.37239583333x158_fill.jpg","src":"gallery_gen\/5bb0074c87228ab415c88e389bd7b3af_fit.jpg?ts=1785686345","width":1217,"height":768,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/8c344bdf47ed64e6cb0451804f3b2a14_246x164_fill.jpg","src":"gallery_gen\/bd6fe3b750a72979eab769c0f74699e6_fit.jpg?ts=1785686345","width":1200,"height":800,"title":"","link":null,"description":"","address":""},{"thumb":"gallery_gen\/2e13d4621d07ce3270573517955700f6_246x164_fill.jpg","src":"gallery_gen\/420151faf73ee01df91144d65d3280d7_fit.jpg?ts=1785686345","width":1200,"height":800,"title":"","link":null,"description":"","address":""}],"border":{"border":"5px none #00008c"},"padding":10,"thumbWidth":100,"thumbHeight":100,"thumbAlign":"center","thumbPadding":6,"thumbAnim":"","thumbShadow":"","imageCover":true,"disablePopup":false,"controlsArrow":"chevron","controlsArrowSize":14,"controlsArrowStyle":{"normal":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}},"hover":{"color":"#DDDDDD","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#222222","forText":true,"css":{"text-shadow":"0px 0px 1px #222222"}}},"active":{"color":"#FFFFFF","shadow":{"angle":135,"distance":0,"size":0,"blur":1,"color":"#000000","forText":true,"css":{"text-shadow":"0px 0px 1px #000000"}}}},"slideOpacity":100,"showPictureCaption":"always","captionIncludeDescription":false,"captionPosition":"center bottom","mapTypeId":"roadmap","markerIconTypeId":"thumbs","zoom":2,"mapCenter":{"latLng":{"lat":41.244772343082,"lng":-5.2734375},"text":"41.244772343082076, -5.2734375"},"key":"AIzaSyD-9xtp38UunEmx7XfJ7eBh-K4w8qQ6SEw","theme":"default","color":"#eeeeee","showSatellite":true,"showZoom":true,"showStreetView":true,"showFullscreen":true,"allowDragging":true,"showRoads":true,"showLandmarks":true,"showLabels":true,"locale":"sw_TZ","pauseOnHover":false});
						lib.appendTo(el);
					})(window.wbmodGalleryLib);
				});
			});
		</script></div><div id="a19893e4fd3d00e53bd0f88cb9d7cdcd" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h2 class="wb-stl-custom46"><span class="text-nocut">$ 25</span></h2>
</div><div id="a19893e4fd5900498e6d75013054703a" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h1 class="wb-stl-custom45"><span class="text-nocut">Kitenge (cotton Wax)</span></h1>
</div><div id="a19893e4fd71003efc4758b62db22c2d" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-custom43"><strong>Jivike Urithi. Sherehekea Tamasha.</strong></p>
</div><div id="a19893e4fd87008cdad00bcd282dfafc" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19893e4fd950049efe4a6a0e880006f" class="wb_element wb-prevent-layout-click" data-plugin="BuyNow">


	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" data-gateway-id="Paypal" target="_blank" style="width: 100%; height: 100%;">
		<input type="hidden" name="cmd" value="_xclick">
		<input type="hidden" name="business" value="">
		<input type="hidden" name="amount" value="1">
		<input type="hidden" name="currency_code" value="USD">
		<input type="hidden" name="button_subtype" value="services">
		<input type="hidden" name="no_note" value="0">
		<input type="hidden" name="shipping" value="">
		<input type="hidden" name="bn" value="JSCProfis_SP">
		<?php global $pluginData; $pluginData = json_decode('{"business":"","itemName":"","amount":"1","currencyCode":"USD","shipping":"","test":false,"html":"<form action=\\"https:\\/\\/www.paypal.com\\/cgi-bin\\/webscr\\" method=\\"post\\" target=\\"_blank\\" class=\\"paypal\\"><input type=\\"hidden\\" name=\\"cmd\\" value=\\"_xclick\\"><input type=\\"hidden\\" name=\\"business\\" value=\\"\\"><input type=\\"hidden\\" name=\\"lc\\" value=\\"US\\"><input type=\\"hidden\\" name=\\"item_name\\" value=\\"\\"><input type=\\"hidden\\" name=\\"amount\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"currency_code\\" value=\\"USD\\"><input type=\\"hidden\\" name=\\"button_subtype\\" value=\\"services\\"><input type=\\"hidden\\" name=\\"no_note\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"shipping\\" value=\\"0\\"><input type=\\"hidden\\" name=\\"bn\\" value=\\"JSCProfis_SP\\"><input type=\\"image\\" src=\\"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/btn\\/btn_buynow_LG.gif\\" border=\\"0\\" name=\\"submit\\" alt=\\"PayPal - The safer, easier way to pay online!\\"><img alt=\\"\\" border=\\"0\\" src=\\"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/scr\\/pixel.gif\\" width=\\"1\\" height=\\"1\\"><\\/form>","__globalVars":["demo"],"logo":"paypal_color.svg","_locale":"en_US","button_label":"","button_color":"transparent","font_family":"Arial,Helvetica,sans-serif","font_size":14,"label_color":"#333333","button_border":{"differ":false,"color":["#eeeeee","#eeeeee","#eeeeee","#eeeeee"],"style":["none","none","none","none"],"weight":[1,1,1,1],"radius":null,"css":{"border":"1px none #eeeeee"},"cssRaw":"border: 1px none #eeeeee;"},"demo":false,"showlogo":true,"logo_width":107,"button_padding":0,"buttonBorderCss":"border-bottom: 1px none #eeeeee;border-left: 1px none #eeeeee;border-right: 1px none #eeeeee;border-top: 1px none #eeeeee;border-radius: 0px;-webkit-border-radius: 0px;-moz-border-radius: 0px;","remoteLogo":"https:\\/\\/www.paypalobjects.com\\/en_US\\/i\\/btn\\/btn_buynow_LG.gif","logo_src":"gallery_gen\\/BuyNow\\/paypal_color.svg","paymentGatewayButton":"<button type=\\"submit\\" id=\\"a19893e4fd950049efe4a6a0e880006f_payment_gateway_button\\" class=\\"btn btn-default btn-sm\\" style=\\"width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 0px;background-color: transparent;border: 1px none #eeeeee;border-radius: 0px 0px 0px 0px; -moz-border-radius: 0px 0px 0px 0px; -webkit-border-radius: 0px 0px 0px 0px;\\"><img src=\\"gallery_gen\\/BuyNow\\/paypal_color.svg\\" alt=\\"BuyNow\\" style=\\"width: 107px; max-width: 100%;\\" \\/><\\/button>","border_radius":{"lt":0,"rt":0,"rb":0,"lb":0,"differ":false,"css":{"border-radius":"0px 0px 0px 0px","-moz-border-radius":"0px 0px 0px 0px","-webkit-border-radius":"0px 0px 0px 0px"},"cssRaw":"border-radius: 0px 0px 0px 0px; -moz-border-radius: 0px 0px 0px 0px; -webkit-border-radius: 0px 0px 0px 0px;"},"clientId":"","clientSecret":"","button_icon":null}'); $pluginData->_extReferenceId = ''; $pluginData->elemId = 'a19893e4fd950049efe4a6a0e880006f'; $pluginData->currLang = 'sw'; $pluginData->currLangLocale = 'sw_TZ'; $pluginData->isPreview = ''; if (!function_exists('_spDefer_wrap_scripts')) { function _spDefer_wrap_scripts($output) { return preg_replace_callback('%<script\\b((?:(?!src=)[^>])*)>(.*?)</script>%is', function($m) { if (strpos($m[2], '_spDefer.done(') !== false) return $m[0]; if (strpos($m[2], 'window._spDefer') !== false) return $m[0]; return '<script>window._spDefer.add(function() {' . $m[2] . '});</script>'; }, $output); } } ob_start(); require dirname(__FILE__).'/main_BuyNow.php'; echo _spDefer_wrap_scripts(ob_get_clean()); ?>
	</form>

</div><div id="a19893e4fdad000e03fe9ad5a7218876" class="wb_element wb-prevent-layout-click" data-plugin="mpesa"><button type="submit" id="a19893e4fdad000e03fe9ad5a7218876_payment_gateway_button" class="btn btn-default btn-sm" style="width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 4px;background-color: #ffffff;border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;"><strong style="display: block; color: #949494;font-family: Arial;font-size: 12px;">Pay with</strong><img src="gallery_gen/mpesa/mpesa.svg" alt="mpesa" style="width: 110px; max-width: 100%;"></button>

<div class="modal fade" id="mpesaModal_a19893e4fdad000e03fe9ad5a7218876" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel">Mpesa</h4>
            </div>
            <div class="modal-body">
                <form id="mpesaForm_a19893e4fdad000e03fe9ad5a7218876" method="post" action="" data-gateway-id="Mpesa">
                    <div class="form-group">
                        <label>Nambari ya simu: <span style="color: #c00;">*</span></label>
                        <input type="text" class="form-control" name="phone" id="phone_a19893e4fdad000e03fe9ad5a7218876" value="">
                    </div>
                    <div class="form-group">
                        <label>Nchi: <span style="color: #c00;">*</span></label>
                        <select class="form-control" id="country_a19893e4fdad000e03fe9ad5a7218876" name="country" required="required">
                            <option value="GHA">Ghana</option>
                            <option value="TZN">Tanzania</option>
                            <option value="LES">Lesotho</option>
                            <option value="DRC">DR Congo</option>
                        </select>
                    </div>
                </form>
                <div id="mpesaCheckoutRender_a19893e4fdad000e03fe9ad5a7218876"></div>
                <div id="mpesaError_a19893e4fdad000e03fe9ad5a7218876" class="alert alert-danger"></div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Funga</button>
                <button id="mpesaBackBtn_a19893e4fdad000e03fe9ad5a7218876" type="button" class="btn btn-primary" onclick="showModal_a19893e4fdad000e03fe9ad5a7218876()">Nyuma</button>
                <button id="mpesaNextBtn_a19893e4fdad000e03fe9ad5a7218876" type="button" class="btn btn-primary" onclick="startPaymentFlow_a19893e4fdad000e03fe9ad5a7218876()">Lipa</button>

            </div>
        </div>
    </div>
</div>
<?php global $pluginData; $pluginData = json_decode('{"__globalVars":["apiKey","isTest"],"apiKey":"","price":"","isTest":true,"providerCode":"","button_label":"Pay with","button_color":"#ffffff","font_family":"Arial","font_size":12,"label_color":"#949494","button_border":{"differ":false,"differRadius":false,"color":["#cccccc","#cccccc","#cccccc","#cccccc"],"style":["solid","solid","solid","solid"],"weight":[1,1,1,1],"css":{"border":"1px solid #cccccc","-moz-border-radius":"0px","-webkit-border-radius":"0px","border-radius":"0px"},"cssRaw":"border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;"},"logo":"mpesa.svg","showlogo":true,"logo_width":110,"border_radius":{"lt":0,"rt":0,"rb":0,"lb":0,"differ":false,"css":{"border-radius":"0px","-moz-border-radius":"0px","-webkit-border-radius":"0px"},"cssRaw":"border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;"},"button_padding":4,"logo_src":"gallery_gen\\/mpesa\\/mpesa.svg","paymentGatewayButton":"<button type=\\"submit\\" id=\\"a19893e4fdad000e03fe9ad5a7218876_payment_gateway_button\\" class=\\"btn btn-default btn-sm\\" style=\\"width: 100%; height: 100%; white-space: normal; overflow: hidden;padding: 4px;background-color: #ffffff;border: 1px solid #cccccc; -moz-border-radius: 0px; -webkit-border-radius: 0px; border-radius: 0px;border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;\\"><strong style=\\"display: block; color: #949494;font-family: Arial;font-size: 12px;\\">Pay with<\\/strong><img src=\\"gallery_gen\\/mpesa\\/mpesa.svg\\" alt=\\"mpesa\\" style=\\"width: 110px; max-width: 100%;\\" \\/><\\/button>","button_icon":null}'); $pluginData->_extReferenceId = ''; $pluginData->elemId = 'a19893e4fdad000e03fe9ad5a7218876'; $pluginData->currLang = 'sw'; $pluginData->currLangLocale = 'sw_TZ'; $pluginData->isPreview = ''; if (!function_exists('_spDefer_wrap_scripts')) { function _spDefer_wrap_scripts($output) { return preg_replace_callback('%<script\\b((?:(?!src=)[^>])*)>(.*?)</script>%is', function($m) { if (strpos($m[2], '_spDefer.done(') !== false) return $m[0]; if (strpos($m[2], 'window._spDefer') !== false) return $m[0]; return '<script>window._spDefer.add(function() {' . $m[2] . '});</script>'; }, $output); } } ob_start(); require dirname(__FILE__).'/main_mpesa.php'; echo _spDefer_wrap_scripts(ob_get_clean()); ?>
</div></div></div></div></div></div></div><div id="a1988e3c7ceb008a7fcae0435be2809d" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/Mwanzo/"><span><svg xmlns="http://www.w3.org/2000/svg" width="1793.982" height="1793.982" viewBox="0 0 1793.982 1793.982" style="display: inline-block; vertical-align: middle; position: relative; top: -1px; height: 1em; width: 1em; overflow: visible; direction: ltr;"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg>&nbsp;Mwanzo</span></a></div></div></div><div id="wb_footer_a188dd9eef5300bb9a9e9122025694a7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-vertical"><div id="a19fc2149cd000eb3b8848562ec6f176" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc386d7d4d77961b3399b7e7" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a19fb429723a03cab5671bd0692f5610" class="wb_element" data-plugin="Button"><a class="wb_button" href="sw/"><span><svg xmlns="http://www.w3.org/2000/svg" width="1793.982" height="1793.982" viewBox="0 0 1793.982 1793.982" style="display: inline-block; vertical-align: middle; position: relative; top: -1px; height: 1em; width: 1em; overflow: visible; direction: ltr;"><text x="65.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg>&nbsp;Home </span></a></div><div id="a188dd9ebc386e9c761088b65418f7a1" class="wb_element wb-layout-element" data-plugin="LayoutElement"><div class="wb_content wb-layout-horizontal"><div id="a188dd9ebc386f7f651dc7e4d0792624" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="sw/Mwanzo/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#4be6e6"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div><div id="a188dd9ebc38700f452a2fef2fcabe01" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="sw/Mwanzo/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1024 1024" style="direction: ltr; color:#ffffff"><text x="64" y="960" font-size="1024" fill="currentColor" style='font-family: "builder-ui-icons-plugins"'></text></svg></a></div></div></div><div id="a188dd9ebc3871cfcba1a4cf7091cb6d" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap" style="height: 100%"><div class="wb-picture-wrapper" style="overflow: visible; display: flex"><a href="sw/Mwanzo/"><svg xmlns="http://www.w3.org/2000/svg" width="40" viewBox="0 0 1793.982 1793.982" style="direction: ltr; color:#ffffff"><text x="129.501415" y="1537.02" font-size="1792" fill="currentColor" style='font-family: "FontAwesome"'></text></svg></a></div></div></div></div></div><div id="a19fc20bdb7e00c6080e244c0b41b351" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><h3 class="wb-stl-custom16" style="text-align: center;">ADDRESS:</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">P,O BOX  DAR- ES - SALAAM, TANZANIA</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">PHONE: +255 746 174403 +255 789  388232 +255 719 083050</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">EMAIL: jukanyefestival@gmail.com</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">info@jukanye.com</h3>

<h3 class="wb-stl-custom16" style="text-align: center;">WEBSITE: www.jukanye.com</h3>
</div><div id="a188dd9ebc38721835f60daecdc81bab" class="wb_element wb_element_picture" data-plugin="Picture" title=""><div class="wb_picture_wrap"><div class="wb-picture-wrapper"><img loading="lazy" alt="" src="gallery_gen/3a0fa4358ae2f4fb06a94eaab03b4403_fit.png?ts=1785686345"></div></div></div><div id="a188dd9ebc387353ef2d51652b5ef64e" class="wb_element wb_text_element" data-plugin="TextArea" style=" line-height: normal;"><p class="wb-stl-footer">© 2025 <a href="http://jukanye.com">jukanye.com</a> - Kuwaenzi Viongozi wa Afrika Walioongoza Harakati za Ukombozi</p>
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
	<script src="js/a188dd9eef5300bb9a9e9122025694a7-bundle.js?ts=20260802185857" type="text/javascript" defer></script>{{hr_out}}<script>
    document.addEventListener('DOMContentLoaded', function () {
        window._spDefer.done();
    });
</script>
</body>
</html>


<?php } ?>
