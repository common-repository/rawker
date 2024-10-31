<?php 

print_r($_POST);

if (!$options = get_option('rawker_options')) {
	$options = array ('header_id' => 'header');
	add_option('rawker_options', $options);
}

if (!$images = get_option('rawker_images')) {
	$images = array ();
	add_option('rawker_images', $images);
}

if (isset($_POST['header_id'])) {
	$header = $_POST['header_id'];
  $options = array ('header_id' => $header);
  update_option('rawker_options', $options);
	$rawker->update_message("Header location saved as '$header'.");
} elseif (isset($_POST['modify_header'])) {
	// Modifying stuff.
} elseif (isset($_POST['add_header'])) {
	$group = $_POST['new_group'] ? $_POST['new_group'] : 0;
	$images[] = array('img' => $_POST['new_image'], 'group' => $group);
	update_option('rawker_images', $images);
	$rawker->update_message('Image successfully added.');
} elseif (isset($_GET['delete_header'])) {
	unset($images[$_GET['delete_header']]);
  update_option('rawker_images', $images);
  $rawker->update_message('Image successfully removed.');
}

?>
<div class="wrap">
  <h2><?php _e('Options', RAWKER_DOMAIN) ?></h2>
  <form method="post">
  <fieldset class="options">
	  <?php _e("This should match the ID of your theme's header element.  For the default theme, Kubrick, this is 'header'.", RAWKER_DOMAIN) ?>
	  <p><label for="header_id"><strong><?php _e("Header ID:", RAWKER_DOMAIN) ?></strong> <input id="header_id" name="header_id" value="<?= $options['header_id'] ?>" /></label></p>
    <p class="submit">
      <input type="submit" name="Submit" value="Save options &raquo;" />
    </p>
  </fieldset>
  </form>
</div>
<div class="wrap">
  <h2 id="banner"><?php _e('Headers', RAWKER_DOMAIN) ?> (<a href="#new"><?php _e('Add New', RAWKER_DOMAIN) ?></a>)</h2>
  <table width="100%" cellspacing="3" cellpadding="3">
    <tr>
      <th scope="col"><?php _e('Image', RAWKER_DOMAIN) ?></th>
      <th scope="col"><?php _e('Translation Group', RAWKER_DOMAIN) ?></th>
      <th scope="col"><?php _e('Delete', RAWKER_DOMAIN) ?></th>
    </tr>
    <?php
		if ($images) {
    	foreach ($images as $key => $image) {
      	$css = ($key % 2) ? 'class="alternate"' : '';
      	?>
				<tr <?= $css ?>>
					<td><img src="<?= $image['img'] ?>" width="600"></td>
					<td><?= ($gengo && $image['group']) ? implode ('<br />', $wpdb->get_col("SELECT p.post_title FROM $wpdb->posts AS p INNER JOIN $gengo->post2lang_table AS p2l ON p.ID = p2l.post_id WHERE p2l.translation_group = $image[group]")) : '' ?></td>
					<td><a href="?page=<?= RAWKER_BASE_DIR . RAWKER_OPTIONS_PAGE ?>&delete_header=<?= $key ?>"><?php _e('Delete', RAWKER_DOMAIN) ?></a></td>
				</tr>
				<?php
    	}
		} else {
  		?><tr><td colspan="3"><?php _e('No headers added yet.', RAWKER_DOMAIN) ?></td></tr><?php
		}
		?>
	</table>
</div>
<script type="text/javascript">
function previewImage(url) {
  var img = new Image();
  img.src = url;
  img.onload = function() {
    document.getElementById('previewImg').src = url;
    document.getElementById('previewImg').style.display = 'block';
  }
}
</script>
<div class="wrap">
  <h2 id="new"><?php _e('Add New Header', RAWKER_DOMAIN) ?></h2>
  <form method="post">
    <table width="100%">
      <tr>
        <th valign="top"><?php _e('Choose an image', RAWKER_DOMAIN) ?></th>
        <td>
          <select name="new_image" onchange="previewImage(this.options[this.selectedIndex].value);">
            <option value=""><?php _e('Choose an image', RAWKER_DOMAIN) ?></option>
						<?php
						if ($image_dir = @dir(get_template_directory() . RAWKER_IMG_DIRECTORY)) {
					    $image_dir_uri = get_template_directory_uri() . RAWKER_IMG_DIRECTORY;
							while(false !== ($file = $image_dir->read())) {
								if (preg_match('/\w*(\.jpg|\.gif|\.png)$/', $file)) {
									?><option value="<?= "$image_dir_uri/$file" ?>"><?= $file ?></option><?php
								}
					    }
					  }
						?>
          </select>
        </td>
      </tr>
      <?php
			if ($gengo) {
				?>
	      <tr>
	        <th><?php _e('Select a language group') ?></th>
	        <td>
	        	<?php
	        	$default_language = ($personal = get_usermeta('gengo_default_language')) ? $personal : get_option('gengo_blog_default_language');
						if ($groups = $wpdb->get_results("SELECT p2l.translation_group, p.post_title FROM $wpdb->posts AS p INNER JOIN $gengo->post2lang_table AS p2l ON p2l.post_id = p.ID WHERE translation_group != 0 AND p2l.language_id = $default_language")) {
							?>
							<select name="new_group">
							<?php
							foreach ($groups as $group) {
								?>
								<option value="<?= $group->translation_group ?>"><?= $group->post_title ?></option>
								<?php
							}
							?>
							</select>
							<?php
						}
						?>
	        </td>
	      </tr>
	      <?php
			}
			?>
      <tr>
        <td colspan="3"><img id="previewImg" width="600" src="" style="display: none" /></td>
      </tr>
    </table>
    <p class="submit">
      <input type="submit" name="add_header" value="<?php _e('Add image', RAWKER_DOMAIN) ?> &raquo;" />
    </p>
  </form>
</div>