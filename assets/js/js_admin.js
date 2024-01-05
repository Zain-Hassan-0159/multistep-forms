(function($) {
    var msf_admin = {
        init: function() {
            msf_admin.toggle_question_choices();
            msf_admin.add_choice_field();
            msf_admin.add_question();
            msf_admin.remove_block();
            msf_admin.delete_question();
            msf_admin.add_form_ls();
            msf_admin.add_hooks_url();
        },

        // Toggle Choices options
        toggle_question_choices:function(){
            $(document).on('change','#MSF_question_type',function(){
                var q_type = $(this).val();
                console.log(q_type);
                if(q_type=='radio' || q_type=='checkbox'){
                    $(document).find('.q_choices').slideDown('slow');
                }else{
                    $(document).find('.q_choices').slideUp('slow');
                }
            });
        },

        add_choice_field:function(){
            $(document).on('click','.add_q_choice',function(){
                var q_choice = `<div class="input-group">
                    <input type="text" name="options[]" class="q_choices_input"  >
                    <button type="button" class="remove"><span class="dashicons dashicons-remove"></span></button>
                </div>  `;
                $(this).prev().append(q_choice);
            });
        },

        add_question:function(){
            $(document).on('click','.add_question',function(){
                var length_of_q = $('.questions .input-group').length;
                var questions = $('#all_q').html();
                var question = `
                <div class="input-group">
                    <select name="msf-form-qs[]" class="msf-form-q">`
                    + questions +
                    `</select>
                    
                    <span class="essential"><label>
                    <input type="hidden" class="essential essential_hidden" value="0" name="essential[${length_of_q}]">
                    <input type="checkbox" value="1" class="essential essential_checked" name="essential[${length_of_q}]" >
                    Essential</label></span>
                    <button type="button" class="remove"><span class="dashicons dashicons-remove"></span></button>
                </div>`;
                $('.questions').append(question);
            });
        },

        add_hooks_url:function(){
            $(document).on('click','.add_hooks_url',function(){
                if(document.querySelectorAll(".form-table").length === 2){
                    return;
                }
                window.form_state = "json";
                var question = `
                    <table style="display: inline-table; border-bottom: 1px solid saddlebrown;" class="input-group form-table">
                        <tr valign="top">
                            <th scope="row">URL</th>
                            <td><input type="text" data-json="json_msf_url_hook[]" data-form="form_msf_url_hook[]" name="json_msf_url_hook[]" value="" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Data Type</th>
                            <td>
                            <select class="form_format" onchange="update_form_format(event, value)" data-json="json_msf_dataType_hook[]" data-form="form_msf_dataType_hook[]" name="json_msf_dataType_hook[]">
                                <option value="json" >JSON</option>
                                <option value="form" >FORM</option>
                            </select>
                            </td>
                        </tr>
                        <tr valign="top">
                                <th scope="row">Hook Data</th>
                                <td>
                                <div class="inputFieldContainer">
                                    <div class="inputPair">
                                       ${repeater_form_action}
                                    </div>
                                </div>
                                <button type="button" class="addInput">+</button>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"></th>
                                <td><button type="button" class="remove"><span class="dashicons dashicons-remove"></span></button></td>
                            </tr>
                    </table>
                    `;
                $('.add_hooks_url_table').append(question);


                var addButtons = document.querySelectorAll('.addInput');
                addButtons.forEach(function(addButton) {
                    addButton.addEventListener('click', function() {
                        var container = addButton.previousElementSibling;
                        var div = document.createElement('div');
                        div.className = 'inputPair';
    
                        var removeBtn = document.createElement('button');
                        removeBtn.innerHTML = '-';
                        removeBtn.className = 'removeInput';
                        removeBtn.addEventListener('click', function() {
                            container.removeChild(div);
                        });
                        div.innerHTML = repeater_form_action;
                        div.appendChild(removeBtn);
    
                        container.appendChild(div);
                        jQuery('.hooks_select2').select2({
                            tags: true
                        }).on('change', function(e) {
                            // Do something when the value changes
                            var targetEl = e.target.parentElement.parentElement.parentElement.parentElement.parentElement;
                            window.form_state = targetEl.querySelector('.form_format').value;
                            update_form_type_names(targetEl);
                        })
                    });
                });
            });
        },

        add_form_ls:function(){
            $(document).on('click','.add_forms_ls',function(){
                var forms = $('#all_forms_ls').html();
                var form = `
                <div class="input-group">
                    <select name="all_forms_ls[]" class="all_forms_ls">`
                    + forms +
                    `</select>
                    
                    <button type="button" class="remove"><span class="dashicons dashicons-remove"></span></button>
                </div>`;
                $('.forms_ls_append').append(form);
            });
        },

        remove_block:function(){
            $(document).on('click','.remove',function(){
                $(this).parents('.input-group').remove();
                $('.questions .input-group .essential_checked').each(function(index) {
                    $(this).attr('name', 'essential[' + (index) + ']');
                });                  
                $('.questions .input-group .essential_hidden').each(function(index) {
                    $(this).attr('name', 'essential[' + (index) + ']');
                });                  
            });
        },

        delete_question: function(){
            jQuery('.delete-question-link').on('click', function(e) {
                e.preventDefault(); // prevent the link from executing immediately
                var questionId = jQuery(this).data('id');
                var nounceId = jQuery(this).data('nounce');
                if (confirm('Are you sure you want to delete this question?')) {
                  // If user clicks "OK", execute the deletion by redirecting to the delete URL
                  window.location.href = window.location.href + '&del_id=' + questionId + '&nounce_id=' + nounceId;
                } else {
                  // If user clicks "Cancel", do nothing
                  return false;
                }
            });
        }
    };
    msf_admin.init();


    // return prodIndexGridHandler;
})(jQuery);

