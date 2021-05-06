<ul class="Skiplinks js-fr-bypasslinks u-hiddenPrint">
	<li><a href="#main">Vai al Contenuto</a></li>
	<li><a class="js-fr-offcanvas-open" href="#menu"
		aria-controls="menu" aria-label="accedi al menu" title="accedi al menu">Vai alla navigazione del sito</a></li>
	</ul>


	<header class="Header Headroom--fixed js-Headroom u-hiddenPrint">

		<div class="Header-banner ">
			<div class="Header-owner Headroom-hideme ">
				<div class="t-MFAD-block-meta-header" style="margin-bottom: 10px;">
					<?php print $metanavigation ?>
					<?php print $loginPopup ?>
				</div>

			</div>
		</div>

		<div class="Header-navbar ">
			<div class="u-layout-wide Grid Grid--alignMiddle u-layoutCenter">
				<?php if ($headerLogo) {?>
				<div class="Header-logo Grid-cell" aria-hidden="true">
					<a href="<?php print PNX_HOST; ?>" tabindex="-1">
						<?php print $headerLogo; ?>
					</a>
				</div>
				<?php }?>

				<div class="Header-title Grid-cell">
					<h1 class="Header-titleLink">
						<a href="<?php print PNX_HOST; ?>">
							<?php print $siteTitle; ?>
						</a>
					</h1>
				</div>

				<div class="Header-searchTrigger Grid-cell">
					<button aria-controls="header-search" class="js-Header-search-trigger Icon Icon-search "
					title="attiva il form di ricerca" aria-label="attiva il form di ricerca" aria-hidden="false">
				</button>
				<button aria-controls="header-search" class="js-Header-search-trigger Icon Icon-close u-hidden "
				title="disattiva il form di ricerca" aria-label="disattiva il form di ricerca" aria-hidden="true">
			</button>
		</div>

		<div class="Header-utils Grid-cell">
			<div class="Header-search" id="header-search">
				<?php print $searchSolr ?>
			</div>
		</div>
	</div>

</div>
</div>
<!-- Header-navbar -->

</header>
