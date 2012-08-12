	</div>
	<?php if (!class_exists('PHP_Timer', false)) { require('PHP/Timer.php'); } ?>
    <footer class="footer">
		<div class="container">
        <div class="copyright">&copy; <?php echo date('Y'); ?> Nick Turner. Inspired by Matt Mueller. Modified by Chris Heng.<br><?php echo htmlentities(PHPUnit_Runner_Version::getVersionString());?> <?php echo htmlentities(PHP_Timer::resourceUsage());?></div>
		</div>
	</footer>
    
    <script src="<?php echo $this->url('template/javascript/jquery.js');?>"></script>
    <script src="<?php echo $this->url('template/javascript/main.js');?>"></script>
	<!--[if lt IE 9]>
	<script src="<?php echo $this->url('template/javascript/html5.js');?>"></script>
	<![endif]-->
  </body>
</html>
