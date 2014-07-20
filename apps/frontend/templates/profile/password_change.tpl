
<section class="password_recovery">
    <div class="min_width">
        <h1>{mlt general/parrword_recovery_page_title}</h1>
        <form class="form  clearfix" method="post" action="" name="form">
            <div class="content">
                <fieldset class="control pass">
                    <label class="label">{mlt general/parrword_recovery_page_new_pass_label}</label>
                    <div class="input_holder"><input type="password" name="data[pass]" value="" /></div>
                    {if isset($errors.pass)}<div class="text error">{mlt general/parrword_recovery_page_new_pass_error}</div>{/if}
                </fieldset>
                <div class="clear"></div>
                <fieldset class="control re_pass">
                    <label class="label">{mlt general/parrword_recovery_page_re_pass_label}</label>
                    <div class="input_holder"><input type="password" name="data[re_pass]" value="" /></div>
                    {if isset($errors.re_pass)}
                        <div class="text error">{mlt general/parrword_recovery_page_re_pass_error}</div>
                    {elseif isset($errors.invalid_re_pass)}
                        <div class="text error">{mlt general/parrword_recovery_page_invalid_re_pass_error}</div>
                    {/if}
                </fieldset>
                <div class="clear"></div>

                <fieldset class="control form_footer">
                    <span class="btn btn_input">{mlt general/parrword_recovery_page_send}<i class="submit"><input type="submit"></i></span>
                </fieldset>
            </div>
        </form>
    </div>
</section>
