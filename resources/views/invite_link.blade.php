<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <link rel="icon" href="/favicon.ico">
    <style type="text/css">
        body {
            background: #252A2A;
            color: #adadad;
        }

        .err {
            color: red
        }

        .join_gr_link {
            display: inline-block;
            background: #adadad;
            color: #252A2A;
            border-radius: 4px;
            padding: 10px;
            margin: 10px;
            text-decoration: none;
        }

        a {
            color: #adadad
        }

    </style>
    <script src="{{ URL::asset('js/jquery.js') }}"></script>

    <script type="text/javascript">
			var lang = navigator.language == 'he_IL' ? 'he_IL' : 'en_US';


            function getAppLink() {
                var userAgent = navigator.userAgent || navigator.vendor || window.opera;
                if (userAgent.match(/iPad/i) || userAgent.match(/iPhone/i) || userAgent.match(/iPod/i)) {
                    // IOS
                    return 'https://itunes.apple.com/il/app/eurochamp/id1102589642';
                }
                else/* if (userAgent.match( /Android/i ))*/ {
                    // Android or unknown
                    return 'https://play.google.com/store/apps/details?id=il.co.compie.eurochamp';
                }
            }

			var strings = {
				'he_IL': {
					'I have opened a group in Eurochamp App:': 'פתחתי קבוצה באפליקציית יורוצ\'אמפ:',
					'Follow the link to join the group:': 'לחץ על הקישור כדי להצטרף:',
					'JOIN GROUP': 'הצטרף לקבוצה',
					'Group not found': 'לא נמצאה קבוצה',
					'Download EuroChamp, The Sport App of Israel:': 'הורד את יורוצזאמפ, אפליקציית הספורט של ישראל:',
					'Download': 'הורד',
					'עברית': 'English'
				}
			};

			strings['en_US'] = swap(strings['he_IL']);

			function t() {
				var str = $(this).text();
				if (strings[lang] && strings[lang][str]) {
					return strings[lang][str];
				}
				return str;
			}

			function swap(json) {
				var ret = {};
				for (var key in json) {
					ret[json[key]] = key;
				}
				return ret;
			}

			$(function () {
				$('.app_dl_link').attr('href', getAppLink());
				$('.join_gr_link').click(function () {
                    var url = '{{$deep_link}}';
                    openURLWithFallback(url, getAppLink());
                });
				$('#lang-sw').on('click', function (e) {
					e.preventDefault();
					lang = (lang == 'he_IL') ? 'en_US' : 'he_IL';
					langUpdate();
				});

				langUpdate();

				function langUpdate() {
					var dir = lang == 'he_IL' ? "rtl" : 'ltr';
					$('.lang-bar').attr('dir', (dir == 'ltr' ? 'rtl' : 'ltr'));
					$('html').attr('lang', lang).attr('dir', dir);
					$('.str').text(t);
				}
			});


            function openURLWithFallback(url, fallbackUrl) {
                window.location = url;
                if (!fallbackUrl) return

                // Mobile detection
                var now = Date.now();
                var localAppInstallTimeout = window.setTimeout(function() {
                    if (Date.now() - now > 1250) return;
                    window.location = fallbackUrl;
                }, 1000);

                // Desktop detection
                window.addEventListener('blur', function() {
                    window.clearTimeout(localAppInstallTimeout);
                });
            }
    </script>
</head>
<body>
<div>
    <div class="lang-bar">
        <a href="#" id="lang-sw"><span class="str">עברית</span></a>
    </div>
    <p style="text-align: center">
        <img src="{{ $img_src }}"/><br/>
        @if($group)
            <span class="str">I have opened a group in Eurochamp App:</span>
            <br/>
            <strong>{{ $group->name }}</strong><br/>
            <br/>
            <span class="str">Follow the link to join the group:</span>
            <br/>
            <a class="join_gr_link" href="#"><span class="str">JOIN GROUP</span></a>
            <br/>
            <br/>
        @else
            <span class="str err">Group not found</span><br/><br/>
        @endif
        <span class="str">Download EuroChamp, The Sport App of Israel:</span><br/>
        <a class="app_dl_link"><span class="str">Download</span></a>
    </p>

</div>
</body>
</html>


