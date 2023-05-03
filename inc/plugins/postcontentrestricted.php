<?php
/***************************************************************************
 *
 *  Post Content Restricted
 *  Author: Taylor M (aka c.widow)
 *  Copyright: TNM Freelance © 2023 - Present
 *  
 *  GitHub: https://github.com/taylornmount
 *
 *  The author of this plugin is not responsible for damages caused by this
 *  plugin. Use at your own risk.
 *  
 *  This software is provided by the copyright holders and contributors “as is”
 *  and any express or implied warranties, including, but not limited to, the 
 *  implied warranties of merchantability and fitness for a particular purpose
 *  are disclaimed. In no event shall the copyright owner or contributors be 
 *  liable for any direct, indirect, incidental, special, exemplary, or 
 *  consequential damages (including, but not limited to, procurement of substitute
 *  goods or services; loss of use, data, or profits; or business interruption)
 *  however caused and on any theory of liability, whether in contract, strict 
 *  liability, or tort (including negligence or otherwise) arising in any way 
 *  out of the use of this software, even if advised of the possibility of such damage.
 *
 ***************************************************************************/

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB")) {
	die("Direct initialization of this file is not allowed.");
}

function postcontentrestricted_info() {
	return array(
		"name"			=> "Post Content Restricted",
		"description"	=> "Restrict the content of posts from guest view in all forums except the ones you choose to keep public. Guests will still be able to see these boards and the thread listing within them but when they open the thread it will give them the \"No Permissions\" error.",
		"website"		=> "https://freelance.taylornmount.com/",
		"author"		=> "Taylor M (c.widow)",
		"authorsite"	=> "https://freelance.taylornmount.com/",
		"version"		=> "1.0",
		"guid" 			=> "",
		"codename"		=> "",
		"compatibility" => "18*"
	);
}

function postcontentrestricted_install() {
    global $db, $mybb;
    // build settings
    $setting_group = array(
        'name' => 'wccpcr',
        'title' => 'Post Content Restricted',
        'description' => 'Set the forums in which showthread will deny guest access to view thread content.',
        'disporder' => 0,
        'isdefault' => 0
    );
    $gid = $db->insert_query("settinggroups", $setting_group);

    $setting_array = array(
        'wccpcr_enable' => array(
            'title' => 'Enable Post Content Restriction',
            'description' => 'Selecting yes will turn this plugin on.',
            'optionscode' => 'yesno',
            'value' => '0', 
            'disporder' => 0
        ),
        'wccpcr_ignoreforums' => array(
            'title' => 'Forums To Ignore',
            'description' => 'Select the forums you wish to allow guests to still see post/thread content in.',
            'optionscode' => "forumselect",
            'value' => '',
            'disporder' => 1
        ),
    );
    foreach($setting_array as $name => $setting)
    {
        $setting['name'] = $name;
        $setting['gid'] = $gid;
        $db->insert_query('settings', $setting);
    }
    rebuild_settings();
}

function postcontentrestricted_is_installed() {
    global $mybb;
    // check if the enabled setting exists, if yes we are good to go
    return $mybb->settings['wccpcr_enable'];
}

function postcontentrestricted_uninstall() {
    global $db;
    // remove settings
    $db->delete_query('settings', "name LIKE 'wccpcr_%'");
    $db->delete_query('settinggroups', "name = 'wccpcr'");
    rebuild_settings();
}

function postcontentrestricted_activate() {
    global $db;
    // enable plugin setting
    $db->update_query("settings", array('value' => 1), "name='wccpcr_enable'");
    rebuild_settings();
}

function postcontentrestricted_deactivate() {
    global $db;
    // disable plugin setting
    $db->update_query("settings", array('value' => 0), "name='wccpcr_enable'");
    rebuild_settings();
}

$plugins->add_hook('showthread_start', 'postcontentrestricted_dopcrthing');
function postcontentrestricted_dopcrthing() {
    global $db, $mybb, $fid;
    if ($mybb->settings['wccpcr_enable'] == 1 && $mybb->user['usergroup'] == 1) {
        // fetch ignored forums
        $fetchignorequery = $db->simple_select("settings", "*", "name='wccpcr_ignoreforums'");
        $fetchignorevalue = $db->fetch_field($fetchignorequery, "value");
        $fetchignorearray = explode(',', $fetchignorevalue);
        if (!in_array($fid, $fetchignorearray)) {
            error_no_permission();
        }
    }
}
