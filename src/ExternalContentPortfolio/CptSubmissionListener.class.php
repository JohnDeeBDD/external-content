<?php

namespace ExternalContentPortfolio;

class CptSubmissionListener{
    
    public function listenForFormSubmission(){
        if (isset($_POST['add-this-url'])){
            $this->createNewCpt($_POST['add-this-url']);
        }
        if (isset($_GET['external-content'])){
            //add_action('admin_footer', array($this, "openNewTabs"));
        }
        if(isset($_GET['external-content-rescan'])){
            $Rescanner = new Rescanner;
            $Rescanner->rescanContent();
        }
    }

    public function isFormSubmitted() {
        if (
            //die("isFormSubmitted!");
            (isset($_POST['add-this-url'])) or
            (isset($_GET['external-content-rescan'])) or
            (isset($_GET['external-content-rescan']))
        ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function createNewCpt($Url){
        $cUrlProcessor  = new cUrlProcessor();
        $cUrlProcessor->fetch($Url);
        $title          = $cUrlProcessor->title;
        $siteType       = $cUrlProcessor->siteType;
        $content        = "This is a new post. PROCESSING. Click 'refresh' in your browser.";
        $args           = array(
            'post_title'    => $title,
            'post_status'   => 'draft',
            'post_type'     => 'external-content',
            'post_content'  => $content,
        );
        $ID             =  wp_insert_post( $args );
        update_post_meta( $ID, "remoteUrl", $Url );
        update_post_meta( $ID, "siteType", $siteType );
        wp_set_post_tags( $ID, "external-content processing" );
        return $ID;
    }

}