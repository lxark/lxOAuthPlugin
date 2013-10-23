<div>
    <h1><?php echo __('Sign in');?></h1>
    <div class="clear"></div>

    <?php include_partial('loginForm', array(
        'formAction' => 'oauth/authenticate'
    )) ?>
</div>