<?php
add_action('wp_footer', function () {
    if (is_singular('ficha_motel')) {
        echo "<script>
(function(){
var e=document.querySelector('.badges span');
if(e)e.textContent=e.textContent.replace(/\$(\d+),(\d{3})/g,'$1.$2');
var t=document.querySelectorAll('.ficha-motel table td:nth-child(3)');
for(var i=0;i<t.length;i++)t[i].textContent=t[i].textContent.replace(/\$(\d+),(\d{3})/g,'$1.$2');
var h=document.querySelector('.ficha-motel h1');
if(h&&h.textContent.match(/^Motel\s+Motel/))h.textContent=h.textContent.replace(/^Motel\s+/,'');
})();
</script>";
    }
});
