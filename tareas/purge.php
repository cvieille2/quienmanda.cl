add_action('init', function () {
    if (isset($_GET['purge_ficha'])) {
        if (defined('LSCWP_V') && class_exists('LiteSpeed\Purge')) {
            LiteSpeed\Purge::purge_all();
        }
        status_header(200);
        header('Content-Type: text/plain');
        echo 'Cache purged';
        exit;
    }
});
