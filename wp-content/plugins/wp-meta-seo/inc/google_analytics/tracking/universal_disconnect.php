<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
    exit();
?>
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    ga('create', '<?php echo esc_html($this->ga_tracking_disconnect['wpms_ga_uax_reference']); ?>', 'auto');
    ga('send', 'pageview');
</script>
