<?php if(isset($request_message)): ?>
    <h2>Zaimアカウントでログインしますか？</h2>
    <?=$request_message?>
<?php endif; ?>

<?php if(isset($data_list)): ?>
    <h2>あなたのZaimデータです</h2>
    <?php $data_list = json_decode($data_list,true); ?>
    <dl>
        <?php foreach ($data_list['me'] as $k => $v) : ?>
            <dt><?=$k?></dt>
            <dd><?=$v?></dd>
        <?php endforeach; ?>
    </dl>
    <p><a href='?action=clear'>ログアウト</a></p>
<?php endif; ?>

<?php if(isset($error_message)): ?>
    <h2>エラーメッセージ</h2>
    <?=$error_message?>
<?php endif; ?>