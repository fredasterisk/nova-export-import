<?php
if (!defined('ABSPATH')) exit;
add_action('admin_menu', function () {
    add_submenu_page(
        'nei-exporter',
        'Importer',
        'Importer',
        'manage_options',
        'nei-importer',
        'nei_cpt_importer_render_page'
    );
});

function nei_cpt_importer_render_page() {
    if (!current_user_can('manage_options')) return;

    $post_types = get_post_types(['public' => true], 'objects');
    ?>
    <div class="wrap">
        <h1>Importer des publications</h1>
        <form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>?action=nei_import">
        >
            <label for="import_file">Fichier JSON :</label><br>
            <input type="file" name="import_file" id="import_file" accept=".json" required><br><br>

            <label for="target_cpt">Choisir le CPT cible :</label><br>
            <select name="target_cpt" id="target_cpt">
                <?php foreach ($post_types as $slug => $pt): ?>
                    <option value="<?= esc_attr($slug) ?>"><?= esc_html($pt->label) ?></option>
                <?php endforeach; ?>
            </select><br><br>

            <input type="submit" name="nei_import_submit" class="button button-primary" value="Importer">
        </form>
    </div>
    <?php
}
