<h2>
    <a href="/logs/<?=$date_data['prev']?>" title="前月" class="circle_button"><前月</a>
    <?=$date_data['target']?>
    <a href="/logs/<?=$date_data['next']?>" title="翌月" class="circle_button">翌月></a>
</h2>
<form action="/logs/delete" method="post" class="delete_form">
    <input type="hidden" name="target" value="<?=$date_data['target']?>">
    <table>
        <tr>
            <th><input type="submit" value="削除"></th>
            <th>日付</th>
            <th>カテゴリ</th>
            <th>ジャンル</th>
            <th>金額</th>
            <th>出金</th>
            <th>入金</th>
            <th>お店</th>
        </tr>
        <?php foreach ($log['money'] as $l) : ?>
            <tr>
                <td class="checkbox_box">
                    <input type="checkbox" name="mode__id[]" value="<?=$l['mode'].'__'.$l['id']?>" id="checkbox_<?=$l['id']?>">
                    <label for="checkbox_<?=$l['id']?>"></label>
                </td>
                <td><?=$l['date']?></td>
                <td><?=$category_list[$l['category_id']]?></td>
                <td><?=$l['genre_id'] ? $genre_list[$l['genre_id']] : ''?></td>
                <td><?=$l['amount']?> 円</td>
                <td><?php if($l['mode']!=='income') echo '出金'; ?></td>
                <td><?php if($l['mode']!=='payment') echo '入金'; ?></td>
                <td><?=$l['place']?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</form>

<form action="/logs/create" method="post" id="create_form">
    <label>
        <span>日付</span>
        <input type="date" name="date" required="required">
    </label>
    <label>
        <span>金額</span>
        <input type="number" name="amount" required="required">&nbsp;円
    </label>
    <label class="label_genre">
        <span>カテゴリ＆ジャンル</span>
        <select name="category__genre" required="required">
            <option selected> - 選択してください - </option>
            <?=$select_genre?>
        </select>
    </label>
    <input type="submit" value="支出登録">
</form>