<?php
$stats = $context->assessment->getStats();
?>

We have completed a site check on  <?php echo $context->assessment->baseUri; ?>

<ul>
    <li><?php echo $stats['total_pages'] ?> Pages</li>
    <li><?php echo $stats['total_html_errors'] ?> HTML Errors</li>
    <li><?php echo round(($stats['total_current_template_html']/$stats['total_pages'])*100) ?>% in current HTML (v<?php echo  $stats['current_template_html'] ?>)</li>
    <li><?php echo round(($stats['total_current_template_dep']/$stats['total_pages']*100)) ?>% in current Dependents (v<?php echo  $stats['current_template_dep'] ?>)</li>
    <li><?php echo $stats['total_bad_links'] ?> Bad Links</li>
</ul>

<a href='http://validator.unl.edu/site/?uri=<?php echo urlencode($context->assessment->baseUri)?>'>View the complete results</a>