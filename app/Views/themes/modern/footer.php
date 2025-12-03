	</div><!-- cotent-wrapper -->
	</div><!-- cotent -->
	</div><!-- site-content -->
	<footer class="shadow">
		<div class="footer-copyright">
			<div class="wrapper">
				<?php 
					$footer = str_replace('{{YEAR}}', date('Y'), $setting_aplikasi['footer_app']);
					echo html_entity_decode($footer);
				?>
			</div>
		</div>
	</footer>
</body>
</html>