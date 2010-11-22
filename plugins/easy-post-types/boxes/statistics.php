<?php
$stats = $this->dbclass->getStatsPerContent($content['systemkey']);
?>
<p>
    <?php echo $content['label']; ?> published: <span class="status-display"><?php echo empty($stats['publish']->n)?0:$stats['publish']->n; ?></span><br/>
    <?php echo $content['label']; ?> in draft: <span class="status-display"><?php echo empty($stats['draft']->n)?0:$stats['draft']->n; ?></span><br/>
    <?php echo $content['label']; ?> in trash: <span class="status-display"><?php echo empty($stats['trash']->n)?0:$stats['trash']->n; ?></span>
</p>
