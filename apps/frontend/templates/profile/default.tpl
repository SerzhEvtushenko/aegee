<link rel="stylesheet" href="css/uniform.default.css" type="text/css" media="screen, projection"/>
<link rel="stylesheet" href="css/jquery.gritter.css" />
<script src="js/libs/jquery.gritter.min.js"></script>

<script type="text/javascript" src="js/libs/jquery.form.js"></script>
<script type="text/javascript" src="js/libs/jquery.iframe-transport.js"></script>

<section class="registration">
    <div class="min_width">
        <h1>{mlt profile/profile}</h1>
        <div class="form form_registration clearfix">
            <form action="{url_to profile_save_avatar}" id="profile-avatar-form" method="post" enctype="multipart/form-data" >
                <fieldset class="photo_control">
                    <div class="img">
                        <img data-role="avatar" src="{if isset($user.avatar.sizes.small.link)}{$user.avatar.sizes.small.link}{else}images/default_avatar.png{/if}" alt=""/>
                        <span class="btn btn_input">{mlt profile/load_photo}<i class="submit">
                                <input name="avatar" class="user_avatar" type="file"/>
                            </i></span>
                        <i class="loader none"></i>
                    </div>
                </fieldset>
            </form>

            <div class="content">
                <!--  line  -->
                <fieldset class="control first_name">
                    <label class="label">{mlt profile/first_name}</label>
                    <div class="input_holder"><input type="text" data-field="first_name" value="{$user.first_name}" /></div>
                    <div class="text error none">{mlt profile/error_text}</div>
                </fieldset>
                <fieldset class="control last_name">
                    <label class="label">{mlt profile/last_name}</label>
                    <div class="input_holder"><input type="text" data-field="last_name" value="{$user.last_name}" /></div>
                    <div class="text error none">{mlt profile/error_text}</div>
                </fieldset>
                <div class="clear"></div>
                <fieldset class="control email">
                    <label class="label">{mlt profile/email}</label>
                    <div class="input_holder"><input type="text"  data-field="email" value="{$user.email}" /></div>
                    <div class="text error none">{mlt profile/error_text}</div>
                </fieldset>
                <fieldset class="control phone">
                    <label class="label">{mlt profile/phone}</label>
                    <div class="input_holder"><input type="text" class="phone_mask" data-field="phone" value="{$user.phone}" /></div>
                    <div class="text error none">{mlt profile/error_text}</div>
                </fieldset>

                <div class="clear"></div>
                <fieldset class="control ">
                    <label class="label">{mlt profile/address}</label>
                    <div class="input_holder"><textarea data-field="post_address">{$user.post_address}</textarea></div>
                </fieldset>
                <!--  line  -->
                <fieldset class="control">
                    <label class="label">{mlt profile/sex}</label>
                    <div class="input_holder">
                        <label class="checkbox act"><input type="radio" {if 'female' == $user.sex}checked="checked"{/if} value="female" name="sex">{mlt profile/female}</label>
                        <label class="checkbox"><input type="radio" {if 'male' == $user.sex}checked="checked"{/if} value="male" name="sex">{mlt profile/male}</label>
                        <script>$('input[type=radio]').uniform()</script>
                    </div>
                </fieldset>
                <div class="clear"></div>


                <fieldset class="control birthday">
                    <label class="label">{mlt profile/birthday}</label>
                    <div class="input_holder input_icon">
                        <input type="text" value="{date $user.birthday 'd m Y'}" data-field="birthday"  class="birthday"/><i class="icon icon_date"></i>
                    </div>
                    <div class="text error none">{mlt profile/error_text}</div>
                </fieldset>
                <fieldset class="control">
                    <label class="label">{mlt profile/aegee_card}</label>
                    <div class="input_holder"><input type="text" data-field="aegee_card" value="{$user.aegee_card}" /></div>
                </fieldset>
                <div class="clear"></div>

                <fieldset class="control university">
                    <label class="label">{mlt profile/university}</label>
                    <div class="input_holder"><input type="text" data-field="university" value="{$user.university}" /></div>
                </fieldset>
                <fieldset class="control speciality">
                    <label class="label">{mlt profile/speciality}</label>
                    <div class="input_holder"><input type="text" data-field="speciality" value="{$user.speciality}" /></div>
                </fieldset>
                <div class="clear"></div>

                <fieldset class="control big">
                    <label class="label">{mlt profile/work_place}</label>
                    <div class="input_holder"><input type="text" data-field="work_place" value="{$user.work_place}" /></div>
                </fieldset>
                <fieldset class="control big">
                    <label class="label">{mlt profile/work_position}</label>
                    <div class="input_holder"><input type="text" data-field="work_position" value="{$user.work_position}" /></div>
                </fieldset>
                <div class="clear"></div>

                <!--  line  -->
                <fieldset class="control big">
                    <label class="label">{mlt profile/interests}</label>
                    <div class="input_holder"><textarea data-field="interests">{$user.interests}</textarea></div>
                </fieldset>
                <fieldset class="control big">
                    <label class="label">{mlt profile/description}</label>
                    <div class="input_holder"><textarea data-field="describe_yourself">{$user.describe_yourself}</textarea></div>
                </fieldset>
                <div class="clear"></div>

                <fieldset class="control big">
                    <label class="label">{mlt profile/expirience}</label>
                    <div class="input_holder"><textarea data-field="other_experience">{$user.other_experience}</textarea></div>
                </fieldset>
                <fieldset class="control big">
                    <label class="label">{mlt profile/more}</label>
                    <div class="input_holder"><textarea data-field="know_more">{$user.know_more}</textarea></div>
                </fieldset>
                <div class="clear"></div>

                <fieldset class="control big">
                    <label class="label">{mlt profile/why_join}</label>
                    <div class="input_holder"><textarea data-field="why_join">{$user.why_join}</textarea></div>
                </fieldset>
                <fieldset class="control big">
                    <label class="label">{mlt profile/how_learned}</label>
                    <div class="input_holder"><textarea data-field="how_learned">{$user.how_learned}</textarea></div>
                </fieldset>
                <div class="clear"></div>

                <fieldset class="control big">
                    <label class="label">{mlt profile/like_to_visit}</label>
                    <div class="input_holder"><textarea data-field="like_to_visit">{$user.like_to_visit}</textarea></div>
                </fieldset>
                <fieldset class="control big">
                    <label class="label">{mlt profile/extent_active}</label>
                    <div class="input_holder"><textarea data-field="extent_active">{$user.extent_active}</textarea></div>
                </fieldset>
                <div class="clear"></div>

                <fieldset class="control old_pass">
                    <span  class="label changePassword" >{mlt profile/change_pass}</span>
                </fieldset>
                <div class="clear"></div>
                <div class="password-container">
                    <fieldset class="control old_pass">
                        <label class="label">{mlt profile/old_pass}</label>
                        <div class="input_holder"><input type="password" data-field="old_pass" value="" /></div>
                        <div class="text error none">{mlt profile/error_text}</div>
                    </fieldset>
                    <div class="clear"></div>
                    <fieldset class="control pass">
                        <label class="label">{mlt profile/new_pass}</label>
                        <div class="input_holder"><input type="password" data-field="pass" value="" /></div>
                        <div class="text error none">{mlt profile/error_text}</div>
                    </fieldset>
                    <fieldset class="control re_pass">
                        <label class="label">{mlt profile/re_pass}</label>
                        <div class="input_holder"><input type="password" data-field="re_pass" value="" /></div>
                        <div class="text error none">{mlt profile/error_text}</div>
                    </fieldset>
                </div>

                <div class="clear"></div>

                <fieldset class="control form_footer">
                    <span class="btn btn_input" id="update-user">{mlt profile/save}</span>
                </fieldset>
            </div>
        </div>
    </div>

    {if isset($newUser)}
        <input type="hidden" id="newUserPopup">
    {/if}
</section>
