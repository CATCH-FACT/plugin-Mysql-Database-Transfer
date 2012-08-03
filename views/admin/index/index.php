<?php
$head = array('bodyclass' => 'database-transfer primary', 
              'title' => 'Database Transfer',
			'content_class' => 'horizontal-nav');
head($head);
?>
<h1><?php echo $head['title']; ?></h1>
<?php echo $this->navigation()->menu()->setUlClass('section-nav'); ?>

<div id="primary">
    <h2>Step 1: Insert database Settings</h2>
    <?php echo flash(); ?>
    <?php echo $this->form; ?>
</div>
<?php foot(); ?>