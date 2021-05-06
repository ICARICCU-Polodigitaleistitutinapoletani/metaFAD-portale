<?php include('../public/static/museoweb/templates/Default/_head.php'); ?>
<body class="t-MFAD">
	<?php include('../public/static/museoweb/templates/Default/_header.php'); ?>
	<div id="main" class="mcp-module-main-parallax">

		<!-- content -->
		<div class="u-layout-r-withGutter u-background-grey-10 u-padding-bottom-xxl">

			<div class="u-layout-centerContent">

				<div class="u-layout-wide u-lg-size12of12 u-md-size12of12 u-layoutLeft u-padding-r-bottom">

					<div class="Grid">

						<div class="Grid-cell u-margin-r-top u-md-size12of12 u-lg-size12of12">


							<!-- t-MFAD-result-search -->
							<div class="t-MFAD-result-search">

								<!-- mcp-module-results-tool -->
								<div class="mcp-module-results-tool clearfix">

									<!-- module-column -->
									<div class="module-column pull-left ">

										<div class="bcp-count-results">
											<div class="bcp-count-results">
												<?php print $searchSections ?>
											</div>
										</div>

									</div>
									<!-- module-column -->

								</div>
								<!-- mcp-module-results-tool -->
								<?php print $breadcrumbs ?>
								<div class="module-list-archive mcp-module-list-archive--results-list  clearfix">
									<?php print $filters ?>
									<!-- module-column -->
									<section class="module-column mcp-module-column-content-search w-all-wm-xxl bw-f no-gutter padding-top padding-left w-2-3 last padding-right">
										<!-- mcp-module-results-tool -->
			                              <?php print $paginate ?>
			                            <!-- mcp-module-results-tool -->

										<!-- block-item-post -->
										<?php print $content ?>
										<!-- block-item-post -->

										<!-- module-row -->
										<div class="module-row padding-bottom clearboth clearfix">

											<!-- bcp-count-results -->
											<div class="bcp-count-results pull-right">
												<div class="pagination paginationBottom">

												</div>
											</div>
											<script>
											$('.paginationBottom').html($('.pagination').first().html());
											</script>
											<!-- bcp-count-results -->

										</div>
										<!-- module-row -->

									</section>
									<!-- module-column -->

								</div>

							</div>
							<!-- t-MFAD-result-search -->


						</div>

					</div>

				</div>

			</div>

		</div>
		<!-- content -->

	</div>
	<!-- main -->


	<!-- outer -->
	<?php print $detailBox;?>
	<script>
	$(document).ready(function(){
	  $(function () {
		  $('.js-back-top').click(function () {
			  $('body,html').animate({
				  scrollTop: 0
			  }, 800);
			  return false;
		  });
	  });
	});

	$(document).ready(function(){
	  $('h4.block-title').each(function(){
		if($(this).html() === 'Digitale')
		{
		  $(this).siblings('ul').children('li').children('a').html('tutti i documenti');
		}
		if($(this).html() === 'Localizzazione')
		{
		  $(this).siblings('ul').first().children('li').each(function(){
			var html = $(this).children('a').html();
			if(html !== undefined)
			{
			  $(this).children('a').html(subLocalication(html));
			}
		  });
		  $(this).siblings('ul.js-collapse-box').children('li').each(function(){
			var html = $(this).find('a').html();
			if(html !== undefined)
			{
			  $(this).find('a').html(subLocalication(html));
			}
		  });
		}
	  });

	  $('span.bcp-field-value').each(function(){
		if($(this).html() === 'true')
		{
		  $(this).html('tutti i documenti');
		}
	  });

	  function subLocalication(html)
	  {
		html = html.replace('cappella-del-tesoro-di-san-gennaro','Cappella del Tesoro di San Gennaro');
		html = html.replace('societa-napoletana-di-storia-patria','Società Napoletana di Storia Patria');
		html = html.replace('cappella-del-tesoro-di-san-gennaro','Cappella del tesoro di San Gennaro');
		html = html.replace('fondazione-biblioteca-benedetto-croce','Fondazione biblioteca Benedetto Croce');
		html = html.replace('pio-monte-della-misericordia','Pio Monte della Misericordia');
		html = html.replace('istituto-centrale-per-gli-archivi-(icar)','Istituto centrale per gli archivi (ICAR)');
		return html;
	  }
	})
	</script>

	<?php include('../public/static/museoweb/templates/Default/_footer_long.php'); ?>
