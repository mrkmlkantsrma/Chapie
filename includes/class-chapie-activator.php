<?php

/**
 * Fired during plugin activation
 *
 * @link       https://https://Chapie.com
 * @since      1.0.0
 *
 * @package    Chapie
 * @subpackage Chapie/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Chapie
 * @subpackage Chapie/includes
 * @author     Chapie <Chapie@gmail.com>
 */
class Chapie_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		global $wpdb;

		$chapie_chat_metadata = $wpdb->prefix . 'chapie_chat_metadata';

		if ($wpdb->get_var("SHOW TABLES LIKE '$chapie_chat_metadata'") != $chapie_chat_metadata) {

			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE $chapie_chat_metadata (
				message_id int(255) NOT NULL AUTO_INCREMENT,
				incoming_message_id int(255) NOT NULL,
				outgoing_message_id int(255) NOT NULL,
				message varchar(1000) NOT NULL,
				message_time DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
				seen_time DATETIME DEFAULT NULL,
				deliver_status TINYINT(1) DEFAULT 0 NOT NULL,
				seen_status TINYINT(1) DEFAULT 0 NOT NULL,
				send_status TINYINT(1) DEFAULT 0 NOT NULL,
				PRIMARY KEY (message_id)
			) $charset_collate;";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
			dbDelta($sql);
		}

		$chapie_chat_usermeta = $wpdb->prefix . 'chapie_chat_usermeta';

		if ($wpdb->get_var("SHOW TABLES LIKE '$chapie_chat_usermeta'") != $chapie_chat_usermeta) {

			$charset_collate = $wpdb->get_charset_collate();
			$sql = "CREATE TABLE $chapie_chat_usermeta (
				id int(255) NOT NULL AUTO_INCREMENT,
				user_id int(255) NOT NULL,
				unique_id int(255) NOT NULL,
				fname varchar(255) NOT NULL,
				lname varchar(255) DEFAULT NULL,
				email varchar(255) NOT NULL,
				password varchar(255) NOT NULL,
				img varchar(255) DEFAULT NULL,
				status varchar(255) NOT NULL,
				PRIMARY KEY  (id)
			) $charset_collate;";

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
			dbDelta($sql);
		}

		$users = get_users();

		foreach ($users as $user) {

			$user_id = $user->ID;
			$email = $user->user_email;
			$name_parts = explode(' ', $user->display_name);
			$fname = isset($name_parts[0]) ? $name_parts[0] : '';
			$lname = isset($name_parts[1]) ? $name_parts[1] : '';
			$unique_id = rand(time(), 100000000);
			$status = 'Offline now';
			$img = get_avatar_url($user_id);

			$user_exists = $wpdb->get_var($wpdb->prepare(
				"SELECT COUNT(*) FROM $chapie_chat_usermeta WHERE user_id = %d OR email = %s",
				$user_id, $email
			));

			if ($user_exists == 0) {
				$wpdb->insert(
					$chapie_chat_usermeta,
					array(
						'user_id' => $user_id,
						'unique_id' => $unique_id,
						'fname' => $fname,
						'lname' => $lname,
						'email' => $email,
						'password' => $unique_id,
						'img' => $img,
						'status' => $status
					),
					array(
						'%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s'
					)
				);
			}
		}
	}

}
