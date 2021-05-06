<?php include('../public/static/museoweb/templates/Default/_head.php'); ?>
<body class="t-MFAD">
        <div id="outer" class="full-screen">
           <div id="main">
               <div class="module-list-archive clearfix">
                    <?php print $filters ?>
                   <section class="module-column mcp-module-column-content-search w-all-wm-xxl bw-f no-gutter last">
                        <?php print $content ?>

                   </section>
               </div>
           </div>
        </div>


<?php print $tail ?>

            <script>
            $(document).ready(function(){

            //Nasconde aggiunta a carrello
            setTimeout(function(){$('#messageSuccess').toggle(500)}, 3000);

            $('#outer').height($(window).height());
            $('.bcp-block-pop-up-cart').height($(window).height());
            $('.block-my-pop-up-content').height($(window).height());
            });
            </script>



        </body>
</html>
