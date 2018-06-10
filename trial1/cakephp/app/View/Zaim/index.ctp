<? if(isset($request_message)): ?>
    <h2>ようこそ</h2>
    <?=$request_message?>
<? endif; ?>

<? if(isset($data_list)): ?>
    <h2>あなたのZaimデータです</h2>
    <? $data_list = json_decode($data_list,true); ?>
    <dl>
        <? foreach ($data_list['me'] as $k => $v) : ?>
            <dt><?=$k?></dt>
            <dd><?=$v?></dd>
        <? endforeach; ?>
    </dl>
    <p><a href='?action=clear'>ログアウト</a></p>
<? endif; ?>

<? if(isset($error_message)): ?>
    <h2>エラーです</h2>
    <?=$error_message?>
<? endif; ?>