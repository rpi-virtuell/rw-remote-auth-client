<?php
/**
 * Created by PhpStorm.
 * User: Joachim
 * Date: 27.08.2017
 * Time: 22:52
 */
?>
<div id="user-registration" class="user-registration">
	<nav class="user-registration-MyAccount-navigation">
		<ul>
			<li class="user-registration-MyAccount-navigation-link user-registration-MyAccount-navigation-link--dashboard is-active">
				<a href="http://singlesite.reliwerk.de/my-account/">Dashboard</a>
			</li>
		</ul>
	</nav>

	<div class="user-registration-MyAccount-content">
		<?php echo RW_Remote_Auth_Client_Helper::login_form_shortcode(); ?>
	</div>
</div>
<?php