<?php

/**
 * Fired during plugin ajax
 *
 * @link       https://https://Chapie.com
 * @since      1.0.0
 *
 * @package    Chapie
 * @subpackage Chapie/includes
 */

/**
 * Fired during plugin ajax.
 *
 * This class defines all code necessary to run during the plugin's ajax.
 *
 * @since      1.0.0
 * @package    Chapie
 * @subpackage Chapie/includes
 * author     Chapie <Chapie@gmail.com>
 */
class Chapie_Ajax {

    /**
     * Handle AJAX request to get user data.
     *
     * @since    1.0.0
     */
    public static function get_user_data() {
        // Check the nonce for security
        check_ajax_referer( 'chapie_nonce', 'security' );

        // Get the user ID from the AJAX request
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

        if ($user_id) {
            // Get user data
            $user_info = get_userdata($user_id);

            if ($user_info) {
                $response = array(
                    'success' => true,
                    'data'    => array(
                        'ID'       => $user_info->ID,
                        'username' => $user_info->user_login,
                        'email'    => $user_info->user_email,
                    ),
                );
            } else {
                $response = array('success' => false, 'data' => 'User not found');
            }
        } else {
            $response = array('success' => false, 'data' => 'Invalid user ID');
        }

        // Send the JSON response
        wp_send_json($response);
    }


    public static function chapie_send_message() {
        check_ajax_referer( 'chapie_nonce', 'security' );
    
        if ( ! isset( $_POST['message'] ) || empty( $_POST['message'] ) ) {
            wp_send_json_error( array( 'message' => 'No message provided' ) );
        }
        if ( ! isset( $_POST['reciever'] ) || empty( $_POST['reciever'] ) ) {
            wp_send_json_error( array( 'message' => 'No user provided' ) );
        }

        global $wpdb;

        $chapie_chat_usermeta = $wpdb->prefix . 'chapie_chat_usermeta';
        $chapie_chat_metadata = $wpdb->prefix . 'chapie_chat_metadata';

        $user_id = $_POST['reciever'];
        $reciever_id = $wpdb->get_var($wpdb->prepare("SELECT unique_id FROM $chapie_chat_usermeta WHERE user_id = %d", $user_id));

        $current_time = time();
        $timezone_offset_minutes = 330;
        $timezone_name = timezone_name_from_abbr("", $timezone_offset_minutes*60, false);
        date_default_timezone_set($timezone_name);

        $currentDate = new DateTime();
        $current_time = $currentDate->format('Y-m-d h:i A');

        $data = array(
            'incoming_message_id' => $_SESSION['unique_id'],
            'outgoing_message_id' => $reciever_id,
            'message' => sanitize_text_field($_POST['message']),
            'message_time' => $current_time
        );

        $format = array( '%d', '%d', '%s', '%s');
    
        $inserted = $wpdb->insert( $chapie_chat_metadata, $data, $format );
    
        if ( $inserted ) {
            $inserted_id = $wpdb->insert_id;

            $update_data = array(
                'send_status' => 1,
                'deliver_status' => 1
            );
    
            $update_format = array( '%d', '%d' );
    
            $updated = $wpdb->update( 
                $chapie_chat_metadata, 
                $update_data, 
                array( 'message_id' => $inserted_id ), 
                $update_format, 
                array( '%d' ) 
            );
    
            if ( $updated ) {
                wp_send_json_success( array( 'message' => $data['message'] ) );
            } else {
                wp_send_json_error( array( 'message' => 'Failed to update message status' ) );
            }
        } else {
            wp_send_json_error( array( 'message' => 'Failed to insert message' ) );
        }
    }


    public static function get_chapie_chat() {
        global $wpdb;
    
        $outgoing_id = intval($_SESSION['unique_id']);
        $reciever_id = intval($_POST['incoming_id']);
    
        $chapie_chat_usermeta = $wpdb->prefix . 'chapie_chat_usermeta';
        $chapie_chat_metadata = $wpdb->prefix . 'chapie_chat_metadata';

        $incoming_id = $wpdb->get_var($wpdb->prepare("SELECT unique_id FROM $chapie_chat_usermeta WHERE user_id = %d", $reciever_id));
        $incoming_id = intval($incoming_id);

        $output = "";
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT messages.*, users.* 
                 FROM {$chapie_chat_metadata} AS messages 
                 LEFT JOIN {$chapie_chat_usermeta} AS users 
                 ON users.unique_id = messages.incoming_message_id 
                 WHERE (messages.outgoing_message_id = %d AND messages.incoming_message_id = %d) 
                 OR (messages.outgoing_message_id = %d AND messages.incoming_message_id = %d) 
                 ORDER BY messages.message_id",
                $outgoing_id, $incoming_id, $incoming_id, $outgoing_id
            )
        );
        $count = count($results);
        $output = '<input type="hidden" value="'.$count.'" class="message_count">';

        if ($results) {
            foreach ($results as $row) {
                // echo '<pre>'; print_r($row); echo '</pre>';

                $timezone_offset_minutes = 330;
                $timezone_name = timezone_name_from_abbr("", $timezone_offset_minutes*60, false);
                date_default_timezone_set($timezone_name);
                $message_time = $row->message_time;
                $dateTime = DateTime::createFromFormat('Y-m-d h:i A', $message_time);

                // Get the current date and time in the site's timezone
                $currentDate = new DateTime();
                // Check if the message time is today
                if ($dateTime->format('Y-m-d') == $currentDate->format('Y-m-d')) {
                    $message_timeing = $dateTime->format('h:i A') . ", Today";
                } else {
                    $message_timeing = $dateTime->format('h:i A, Y-m-d');
                }
                if ($row->outgoing_message_id == $outgoing_id) {
                    $output .= '<li>
                                    <div class="message-data">
                                        <span class="message-data-name"><i class="fa fa-circle online"></i>'. ucfirst($row->fname).'</span>
                                        <span class="message-data-time">'.$message_timeing.'</span>
                                    </div>
                                    <div class="message my-message">' . esc_html($row->message) . '</div>
                                </li>';
                } else {
                    $output .= '<li class="clearfix">
                                    <div class="message-data align-right">
                                        <span class="message-data-time" >'.$message_timeing.'</span>
                                        <span class="message-data-name" >'.ucfirst($row->fname).'</span> <i class="fa fa-circle me"></i>
                                    </div>
                                    <div class="message other-message float-right">' . esc_html($row->message) . '</div>
                                </li>';
                }
            }
        } else {
            $output .= '<div class="no-messages">No messages to show.</div>';
        }
    
        echo $output;
        wp_die(); // Required to terminate immediately and return a proper response
    }
    /**
     * Register AJAX actions.
     *
     * @since    1.0.0
     */
    public static function register_ajax_actions() {
        add_action( 'wp_ajax_chapie_get_user_data', array( __CLASS__, 'get_user_data' ) );
        add_action( 'wp_ajax_nopriv_chapie_get_user_data', array( __CLASS__, 'get_user_data' ) );

        add_action( 'wp_ajax_chapie_send_message', array( __CLASS__, 'chapie_send_message' ) );
        add_action( 'wp_ajax_nopriv_chapie_send_message', array( __CLASS__, 'chapie_send_message' ) );

        add_action( 'wp_ajax_get_chapie_chat', array( __CLASS__, 'get_chapie_chat' ) );
        add_action( 'wp_ajax_nopriv_get_chapie_chat', array( __CLASS__, 'get_chapie_chat' ) );
    }
}

