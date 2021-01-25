<script type="text/javascript">
    function getAppLink() {
        var userAgent = navigator.userAgent || navigator.vendor || window.opera;
        if  (userAgent.match( /iPad/i ) || userAgent.match( /iPhone/i ) || userAgent.match( /iPod/i )) {
            // IOS
            return 'https://itunes.apple.com/il/app/eurochamp/id1102589642';
        }
        else/* if (userAgent.match( /Android/i ))*/ {
            // Android or unknown
            return 'https://play.google.com/store/apps/details?id=il.co.compie.eurochamp';
        }
    }

    window.location = getAppLink();
</script>