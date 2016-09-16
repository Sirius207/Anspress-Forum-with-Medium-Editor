
<?php
/**
 * AnsPress question info widget
 * Widget for showing question stats
 * @package AnsPress
 * @author Rahul Aryan <support@anspress.io>
 * @license GPL 2+ GNU GPL licence above 2+
 * @link http://anspress.io
 * @since 2.0.0-alpha2
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
        wp_die();
}

class AnsPress_Info_Widget extends WP_Widget {

        /**
         * Initialize the class
         */
        public function __construct() {
                parent::__construct(
                        'ap_info_widget',
                        __( '(AnsPress) Question Info', 'anspress-question-answer' ),
                        array( 'description' => __( 'Shows question stats in single question page.', 'anspress-question-answer' ) )
                );
        }

        public function widget( $args, $instance ) {
                $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
                $title = apply_filters( 'widget_title', $title );

                echo $args['before_widget'];
                if ( ! empty( $title ) ) {
                        echo $args['before_title'] . $title . $args['after_title'];
                }

                $ans_count          = ap_question_get_the_answer_count();
                $last_active        = ap_question_get_the_active_ago();
                $total_subs         = ap_question_get_the_subscriber_count();
                $view_count         = ap_question_get_the_view_count();
                $license            = ap_question_get_the_license();
                $last_active_time = ap_human_time( mysql2date( 'G', $last_active ) );
                /*save the question ID in $question_id: prevent the change of $question object later */
                $question_id = ap_question_get_the_ID();
                /*configure questions object: query all the author`s question  */
                global $questions;
                /*configure the post object to get current question object again*/
                global $post;        
                echo '<div class="ap-widget-inner">';

                if ( is_question() ) { 
                                
                        echo'<ul class="ap-stats-widget" style="background-color:#f9f9f9;">
                                <li style="        padding: .5em .5em .4em .8em;
                                                                    border-bottom: 1px solid #ccc;
                                                                    background: linear-gradient(#fff,#e9e9e9);
                                                                    border-radius: 5px 5px 0 0;
                                                                    font-size: 18px;">
                                            <b>About This Post</b>
                            </li>';
                                    /*get question object, display the favorites votes*/
                                    ap_the_question(); 
                                echo'<li>
                                        <div class="row">
                                                <div class="col-md-6">
                                                        <span class="stat-label apicon-eye"></span><b>';
                                                        //printf(__( 'Views', 'anspress-question-answer' )); 
                                                        /*display view times & vote times */
                                                        printf( sprintf( _n( 'One time', '%d views', $view_count, 'anspress-question-answer' ), $view_count ) ); 
                                                        echo'</b><br>
                                                        <span class="stat-label apicon-heart"></span>
                                                        <span class="net-vote-count" data-view="ap-net-vote" itemprop="upvoteCount">
                                                                <b>'.ap_net_vote().' favorites'.'</b>
                                                        </span>
                                                </div>
                                                <div class="col-md-6">';
                                                        /* display post time*/
                                                        ap_question_the_time();
                                echo'</div>
                                        </li>';
                                echo '<li>';
                                    echo $license;
                                echo'</li>
                                <li style="height:120px;">
                                        <div class="ap-avatar" style="position:relative;">
                                    <a href="'; ap_question_the_author_link();  echo'">';/*display user avatar*/
                                                       ap_question_the_author_avatar( ap_opt('avatar_size_qquestion' ) );
                            echo '</a>
                            </div>
                                  <div style="display:inline-block;">
                                          <a class="ap-uw-name" style="font-size:20px;" href="'; ap_user_the_link();echo'">';
                                                  /* display user name*/
                                                  ap_user_the_display_name();
                                          echo'</a>';
                                          /* display subscribe button*/
                                    ap_subscribe_btn_html();          
                    echo'</div>
                            <div>
                                    <p style="margin-bottom:0;">Bio:<br>';
                                            /*display user introduction*/
                                            ap_user_the_meta('description' ); 
                                    echo '</p>
                            </div>
                    </li>
                    <li>
                            <strong>More By '; ap_user_the_display_name(); echo': </strong>';/*display user name*/
                            // $questions = new Question_Query( array( 'author' => ap_question_get_author_id() ) );
                            $questions = ap_get_questions( array( 'author' => ap_get_displayed_user_id() ) );
                                   if ( ap_have_questions() ) : 
                                                echo'<div class="ap-questions">';
                                                /* Start the Loop */
                                                        while ( ap_questions() ) : ap_the_question();
                                                                echo '<div class="no-overflow" style="margin:3px 0;"><a href="';
                                                                                ap_question_the_permalink();
                                                                                echo '" class="ap-user-posts-title">';
                                                                                the_title();
                                                                echo '</a></div>';  
                                                        endwhile;
                                                echo '</div>';
                                        else :_e('No question asked by this user yet.', 'anspress-question-answer');
                                        endif;
                                echo '</li>';
                                $post = get_post( $question_id, OBJECT );
                                echo '<li>
                                                <div class="ap-question-meta clearfix">';
                                                /*display question tag list*/
                                                 echo ap_display_question_metas(); 
                                echo '</div>
                                        </li>';
            echo '</ul>';
                        } else {
                                _e( 'This widget can only be used in single question page', 'anspress-question-answer' );
                        }  

                echo '</div>';
                        echo $args['after_widget']; 
        }

        public function form( $instance ) {
                if ( isset( $instance[ 'title' ] ) ) {
                        $title = $instance[ 'title' ];
                } else {
                        $title = __( 'Question stats', 'anspress-question-answer' );
                }
                ?>
        <p>
                        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'anspress-question-answer' ); ?></label>
                        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
                <?php
        }

        /**
         * Sanitize widget form values as they are saved.
         *
         * @see WP_Widget::update()
         *
         * @param array $new_instance Values just sent to be saved.
         * @param array $old_instance Previously saved values from database.
         *
         * @return array Updated safe values to be saved.
         */
        public function update( $new_instance, $old_instance ) {
                $instance = array();
                $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

                return $instance;
        }
}

function ap_info_register_widgets() {
        register_widget( 'AnsPress_Info_Widget' );
}

add_action( 'widgets_init', 'ap_info_register_widgets' );