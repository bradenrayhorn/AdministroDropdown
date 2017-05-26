<?php
    $plugin = $administro->plugins['Dropdown'];
    $plugin->load();
?>
<script src="//rubaxa.github.io/Sortable/Sortable.js"></script>
<div class='title'>
    Dropdown File Viewer
</div>
<div class='spacer'></div>
<?php
    foreach($plugin->dropdowns as $id => $dd) {
        echo '<div><b>' . $dd['name'] . '</b></div><div id="' . $id . '_sort">';
        foreach($dd['files'] as $name => $file) {
            echo '<div data-id="' . $name . '">- ' . $name . ' (' . $file . ')</div>';
        }
        echo '</div>';
    }
?>
<div class='title sub'>
    Create Dropdown
</div>
<form method='post' action='<?php echo $administro->baseDir . 'form/adddropdown' ?>'>
    <div class='row'>
        <div class='two columns'>
            <label>Name</label>
            <input type='text' name='name' required>
        </div>
        <div class='two columns'>
            <label>Permission</label>
            <input type='text' name='permission'>
        </div>
    </div>
    <input type='hidden' name='nonce' value='<?php echo $administro->generateNonce('adddropdown'); ?>'>
    <input class="button-primary" type="submit" value="Create Dropdown">
</form>
<div class='title sub'>
    Add Item
</div>
<form method='post' action='<?php echo $administro->baseDir . 'form/adddropdownfile' ?>' enctype='multipart/form-data'>
    <div class='row'>
        <div class='two columns'>
            <label>Dropdown</label>
            <select name='dropdown'>
                <?php
                    foreach($plugin->dropdowns as $id => $dd) {
                        echo '<option value="' . $id . '">' . $dd['name'] . '</option>';
                    }
                ?>
            </select>
        </div>
        <div class='two columns'>
            <label>Item Name</label>
            <input type='text' name='name' required>
        </div>
    </div>
    <div class='row'>
        <label>File</label>
        <input type="file" name="file" required>
    </div>
    <input type='hidden' name='nonce' value='<?php echo $administro->generateNonce('adddropdownfile'); ?>'>
    <input class="button-primary" type="submit" value="Add Item">
</form>
<script>
    <?php
        foreach($plugin->dropdowns as $id => $dd) {
            echo 'var '.$id.'sort = Sortable.create('.$id.'_sort, {dataIdAttr: "data-id", onUpdate: function(evt){saveList("'.$id.'");}});';
        }
    ?>

    function saveList(id) {
        var e = window[id + 'sort'];
        var xmlhttp = new XMLHttpRequest();
        var params = "dropdown="+id+"&nonce=<?php echo $administro->generateNonce('sortdropdown'); ?>&data="+e.toArray();
        xmlhttp.open("POST", "<?php echo $administro->baseDir; ?>form/sortdropdown", true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(params);
    }
</script>
