  <section class="box rounded suite <?php echo "suite-{$suiteno}".($suiteno % 1 ? ' odd' : ' even').($suiteno == ($numsuites - 1) ? ' last ' : ' ').$suite['status'].($suite['status'] !== 'passed' ? ' open' : ' closed'); ?>">
    <div class="icon"></div>
    <div class="name">
        <h1><?php $tmp = explode('\\', $suite['name']); echo htmlentities(end($tmp)); ?></h1>
        <div class="stats"><?php foreach($suite['stats'] as $what => $count) { echo "<span class=\"badge" . ((int)$count ? ' badge-' . $what : '') . "\" title=\"" . htmlentities(ucwords($what)) . "\">".(int)$count.'</span>'; }?></div>
        <div class="desc"><span class="badge badge-assertions" title="Assertions"><?php echo (int)$suite['assertions'] ?></span><span class="badge<?php if ($suite['deprecated'] + $suite['errors']) echo ' badge-problems'; ?>" title="Errors"><?php echo $suite['deprecated'] + $suite['errors']; ?></span><span class="badge badge-time" title="Time Elapsed"><?php printf('%06f', $suite['time']); ?>s</span></div>
    </div>
    <div class="expand-button"></div>
    <div class="more tests">
      <?php
      $testno = 0;
      $numtests = count($suite['tests']);
      foreach($suite['tests'] as $testname => $test) {
        echo '<hr class="'.($testno == 0 ? 'big' : 'small').'">';
        include('test.php');
        $testno++;
      }
      ?>
    </div>
  </section>
