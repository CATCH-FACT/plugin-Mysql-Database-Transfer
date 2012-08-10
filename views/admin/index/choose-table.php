<?php 
    head(array('title' => 'MySQL Database Transfer', 'bodyclass' => 'primary', 
        'content_class' => 'horizontal-nav'));
?>
<h1>MySQL Database Transfer</h1>
<?php echo $this->navigation()->menu()->setUlClass('section-nav'); ?>

<div id="primary">
    <h2>Step 2: Select Table</h2>
    <?php echo flash(); ?>

    <?php
    echo $this->form;
    ?>
    
</div>
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function () {
    Omeka.DatabaseTransfer.enableElementMapping();
});
//]]>
</script>
<?php 
    foot(); 
?>
