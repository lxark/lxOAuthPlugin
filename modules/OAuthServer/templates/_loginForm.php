<?php echo form_tag($formAction); ?>
    <?php if ($sf_request->hasError('signIn')): ?>
        <span class='error'><?php echo __($sf_request->getError('signIn')) ?></span>
    <?php endif ?>

    <div class="form-row">
        <?php if ($sf_request->hasError('login')): ?>
            <span class='error'><?php echo __($sf_request->getError('login')) ?></span>
        <?php endif ?>
        <?php echo input_tag('login', $sf_params->get('login'), array('placeholder' => __('Login'))) ?>
    </div>

    <div class="form-row">
        <?php if ($sf_request->hasError('password')): ?>
            <span class='error'><?php echo __($sf_request->getError('password')) ?></span>
        <?php endif ?>
        <?php echo input_password_tag('password', '', array('placeholder' => __('Password'))) ?>
    </div>

    <div class="form-row">
        <?php echo submit_tag(__('Validate')); ?>
    </div>
</form>
