<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://https://Chapie.com
 * @since      1.0.0
 *
 * @package    Chapie
 * @subpackage Chapie/public/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<?php 
if (!session_id()) {
    session_start();
}

if (isset($_SESSION['unique_id']) && isset($_SESSION['user_id'])) {
    $unique_id = $_SESSION['unique_id'];
    $user_id = $_SESSION['user_id'];

    global $wpdb;
    $chapie_chat_usermeta = $wpdb->prefix . 'chapie_chat_usermeta';

    $status = 'Active now';
    $wpdb->update(
        $chapie_chat_usermeta,
        array('status' => $status),
        array('unique_id' => $unique_id, 'user_id' => $user_id),
        array('%s'),
        array('%d', '%d')
    );
}
?>

<div class="chapie-chat clearfix">
    <div class="chapie-chat-box">
        <div class="people-list expanded" id="people-list">
        <?php if (is_user_logged_in()) { ?>
                <div class="search">
                    <input type="text" placeholder="search" />
                    <i class="fa fa-search"></i>
                </div>
                <ul class="list">

                    <?php $current_user = wp_get_current_user();
                        $current_user_id = $current_user->ID;
                        $users = get_users();
                        $count = 0;
                        $status = 'offline';
                        $img = 'https://s3-us-west-2.amazonaws.com/s.cdpn.io/195612/chat_avatar_02.jpg';
                        foreach ($users as $user) { 
                            if($current_user_id != $user->ID){
                                if ($count % 2 == 0) {
                                $status = 'online';
                                $img = 'https://s3-us-west-2.amazonaws.com/s.cdpn.io/195612/chat_avatar_01.jpg';
                                } else {
                                    $status = 'offline';
                                    $img = 'https://s3-us-west-2.amazonaws.com/s.cdpn.io/195612/chat_avatar_02.jpg';
                                }
                                ?>
                                <a href="javascript:void(0);" class="chatuser" user-id="<?php echo $user->ID; ?>">
                                    <li class="clearfix">
                                        <img src="<?php echo $img; ?>" alt="avatar" />
                                        <div class="about">
                                            <div class="name"><?php echo $user->display_name; ?></div>
                                            <div class="status">
                                            <i class="fa fa-circle <?php echo $status; ?>"></i> <?php echo $status; ?>
                                            </div>
                                        </div>
                                    </li>
                                </a><?php
                                $count++;
                            }
                        }
                    ?>
                </ul>
            <?php }else{ ?>
                <div class="login_first" style="background-image:(<?php echo CHAPIE_PLUGIN_PUBLIC_IMAGES_URL; ?>/login_first.jpg);">
                    <p>Please Login to website.</p>
                </div>
           <?php } ?>
        </div>
        <div class="chat">
        <div class="chat-header clearfix">
            <a href="javascript:void(0);" class="backchatuser"><i class="bi bi-arrow-left-circle"></i></a>
            <img src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/195612/chat_avatar_01_green.jpg" alt="avatar" />
            <div class="chat-about">
            <div class="chat-with"></div>
            <div class="chat-num-messages">902 messages</div>
            </div>
            <div class="status">
                <i class="fa fa-circle online"></i>
            </div>
        </div>
        
        <div class="chat-history">
            <ul id="chat-history-box" class="chat-history-box">
                <li class="clearfix">
                    <div class="message-data align-right">
                        <span class="message-data-time" >10:10 AM, Today</span> &nbsp; &nbsp;
                        <span class="message-data-name" >Olia</span> <i class="fa fa-circle me"></i>
                    </div>
                    <div class="message other-message float-right">
                        Hi
                    </div>
                </li>
                
                <li>
                    <div class="message-data">
                    <span class="message-data-name"><i class="fa fa-circle online"></i> Vincent</span>
                    <span class="message-data-time">10:12 AM, Today</span>
                    </div>
                    <div class="message my-message">
                    Are we meeting today?
                    </div>
                </li>
            
            </ul>

            <!-- <li>
                <div class="message-data">
                <span class="message-data-name"><i class="fa fa-circle online"></i> Vincent</span>
                <span class="message-data-time">10:31 AM, Today</span>
                </div>
                <i class="fa fa-circle online"></i>
                <i class="fa fa-circle online" style="color: #AED2A6"></i>
                <i class="fa fa-circle online" style="color:#DAE9DA"></i>
            </li> -->
            
        </div> <!-- end chat-history -->
        
        <div class="chat-message clearfix">
            <textarea name="message-to-send" id="message-to-send" placeholder ="Type your message" rows="3"></textarea>
            <button class="send-message"><i class="bi bi-arrow-return-left"></i></button>    
            <button class="send-file" style="display: none;"><i class="fa fa-file-o"></i></button>    
            <button class="send-image" style="display: none;"><i class="fa fa-file-image-o"></i></button>    
        </div> <!-- end chat-message -->
        
        </div> <!-- end chat -->
    </div> <!-- end chapie chat box -->
    <div class="icon">
        <div class="user">
        <i class="bi bi-person-circle me-2"></i>
        <?php if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $current_username = $current_user->display_name;
                echo $current_username;
            }else{
                echo "Guest";
            } ?>
        </div>
        <i class="chat-opn bi bi-x-lg"></i>
        <i class="chat-cls bi bi-chat-dots-fill"></i>
    </div>
</div> <!-- end container -->

<script id="message-template" type="text/x-handlebars-template">
  <li class="clearfix">
    <div class="message-data align-right">
    <span class="message-data-time" >{{time}}, Today</span> &nbsp; &nbsp;
    <span class="message-data-name" >Olia</span> <i class="fa fa-circle me"></i>
    </div>
    <div class="message other-message float-right">
    {{messageOutput}}
    </div>
</li>

</script>

<script id="message-response-template" type="text/x-handlebars-template">
  <li>
    <div class="message-data">
      <span class="message-data-name"><i class="fa fa-circle online"></i> Vincent</span>
      <span class="message-data-time">{{time}}, Today</span>
    </div>
    <div class="message my-message">
      {{response}}
    </div>
  </li>
</script>
