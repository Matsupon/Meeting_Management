<?php

// This path comes from your previous error screen
$target = '/home/vol1_6/infinityfree.com/if0_41428058/htdocs/storage/app/public';
$shortcut = '/home/vol1_6/infinityfree.com/if0_41428058/htdocs/public/storage';

if (file_exists($shortcut)) {
    echo "The shortcut already exists. Checking if it's a folder or link...<br>";
    if (is_link($shortcut)) {
        echo "It is a symlink.";
    } else {
        echo "It is a regular folder. You might need to delete the 'storage' folder inside 'public' first via FTP.";
    }
} else {
    if (symlink($target, $shortcut)) {
        echo "Success! The storage link has been created.";
    } else {
        echo "Failed to create the link. Check if the target path is correct.";
    }
}
?>