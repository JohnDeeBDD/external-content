<?php

namespace ExternalContentPortfolio;

class ExternalContentPortfolioPlugin{
    
    public function __construct(){

    }
       
    public function registerCptExternalContent(){
        include_once('CPTexternalContent.php');
    }

    public function modifyExternalContentCptEditor(){
        add_action( 'admin_enqueue_scripts', array(new CptEditorListModifier, 'enqueueScripts' ));
    }
    
    public function listenForNewExternalContentCptSubmission(){

        $CptSubmissionListener = new CptSubmissionListener();
        if($CptSubmissionListener->isFormSubmitted()) {
            //add_action('init', array(new CptSubmissionListener, '';[]''));
        }
    }
}