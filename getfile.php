<?php // $Id: getfile.php,v 1.3 2011/03/01 11:14:14 davmon Exp $

/**
 * Gets a file from the videos repository
 *
 * @package      blocks/myvideos
 * @copyright    2010 David Monllao <david.monllao@urv.cat>
 * @license      http://www.gnu.org/licenses/gpl-2.0.txt
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/lib/filelib.php');


$videoid = optional_param('videoid', 0, PARAM_INT);
$thumb = optional_param('thumb', 0, PARAM_INT);

$video = $DB->get_record('myvideos_video', array('id' => $videoid));

if (!$video || $video->link == 1) {
    die();
}

// Private video
if ($video->publiclevel == 0 && $video->userid != $USER->id &&
        !has_capability('moodle/site:doanything', context_system::instance())) {
    die();

// Moodle video (only accessible to authenticated Moodle users
} else if ($video->publiclevel == 1 && $USER->id == 0 &&
        !has_capability('moodle/site:doanything', context_system::instance())) {
    die();
}


if ($thumb) {
    $dir = 'thumbs';
    $resource = str_replace('.flv', '.jpg', $video->video);
} else {
    $dir = 'videos';
    $resource = $video->video;
}

session_write_close();

$filepath = $CFG->dataroot.'/myvideos/'.$video->userid.'/'.$dir.'/'.$resource;
send_file($filepath, $resource);

?>
