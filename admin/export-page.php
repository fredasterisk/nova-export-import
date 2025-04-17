<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    add_menu_page(
        'Nova Exp/Imp',
        'Nova Exp/Imp',
        'manage_options',
        'nei-main',
        'nei_cpt_exporter_render_page',
        'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0iY3VycmVudENvbG9yIj48cGF0aCBkPSJNMyAxOUgyMVYyMUgzVjE5Wk0xMyA1LjgyODQzVjE3SDExVjUuODI4NDNMNC45Mjg5MyAxMS44OTk1TDMuNTE0NzIgMTAuNDg1M0wxMiAyTDIwLjQ4NTMgMTAuNDg1M0wxOS4wNzExIDExLjg5OTVMMTMgNS44Mjg0M1oiPjwvcGF0aD48L3N2Zz4=',
        30
    );

    add_submenu_page(
        'nei-main',
        'Exporter',
        'Exporter',
        'manage_options',
        'nei-exporter',
        'nei_cpt_exporter_render_page'
    );

});

function nei_cpt_exporter_render_page() {
    if (!current_user_can('manage_options')) return;

    if (isset($_POST['nei_export_cpt'])) {
        nei_cpt_exporter_handle_export(); // définie dans includes/exporter.php
        return;
    }

    $post_types = get_post_types(['public' => true], 'objects');
    ?>
    <div class="wrap">
        <h1>Exporter des publications</h1>
        <form method="post">
            <label for="cpt_select">Choisir un CPT :</label>
            <select id="cpt_select" name="cpt">
                <?php foreach ($post_types as $slug => $pt): ?>
                    <option value="<?= esc_attr($slug) ?>"><?= esc_html($pt->label) ?></option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <input type="submit" name="nei_load_fields" class="button button-secondary" value="Charger les champs ACF">
        </form>
        <?php
        if (isset($_POST['nei_load_fields'])) {
            $cpt = sanitize_text_field($_POST['cpt']);
            $sample = get_posts(['post_type' => $cpt, 'numberposts' => 1]);
            if ($sample) {
                $fields = get_field_objects($sample[0]->ID);
                if ($fields) {
                    echo '<form method="post">';
                    echo '<input type="hidden" name="cpt" value="' . esc_attr($cpt) . '">';
                    foreach ($fields as $key => $field) {
                        echo '<label><input type="checkbox" checked name="acf_fields[]" value="' . esc_attr($key) . '">' . esc_html($field['label']) . '</label><br>';
                    }
                    echo '<br><input type="submit" name="nei_export_cpt" class="button button-primary" value="Exporter">';
                    echo '</form>';
                } else {
                    echo '<p>Aucun champ ACF trouvé pour ce CPT.</p>';
                }
            } else {
                echo '<p>Aucun contenu trouvé pour ce CPT.</p>';
            }
        }
        ?>
    </div>
    <?php
}
