<div>
    <h1><?php echo __('Authorize');?></h1>
    <div class="clear"></div>

    <p><?php echo __('authorizations_rights') ?></p>

    <?php include_partial('loginForm', array(
        'formAction' => 'oauth/authorize'
    )) ?>
</div>