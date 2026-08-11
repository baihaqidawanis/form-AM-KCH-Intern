<?php
/**
 * Generator script: Replicate Joeya template for 13 Filling machines.
 * Run once from project root: php generate_machines.php
 * 
 * Generates per machine:
 *   - app/controllers/{Class}Controller.php
 *   - app/views/partials/{key}/add.php, list.php, list2.php, view.php, edit.php
 *   - database/migrations/2026-08-07_create_{key}.sql
 */

$machines = array(
    array('key' => 'illapak_1',  'label' => 'Illapak 1',  'class' => 'Illapak_1'),
    array('key' => 'illapak_2',  'label' => 'Illapak 2',  'class' => 'Illapak_2'),
    array('key' => 'illapak_3',  'label' => 'Illapak 3',  'class' => 'Illapak_3'),
    array('key' => 'illapak_4',  'label' => 'Illapak 4',  'class' => 'Illapak_4'),
    array('key' => 'illapak_5',  'label' => 'Illapak 5',  'class' => 'Illapak_5'),
    array('key' => 'illapak_6',  'label' => 'Illapak 6',  'class' => 'Illapak_6'),
    array('key' => 'illapak_7',  'label' => 'Illapak 7',  'class' => 'Illapak_7'),
    array('key' => 'illapak_8',  'label' => 'Illapak 8',  'class' => 'Illapak_8'),
    array('key' => 'illapak_9',  'label' => 'Illapak 9',  'class' => 'Illapak_9'),
    array('key' => 'illapak_10', 'label' => 'Illapak 10', 'class' => 'Illapak_10'),
    array('key' => 'illapak_11', 'label' => 'Illapak 11', 'class' => 'Illapak_11'),
    array('key' => 'illapak_12', 'label' => 'Illapak 12', 'class' => 'Illapak_12'),
    array('key' => 'unifill_b',  'label' => 'Unifill B',  'class' => 'Unifill_b'),
);

$base = __DIR__;
$template_controller = file_get_contents("$base/app/controllers/JoeyaController.php");
$template_views = array(
    'add.php'   => file_get_contents("$base/app/views/partials/joeya/add.php"),
    'list.php'  => file_get_contents("$base/app/views/partials/joeya/list.php"),
    'list2.php' => file_get_contents("$base/app/views/partials/joeya/list2.php"),
    'view.php'  => file_get_contents("$base/app/views/partials/joeya/view.php"),
    'edit.php'  => file_get_contents("$base/app/views/partials/joeya/edit.php"),
);
$template_sql = file_get_contents("$base/database/migrations/2026-08-07_create_joeya.sql");

$created = 0;
$errors = array();

