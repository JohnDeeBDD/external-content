<?php

namespace ExternalContentPortfolio;

interface WPCronBackgroundProcessManagerInterface {
    /**
     * @return boolean
     * determines if the condition to continue the process is still active
     * used to initiate the process, and determine if the process should continue
     */
    public function isProcessConditionActive($tagID, $post_type);

    /**
     * @return bool; false for failure
     * @return bool; true for success
     */
    public function initiateProcess($whichProcess);

    /**
     * @return bool; false for failure
     * @return bool; true for success
     */
    public function continueProcess($tagID, $post_type);
}

class WPCronBackgroundProcessManager implements WPCronBackgroundProcessManagerInterface {

    public function enableBackgroundProcess(){
        add_action('continueProcess', array(new \Parler\WPCronBackgroundProcessManager, "continueProcess"), 10, 2);
        add_filter( 'cron_schedules', array($this, 'cronFiveSeconds' ));
    }

    public function cronFiveSeconds( $schedules ) {
        $schedules['five_seconds'] = array(
            'interval' => 5,
            'display'  => esc_html__( 'Every Five Seconds' ),
        );

        return $schedules;
    }

    public function isProcessConditionActive($tagID, $post_type) {
        $bool = FALSE;
        $args = array(
            'post_type' => $post_type,
            'posts_per_page' => 1,
            'post_status'   => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => 'parler',
                    'field' => 'id',
                    'terms' => $tagID,
                    'operator' => 'NOT IN'
                )
            )
        );
        $untagged = new \WP_Query($args);
        if ($untagged->have_posts()) {
            while ($untagged->have_posts()) {
                $untagged->the_post();
                $bool = TRUE;
            }
        }
        //var_dump($bool);die();
        return $bool;
        //return false;
    }

    public function initiateProcess($args) {
        $CPT = $args['CPT'];
        $termID =$args['termID'];
        // var_dump($args);die();

        if (! wp_next_scheduled (  'continueProcess', $args)) {
            if($this->isProcessConditionActive($termID, $CPT)){
                wp_schedule_event( time(), 'five_seconds',  'continueProcess', $args);
            }
        }
    }

    public function continueProcess($tagID, $post_type ){
        ob_start();
        var_dump($tagID);
        var_dump($post_type);
        $result = ob_get_clean();
        $my_post = array(
            'post_title'    => "RESULT: $result",
            'post_status'   => 'publish',
            'post_author'   => 1,
        );
        //wp_insert_post( $my_post );




        $CPT = "post";
        $termID =2;

        $args = array(
            'post_type' => $CPT,
            'posts_per_page' => 200,
            'post_status'   => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => 'parler',
                    'field' => 'id',
                    'terms' => $termID,
                    'operator' => 'NOT IN'
                )
            )
        );
        //ob_start();
        //var_dump($args);
        //$result = ob_get_clean();
        //$TRACEPOST = array('post_title'    => "RESULT $result",'post_status'   => 'publish','post_author'   => 1,);wp_insert_post( $TRACEPOST );
        $query = new \WP_Query( $args );
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                //  $TRACEPOST = array('post_title'    => "postID: $post_id tagID: $tagID",'post_status'   => 'publish','post_author'   => 1,);wp_insert_post( $TRACEPOST );
                $query->the_post();
                $post_id = get_the_ID();
                wp_set_post_terms($post_id, $termID, 'parler', TRUE);
            }
        }
        wp_reset_postdata();

        if(($this->isProcessConditionActive($termID, $CPT)) == FALSE){
            $details = array("CPT" => $CPT, "termID" => $termID);
            $timestamp = wp_next_scheduled( "continueProcess", $details);
            wp_unschedule_event( $timestamp,"continueProcess", $details);
        }
    }

    public function doProcess(){

    }


    public function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}