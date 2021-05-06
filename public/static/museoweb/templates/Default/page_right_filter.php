<?php include('../public/static/museoweb/templates/Default/_head.php'); ?>
<body class="t-MFAD">
<?php include('../public/static/museoweb/templates/Default/_header.php'); ?>

<div id="main">
  <section>
	<div class="u-layout-wide u-layoutCenter u-layout-withGutter u-padding-r-top u-padding-bottom-xxl u-layout-r-withGutter">
	  <div class="Grid Grid--withGutter">
		<div class="Grid-cell u-md-size12of12 u-lg-size12of12">
			<?php print $content ?>

			<div class="bcp-count-results pull-right">
			  <div class="pagination paginationBottom"></div>
			</div>
			<script>
			  $('.paginationBottom').html($('.pagination').first().html());
			</script>
		</div>
	</div>
  </div>
  </section>
</div>


<?php include('../public/static/museoweb/templates/Default/_footer_long.php'); ?>
