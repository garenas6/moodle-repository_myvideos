<?php

/**
 * This plugin is used to access myvideos files.
 *
 * @package    repository
 * @subpackage myvideos
 * @copyright  2013 David Monllaó
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->libdir . '/filelib.php');

/**
 * Myvideos repository class
 *
 * @package    repository
 * @subpackage myvideos
 * @copyright  2013 David Monllaó
 */
class repository_myvideos extends repository {

    /**
     * Constructor
     *
     * @param int $repositoryid repository ID
     * @param int $context context ID
     * @param array $options
     */
    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
        global $CFG, $USER;
        parent::__construct($repositoryid, $context, $options);
        $this->videos_path = $CFG->dataroot . '/myvideos/' . $USER->id . '/videos/';
    }

    public function get_listing($path = '', $page = '') {
        global $CFG, $OUTPUT, $DB;
        $list = array();
        $list['list'] = array();
        $list['manage'] = false;
        $list['dynload'] = true;
        $list['nologin'] = true;
        $list['nosearch'] = true;
        // retrieve list of files and directories and sort them
        $fileslist = array();
        if ($dh = opendir($this->videos_path)) {
            while (($file = readdir($dh)) != false) {
                if ( $file != '.' and $file !='..') {
                    if (is_file($this->videos_path.$file)) {
                        $fileslist[] = $file;
                    }
                }
            }
        }

        collatorlib::asort($fileslist, collatorlib::SORT_STRING);
        foreach ($fileslist as $file) {

            // Getting video's title.
            list($userid, $timestamp, $videoid) = explode('_', substr($file, 0, strpos($file, '.')));
            $title = $DB->get_field('myvideos_video', 'title', array('id' => $videoid));

            $thumbparams = array('videoid='.$videoid, 'thumb=1');
            $thumburl = $CFG->wwwroot . '/repository/myvideos/getfile.php?' .
                implode('&', $thumbparams);

            $list['list'][] = array(
                'title' => $title,
                'source' => $this->videos_path.$file,
                'size' => filesize($this->videos_path.$file),
                'datecreated' => filectime($this->videos_path.$file),
                'datemodified' => filemtime($this->videos_path.$file),
                'thumbnail' => $thumburl,
                'icon' => $thumburl
            );
        }

        return $list;
    }

    /**
     * Return file path
     * @return array
     */
    public function get_file($file, $title = '') {
        global $CFG;
        // this is a hack to prevent move_to_file deleteing files
        // in local repository
        $CFG->repository_no_delete = true;
        return array('path'=>$file, 'url'=>'');
    }

    /**
     * User cannot use the external link to dropbox
     *
     * @return int
     */
    public function supported_returntypes() {
        return FILE_INTERNAL;
    }

    /**
     * Repository method to serve the referenced file
     *
     * @see send_stored_file
     *
     * @param stored_file $storedfile the file that contains the reference
     * @param int $lifetime Number of seconds before the file should expire from caches (default 24 hours)
     * @param int $filter 0 (default)=no filtering, 1=all files, 2=html files only
     * @param bool $forcedownload If true (default false), forces download of file rather than view in browser/plugin
     * @param array $options additional options affecting the file serving
     */
    public function send_file($storedfile, $lifetime=86400 , $filter=0, $forcedownload=false, array $options = null) {
        $reference = $storedfile->get_reference();
        if ($reference{0} == '/') {
            $file = $this->videos_path.substr($reference, 1, strlen($reference)-1);
        } else {
            $file = $this->videos_path.$reference;
        }
        if (is_readable($file)) {
            $filename = $storedfile->get_filename();
            if ($options && isset($options['filename'])) {
                $filename = $options['filename'];
            }
            $dontdie = ($options && isset($options['dontdie']));
            send_file($file, $filename, $lifetime , $filter, false, $forcedownload, '', $dontdie);
        } else {
            send_file_not_found();
        }
    }
}
