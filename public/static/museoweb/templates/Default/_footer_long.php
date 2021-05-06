<div class="u-background-95 u-hiddenPrint">
    <div class="u-layout-wide u-layoutCenter u-layout-r-withGutter">
        <footer class="Footer u-background-95">

  <div class="Grid Grid--withGutter">

    <div class="Footer-block Grid-cell u-md-size1of4 u-lg-size2of4">

      <div class="Footer-subBlock">
        <h3 class="Footer-subTitle">metaFAD è un progetto</h3>
        <?php if ($footerLogo) {
          foreach($footerLogo as $item) {
            echo '<p>'.$item.'</p>';
          }
        } ?>
      </div>
    </div>

    <div class="Footer-block Grid-cell u-md-size1of4 u-lg-size1of4">
      <div class="Footer-subBlock">
      </div>
    </div>

    <div class="Footer-block Grid-cell u-md-size1of4 u-lg-size1of4">
      <div class="Footer-subBlock">
        <h3 class="Footer-subTitle">Seguici su</h3>
        <ul class="Footer-socialIcons">
          <li><a href="https://www.facebook.com/" rel="external"><span class="Icon Icon-facebook"></span><span class="u-hiddenVisually">Facebook</span></a></li>
          <li><a href="https://twitter.com/" rel="external"><span class="Icon Icon-twitter"></span><span class="u-hiddenVisually">Twitter</span></a></li>
        </ul>
      </div>
    </div>

  </div>

</footer>

    </div>
</div>

<a href="#" title="torna all'inizio del contenuto" class="ScrollTop js-scrollTop js-scrollTo">
  <i class="ScrollTop-icon Icon-collapse" aria-hidden="true"></i>
  <span class="u-hiddenVisually">torna all'inizio del contenuto</span>
</a>


<script>__PUBLIC_PATH__ = 'public/static/museoweb/templates/Default/ita-web-toolkit/build/'</script>

<?php print $tail ?>

</body>
</html>
