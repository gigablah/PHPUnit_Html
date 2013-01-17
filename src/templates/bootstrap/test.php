<div class="test <?php echo "test-{$testno}".($testno % 1 ? ' odd' : ' even').($testno == ($numtests - 1) ? ' last ' : ' ').$test['status'].($test['status'] !== 'passed' ? ' closed' : ' closed'); ?>">
  <div class="icon"></div>
  <div class="name"><?php echo htmlentities($test['name']); ?></div>
  <div class="expand-button"></div>
  <div class="desc"><span class="badge badge-assertions" title="Assertions"><?php echo (int)$test['assertions'] ?></span><span class="badge<?php if (count($test['deprecated']) + count($test['errors'])) echo ' badge-problems'; ?>" title="Errors"><?php echo count($test['deprecated']) + count($test['errors']); ?></span><span class="badge badge-time" title="Time Elapsed"><?php printf('%06f', $test['time']); ?>s</span></div>
  <div class="more">
    <hr>
    <?php if (isset($test['result']['e'])) { ?>
    <div class="result"><pre>
    <?php echo htmlentities(PHPUnit_Framework_TestFailure::exceptionToString($test['result']['e']).PHPUnit_Util_Filter::getFilteredStacktrace($test['result']['e'])); ?>
    </pre></div>
    <?php } ?>
    <?php if ($test['errors'] !== null) { foreach($test['errors'] as $error) { $e = $error['e']; include('exception.php'); } } ?>
    <?php if ($test['deprecated'] !== null) { foreach($test['deprecated'] as $deprecated) { include('deprecated.php'); } } ?>
    <?php if ($test['output'] !== null && $test['output'] !== '') { ?>
    <div class="output rounded show">
      <pre><?php echo htmlentities($test['output']); ?></pre>
    </div>
    <?php } ?>
    <div class="source closed">
      <div class="toggle-button"></div>
      <div class="listing rounded show"><?php echo $this->listing($suite, $test); ?></div>
      <div class="clear"></div>
    </div>
  </div>
  <div style="clear:both"></div>
</div>
