<h2>あなたのZaimデータです</h2>

<div id="data_box">

    <div id="user_box">
        <img src="<?=$user_data['me']['profile_image_url']?>" alt="アイコン"><br>
        <?=$user_data['me']['name']?> さん
    

    </div><div id="form_box">
    

    </div><div id="log_box">
        <table>
            <tr>
                <th>削除</th>
                <th>日付</th>
                <th>カテゴリ</th>
                <th>金額</th>
                <th>出金</th>
                <th>入金</th>
                <th>お店</th>
                <th>品目</th>
                <th>メモ</th>
            </tr>
            <?php foreach ($money_data['money'] as $d) : ?>
                <tr>
                    <td><input type="checkbox" name="id" value"<?=$d['id']?>"></td>
                    <td><?=$d['date']?></td>
                    <td><?=$d['category_id']?></td>
                    <td><?=$d['amount']?></td>
                    <td><?=$d['mode']==='payment' ? '出金' : ''?></td>
                    <td><?=$d['mode']==='income' ? '入金' : ''?></td>
                    <td><?=$d['place']?></td>
                    <td><?=$d['name']?></td>
                    <td><?=$d['comment']?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

</div><!-- /#data_box -->
