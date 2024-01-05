<?php
$postID = isset($_GET['postID']) && $_GET['postID'] !== ''  ?  sanitize_text_field($_GET['postID'])  : '';
$formID = isset($_GET['formID']) && $_GET['formID'] !== '' ? sanitize_text_field($_GET['formID']) : '';

if($postID == '' || $formID == ''){
    wp_die('Not Allowed Directely Access');
}

$form_id = absint($formID);
$all_forms_ls = get_post_meta($form_id, 'all_forms_ls', true);
$msf_form_questions = get_post_meta($form_id, 'msf_form_questions', true);

// echo "<pre>";
// print_r($all_forms_ls);

function get_all_entries($table_name){
    global $wpdb;
    $table_name = $wpdb->prefix . $table_name;
    $questions = $wpdb->get_results("SELECT * FROM $table_name");
    return $questions;
}

function get_specific_entry($table_name, $id){
    global $wpdb;
    $table_name = $wpdb->prefix . $table_name;
    $questions = $wpdb->get_results("SELECT * FROM $table_name WHERE id='$id'");
    return $questions;
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Cards</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <style>
            .line{
                position: relative;
            }
            .line:after {
                content: "";
                width: 0;
                height: 4px;
                background-color: #f3e000;
                transition: width 1s ease;
                animation-name: grow;
                animation-duration: 10s;
                animation-fill-mode: forwards;
                animation-iteration-count: infinite;
                position: absolute;
                left: 10px;
                bottom: 0;
                border-radius: 8px;
            }

            @keyframes grow {
                from {
                    width: 0;
                }
                to {
                    width: 90%;
                }
            }

            body{
                font-family: Helvetica;
                background-color: #f3f5f7;
            }
            .logo{
                width: 65%;
                margin: 0 auto;
                padding: 20px 0px;
            }
            .logo img{
                max-width: 150px;
            }
            .main{
                width: 60%;
                margin: 0 auto;
                border-radius: 10px;
                background-color: #fff;
                box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            }

            .main .text {
                padding: 40px 20px 0;
            }
            .main .cards-wrapper {
                padding: 0 20px 40px;
            }
            p{
                font-size: 19px;
                color: #616c7a;
            }
            .hover-it{
                display: flex;
            }

            .card input{
                /* do not disturb me */
            }
            .card label{
                font-size: 14px;
                font-weight: 600px;
                font-family: Helvetica;

            }
            .card{
                min-width: 310px;
                width: 100%;
            }
            .card-txt{
                margin-left: 10px
            }
            .main-radio-btn{
                display: flex;
                padding: 10px;
                border-radius: 10px;
                box-shadow: rgba(0, 0, 0, 0.12) 0px 1px 3px, rgba(0, 0, 0, 0.24) 0px 1px 2px;
            }
            .main-radio-btn:hover{
                background-color: #f8f9fa;
            }
            .card-down{
                position: relative;
                display: none;
                transition:  ease-out  1s;
                padding: 20px ;
                border-radius: 0px 0px 10px 10px;
                box-shadow: rgba(0, 0, 0, 0.12) 0px 1px 3px, rgba(0, 0, 0, 0.24) 0px 1px 2px;
            }
            .card-down.card-show {
                display: block;
            }
            .card.card-active .card-down {
                display: block;
            }
            .cards-wrapper {
                display: flex;
                gap: 20px;
                flex-wrap: wrap;
            }
            /* .intro .card-down{
                opacity: 1;
                top: 0px;
            
            } */
            form select{
                width: 100%;
                padding: 10px 10px;
                margin: 20px 0px;
                border-radius: 50px;
                border: 1px solid #c3c4c4;
            }
            .new{
                background-color: red;
            }

            form label{
                width: 100%;
                padding: 10px 0px;
                cursor:pointer;
            }
            .free-quote{
                width: 100%;
                font-size: 16px;
                border: none;
                border-radius: 50px;
                margin: 20px 0px;
                padding: 10px 0px;
                color: #ffff;
                background-color: <?php echo get_option('msf_button_bg') ? esc_attr(get_option('msf_button_bg')) : '#006eee'; ?>;
                cursor: pointer;
            }
            .free-quote:hover{
                background-color: <?php echo get_option('msf_button_bg') ? esc_attr(get_option('msf_button_bg')) : '#006eee'; ?>;
            }
            .privacy-p{
                font-size: 13px;
            }
            @media (max-width: 998px) {

            }

            @media (max-width: 560px) {
                .main{
                    width: 100%;
                }

                .question h2{
                    font-size: 14px;
                }
            }

             .input-group label{
                width: 100%;
                display: block;
                font-weight: bold;
                margin-bottom: 10px;
            }

            .q_choices{
                margin-top: 20px;
                /* display: none; */
            }

            .q_choices .q_choices_lable{
                font-weight: bold;
                margin: 10px 0;
                
            }
            .q_choices .q_choice_input .input-group{
                margin-bottom: 10px;
                padding-bottom: 10px;
                border-bottom: 1px solid #c3c4c7;
                display: flex;
                justify-content: space-between;
            }
            .q_choices .q_choice_input .input-group .q_choices_input{
                flex: 0 0 95%;
            }

            .msf-row .input-group button,
            .q_choices .add_q_choice{
                margin-left: auto;
                border: 0;
                background: #2c3338;
                color: #fff;
                padding: 10px 15px;
                border-radius: 5px;
                display: block;
                font-weight: bold;
                cursor: pointer;
            }

            .msf-row .input-group .save_question{
                margin-left: initial;
            }
            .remove{
                border: 0;
                background: #2c3338;
                color: #fff;
                padding: 4px 8px;
                border-radius: 5px;
                margin-left: 10px;
                cursor: pointer;
            }

            /* Leads Content */
            .msf-leads-content{
                /* background: white; */
                padding: 10px;
            }
            .msf-leads-content .msf-tabs-list{
                
                width: fit-content;
                border-bottom: none;
                border-top-left-radius: 10px;
                border-top-right-radius: 10px;
                margin-bottom: 4px;
            }

            .msf-leads-content .msf-tabs-list li{
                display: inline-block;
                margin-bottom: 0;
                background-color: white;
                outline: none;
            }

            .msf-leads-content .msf-tabs-list li:hover,
            .msf-leads-content .msf-tabs-list li.current{
                background-color: #2c3338;
            }

            .msf-leads-content .msf-tabs-list li:hover a,
            .msf-leads-content .msf-tabs-list li.current a{
                color: white;
            }

            .msf-leads-content .msf-tabs-list li:first-of-type{
                border-top-left-radius: 10px;
            }

            .msf-leads-content .msf-tabs-list li:last-of-type{
                border-top-right-radius: 10px;
            }



            .msf-leads-content .msf-tabs-list li a{
                display: block;
                padding: 10px 20px;
                text-decoration: none;
                color: #2c3338;
                font-weight: bold;
                font-size: 12px;
                outline: none;
            }

            .msf-tabs-content{
                background: white;
                min-height: 50vh;
                padding: 10px;
                border-radius: 0 10px 10px;
            }

            .msf-row{
                display: flex;
                gap: 10px;
                margin-bottom: 10px;
                flex-wrap: wrap;
            }

            .msf-row .msf-col-6{
                flex: 1;
                background: #fbfbfb;
                padding: 20px;
            }

          
            .msf-row .input-group -q{
                flex: 0 0 65%;
                margin-right: 20px;
            }
            .bg-grey{
                background: #f4f4f4b5;
            }

            .row_1{
                margin-bottom: 20px;
            }

            h2{
                margin-top: 0;
                margin-bottom: 10px;
                font-size: 18px;
            }

            label{
                cursor: initial;
                font-weight: bold;
                font-size: 14px;
                margin-bottom: -2px;
            }

            .options{
                margin-bottom: 20px;
            }

            .msf-col-12{
                margin-bottom: 10px;
            }

            .success_info{
                background-color: #f3f3f3;
                color: black;
                padding: 6px 10px;
                font-size: 16px;
                border-left: 8px solid green;
            }

            .error_info{
                background-color: #f3f3f3;
                color: black;
                padding: 6px 10px;
                font-size: 16px;
                border-left: 8px solid rgb(128, 0, 26);
            }


            .msf-heading {
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
            color: #333;
            }


            label {
            display: block;
            font-size: 14px;
            color: #333;
            }

            textarea,
            input[type="text"],
            select {
            border: none;
            border-radius: 5px;
            background-color: #f5f5f5 !important;
            padding: 10px !important;
            font-size: 14px;
            width: -webkit-fill-available;
            color: #333;
            }

            textarea:focus,
            input[type="text"]:focus,
            select:focus {
            outline: none;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            }

            .main .next_btn {
                text-align: center;
                background-color: <?php echo get_option('msf_button_bg_next') ? esc_attr(get_option('msf_button_bg_next')) : '#006eee'; ?>;
            }
            .main .next_btn:hover {
            background-color: <?php echo get_option('msf_button_bg_next_hover') ? esc_attr(get_option('msf_button_bg_next_hover')) : '#006eee'; ?>;
            }


            /* Popup container - can be anything you want */
            .popup {
            position: relative;
            display: inline-block;
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            }

            /* The actual popup */
            .popup .popuptext {
                visibility: hidden;
                width: 160px;
                background-color: #f5f5f5;
                color: #fff;
                text-align: center;
                border-radius: 6px;
                padding: 8px 0;
                position: absolute;
                z-index: 1;
                bottom: 125%;
                left: 50%;
                margin-left: -80px;
                padding: 40px 20px;
            }

            /* Popup arrow */
            .popup .popuptext::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #555 transparent transparent transparent;
            }

            /* Toggle this class - hide and show the popup */
            .popup .show {
            visibility: visible;
            -webkit-animation: fadeIn 1s;
            animation: fadeIn 1s;
            }

            /* Add animation (fade in the popup) */
            @-webkit-keyframes fadeIn {
            from {opacity: 0;} 
            to {opacity: 1;}
            }

            @keyframes fadeIn {
            from {opacity: 0;}
            to {opacity:1 ;}
            }
            h4.formSuccess{
                color: #12AD2B;
            }

            .d-none{
                display: none !important;
            }

            .overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.7); /* Black with 70% opacity */
                z-index: 1000; /* To ensure it's above other content */
                display: none; /* Initially hidden */
            }

            .popup {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%); /* Centering technique */
                background-color: white;
                border-radius: 10px;
                box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2); /* Some shadow for depth */
                z-index: 1001; /* Above the overlay */
                padding: 20px !important;
            }
            .question{
                cursor: pointer;
                border-radius: 10px;
                box-shadow: rgba(0, 0, 0, 0.12) 0px 0px 2px, rgba(0, 0, 0, 0.24) 0px 0px 2px;
                padding: 10px;
                margin-bottom: 10px;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .question h2{ 
                margin: 0;
            }

            .rotate{
                transform: rotate(180deg);
            }

        </style>
        <!-- TrustedForm -->
        <script type="text/javascript">
            (function() {
            var tf = document.createElement('script');
            tf.type = 'text/javascript'; tf.async = true;
            tf.src = ("https:" == document.location.protocol ? 'https' : 'http') + "://api.trustedform.com/trustedform.js?field=xxTrustedFormCertUrl&ping_field=xxTrustedFormPingUrl&l=" + new Date().getTime() + Math.random();
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(tf, s);
            })();
        </script>
        <noscript>
            <img src="https://api.trustedform.com/ns.gif" />
        </noscript>
        <!-- End TrustedForm -->
    </head>

    <body>
        <div class="main">
            <div class="text">
                <h2>Thank you, we are on it!</h2>
                <p>Our experts are now working to match you with the best home remodeling pros in your area.
                    Handpicked top contractors will provide you with a free quote that meets your project, budget
                    and quality requirements.</p>
                <p><b>Keep an eye on your phone! </b>You will receive a call about your free quote(s) shortly.</p>
                <hr>
                <h3>While you wait, see below additional services<br>
                    that have been very popular within our network recently.</h3>
                <p>Click any of the below, it is fast and efficient.</p>
            </div>
            <div class="cards-wrapper">
                <?php
                if(!empty($all_forms_ls)){
                    foreach($all_forms_ls as $formId){
                        $formId = absint($formId);
                        $post = get_post($formId);
                        if($formId === $form_id){
                            continue;
                        }
                        $htmlId = 'form_' . $formId;
                        $msf_form_questions = get_post_meta($formId, 'msf_form_questions', true);
                        ?>
                        <div class="hover-it"> <!--div for click to reveal full card-->
                            <div class="card"> <!--card-div-->
                                <label class="main-radio-btn" id="<?php echo $htmlId; ?>" for="<?php echo $htmlId; ?>">
                                    <input type="radio"  name="radio-card" class="radio-card" value="<?php echo $htmlId; ?>" parent="<?php echo $htmlId; ?>">
                                    <span class="card-txt" for="<?php echo $htmlId; ?>"><b><?php echo $post->post_title; ?></b></span><br>
                                </label>
                                <div class="card-down">
                                    <form action="" method="post">
                                        <?php
                                            if(!empty($msf_form_questions)){
                                                $count = 0;
                                                foreach($msf_form_questions as $ms_question){
                                                    $questions = get_specific_entry('questions', $ms_question['question_id']);
                                                    $count++;
                                                    $question = $questions[0];
                                                    $id = $question->id;
                                                    $cat_id = $question->category_id;
                                                    $required = $question->require;
                                                    $question_text = $question->question;
                                                    $field_name = $question->field_name;
                                                    $field_name = str_replace(' ', '_', $field_name);
                                                    $field_name = $field_name . '_' . $id;
                                                    $question_type = $question->question_type;
                                                    $options = unserialize($question->options);
                                                    if('1' === $ms_question['essential']){
                                                        ?>
                                                        <div class="slide">
                                                            <div class="question">
                                                                <h2>
                                                                    <?php echo $question_text; ?> 
                                                                    <sapn class="essential"><?php echo $required == 1 ? "*" : ""; ?></sapn>
                                                                </h2>
                                                                <svg style="width: 15px; margin-left: 10px;" xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M201.4 342.6c12.5 12.5 32.8 12.5 45.3 0l160-160c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L224 274.7 86.6 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160z"/></svg>
                                                            </div>
                                                            <div class="options d-none">
                                                                <?php
                                                                if($question_type === 'radio' && !empty($options) ){
                                                                    $counter = 1;
                                                                    foreach($options as $option){
                                                                        $rand = rand(0,100);
                                                                    ?>
                                                                    <Label for="<?php echo $field_name . '_' . $counter . $rand; ?>">
                                                                        <input type="radio" class="" name="<?php echo $field_name; ?>" id="<?php echo $field_name . '_' . $counter . $rand; ?>" value="<?php echo $option; ?>" <?php echo $required == 1 ? "required" : ""; ?> >
                                                                        <span><?php echo $option; ?></span>
                                                                    </Label>
                                                                    <?php
                                                                    $counter++;
                                                                    }
                                                                }else
                                                                if($question_type === 'checkbox' && !empty($options) ){
                                                                    $counter = 1;
                                                                    foreach($options as $option){
                                                                        $rand = rand(0,100);
                                                                    ?>
                                                                    <Label for="<?php echo $field_name . '_' . $counter . $rand; ?>">
                                                                        <input type="checkbox" class="" name="<?php echo $field_name; ?>[]" id="<?php echo $field_name . '_' . $counter . $rand; ?>" value="<?php echo $option; ?>">
                                                                        <span><?php echo $option; ?></span>
                                                                    </Label>
                                                                    <?php
                                                                    $counter++;
                                                                    }
                                                                }else
                                                                if($question_type === 'text' && !empty($options) ){
                                                                    $counter = 1;
                                                                    foreach($options as $option){
                                                                        $rand = rand(0,100);
                                                                    ?>
                                                                    <p>
                                                                        <input type="text" placeholder="<?php echo $option; ?>" class="" name="<?php echo $field_name; ?>[]" id="<?php echo $field_name . '_' . $counter . $rand; ?>">
                                                                        <span></span>
                                                                    </p>
                                                                    <?php
                                                                    $counter++;
                                                                    }
                                                                }
                                                                ?>
                                                            </div>
                                                        </div> 
                                                        <?php
                                                    }else{
                                                        if($question_type === 'radio' && !empty($options) ){
                                                            ?>
                                                            <input type="hidden" name="<?php echo $field_name; ?>" value="al_saved">
                                                            <?php
                                                        }else
                                                        if($question_type === 'checkbox' && !empty($options) ){
                                                            ?>
                                                            <input type="hidden" name="<?php echo $field_name; ?>[]" value="al_saved">
                                                            <?php
                                                        }else
                                                        if($question_type === 'text' && !empty($options) ){
                                                            ?>
                                                            <input type="hidden" name="<?php echo $field_name; ?>[]" value="al_saved">
                                                            <?php
                                                        }
                                                    }
                                                }
                                            }
                                        ?>
                                        <input type="hidden" name="form_id" value="<?php echo $formId; ?>" >

                                        <button  type="submit" class="free-quote next_btn"><b>Confirm</b></button>

                                        <div class="info">
                                            <?php 
                                            $consent_description = get_post_meta($formId, 'msf-consent-description', true);

                                            // 1. Extract shortcode using regex
                                            preg_match_all('/\[(consentPopup[^\]]*)\]/', $consent_description, $matches);
                                            
                                            // $matches[0] contains the full shortcodes
                                            // $matches[1] contains just the inner part of the shortcodes
                                            
                                            // 2. Loop through each found shortcode and replace it in the original string
                                            foreach ($matches[0] as $match) {
                                                $executed_shortcode = do_shortcode($match); // Execute the shortcode
                                                
                                                // 3. Replace the executed shortcode back into the original string
                                                $consent_description = str_replace($match, $executed_shortcode, $consent_description);
                                            }
                                            
                                            // Now $consent_description contains the original text with the executed shortcode
                                            echo $consent_description;
                                    
                                            ?>
                                        </div>
                                        <div class="overlay"></div>
                                        <div class="popup d-none"><?php echo get_post_meta($formId, 'msf-popup-description', true); ?></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
        <script>
            jQuery(document).ready(function () {
                jQuery('.main-radio-btn').on('click', function (e) {

                    jQuery('.card-down').removeClass('card-show');

                    if (jQuery(this).children('.radio-card').prop("checked")) {
                        jQuery(this).next('.card-down').removeClass('card-show');
                        jQuery(this).children('.radio-card').prop("checked", false);
                    } else {
                        jQuery(this).children('.radio-card').prop("checked", true);
                        jQuery(this).next('.card-down').addClass('card-show');
                    }
                });

                jQuery("form").submit(function (event) {
                    event.preventDefault();
                    // Collecting the whole form data
                    jQuery(this).find('.free-quote').addClass("line");
                    form_data = new FormData(this);
                    let existingFormData = localStorage.getItem('form_data_msf');
                    let form_data_array = Array.from(form_data.entries());
                    // console.log(JSON.parse(existingFormData));
                    // return false;
                    if (existingFormData) {
                        existingFormData = JSON.parse(existingFormData);
                        form_data_array.forEach(element => {
                            if(element[1] == 'al_saved'){
                                if (existingFormData.hasOwnProperty(element[0])) {
                                    form_data.append(element[0], existingFormData[element[0]]);
                                }
                            }
                        });
                        form_data.append('address_line_1_msf', existingFormData['address_line_1_msf']);
                        form_data.append('city_msf', existingFormData['city_msf']);
                        form_data.append('email_msf', existingFormData['email_msf']);
                        form_data.append('firstname_msf', existingFormData['firstname_msf']);
                        form_data.append('lastname_msf', existingFormData['lastname_msf']);
                        form_data.append('phone_msf', existingFormData['phone_msf']);
                        form_data.append('state_msf', existingFormData['state_msf']);
                        form_data.append('zip_msf', existingFormData['zip_msf']);
                        localStorage.removeItem('form_data_msf');
                    }
                    form_data_array = Array.from(form_data.entries());
                    let data_entries = {};
                    form_data_array.forEach(element => {
                        data_entries[element[0]] = element[1];
                    });

                    localStorage.setItem('form_data_msf', JSON.stringify(data_entries));
                    // console.log(JSON.parse(existingFormData));
                    // return false;
                    form_data.append('action', 'leads_msf');
                    form_data.append('security', '<?php  echo wp_create_nonce( 'leads_msf_signature' ); ?>');
                   // console.log(form_data);
                    // Transfering data through AJAX
                    jQuery.ajax({
                        url: '<?php  echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        contentType: false,
                        processData: false,
                        data: form_data,
                        success: function (response) {
                            if(response.success){
                                let postID = response.data.id;
                                let formID = response.data.form_id;
                                jQuery('.free-quote.line').removeClass("line");
                                event.currentTarget.innerHTML = `<h4 class="formSuccess">Form Sent Successfuly</h4>`;
                                let slug = response.data.slug;
                                console.log(event);
                               // window.location.href = '/'+slug+'?postID=' + postID + '&formID=' + formID;
                                return;
                            }else{
                                return;
                            }
                        }
                    });
    
                    return false;
                });
            
            });

            function myFunction(event) {
                event.target.lastElementChild.classList.toggle("show");
            }

            jQuery(document).ready(function($) {
                $('.target_popup').on('click', function(event) {
                    event.preventDefault();
                    
                    var popup = $(this).parent().parent().find('.popup');
                    popup.toggleClass('d-none');
                    var overlay = $(this).parent().parent().find('.overlay');
                    // Show or hide overlay based on popup visibility
                    if(popup.is(':visible')) {
                        overlay.show();
                    } else {
                        overlay.hide();
                    }
                });

                // Optionally: Close the popup when clicking outside
                $('.overlay').on('click', function() {
                    var popup = $(this).parent().parent().find('.popup');
                    popup.toggleClass('d-none');
                    $(this).hide();
                });

                $('.question').on('click', function() {
                    var drop_content = $(this).parent().find('.options');
                    drop_content.toggleClass('d-none');
                   $(this).parent().find('svg').toggleClass('rotate');

                });

                // $('.target_popup').on('click', function() {
                //     $(this).parent().parent().find('.popup').removeClass('d-none');
                // });
            });


        </script>
    </body>
</html>