foreach ($machines as $m) {
    $key   = $m['key'];
    $label = $m['label'];
    $class = $m['class'];
    $pk    = "id_$key";

    echo "\n=== Generating: $label ($key) ===\n";

    // --- Controller ---
    $ctrl = $template_controller;
    $ctrl = str_replace('Autonomous Maintenance Joeya (Filling)', "Autonomous Maintenance $label (Filling)", $ctrl);
    $ctrl = str_replace('class JoeyaController', "class {$class}Controller", $ctrl);
    $ctrl = str_replace("\$this->tablename = 'joeya'", "\$this->tablename = '$key'", $ctrl);
    // Kendala table (both quoted and bare for raw SQL)
    $ctrl = str_replace("'kendala_joeya'", "'kendala_$key'", $ctrl);
    $ctrl = str_replace('kendala_joeya', "kendala_$key", $ctrl);
    // Primary key column
    $ctrl = str_replace('id_joeya', $pk, $ctrl);
    // Display labels
    $ctrl = str_replace('AM Joeya', "AM $label", $ctrl);
    $ctrl = str_replace("'Joeya'", "'$label'", $ctrl);
    // View paths
    $ctrl = str_replace("'joeya/", "'$key/", $ctrl);
    // Redirect paths
    $ctrl = str_replace("redirect('joeya')", "redirect('$key')", $ctrl);
    // Error message
    $ctrl = str_replace("for Joeya", "for $label", $ctrl);
    // Table alias prefix in SQL queries (MUST be last to avoid conflicts)
    $ctrl = str_replace('joeya.', "$key.", $ctrl);

    $ctrl_path = "$base/app/controllers/{$class}Controller.php";
    if (file_exists($ctrl_path)) {
        $errors[] = "SKIP (exists): $ctrl_path";
        echo "  SKIP controller (already exists)\n";
    } else {
        file_put_contents($ctrl_path, $ctrl);
        echo "  Created controller\n";
        $created++;
    }

    // --- Views ---
    $view_dir = "$base/app/views/partials/$key";
    if (!is_dir($view_dir)) { mkdir($view_dir, 0755, true); }

    foreach ($template_views as $filename => $tpl) {
        $view = $tpl;
        $view = str_replace("assets/images/joeya/joeya ", "assets/images/$key/$key ", $view);
        $view = str_replace("'joeya-add-'", "'$key-add-'", $view);
        $view = str_replace('id="joeya-add-form"', "id=\"$key-add-form\"", $view);
        $view = str_replace('Autonomous Maintenance Joeya', "Autonomous Maintenance $label", $view);
        $view = str_replace('Add New Joeya', "Add New $label", $view);
        $view = str_replace('AM Joeya', "AM $label", $view);
        $view = str_replace('>Joeya<', ">$label<", $view);
        $view = str_replace("'Joeya'", "'$label'", $view);
        $view = str_replace("print_link('joeya", "print_link('$key", $view);
        $view = str_replace("print_link(\"joeya", "print_link(\"$key", $view);
        $view = str_replace("'joeya/list2", "'$key/list2", $view);
        $label_lower = strtolower($label);
        $view = str_replace("stripos(\$o['label'], 'joeya')", "stripos(\$o['label'], '$label_lower')", $view);
        $view = str_replace("stripos(\$option['label'], 'joeya')", "stripos(\$option['label'], '$label_lower')", $view);
        $view = str_replace('data Joeya', "data $label", $view);
        $view = str_replace('Joeya harus', "$label harus", $view);
        $view = str_replace('modul ini sengaja tidak memakai entry SIG', 'modul ini sengaja tidak memakai entry SIG', $view);
        // Catch remaining 'joeya' references in render_page
        $view = str_replace("'joeya/", "'$key/", $view);
        // Primary key in data arrays (view.php: $data['id_joeya'], list2.php: $row['id_joeya'])
        $view = str_replace('id_joeya', $pk, $view);
        // Kendala table name in raw references
        $view = str_replace('kendala_joeya', "kendala_$key", $view);

        $view_path = "$view_dir/$filename";
        if (file_exists($view_path)) {
            $errors[] = "SKIP (exists): $view_path";
            echo "  SKIP view $filename (already exists)\n";
        } else {
            file_put_contents($view_path, $view);
            echo "  Created view $filename\n";
            $created++;
        }
    }

    // --- SQL Migration ---
    $sql = $template_sql;
    $sql = str_replace('Autonomous Maintenance Joeya (Filling)', "Autonomous Maintenance $label (Filling)", $sql);
    $sql = str_replace('`joeya`', "`$key`", $sql);
    $sql = str_replace('`id_joeya`', "`$pk`", $sql);
    $sql = str_replace('`kendala_joeya`', "`kendala_$key`", $sql);

    $sql_path = "$base/database/migrations/2026-08-07_create_{$key}.sql";
    if (file_exists($sql_path)) {
        $errors[] = "SKIP (exists): $sql_path";
        echo "  SKIP migration (already exists)\n";
    } else {
        file_put_contents($sql_path, $sql);
        echo "  Created migration\n";
        $created++;
    }
}

echo "\n========== SUMMARY ==========\n";
echo "Files created: $created\n";
if ($errors) {
    echo "Skipped (already existed):\n";
    foreach ($errors as $e) echo "  $e\n";
}
echo "Done.\n";
