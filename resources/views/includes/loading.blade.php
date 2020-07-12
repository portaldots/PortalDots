<div class="loading" id="loading">
    <div class="loading-noscript" id="js-noscript">JavaScript を有効にしてください</div>
    <div class="loading-isie" id="is-ie">
        お使いのブラウザには対応していません
    </div>
    <div class="loading-circle" id="loading-circle"></div>
    <script>
        'use strict'; {
            const noscript = document.getElementById('js-noscript');
            noscript.parentNode.removeChild(noscript);
        }
        if (navigator.userAgent.match(/msie|trident|edge/i)) {
            const isie = document.getElementById("is-ie");
            isie.style.display = "block";
            isie.parentNode.removeChild(document.getElementById('loading-circle'));
        }
    </script>
</div>
