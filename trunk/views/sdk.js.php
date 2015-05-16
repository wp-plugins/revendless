<?php

$settings = array(
	'platform' => 'wordpress'
);

if(isset($this->settings['url_shortener']) && !empty($this->settings['url_shortener'])) {
	$settings['shortener'] = $this->settings['url_shortener'];
}

?><!-- Revendless SDK - http://www.revendless.com -->
<script type="text/javascript">
	(function(w,d,s,u,n,t,p){w['RevendlessObject']=n;w[n]=w[n]||function(){(w[n].e=w[n].e||[]).push(arguments)};t=d.createElement(s);p=d.getElementsByTagName(s)[0];p.parentNode.insertBefore(t,p);t.async=1;t.src=u;})
	(window,document,'script','//sdk.revendless.com/sdk.js','rev');
	rev('init','<?php print $this->settings['api_key']; ?>', <?php print json_encode($settings); ?>);
</script>
<!-- / Revendless SDK / v<?php print Revendless_Loader::VERSION ?> -->