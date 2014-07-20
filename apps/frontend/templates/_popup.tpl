<table class="popup_table hide"><tr><td class="pt_td">


    <div class="popup clearfix feedback_popup">
        <div class="popup_header">{mlt general/feedback_title}</div>
        <div class="popup_content">
            <div class="form form_feedback">
                <fieldset class="control name">
                    <label class="label">{mlt general/feedback_name}</label>
                    <div class="input_holder">
                        <input value="" type="text" />
                    </div>
                </fieldset>
                <fieldset class="control email">
                    <label class="label">{mlt general/feedback_email}</label>
                    <div class="input_holder">
                        <input type="text" value="" />
                    </div>
                    <!--<div class="text error">Field can’t be empty</div>-->
                </fieldset>
                <fieldset class="control description">
                    <label class="label">{mlt general/feedback_text}</label>
                    <div class="input_holder">
                        <textarea ></textarea>
                    </div>
                    <!--<div class="text error">Field can’t be empty</div>-->
                </fieldset>
                <fieldset class="control form_footer">
                    <span class="btn btn_input" id="send-feedback">{mlt general/feedback_btn_send}</span>
                </fieldset>
            </div>
        </div>
        <i class="icon icon_close close"></i>
    </div>

    <div class="popup clearfix new-user-popup">
        <div class="popup_header">{mlt profile/success_registration_title}</div>

        <div class="popup_content">
            <div class="form form_feedback">
                <fieldset class="control">
                    <label class="label">{mlt profile/success_registration_description}</label>
                </fieldset>

            </div>
        </div>
        <i class="icon icon_close close"></i>
    </div>

    <div class="popup clearfix feedback_popup_success">
        <div class="popup_header">{mlt general/feedback_success_title}</div>

        <div class="popup_content">
            <div class="form form_feedback">
                <fieldset class="control">
                    <label class="label">{mlt general/feedback_success_description}</label>
                </fieldset>

            </div>
        </div>
        <i class="icon icon_close close"></i>
    </div>

    {if !slACL::isLoggedIn()}
        <div class="popup clearfix password_recovery_popup">
            <div class="popup_header">{mlt general/parrword_recovery_popup_title}</div>

            <div class="popup_content form ">
                <fieldset class="control email">
                    <label class="label">{mlt general/parrword_recovery_popup_label}</label>
                    <div class="input_holder">
                        <input type="text" value="">
                    </div>
                    <div class="text error none">{mlt general/parrword_recovery_popup_error}</div>
                </fieldset>
                <fieldset class="control form_footer">
                    <span class="btn btn_input" id="password-recovery">{mlt general/parrword_recovery_popup_send}</span>
                </fieldset>
            </div>
            <i class="icon icon_close close"></i>
        </div>

        <div class="popup clearfix password_recovery_success">
            <div class="popup_header">{mlt general/password_recovery_success_title}</div>
            <div class="popup_content">
                <div class="form form_feedback">
                    <fieldset class="control">
                        <label class="label">{mlt general/password_recovery_success_description}</label>
                    </fieldset>

                </div>
            </div>
            <i class="icon icon_close close"></i>
        </div>

    {/if}
    <div class="back none" id="back"></div>
</td></tr></table>
