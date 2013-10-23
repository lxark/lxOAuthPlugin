<div>
    <p><?php echo __('authorizations_rights'); ?></p>
    <form method="POST" action="<?php echo $sf_request->getUri() ?>">
        <input type="submit" value="<?php echo __('i_accept'); ?>" />
    </form>
    <form method="POST" action="<?php echo $sf_request->getUri() ?>">
        <input type="submit" value="<?php echo __('i_refuse'); ?>" />
    </form>
</div>