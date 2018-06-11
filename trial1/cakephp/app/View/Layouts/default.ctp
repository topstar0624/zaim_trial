<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @package       app.View.Layouts
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<title><?php echo $this->fetch('title'); ?></title>
	<?php
		echo $this->Html->meta('icon');

		echo $this->Html->css('cake.generic');
		echo $this->Html->css('cake.hoshisaki');
		echo $this->Html->css('https://use.fontawesome.com/releases/v5.0.13/css/all.css',
			array(
				'integrity' => 'sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp',
				'crossorigin' => 'anonymous',
			)
		);

		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
</head>
<body id="body_<?=$this->action?>">
	<div id="container">

		<header id="header">
			<h1><i class="fas fa-calculator"></i>&nbsp;<?php echo $this->Html->link('シンプル家計簿', '/'); ?></h1>
			
			<div id="header_button" class="actions">
				<?php if(isset($authorize_url)) { ?>
					<a href="<?=$authorize_url?>" title="ログイン">ログイン</a>
				<?php } else { ?>
					<a href="/logout" title="ログアウト">ログアウト</a>
				<?php } ?>
			</div>
		</header>
		
		<main id="content">
			<?php echo $this->Flash->render(); ?>
			<?php echo $this->fetch('content'); ?>
		</main>
		
		<footer id="footer">
			&copy;2018 ほしさきひとみ
		</footer>
	</div>
	<?php echo $this->element('sql_dump'); ?>
</body>
</html>
